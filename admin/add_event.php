<?php
session_start();
require_once '../connectpdo.php';

// Zugriffsschutz: Nur Admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert.");
}

// 1. Alle Artists für das Dropdown laden
$artists = $pdo->query("SELECT pkArtist, fldArtistName FROM tblartist ORDER BY fldArtistName ASC")->fetchAll(PDO::FETCH_ASSOC);

// 2. Alle Räume für das Dropdown laden
$raeume = $pdo->query("SELECT pkRaumplanID, fldRaumName FROM tblraumplan ORDER BY fldRaumName ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $artistId = $_POST['artist_id'];
    $raumId   = $_POST['raum_id'];
    $name     = $_POST['event_name'];
    $datum    = $_POST['datum'];
    $preis    = $_POST['preis'];

    try {
        $pdo->beginTransaction();

        // 3. Event in tblevent speichern
        $stmt = $pdo->prepare("INSERT INTO tblevent (fkRaumplan, fkArtist, fldEventName, fldEventDatum, fldBasisPreis) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$raumId, $artistId, $name, $datum, $preis]);
        $newEventId = $pdo->lastInsertId();

        // 4. Raumkapazität abrufen
        $stmtRaum = $pdo->prepare("SELECT fldKapazitaet FROM tblraumplan WHERE pkRaumplanID = ?");
        $stmtRaum->execute([$raumId]);
        $kapazitaet = $stmtRaum->fetchColumn();

        // 5. Automatische Sitzplatz-Generierung
        if ($kapazitaet) {
            $stmtSeat = $pdo->prepare("INSERT INTO tblseat (fkEvent, fldReihe, fldSeatNumber, fldStatus) VALUES (?, ?, ?, 'frei')");
            for ($i = 1; $i <= $kapazitaet; $i++) {
                $reihe = ceil($i / 10); // 10 Plätze pro Reihe
                $stmtSeat->execute([$newEventId, $reihe, $i]);
            }
        }

        $pdo->commit();
        $success = "Event '$name' wurde mit $kapazitaet Sitzplätzen erstellt!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Fehler: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Event erstellen - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 500px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #28a745; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .msg { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h2>Neues Event anlegen</h2>

    <?php if(isset($success)) echo "<div class='msg success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='msg error'>$error</div>"; ?>

    <form method="post">
        <div class="form-group">
            <label>Event Name:</label>
            <input type="text" name="event_name" required>
        </div>

        <div class="form-group">
            <label>Datum & Uhrzeit:</label>
            <input type="datetime-local" name="datum" required>
        </div>

        <div class="form-group">
            <label>Basispreis (CHF):</label>
            <input type="number" step="0.01" name="preis" required>
        </div>

        <div class="form-group">
            <label>Artist auswählen:</label>
            <select name="artist_id" required>
                <option value="">-- Bitte wählen --</option>
                <?php foreach ($artists as $a): ?>
                    <option value="<?= $a['pkArtist'] ?>"><?= htmlspecialchars($a['fldArtistName']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Raum auswählen:</label>
            <select name="raum_id" required>
                <option value="">-- Bitte wählen --</option>
                <?php foreach ($raeume as $r): ?>
                    <option value="<?= $r['pkRaumplanID'] ?>"><?= htmlspecialchars($r['fldRaumName']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">Event & Sitzplätze erstellen</button>
    </form>
    <br>
    <a href="admin_dashboard.php">Zurück zum Dashboard</a>
</body>
</html>