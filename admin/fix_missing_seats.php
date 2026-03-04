<?php
session_start();
require_once '../connectpdo.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Kein Admin.");

// Suche Events, die noch keine Einträge in tblseat haben
$sql = "SELECT e.pkEvent, e.fkRaumplan, r.fldKapazitaet 
        FROM tblevent e
        JOIN tblraumplan r ON e.fkRaumplan = r.pkRaumplanID
        LEFT JOIN tblseat s ON e.pkEvent = s.fkEvent
        WHERE s.pkSeat IS NULL";

$events = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

foreach ($events as $event) {
    $stmtSeat = $pdo->prepare("INSERT INTO tblseat (fkEvent, fldReihe, fldSeatNumber, fldStatus) VALUES (?, ?, ?, 'frei')");
    for ($i = 1; $i <= $event['fldKapazitaet']; $i++) {
        $reihe = ceil($i / 10);
        $stmtSeat->execute([$event['pkEvent'], $reihe, $i]);
    }
    echo "Sitze für Event ID " . $event['pkEvent'] . " generiert.<br>";
}
?>