<?php
session_start();
include 'connectpdo.php';

// Zugriffsschutz: Nur Admins dürfen hierhin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $artistId = $_POST['artist_id'];
    $raumId = $_POST['raum_id'];
    $name = $_POST['event_name'];
    $datum = $_POST['datum'];
    $preis = $_POST['preis'];

    // 1. Event in tblevent speichern
    $stmt = $pdo->prepare("INSERT INTO tblevent (fkRaumplan, fkArtist, EventName, EventDatum, BasisPreis) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$raumId, $artistId, $name, $datum, $preis]);
    $newEventId = $pdo->lastInsertId();

    // 2. Raumkapazität abrufen
    $stmtRaum = $pdo->prepare("SELECT Kapazitaet FROM tblraumplan WHERE pkRaumplanID = ?");
    $stmtRaum->execute([$raumId]);
    $kapazitaet = $stmtRaum->fetchColumn();

    // 3. Automatische Sitzplatz-Generierung (Businesslogik)
    for ($i = 1; $i <= $kapazitaet; $i++) {
        $reihe = ceil($i / 10); // Beispiel: 10 Plätze pro Reihe
        $stmtSeat = $pdo->prepare("INSERT INTO tblseat (fkEvent, Reihe, SeatNumber, Status) VALUES (?, ?, ?, 'frei')");
        $stmtSeat->execute([$newEventId, $reihe, $i]);
    }

    echo "Event erstellt und $kapazitaet Sitzplätze generiert!";
}
?>

<form method="post">
    Event Name: <input type="text" name="event_name" required><br>
    Datum: <input type="datetime-local" name="datum" required><br>
    Preis: <input type="number" step="0.01" name="preis" required><br>
    Artist ID: <input type="number" name="artist_id" required><br>
    Raumplan ID: <input type="number" name="raum_id" required><br>
    <button type="submit">Event & Sitze erstellen</button>
</form>
