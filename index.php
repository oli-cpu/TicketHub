<?php
include("connectpdo.php");

// SQL exakt nach deinem Modell: fld-Präfixe hinzugefügt
$sql = "SELECT pkEvent, fldEventName, fldBasisPreis FROM tblevent ORDER BY fldEventDatum ASC";

try {
    $stmt = $pdo->query($sql);
} catch (PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>TicketHub - Startseite</title>
    <style>
        .nav { background: #333; padding: 10px; color: white; }
        .login-btn { color: white; border: 1px solid white; padding: 5px 10px; text-decoration: none; float: right; }
    </style>
</head>
<body>

<div class="nav">
    <span>TicketHub</span>
    <a href="auth/loginpdo.php" class="login-btn">Login</a>
</div>

<h1>Aktuelle Events</h1>

<table border="1" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Event</th>
            <th>Preis</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <tr>
            <td><?= htmlspecialchars($row['pkEvent']) ?></td>
            <td><?= htmlspecialchars($row['fldEventName']) ?></td>
            <td>CHF <?= htmlspecialchars($row['fldBasisPreis']) ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>