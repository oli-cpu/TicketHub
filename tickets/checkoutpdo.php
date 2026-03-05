<?php
session_start();
require_once '../connectpdo.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit;
}

// TELEGRAM KONFIGURATION
function sendTelegramUpdate($msg) {
    $token = "8389004700:AAEAF4iNSYTGewI2hkWPRKLLODvhtrSURAw";
    $chat_id = "6885951649";
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg) . "&parse_mode=html";
    @file_get_contents($url);
}

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) { die("Kein Event ausgewählt."); }

// 1. Event-Details laden
$stmtEvent = $pdo->prepare("SELECT fldEventName, fldBasisPreis FROM tblevent WHERE pkEvent = ?");
$stmtEvent->execute([$event_id]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

// 2. Sitzplätze NUMERISCH sortiert laden
$sqlSeats = "SELECT pkSeat, fldReihe, fldSeatNumber, fldStatus
             FROM tblseat
             WHERE fkEvent = ?
             ORDER BY (fldReihe + 0) ASC, (fldSeatNumber + 0) ASC";
$stmtSeats = $pdo->prepare($sqlSeats);
$stmtSeats->execute([$event_id]);
$all_seats = $stmtSeats->fetchAll(PDO::FETCH_ASSOC);

$rows = [];
foreach ($all_seats as $seat) {
    $rows[$seat['fldReihe']][] = $seat;
}
ksort($rows, SORT_NUMERIC);

// Warenkorb-Logik
if (isset($_POST['confirm_selection']) && !empty($_POST['seat_ids'])) {
    $selected_ids = explode(',', $_POST['seat_ids']);
    $addedSeats = []; // Für Telegram Info

    foreach ($selected_ids as $seat_id) {
        $stmtSeatInfo = $pdo->prepare("SELECT fldReihe, fldSeatNumber FROM tblseat WHERE pkSeat = ? AND fldStatus = 'frei'");
        $stmtSeatInfo->execute([$seat_id]);
        $seatDetails = $stmtSeatInfo->fetch(PDO::FETCH_ASSOC);

        if ($seatDetails) {
            $seatItem = [
                'event_id'   => $event_id,
                'event_name' => $event['fldEventName'],
                'seat_id'    => $seat_id,
                'reihe'      => $seatDetails['fldReihe'],
                'platz'      => $seatDetails['fldSeatNumber'],
                'price'      => $event['fldBasisPreis']
            ];
            $_SESSION['cart'][] = $seatItem;
            $addedSeats[] = "R" . $seatDetails['fldReihe'] . "/P" . $seatDetails['fldSeatNumber'];
        }
    }

    // OPTIONAL: Telegram Info wenn etwas in den Warenkorb gelegt wird
    if (!empty($addedSeats)) {
        $tgMsg = "🛒 <b>Warenkorb Update</b>\n";
        $tgMsg .= "User #" . $_SESSION['user_id'] . " hat Plätze gewählt:\n";
        $tgMsg .= "Event: " . $event['fldEventName'] . "\n";
        $tgMsg .= "Plätze: " . implode(", ", $addedSeats);
        sendTelegramUpdate($tgMsg);
    }

    header("Location: ../cart/cartpdo.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitzplatz wählen - TicketHub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #000000;
            color: #ffffff;
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        .brand-header { font-size: 2rem; font-weight: bold; margin-bottom: 20px; }
        .brand-header span { background: #ff9900; color: #000; padding: 2px 8px; border-radius: 4px; }

        .stage {
            background: linear-gradient(to bottom, #333, #111);
            color: #888;
            padding: 15px;
            width: 50%;
            margin: 0 auto 60px;
            border-radius: 5px;
            font-size: 0.8rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            border-bottom: 3px solid #ff9900;
        }

        .seating-plan {
            display: inline-flex;
            flex-direction: column;
            gap: 12px;
            background: #121212;
            padding: 40px;
            border-radius: 15px;
            border: 1px solid #222;
        }

        .row { display: flex; gap: 8px; align-items: center; justify-content: center; }

        .row-label {
            width: 80px;
            font-size: 0.75rem;
            color: #666;
            text-align: right;
            margin-right: 15px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .seat {
            width: 34px;
            height: 34px;
            background: #282828;
            border: 1px solid #333;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s ease;
            color: #fff;
        }

        .seat.frei:hover {
            background: #444;
            border-color: #ff9900;
            transform: translateY(-2px);
        }

        .seat.besetzt {
            background: #0a0a0a;
            color: #333;
            cursor: not-allowed;
            border: 1px solid #1a1a1a;
        }

        .seat.selected {
            background: #ff9900 !important;
            color: #000 !important;
            border-color: #fff;
            box-shadow: 0 0 15px rgba(255,153,0,0.5);
        }

        .legend { display: flex; justify-content: center; gap: 25px; margin: 30px 0; font-size: 0.85rem; color: #888; }
        .legend-item { display: flex; align-items: center; gap: 8px; }
        .legend .box { width: 16px; height: 16px; border-radius: 3px; }

        .checkout-bar {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: #111; padding: 20px;
            border-top: 2px solid #ff9900;
            display: none; justify-content: center; align-items: center; gap: 50px;
        }
        .btn-confirm {
            background: #ff9900; color: #000; padding: 12px 30px;
            border: none; border-radius: 5px; font-weight: bold; cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="brand-header">Ticket<span>Hub</span></div>
    <h1>Wähle deine Plätze für <span><?= htmlspecialchars($event['fldEventName']) ?></span></h1>

    <div class="stage">Bühne / Leinwand</div>

    <div class="seating-plan">
        <?php foreach ($rows as $reihe => $seats): ?>
            <div class="row">
                <div class="row-label">Reihe <?= htmlspecialchars($reihe) ?></div>
                <?php foreach ($seats as $s): ?>
                    <div class="seat <?= htmlspecialchars($s['fldStatus']) ?>"
                         data-id="<?= $s['pkSeat'] ?>"
                         onclick="toggleSeat(this)">
                        <?= htmlspecialchars($s['fldSeatNumber']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="legend">
        <div class="legend-item"><div class="box" style="background: #282828; border: 1px solid #333;"></div> Verfügbar</div>
        <div class="legend-item"><div class="box" style="background: #0a0a0a;"></div> Besetzt</div>
        <div class="legend-item"><div class="box" style="background: #ff9900;"></div> Deine Wahl</div>
    </div>

    <div id="checkoutBar" class="checkout-bar">
        <div id="selectionInfo" style="color: #888; margin-right: 20px;">0 Plätze gewählt</div>
        <div style="font-size: 1.2rem;">Total: <strong style="color:#ff9900;"><span id="totalPrice">0.00</span> CHF</strong></div>
        <form method="POST" style="margin-left: 30px;">
            <input type="hidden" name="seat_ids" id="selectedSeatIds">
            <button type="submit" name="confirm_selection" class="btn-confirm">In den Warenkorb</button>
        </form>
    </div>

    <script>
        let selectedSeats = [];
        const ticketPrice = <?= (float)$event['fldBasisPreis'] ?>;

        function toggleSeat(element) {
            if (element.classList.contains('besetzt')) return;
            const seatId = element.getAttribute('data-id');

            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                selectedSeats = selectedSeats.filter(id => id !== seatId);
            } else {
                element.classList.add('selected');
                selectedSeats.push(seatId);
            }
            updateBar();
        }

        function updateBar() {
            const bar = document.getElementById('checkoutBar');
            document.getElementById('selectedSeatIds').value = selectedSeats.join(',');
            document.getElementById('selectionInfo').innerText = selectedSeats.length + " Plätze gewählt";
            document.getElementById('totalPrice').innerText = (selectedSeats.length * ticketPrice).toFixed(2);
            bar.style.display = selectedSeats.length > 0 ? 'flex' : 'none';
        }
    </script>
</body>
</html>
