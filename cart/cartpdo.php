<?php
session_start();
require_once '../connectpdo.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit();
}

$message = "";

if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    try {
        $pdo->beginTransaction();

        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'];
        }

        $sqlBestellung = "INSERT INTO tblbestellung (fkUser, fldGesamtbetrag, fldStatus) VALUES (?, ?, 'Bezahlt')";
        $stmtB = $pdo->prepare($sqlBestellung);
        $stmtB->execute([$_SESSION['user_id'], $total]);
        $bestellID = $pdo->lastInsertId();

        $sqlTicket = "INSERT INTO tblticket (fkBestellung, fkUser, fkEvent, fkSeat, fldEndpreis) VALUES (?, ?, ?, ?, ?)";
        $stmtT = $pdo->prepare($sqlTicket);
        
        $sqlUpdateSeat = "UPDATE tblseat SET fldStatus = 'besetzt' WHERE pkSeat = ?";
        $stmtU = $pdo->prepare($sqlUpdateSeat);

        foreach ($_SESSION['cart'] as $item) {
            $stmtT->execute([$bestellID, $_SESSION['user_id'], $item['event_id'], $item['seat_id'], $item['price']]);
            $stmtU->execute([$item['seat_id']]);
        }

        $pdo->commit();
        $_SESSION['cart'] = []; 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warenkorb - TicketHub</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #000; 
            color: #fff; 
            padding: 40px 20px; 
            margin: 0;
        }

        .container { 
            background: #121212; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.8); 
            max-width: 800px; 
            margin: auto;
            border: 1px solid #222;
        }

        /* Branding */
        .brand-header {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
        }
        .brand-header span.highlight {
            background: #ff9900;
            color: #000;
            padding: 2px 8px;
            border-radius: 4px;
        }

        h1 { font-size: 1.5rem; margin-bottom: 25px; border-bottom: 1px solid #333; padding-bottom: 15px; }

        /* Table Design */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { text-align: left; color: #888; font-size: 0.8rem; text-transform: uppercase; padding: 10px; border-bottom: 2px solid #333; }
        td { padding: 20px 10px; border-bottom: 1px solid #222; }

        .event-info strong { color: #ff9900; font-size: 1.1rem; }
        .event-info small { color: #bbb; display: block; margin-top: 5px; }

        .price-col { font-weight: bold; font-size: 1.1rem; text-align: right; }

        /* Summen-Bereich */
        .total-row td { 
            border-bottom: none; 
            padding-top: 30px; 
            font-size: 1.4rem; 
            font-weight: bold; 
        }
        .total-amount { color: #ff9900; text-align: right; }

        /* Buttons */
        .actions { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        
        .btn { 
            padding: 15px 30px; 
            border-radius: 4px; 
            cursor: pointer; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 1rem; 
            border: none;
            transition: 0.3s;
        }
        .btn-order { background: #ff9900; color: #000; flex-grow: 1; margin-left: 20px; text-align: center; }
        .btn-order:hover { background: #e68a00; transform: translateY(-2px); }
        
        .btn-back { background: transparent; color: #888; border: 1px solid #444; }
        .btn-back:hover { color: #fff; border-color: #fff; }

        /* Success Message */
        .msg-box { 
            background: rgba(255, 153, 0, 0.1); 
            border: 1px solid #ff9900; 
            color: #ff9900; 
            padding: 20px; 
            border-radius: 4px; 
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="brand-header">
        Ticket<span class="highlight">Hub</span>
    </div>
    
    <?php if ($message): ?>
        <div class="msg-box">
            <h3><?= htmlspecialchars($message) ?></h3>
            <p>Deine Tickets sind nun in deinem Profil verfügbar.</p>
        </div>
        <div style="text-align: center;">
            <a href="../index.php" class="btn btn-order">Zurück zur Startseite</a>
        </div>
    <?php elseif (empty($_SESSION['cart'])): ?>
        <div style="text-align: center; padding: 40px 0;">
            <p style="color: #666; font-size: 1.2rem;">Dein Warenkorb ist momentan leer.</p>
            <a href="../index.php" class="btn btn-order" style="display: inline-block; margin-top: 20px;">Jetzt Events entdecken</a>
        </div>
    <?php else: ?>
        <h1>Dein Warenkorb</h1>
        
        <table>
            <thead>
                <tr>
                    <th>Event & Platzierung</th>
                    <th style="text-align: right;">Preis</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grandTotal = 0;
                foreach ($_SESSION['cart'] as $item): 
                    $grandTotal += $item['price'];
                ?>
                <tr>
                    <td class="event-info">
                        <strong><?= htmlspecialchars($item['event_name']) ?></strong>
                        <small>Reihe <?= htmlspecialchars($item['reihe']) ?>, Platz <?= htmlspecialchars($item['platz']) ?></small>
                    </td>
                    <td class="price-col">CHF <?= number_format($item['price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                
                <tr class="total-row">
                    <td>Gesamtsumme</td>
                    <td class="total-amount">CHF <?= number_format($grandTotal, 2) ?></td>
                </tr>
            </tbody>
        </table>

        <form method="POST" class="actions">
            <a href="../index.php" class="btn btn-back">Weiter einkaufen</a>
            <button type="submit" name="checkout" class="btn btn-order">Kostenpflichtig bestellen</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>