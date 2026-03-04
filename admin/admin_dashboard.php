<?php
session_start();
include 'connectpdo.php';

if ($_SESSION['role'] !== 'admin') die("Kein Admin.");

// SQL für Auslastung pro Event
$sql = "SELECT e.EventName,
        (SELECT COUNT(*) FROM tblseat WHERE fkEvent = e.pkEvent AND Status = 'besetzt') as verkaufte_sitze,
        (SELECT COUNT(*) FROM tblseat WHERE fkEvent = e.pkEvent) as gesamt_sitze
        FROM tblevent e";

$stats = $pdo->query($sql)->fetchAll();

echo "<h2>Admin Dashboard - Event Auslastung</h2>";
foreach ($stats as $s) {
    echo "Event: " . $s['EventName'] . " | Verkauft: " . $s['verkaufte_sitze'] . " / " . $s['gesamt_sitze'] . "<br>";
}
?>
<a href="add_event.php">Neues Event anlegen</a> | <a href="logout.php">Logout</a>
