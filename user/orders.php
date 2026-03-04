<?php
session_start();
require_once '../connectpdo.php';

// Prüfung: Ist der User eingeloggt?
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// FEHLER-DIAGNOSE (Nur zum Testen):
// $check = $pdo->query("SELECT COUNT(*) FROM tblticket WHERE fkUser = $user_id")->fetchColumn();
// echo "Anzahl Tickets in DB für dich: " . $check;

// SQL: Gekaufte Tickets abrufen
// WICHTIG: Prüfe, ob die Spaltennamen pkTicketID oder pkTicket heißen!
$sql = "SELECT 
            t.pkTicket, 
            e.fldEventName, 
            e.fldEventDatum, 
            s.fldReihe, 
            s.fldSeatNumber, 
            t.fldEndpreis,
            b.fldBestellDatum,
            b.fldStatus
        FROM tblticket t
        JOIN tblevent e ON t.fkEvent = e.pkEvent
        JOIN tblseat s ON t.fkSeat = s.pkSeat
        JOIN tblbestellung b ON t.fkBestellung = b.pkBestellungID
        WHERE t.fkUser = ?
        ORDER BY b.fldBestellDatum DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Meine Tickets - TicketHub</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 30px; }
        .container { max-width: 800px; margin: auto; }
        .ticket-card { 
            background: white; border-radius: 12px; padding: 20px; margin-bottom: 15px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #28a745;
            display: flex; justify-content: space-between; align-items: center;
        }
        .empty { text-align: center; padding: 50px; background: white; border-radius: 12px; }
        .btn-back { text-decoration: none; color: #007bff; font-weight: bold; margin-bottom: 20px; display: inline-block; }
    </style>
</head>
<body>

<div class="container">
    <a href="../index.php" class="btn-back">← Zurück zum Shop</a>
    <h2>Deine gekauften Tickets</h2>

    <?php if (empty($tickets)): ?>
        <div class="empty">
            <p>Du hast noch keine Tickets in deinem Konto.</p>
            <p><small>(Überprüfe, ob du in der <strong>cartpdo.php</strong> auf 'Bestellen' geklickt hast!)</small></p>
        </div>
    <?php else: ?>
        <?php foreach ($tickets as $t): ?>
            <div class="ticket-card">
                <div>
                    <div style="font-size: 18px; font-weight: bold;"><?= htmlspecialchars($t['fldEventName']) ?></div>
                    <div style="color: #666; font-size: 14px;">
                        📅 <?= date("d.m.Y H:i", strtotime($t['fldEventDatum'])) ?> Uhr<br>
                        💺 Reihe <?= htmlspecialchars($t['fldReihe']) ?>, Platz <?= htmlspecialchars($t['fldSeatNumber']) ?>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: bold;">CHF <?= number_format($t['fldEndpreis'], 2) ?></div>
                    <div style="font-size: 12px; color: #28a745;">Status: <?= htmlspecialchars($t['fldStatus']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>