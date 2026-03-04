<?php
session_start();
include("connectpdo.php");

// Prüfen, ob der User eingeloggt ist (für die Logik im Hintergrund)
$isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';

// Warenkorb Logik: Nur ausführen, wenn eingeloggt und Button gedrückt
if (isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $preis = $_POST['preis'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$event_id])) {
        $_SESSION['cart'][$event_id]['qty']++;
    } else {
        $_SESSION['cart'][$event_id] = [
            'name' => $event_name,
            'price' => $preis,
            'qty' => 1
        ];
    }
}

// SQL: Daten für die Übersicht
$sql = "SELECT 
            e.pkEvent, e.fldEventName, e.fldEventDatum, e.fldBasisPreis, 
            a.fldArtistName, o.fldOrtName
        FROM tblevent e
        LEFT JOIN tblartist a ON e.fkArtist = a.pkArtist
        LEFT JOIN tblraumplan r ON e.fkRaumplan = r.pkRaumplanID
        LEFT JOIN tblort o ON r.fkOrt = o.pkOrt
        ORDER BY e.fldEventDatum ASC";

$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>TicketHub - Events</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-add { background: #28a745; color: white; }
        .btn-login { background: #007bff; color: white; }
    </style>

    <script>
        // Die JavaScript Funktion für das Popup
        function checkLogin(event) {
            var loggedIn = <?php echo $isLoggedIn; ?>;
            if (!loggedIn) {
                event.preventDefault(); // Verhindert das Absenden des Formulars
                alert("Bitte melden Sie sich erst an, um Tickets in den Warenkorb zu legen!");
                window.location.href = "auth/loginpdo.php"; // Optional: Direkt zum Login leiten
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="header">
    <h1>TicketHub</h1>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Eingeloggt als ID: <?= $_SESSION['user_id'] ?></span>
            <a href="auth/logoutpdo.php" class="btn btn-login" style="background: #6c757d;">Logout</a>
        <?php else: ?>
            <a href="auth/loginpdo.php" class="btn btn-login">Login</a>
        <?php endif; ?>
        <a href="cart/cartpdo.php" class="btn" style="background: #ffc107;">🛒 (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
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
                <form method="POST" onsubmit="return checkLogin(event)">
                    <input type="hidden" name="event_id" value="<?= $row['pkEvent'] ?>">
                    <input type="hidden" name="event_name" value="<?= $row['fldEventName'] ?>">
                    <input type="hidden" name="preis" value="<?= $row['fldBasisPreis'] ?>">
                    <button type="submit" name="add_to_cart" class="btn btn-add">🛒 zum Warenkorb hinzufügen</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>