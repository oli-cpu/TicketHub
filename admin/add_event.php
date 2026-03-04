<?php
session_start();
include '../connectpdo.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $artistId = $_POST['artist_id'];
    $raumId = $_POST['raum_id'];
    $name = $_POST['event_name'];
    $datum = $_POST['datum'];
    $preis = $_POST['preis'];

    $stmt = $pdo->prepare("INSERT INTO tblevent (fkRaumplan, fkArtist, EventName, EventDatum, BasisPreis) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$raumId, $artistId, $name, $datum, $preis]);
    $newEventId = $pdo->lastInsertId();

    $stmtRaum = $pdo->prepare("SELECT Kapazitaet FROM tblraumplan WHERE pkRaumplanID = ?");
    $stmtRaum->execute([$raumId]);
    $kapazitaet = $stmtRaum->fetchColumn();

    if ($kapazitaet) {
        for ($i = 1; $i <= $kapazitaet; $i++) {
            $reihe = ceil($i / 10);
            $stmtSeat = $pdo->prepare("INSERT INTO tblseat (fkEvent, Reihe, SeatNumber, Status) VALUES (?, ?, ?, 'frei')");
            $stmtSeat->execute([$newEventId, $reihe, $i]);
        }
        echo "Event erstellt und $kapazitaet Sitzplätze generiert!";
    }
}
?>