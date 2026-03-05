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

        $stmt = $pdo->prepare("INSERT INTO tblevent (fkRaumplan, fkArtist, fldEventName, fldEventDatum, fldBasisPreis) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$raumId, $artistId, $name, $datum, $preis]);
        $newEventId = $pdo->lastInsertId();

        $stmtRaum = $pdo->prepare("SELECT fldKapazitaet FROM tblraumplan WHERE pkRaumplanID = ?");
        $stmtRaum->execute([$raumId]);
        $kapazitaet = $stmtRaum->fetchColumn();

        if ($kapazitaet) {
            $stmtSeat = $pdo->prepare("INSERT INTO tblseat (fkEvent, fldReihe, fldSeatNumber, fldStatus) VALUES (?, ?, ?, 'frei')");
            for ($i = 1; $i <= $kapazitaet; $i++) {
                $reihe = ceil($i / 10); 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event erstellen - Admin Panel</title>
    <style>
        /* Globaler Dark Look */
        body { 
            font-family: Arial, sans-serif; 
            background-color: #000000; 
            color: #ffffff; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            padding: 20px;
        }

        .container { 
            width: 100%;
            max-width: 500px; 
            background: #121212; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.6); 
            border: 1px solid #222;
        }

        /* Branding */
        .brand {
            text-align: center;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .brand span.highlight {
            background: #ff9900;
            color: #000;
            padding: 2px 8px;
            border-radius: 4px;
        }

        h2 { 
            font-size: 1.1rem; 
            text-align: center; 
            color: #888; 
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Formular Styling */
        .form-group { margin-bottom: 20px; }
        
        label { 
            display: block; 
            font-size: 0.8rem; 
            color: #666; 
            margin-bottom: 8px; 
            font-weight: bold;
            text-transform: uppercase;
        }

        input, select { 
            width: 100%; 
            padding: 12px; 
            background: #282828; 
            border: 1px solid #333; 
            border-radius: 4px; 
            color: #fff; 
            font-size: 0.95rem; 
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #ff9900;
        }

        /* Dropdown Pfeil Anpassung */
        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23ff9900' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }

        button { 
            width: 100%; 
            padding: 15px; 
            background-color: #ff9900; 
            color: #000; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 1rem; 
            font-weight: bold;
            margin-top: 10px;
            transition: background 0.2s, transform 0.1s;
        }

        button:hover { background-color: #e68a00; }
        button:active { transform: scale(0.98); }

        .back-link { 
            display: block; 
            text-align: center; 
            margin-top: 25px; 
            color: #555; 
            text-decoration: none; 
            font-size: 0.9rem;
        }
        .back-link:hover { color: #ff9900; }

        /* Feedback-Boxen */
        .msg { padding: 15px; border-radius: 4px; margin-bottom: 25px; text-align: center; font-size: 0.9rem; font-weight: bold; border: 1px solid; }
        .success { background: rgba(255, 153, 0, 0.1); color: #ff9900; border-color: #ff9900; }
        .error { background: rgba(255, 68, 68, 0.1); color: #ff4444; border-color: #ff4444; }
    </style>
</head>
<body>

    <div class="container">
        <div class="brand">
            Ticket<span class="highlight">Hub</span>
        </div>

        <h2>Neues Event anlegen</h2>

        <?php if(isset($success)) echo "<div class='msg success'>$success</div>"; ?>
        <?php if(isset($error)) echo "<div class='msg error'>$error</div>"; ?>

        <form method="post">
            <div class="form-group">
                <label>Event Name</label>
                <input type="text" name="event_name" placeholder="z.B. Rock Night 2026" required>
            </div>

            <div class="form-group">
                <label>Datum & Uhrzeit</label>
                <input type="datetime-local" name="datum" required>
            </div>

            <div class="form-group">
                <label>Basispreis (CHF)</label>
                <input type="number" step="0.01" name="preis" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label>Artist</label>
                <select name="artist_id" required>
                    <option value="">-- Artist wählen --</option>
                    <?php foreach ($artists as $a): ?>
                        <option value="<?= $a['pkArtist'] ?>"><?= htmlspecialchars($a['fldArtistName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Raum / Location</label>
                <select name="raum_id" required>
                    <option value="">-- Raum wählen --</option>
                    <?php foreach ($raeume as $r): ?>
                        <option value="<?= $r['pkRaumplanID'] ?>"><?= htmlspecialchars($r['fldRaumName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Event & Sitzplätze erstellen</button>
        </form>

        <a href="admin_dashboard.php" class="back-link">Zurück zum Admin-Dashboard</a>
    </div>

</body>
</html>