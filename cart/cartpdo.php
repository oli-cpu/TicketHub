<?php
session_start();
require_once '../connectpdo.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit();
}

$message = "";

// Bestellung abschließen
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    try {
        $pdo->beginTransaction();

        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'];
        }

        // 1. Bestellung anlegen
        $sqlBestellung = "INSERT INTO tblbestellung (fkUser, fldGesamtbetrag, fldStatus) VALUES (?, ?, 'Bezahlt')";
        $stmtB = $pdo->prepare($sqlBestellung);
        $stmtB->execute([$_SESSION['user_id'], $total]);
        $bestellID = $pdo->lastInsertId();

        // 2. Tickets anlegen und Sitze sperren
        $sqlTicket = "INSERT INTO tblticket (fkBestellung, fkUser, fkEvent, fkSeat, fldEndpreis) VALUES (?, ?, ?, ?, ?)";
        $stmtT = $pdo->prepare($sqlTicket);
        
        $sqlUpdateSeat = "UPDATE tblseat SET fldStatus = 'besetzt' WHERE pkSeat = ?";
        $stmtU = $pdo->prepare($sqlUpdateSeat);

        foreach ($_SESSION['cart'] as $item) {
            $stmtT->execute([$bestellID, $_SESSION['user_id'], $item['event_id'], $item['seat_id'], $item['price']]);
            $stmtU->execute([$item['seat_id']]);
        }

        $pdo->commit();
        $_SESSION['cart'] = []; // Warenkorb leeren
        $message = "Bestellung erfolgreich abgeschlossen!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Fehler: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Warenkorb - TicketHub</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f7f6; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; color: white; display: inline-block; }
        .btn-order { background: #28a745; }
        .btn-back { background: #6c757d; }
    </style>
</head>
<body>

<div class="container">
    <h1>Dein Warenkorb 🛒</h1>
    
    <?php if ($message): ?>
        <p style="color: green; font-weight: bold;"><?= $message ?></p>
        <a href="../index.php" class="btn btn-back">Zurück zur Übersicht</a>
    <?php elseif (empty($_SESSION['cart'])): ?>
        <p>Dein Warenkorb ist leer.</p>
        <a href="../index.php" class="btn btn-back">Events ansehen</a>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Event & Sitzplatz</th>
                    <th>Preis</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grandTotal = 0;
                foreach ($_SESSION['cart'] as $item): 
                    $grandTotal += $item['price'];
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item['event_name']) ?></strong><br>
                        <small>Reihe <?= htmlspecialchars($item['reihe']) ?>, Platz <?= htmlspecialchars($item['platz']) ?></small>
                    </td>
                    <td>CHF <?= number_format($item['price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="font-weight: bold; background: #eee;">
                    <td>Gesamtbetrag</td>
                    <td>CHF <?= number_format($grandTotal, 2) ?></td>
                </tr>
            </tbody>
        </table>

        <form method="POST">
            <a href="../index.php" class="btn btn-back">Weiter einkaufen</a>
            <button type="submit" name="checkout" class="btn btn-order">Kostenpflichtig bestellen</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>