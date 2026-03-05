<?php
session_start();
require_once '../connectpdo.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit;
}

$user_id = $_SESSION['user_id'];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meine Tickets - TicketHub</title>
    <style>
        /* Globaler Dark Look */
        body { 
            font-family: Arial, sans-serif; 
            background: #000000; 
            color: #ffffff; 
            margin: 0; 
            padding: 40px; 
        }

        .container { max-width: 900px; margin: auto; }

        /* Branding */
        .brand-header {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            letter-spacing: -1px;
        }
        .brand-header span.highlight {
            background: #ff9900;
            color: #000;
            padding: 2px 8px;
            border-radius: 4px;
            margin-left: 4px;
        }

        /* Ticket-Card Design */
        .ticket-card { 
            background: #121212; 
            border-radius: 8px; 
            padding: 25px; 
            margin-bottom: 20px; 
            border: 1px solid #222;
            border-left: 6px solid #ff9900; /* Der ikonische Akzent */
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            transition: transform 0.2s;
        }
        
        .ticket-card:hover {
            transform: scale(1.01);
            background: #181818;
        }

        .ticket-info h3 { 
            margin: 0 0 10px 0; 
            font-size: 1.4rem; 
            color: #ff9900;
        }

        .details { 
            color: #bbb; 
            font-size: 0.95rem; 
            line-height: 1.6;
        }

        .details span { color: #fff; font-weight: bold; }

        .ticket-meta { text-align: right; }

        .price { 
            font-size: 1.5rem; 
            font-weight: bold; 
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(255, 153, 0, 0.1);
            color: #ff9900;
            border: 1px solid #ff9900;
            border-radius: 4px;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* Empty State */
        .empty { 
            text-align: center; 
            padding: 60px; 
            background: #121212; 
            border-radius: 8px; 
            border: 1px dashed #444;
        }

        .btn-back { 
            text-decoration: none; 
            color: #ff9900; 
            font-weight: bold; 
            display: inline-block; 
            margin-bottom: 25px;
            font-size: 0.9rem;
        }

        .btn-back:hover { text-decoration: underline; }

    </style>
</head>
<body>

<div class="container">
    <div class="brand-header">
        Ticket<span class="highlight">Hub</span>
    </div>

    <a href="../index.php" class="btn-back">← Zurück zum Shop</a>
    
    <h2 style="margin-bottom: 30px;">Deine Tickets</h2>

    <?php if (empty($tickets)): ?>
        <div class="empty">
            <p style="font-size: 1.2rem; color: #888;">Du hast noch keine Tickets erworben.</p>
            <p><small style="color: #555;">Besuche den Shop, um aktuelle Events zu finden.</small></p>
        </div>
    <?php else: ?>
        <?php foreach ($tickets as $t): ?>
            <div class="ticket-card">
                <div class="ticket-info">
                    <h3><?= htmlspecialchars($t['fldEventName']) ?></h3>
                    <div class="details">
                        Datum: <span><?= date("d.m.Y - H:i", strtotime($t['fldEventDatum'])) ?> Uhr</span><br>
                        Location: <span>Zentralarena</span><br> Platz: <span>Reihe <?= htmlspecialchars($t['fldReihe']) ?>, Sitz <?= htmlspecialchars($t['fldSeatNumber']) ?></span>
                    </div>
                </div>
                
                <div class="ticket-meta">
                    <div class="price">CHF <?= number_format($t['fldEndpreis'], 2) ?></div>
                    <div class="status-badge"><?= htmlspecialchars($t['fldStatus']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>