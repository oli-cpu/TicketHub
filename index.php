<?php
session_start();
require_once 'connectpdo.php';

// Prüfen, ob der User eingeloggt ist für das JavaScript-Popup
$isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';

// SQL: Daten für die Übersicht mit Joins zu Artist und Ort (basierend auf deinem ER-Modell)
$sql = "SELECT 
            e.pkEvent, 
            e.fldEventName, 
            e.fldEventDatum, 
            e.fldBasisPreis, 
            a.fldArtistName, 
            o.fldOrtName
        FROM tblevent e
        LEFT JOIN tblartist a ON e.fkArtist = a.pkArtist
        LEFT JOIN tblraumplan r ON e.fkRaumplan = r.pkRaumplanID
        LEFT JOIN tblort o ON r.fkOrt = o.pkOrt
        ORDER BY e.fldEventDatum ASC";

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
    <title>TicketHub - Übersicht</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; margin: 0; }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav-links { display: flex; gap: 10px; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #007bff; color: white; text-transform: uppercase; font-size: 14px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; display: inline-block; }
        .btn-buy { background: #28a745; color: white; }
        .btn-buy:hover { background: #218838; }
        .btn-login { background: #007bff; color: white; }
        .btn-admin { background: #6c757d; color: white; }
    </style>

    <script>
        function checkLogin(event) {
            var loggedIn = <?php echo $isLoggedIn; ?>;
            if (!loggedIn) {
                event.preventDefault();
                alert("Bitte melden Sie sich erst an, um Tickets zu kaufen!");
                window.location.href = "auth/loginpdo.php";
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="header">
    <h1>TicketHub</h1>
    <div class="nav-links">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin/admin_dashboard.php" class="btn btn-admin">⚙️ Admin Dashboard</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <span>👤 ID: <?= htmlspecialchars($_SESSION['user_id']) ?></span>
            <a href="auth/logoutpdo.php" class="btn" style="background: #dc3545; color: white;">Logout</a>
        <?php else: ?>
            <a href="auth/loginpdo.php" class="btn btn-login">Login</a>
        <?php endif; ?>
        
        <a href="cart/cartpdo.php" class="btn" style="background: #ffc107; color: #333;">🛒 Warenkorb (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Event</th>
            <th>Artist</th>
            <th>Ort</th>
            <th>Datum</th>
            <th>Preis</th>
            <th>Aktion</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <tr>
            <td><strong><?= htmlspecialchars($row['fldEventName']) ?></strong></td>
            <td><?= htmlspecialchars($row['fldArtistName'] ?? 'TBA') ?></td>
            <td><?= htmlspecialchars($row['fldOrtName'] ?? 'TBA') ?></td>
            <td><?= date("d.m.Y H:i", strtotime($row['fldEventDatum'])) ?></td>
            <td>CHF <?= number_format($row['fldBasisPreis'], 2) ?></td>
            <td>
                <a href="tickets/checkoutpdo.php?event_id=<?= $row['pkEvent'] ?>" 
                   class="btn btn-buy" 
                   onclick="return checkLogin(event)">
                   🎟️ Ticket kaufen
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>