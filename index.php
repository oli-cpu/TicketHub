<?php
include("connectpdo.php");

// SQL auf Ticket-System (tblevent) angepasst
$sql = "SELECT pkEvent AS pkArtikelID, EventName AS fldArtikelname, BasisPreis AS fldPreis 
        FROM tblevent 
        ORDER BY BasisPreis DESC";

$stmt = $pdo->query($sql);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>TicketHub Übersicht</title>
</head>
<body>
    <h1>TicketHub Startseite</h1>
    <div>
        <a href="admin/add_event.php">+ Neues Event</a>
        <a href="auth/loginpdo.php">Login</a>
    </div>
    <table border="1">
        <thead>
            <tr><th>ID</th><th>Event</th><th>Preis</th><th>Aktion</th></tr>
        </thead>
        <tbody>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['pkArtikelID']) ?></td>
                <td><?= htmlspecialchars($row['fldArtikelname']) ?></td>
                <td>CHF <?= htmlspecialchars($row['fldPreis']) ?></td>
                <td><a href='edit.php?pk=<?= $row['pkArtikelID'] ?>'>✏</a></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>