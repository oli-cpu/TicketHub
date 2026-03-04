<?php
session_start();
require_once '../connectpdo.php';

// Falls nicht eingeloggt, zurück zur index
if (!isset($_SESSION['user_id'])) {
    header("Location: /prj-TicketHub/auth/loginpdo.php");
    exit();
}

$message = "";

// Bestellung abschliessen
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    try {
        $pdo->beginTransaction();

        // 1. Gesamtsumme berechnen
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        // 2. Eintrag in tblbestellung erstellen
        $sqlBestellung = "INSERT INTO tblbestellung (fkUser, fldGesamtbetrag, fldStatus) VALUES (?, ?, 'Bezahlt')";
        $stmtB = $pdo->prepare($sqlBestellung);
        $stmtB->execute([$_SESSION['user_id'], $total]);
        $bestellID = $pdo->lastInsertId();

        // 3. Pro Ticket einen Eintrag in tblticket erstellen
        $sqlTicket = "INSERT INTO tblticket (fkBestellung, fkUser, fkEvent, fldEndpreis) VALUES (?, ?, ?, ?)";
        $stmtT = $pdo->prepare($sqlTicket);

        foreach ($_SESSION['cart'] as $eventId => $item) {
            for ($i = 0; $i < $item['qty']; $i++) {
                $stmtT->execute([$bestellID, $_SESSION['user_id'], $eventId, $item['price']]);
            }
        }

        $pdo->commit();
        $_SESSION['cart'] = []; // Warenkorb leeren
        $message = "Vielen Dank! Deine Bestellung wurde erfolgreich abgeschlossen.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Fehler bei der Bestellung: " . $e->getMessage();
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
        .btn-order { background: #28a745; font-size: 1.1em; }
        .btn-back { background: #6c757d; }
    </style>
</head>
<body>

<div class="container">
    <h1>Dein Warenkorb 🛒</h1>
    
    <?php if ($message): ?>
        <p style="color: green; font-weight: bold;"><?= $message ?></p>
        <a href="/prj-TicketHub/index.php" class="btn btn-back">Zurück zu den Events</a>
    <?php elseif (empty($_SESSION['cart'])): ?>
        <p>Dein Warenkorb ist leer.</p>
        <a href="/prj-TicketHub/index.php" class="btn btn-back">Jetzt Tickets finden</a>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Anzahl</th>
                    <th>Preis pro Ticket</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grandTotal = 0;
                foreach ($_SESSION['cart'] as $id => $item): 
                    $subtotal = $item['price'] * $item['qty'];
                    $grandTotal += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['qty'] ?>x</td>
                    <td>CHF <?= number_format($item['price'], 2) ?></td>
                    <td>CHF <?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="font-weight: bold; background: #eee;">
                    <td colspan="3">Gesamtbetrag</td>
                    <td>CHF <?= number_format($grandTotal, 2) ?></td>
                </tr>
            </tbody>
        </table>

        <form method="POST">
            <a href="/prj-TicketHub/index.php" class="btn btn-back">Weiter einkaufen</a>
            <button type="submit" name="checkout" class="btn btn-order">Kostenpflichtig bestellen</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>