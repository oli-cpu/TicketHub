<?php
session_start();
require_once '../connectpdo.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit;
}

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) { die("Kein Event ausgewählt."); }

$stmtEvent = $pdo->prepare("SELECT fldEventName, fldBasisPreis FROM tblevent WHERE pkEvent = ?");
$stmtEvent->execute([$event_id]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

$stmtSeats = $pdo->prepare("SELECT pkSeat, fldReihe, fldSeatNumber, fldStatus FROM tblseat WHERE fkEvent = ? ORDER BY fldReihe, fldSeatNumber");
$stmtSeats->execute([$event_id]);
$all_seats = $stmtSeats->fetchAll(PDO::FETCH_ASSOC);

$rows = [];
foreach ($all_seats as $seat) {
    $rows[$seat['fldReihe']][] = $seat;
}

if (isset($_POST['confirm_selection']) && !empty($_POST['seat_ids'])) {
    $selected_ids = explode(',', $_POST['seat_ids']);
    foreach ($selected_ids as $seat_id) {
        $stmtSeatInfo = $pdo->prepare("SELECT fldReihe, fldSeatNumber FROM tblseat WHERE pkSeat = ? AND fldStatus = 'frei'");
        $stmtSeatInfo->execute([$seat_id]);
        $seatDetails = $stmtSeatInfo->fetch(PDO::FETCH_ASSOC);
        
        if ($seatDetails) {
            $_SESSION['cart'][] = [
                'event_id'   => $event_id,
                'event_name' => $event['fldEventName'],
                'seat_id'    => $seat_id,
                'reihe'      => $seatDetails['fldReihe'],
                'platz'      => $seatDetails['fldSeatNumber'],
                'price'      => $event['fldBasisPreis']
            ];
        }
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
        /* Globaler Dark Look */
        body { 
            font-family: Arial, sans-serif; 
            background: #000000; 
            color: #ffffff; 
            margin: 0; 
            padding: 20px; 
            text-align: center; 
        }

        h1 { font-size: 1.8rem; margin-bottom: 30px; letter-spacing: -1px; }
        h1 span { color: #ff9900; }

        /* Bühne im Kino-Look */
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

        /* Sitzplan Layout */
        .seating-plan { 
            display: flex; 
            flex-direction: column; 
            gap: 12px; 
            align-items: center; 
            margin-bottom: 100px; 
        }

        .row { display: flex; gap: 8px; align-items: center; }

        .row-label { 
            width: 80px; 
            font-size: 0.75rem; 
            color: #666; 
            text-align: right; 
            margin-right: 15px; 
            text-transform: uppercase;
        }

        /* Die Sitze */
        .seat {
            width: 32px; 
            height: 32px; 
            border-radius: 4px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 10px;
            font-weight: bold;
            cursor: pointer; 
            transition: all 0.2s ease; 
            background: #282828;
            color: #fff;
            border: 1px solid #333;
        }

        /* Status-Farben */
        .seat.frei:hover { 
            background: #444; 
            border-color: #ff9900; 
            transform: translateY(-2px); 
        }

        .seat.besetzt { 
            background: #121212; 
            color: #444; 
            cursor: not-allowed; 
            border: 1px solid #1a1a1a; 
        }

        .seat.selected { 
            background: #ff9900 !important; 
            color: #000 !important; 
            border-color: #fff;
            box-shadow: 0 0 15px rgba(255,153,0,0.4);
        }

        /* Legende */
        .legend { 
            display: flex; 
            justify-content: center; 
            gap: 25px; 
            margin: 30px 0; 
            font-size: 0.85rem; 
            color: #888;
        }
        .legend-item { display: flex; align-items: center; gap: 8px; }
        .legend .box { width: 16px; height: 16px; border-radius: 3px; }

        /* Checkout Bar unten */
        .checkout-bar { 
            position: fixed; 
            bottom: 0; left: 0; right: 0; 
            background: #121212; 
            padding: 20px 40px; 
            border-top: 1px solid #333;
            display: none; 
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .checkout-content { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            width: 100%; 
            max-width: 1000px; 
            margin: 0 auto; 
            gap: 30px;
        }

        .price-tag { font-size: 1.2rem; }
        .price-tag span { color: #ff9900; font-weight: bold; }

        .btn-confirm { 
            background: #ff9900; 
            color: #000; 
            padding: 12px 25px; 
            border: none; 
            border-radius: 4px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s;
        }
        .btn-confirm:hover { background: #e68a00; transform: scale(1.05); }

    </style>
</head>
<body>

    <h1>Wähle deine Plätze für <span><?= htmlspecialchars($event['fldEventName']) ?></span></h1>
    
    <div class="stage">Bühne / Leinwand</div>

    <div class="seating-plan">
        <?php foreach ($rows as $reihe => $seats): ?>
            <div class="row">
                <div class="row-label">R-<?= htmlspecialchars($reihe) ?></div>
                <?php foreach ($seats as $s): ?>
                    <div class="seat <?= htmlspecialchars($s['fldStatus']) ?>" 
                         data-id="<?= $s['pkSeat'] ?>" 
                         data-info="R<?= htmlspecialchars($reihe) ?>/P<?= htmlspecialchars($s['fldSeatNumber']) ?>"
                         onclick="toggleSeat(this)">
                        <?= htmlspecialchars($s['fldSeatNumber']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="legend">
        <div class="legend-item"><div class="box" style="background: #282828; border: 1px solid #333;"></div> Verfügbar</div>
        <div class="legend-item"><div class="box" style="background: #121212;"></div> Besetzt</div>
        <div class="legend-item"><div class="box" style="background: #ff9900;"></div> Deine Wahl</div>
    </div>

    <div id="checkoutBar" class="checkout-bar">
        <div class="checkout-content">
            <div id="selectionInfo" style="color: #888;">0 Plätze ausgewählt</div>
            <div class="price-tag">Gesamt: <span id="totalPrice">0.00</span> CHF</div>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="seat_ids" id="selectedSeatIds">
                <button type="submit" name="confirm_selection" class="btn-confirm">In den Warenkorb</button>
            </form>
        </div>
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
            const hiddenInput = document.getElementById('selectedSeatIds');
            const infoText = document.getElementById('selectionInfo');
            const priceDisplay = document.getElementById('totalPrice');

            if (selectedSeats.length > 0) {
                bar.style.display = 'flex';
                hiddenInput.value = selectedSeats.join(',');
                infoText.innerText = selectedSeats.length + (selectedSeats.length === 1 ? " Platz" : " Plätze") + " gewählt";
                priceDisplay.innerText = (selectedSeats.length * ticketPrice).toFixed(2);
            } else {
                bar.style.display = 'none';
            }
        }
    </script>

</body>
</html>