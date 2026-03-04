<?php
session_start();
require_once 'connectpdo.php';

// Login-Status für JavaScript-Check
$isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';

// SQL: Events mit Joins laden (fld-Präfixe laut deinem Modell)
$sql = "SELECT 
            e.pkEvent, e.fldEventName, e.fldEventDatum, e.fldBasisPreis, 
            a.fldArtistName, o.fldOrtName
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
    <title>TicketHub - Startseite</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
        
        /* Header & Navigation */
        .header { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .nav-right { display: flex; align-items: center; gap: 15px; }

        /* Profil-Bento Style */
        .profile-menu { position: relative; }
        .profile-trigger { 
            background: #007bff; color: white; padding: 10px 20px; border-radius: 30px; 
            cursor: pointer; font-weight: 600; transition: 0.3s; border: none;
        }
        .profile-trigger:hover { background: #0056b3; }

        .bento-dropdown { 
            display: none; position: absolute; right: 0; top: 50px; width: 240px; 
            background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
            z-index: 1000; padding: 12px; 
        }
        .bento-dropdown.active { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        
        .bento-item { 
            background: #f8f9fa; padding: 15px; text-align: center; text-decoration: none; color: #333; 
            font-size: 13px; border-radius: 12px; transition: 0.2s; display: flex; flex-direction: column; align-items: center; gap: 8px;
        }
        .bento-item:hover { background: #e9ecef; transform: translateY(-2px); }
        .bento-item.full { grid-column: span 2; }
        .bento-item.logout { color: #dc3545; background: #fff1f0; font-weight: bold; }

        /* Tabelle */
        table { width: 100%; border-collapse: collapse; margin-top: 30px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #007bff; color: white; font-weight: 500; }
        .btn { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; cursor: pointer; border: none; }
        .btn-buy { background: #28a745; color: white; font-weight: bold; }
        .cart-btn { background: #ffc107; color: #333; font-weight: bold; }
    </style>

    <script>
        // Bento Menu Umschalter
        function toggleBento() {
            var menu = document.getElementById("bentoMenu");
            menu.classList.toggle("active");
        }

        // Schließen wenn man außerhalb klickt
        window.onclick = function(event) {
            if (!event.target.closest('.profile-menu')) {
                var menu = document.getElementById("bentoMenu");
                if (menu && menu.classList.contains('active')) {
                    menu.classList.remove("active");
                }
            }
        }

        function checkLogin(event) {
            var loggedIn = <?php echo $isLoggedIn; ?>;
            if (!loggedIn) {
                event.preventDefault();
                alert("Bitte loggen Sie sich zuerst ein!");
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
    <div class="nav-right">
        <a href="cart/cartpdo.php" class="btn cart-btn">🛒 Warenkorb (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="profile-menu">
                <button class="profile-trigger" onclick="toggleBento()">
                    👤 Mein Konto ▾
                </button>
                <div class="bento-dropdown" id="bentoMenu">
                    <a href="user/profile.php" class="bento-item">⚙️<br>Profil</a>
                    <a href="user/orders.php" class="bento-item">🎟️<br>Tickets</a>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin/admin_dashboard.php" class="bento-item full" style="background: #e7f3ff; color: #007bff; font-weight: bold;">🛠️ Admin Panel</a>
                    <?php endif; ?>
                    
                    <a href="auth/logoutpdo.php" class="bento-item full logout">🚪 Abmelden</a>
                </div>
            </div>
        <?php else: ?>
            <a href="auth/loginpdo.php" class="btn" style="background: #007bff; color: white; font-weight: bold;">Anmelden</a>
        <?php endif; ?>
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
                <a href="tickets/checkoutpdo.php?event_id=<?= $row['pkEvent'] ?>" class="btn btn-buy" onclick="return checkLogin(event)">🎟️ Ticket kaufen</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>