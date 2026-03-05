<?php
session_start();
require_once 'connectpdo.php';

$isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TicketHub - Premium Events</title>
    <style>
        /* Globales Styling im Dark-Mode */
        body { 
            font-family: Arial, sans-serif; 
            background: #000000; 
            color: #ffffff; 
            margin: 0; 
            padding: 20px; 
        }

        /* Das Logo-Design */
        .brand {
            font-size: 2.2rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            letter-spacing: -1px;
        }
        .brand .text-white { color: #ffffff; }
        .brand .highlight { 
            background: #ff9900; 
            color: #000000; 
            padding: 2px 6px; 
            border-radius: 4px; 
            margin-left: 2px;
        }

        /* Header & Navigation */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: #121212; 
            padding: 10px 40px; 
            border-bottom: 1px solid #333;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-right { display: flex; align-items: center; gap: 20px; }

        /* Buttons */
        .btn { 
            padding: 10px 18px; 
            border-radius: 4px; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: bold;
            cursor: pointer; 
            border: none; 
            transition: 0.2s ease;
        }
        .btn-buy { background: #ff9900; color: #000; }
        .btn-buy:hover { background: #e68a00; }
        .btn-outline { border: 1px solid #ff9900; color: #ff9900; background: transparent; }
        .btn-outline:hover { background: #ff9900; color: #000; }

        /* Bento Dropdown */
        .profile-menu { position: relative; }
        .profile-trigger { 
            background: #282828; 
            color: #fff; 
            padding: 8px 15px; 
            border-radius: 4px; 
            border: 1px solid #444;
            cursor: pointer; 
        }

        .bento-dropdown { 
            display: none; 
            position: absolute; 
            right: 0; 
            top: 45px; 
            width: 220px; 
            background: #1b1b1b; 
            border: 1px solid #333;
            border-radius: 8px; 
            padding: 10px; 
            z-index: 1001; 
        }
        .bento-dropdown.active { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        
        .bento-item { 
            background: #282828; 
            padding: 12px; 
            text-align: center; 
            text-decoration: none; 
            color: #fff; 
            font-size: 12px; 
            border-radius: 4px; 
        }
        .bento-item:hover { background: #333; border: 1px solid #ff9900; }
        .bento-item.full { grid-column: span 2; }
        .bento-item.logout { color: #ff9900; }

        /* Tabelle im modernen Dark-Look */
        .table-container { width: 100%; max-width: 1200px; margin: 40px auto; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: #121212; border-radius: 8px; overflow: hidden; }
        th { background: #1b1b1b; color: #ff9900; padding: 18px; text-align: left; font-size: 13px; text-transform: uppercase; }
        td { padding: 18px; border-bottom: 1px solid #222; font-size: 15px; }
        tr:hover { background: #181818; }
        
        strong { color: #ff9900; }

    </style>

    <script>
        function toggleBento() {
            document.getElementById("bentoMenu").classList.toggle("active");
        }

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
    <a href="#" class="brand">
        <span class="text-white">Ticket</span><span class="highlight">Hub</span>
    </a>
    
    <div class="nav-right">
        <a href="cart/cartpdo.php" class="btn btn-outline">Warenkorb (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="profile-menu">
                <button class="profile-trigger" onclick="toggleBento()">
                    Mein Konto ▾
                </button>
                <div class="bento-dropdown" id="bentoMenu">
                    <a href="user/profile.php" class="bento-item">Profil</a>
                    <a href="user/orders.php" class="bento-item">Tickets</a>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin/admin_dashboard.php" class="bento-item full" style="color: #ff9900;">Admin Panel</a>
                    <?php endif; ?>
                    
                    <a href="auth/logoutpdo.php" class="bento-item full logout">Abmelden</a>
                </div>
            </div>
        <?php else: ?>
            <a href="auth/loginpdo.php" class="btn btn-buy">Anmelden</a>
        <?php endif; ?>
    </div>
</div>

<div class="table-container">
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
                    <a href="tickets/checkoutpdo.php?event_id=<?= $row['pkEvent'] ?>" class="btn btn-buy" onclick="return checkLogin(event)">Ticket kaufen</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>