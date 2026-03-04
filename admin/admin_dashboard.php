<?php
session_start();

// 1. Pfad korrigiert: Eine Ebene höher gehen, um connectpdo.php zu finden
require_once '../connectpdo.php'; 

// 2. Zugriffsschutz: Prüfen, ob der User wirklich ein Admin ist
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert. Keine Admin-Berechtigung.");
}

try {
    // 3. SQL angepasst an dein Modell: fldEventName, pkEvent, fldStatus
    $sql = "SELECT e.pkEvent, e.fldEventName,
            (SELECT COUNT(*) FROM tblseat WHERE fkEvent = e.pkEvent AND fldStatus = 'besetzt') as verkaufte_sitze,
            (SELECT COUNT(*) FROM tblseat WHERE fkEvent = e.pkEvent) as gesamt_sitze
            FROM tblevent e";

    $stats = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - TicketHub</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .stats-box { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
        .nav { margin-bottom: 20px; }
    </style>
</head>
<body>

    <h2>Admin Dashboard - Event Auslastung</h2>
    
    <div class="nav">
        <a href="add_event.php"><strong>+ Neues Event anlegen</strong></a> | 
        <a href="../index.php">Zur Startseite</a> | 
        <a href="../auth/logoutpdo.php" style="color:red;">Logout</a>
    </div>

    <?php if (empty($stats)): ?>
        <p>Keine Events gefunden.</p>
    <?php else: ?>
        <?php foreach ($stats as $s): ?>
            <div class="stats-box">
                <strong>Event:</strong> <?= htmlspecialchars($s['fldEventName']) ?><br>
                <strong>Status:</strong> <?= $s['verkaufte_sitze'] ?> von <?= $s['gesamt_sitze'] ?> Plätzen verkauft
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>