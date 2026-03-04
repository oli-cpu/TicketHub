<?php
session_start();
// Pfad zur zentralen Konfiguration (eine Ebene höher)
include '../connectpdo.php';

// Zugriffsschutz: Nur Admins dürfen hierhin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $artistId = $_POST['artist_id'];
    $raumId   = $_POST['raum_id'];
    $name     = $_POST['event_name'];
    $datum    = $_POST['datum'];
    $preis    = $_POST['preis'];

    try {
        // 1. Event in tblevent speichern (Spaltennamen an ER-Modell angepasst)
        $stmt = $pdo->prepare("INSERT INTO tblevent (fkRaumplan, fkArtist, fldEventName, fldEventDatum, fldBasisPreis) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$raumId, $artistId, $name, $datum, $preis]);
        $newEventId = $pdo->lastInsertId();

        // 2. Raumkapazität abrufen
        $stmtRaum = $pdo->prepare("SELECT fldKapazitaet FROM tblraumplan WHERE pkRaumplanID = ?");
        $stmtRaum->execute([$raumId]);
        $kapazitaet = $stmtRaum->fetchColumn();

        // 3. Automatische Sitzplatz-Generierung
        if ($kapazitaet) {
            for ($i = 1; $i <= $kapazitaet; $i++) {
                $reihe = ceil($i / 10); // Beispiel: 10 Plätze pro Reihe
                // Spaltennamen fldReihe, fldSeatNumber, fldStatus laut Modell
                $stmtSeat = $pdo->prepare("INSERT INTO tblseat (fkEvent, fldReihe, fldSeatNumber, fldStatus) VALUES (?, ?, ?, 'frei')");
                $stmtSeat->execute([$newEventId, $reihe, $i]);
            }
            echo "Event erstellt und $kapazitaet Sitzplätze generiert!";
        } else {
            echo "Event erstellt, aber keine Kapazität im Raumplan gefunden.";
        }
    } catch (PDOException $e) {
        echo "Fehler beim Erstellen des Events: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neues Event erstellen</title>
</head>
<body>
    <h2>Neues Event anlegen</h2>
    <form method="post">
        <label>Event Name:</label><br>
        <input type="text" name="event_name" required><br><br>
        
        <label>Datum und Uhrzeit:</label><br>
        <input type="datetime-local" name="datum" required><br><br>
        
        <label>Basispreis (CHF):</label><br>
        <input type="number" step="0.01" name="preis" required><br><br>
        
        <label>Artist ID:</label><br>
        <input type="number" name="artist_id" required><br><br>
        
        <label>Raumplan ID:</label><br>
        <input type="number" name="raum_id" required><br><br>
        
        <button type="submit">Event & Sitze erstellen</button>
    </form>
    <br>
    <a href="admin_dashboard.php">Zurück zum Dashboard</a>
</body>
</html>