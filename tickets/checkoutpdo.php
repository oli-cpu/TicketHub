<?php
session_start();
require_once '../connectpdo.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit;
}

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) { die("Kein Event ausgewählt."); }

// 1. Event-Details laden
$stmtEvent = $pdo->prepare("SELECT fldEventName, fldBasisPreis FROM tblevent WHERE pkEvent = ?");
$stmtEvent->execute([$event_id]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

// 2. ALLE Sitzplätze laden
$stmtSeats = $pdo->prepare("SELECT pkSeat, fldReihe, fldSeatNumber, fldStatus FROM tblseat WHERE fkEvent = ? ORDER BY fldReihe, fldSeatNumber");
$stmtSeats->execute([$event_id]);
$all_seats = $stmtSeats->fetchAll(PDO::FETCH_ASSOC);

$rows = [];
foreach ($all_seats as $seat) {
    $rows[$seat['fldReihe']][] = $seat;
}

// 3. Warenkorb-Logik für mehrere Plätze
if (isset($_POST['confirm_selection']) && !empty($_POST['seat_ids'])) {
    // Die IDs kommen als kommagetrennter String vom Hidden-Input
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
    <title>Sitzplatz wählen - TicketHub</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; text-align: center; }
        .stage { 
            background: #333; color: white; padding: 10px; width: 60%; margin: 0 auto 50px; 
            border-radius: 0 0 50px 50px; font-weight: bold; text-transform: uppercase;
        }
        .seating-plan { display: flex; flex-direction: column; gap: 10px; align-items: center; margin-bottom: 30px; }
        .row { display: flex; gap: 10px; align-items: center; }
        .row-label { width: 80px; font-weight: bold; color: #666; }
        
        .seat {
            width: 35px; height: 35px; border-radius: 6px; border: 1px solid #ccc;
            display: flex; align-items: center; justify-content: center; font-size: 10px;
            cursor: pointer; transition: 0.2s; background: white;
        }
        .seat.frei:hover { background: #28a745; color: white; transform: scale(1.1); }
        .seat.besetzt { background: #ddd; color: #999; cursor: not-allowed; border: none; }
        .seat.selected { background: #28a745; color: white; border-color: #1e7e34; box-shadow: 0 0 10px rgba(40,167,69,0.5); }
        
        .legend { display: flex; justify-content: center; gap: 20px; margin-top: 20px; font-size: 14px; }
        .legend-item { display: flex; align-items: center; gap: 5px; }

        .checkout-bar { 
            position: fixed; bottom: 0; left: 0; right: 0; background: white; 
            padding: 20px; box-shadow: 0 -5px 15px rgba(0,0,0,0.1); display: none; 
        }
        .btn-confirm { background: #28a745; color: white; padding: 10px 30px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

    <h1>Sitzplatz wählen: <?= htmlspecialchars($event['fldEventName']) ?></h1>
    
    <div class="stage">Bühne</div>

    

    <div class="seating-plan">
        <?php foreach ($rows as $reihe => $seats): ?>
            <div class="row">
                <div class="row-label">Reihe <?= htmlspecialchars($reihe) ?></div>
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
        <div class="legend-item"><div class="seat frei" style="width: 20px; height: 20px; cursor: default;"></div> Verfügbar</div>
        <div class="legend-item"><div class="seat besetzt" style="width: 20px; height: 20px; cursor: default;"></div> Besetzt</div>
        <div class="legend-item"><div class="seat selected" style="width: 20px; height: 20px; cursor: default;"></div> Deine Wahl</div>
    </div>

    <div id="checkoutBar" class="checkout-bar">
        <span id="selectionInfo"></span> | <strong>Total: <span id="totalPrice">0.00</span> CHF</strong>
        <form method="POST" style="display: inline; margin-left: 20px;">
            <input type="hidden" name="seat_ids" id="selectedSeatIds">
            <button type="submit" name="confirm_selection" class="btn-confirm">Alle in den Warenkorb</button>
        </form>
    </div>

    <script>
        let selectedSeats = [];
        const ticketPrice = <?= (float)$event['fldBasisPreis'] ?>;

        function toggleSeat(element) {
            if (element.classList.contains('besetzt')) return;

            const seatId = element.getAttribute('data-id');
            const seatInfo = element.getAttribute('data-info');

            if (element.classList.contains('selected')) {
                // Abwählen
                element.classList.remove('selected');
                selectedSeats = selectedSeats.filter(id => id !== seatId);
            } else {
                // Auswählen
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
                bar.style.display = 'block';
                hiddenInput.value = selectedSeats.join(',');
                infoText.innerText = selectedSeats.length + " Plätze ausgewählt";
                priceDisplay.innerText = (selectedSeats.length * ticketPrice).toFixed(2);
            } else {
                bar.style.display = 'none';
                hiddenInput.value = "";
            }
        }
    </script>

</body>
</html>