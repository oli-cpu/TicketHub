<?php
session_start();
require_once '../connectpdo.php';

// Prüfung: Ist der User eingeloggt?
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit;
}

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) { die("Kein Event ausgewählt."); }

// 1. Event-Details laden (Spaltennamen laut deinem Modell)
$stmtEvent = $pdo->prepare("SELECT fldEventName, fldBasisPreis FROM tblevent WHERE pkEvent = ?");
$stmtEvent->execute([$event_id]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

if (!$event) { die("Event nicht gefunden."); }

// 2. Verfügbare Sitzplätze für dieses Event laden
$stmtSeats = $pdo->prepare("SELECT pkSeat, fldReihe, fldSeatNumber FROM tblseat WHERE fkEvent = ? AND fldStatus = 'frei'");
$stmtSeats->execute([$event_id]);
$seats = $stmtSeats->fetchAll(PDO::FETCH_ASSOC);

// 3. Logik: In den Warenkorb legen
if (isset($_POST['confirm_selection'])) {
    $seat_id = $_POST['seat_id'];
    
    // Sitzplatz-Details (Reihe/Nummer) für die Anzeige abrufen
    $stmtSeatInfo = $pdo->prepare("SELECT fldReihe, fldSeatNumber FROM tblseat WHERE pkSeat = ?");
    $stmtSeatInfo->execute([$seat_id]);
    $seatDetails = $stmtSeatInfo->fetch(PDO::FETCH_ASSOC);
    
    // Daten in die Session schreiben
    $_SESSION['cart'][] = [
        'event_id'   => $event_id,
        'event_name' => $event['fldEventName'],
        'seat_id'    => $seat_id,
        'reihe'      => $seatDetails['fldReihe'],
        'platz'      => $seatDetails['fldSeatNumber'],
        'price'      => $event['fldBasisPreis']
    ];
    
    header("Location: ../cart/cartpdo.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Sitzplatz wählen - <?= htmlspecialchars($event['fldEventName']) ?></title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f7f6; }
        .checkout-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 500px; margin: auto; }
        select, button { width: 100%; padding: 10px; margin-top: 10px; border-radius: 4px; border: 1px solid #ddd; }
        .btn-confirm { background: #28a745; color: white; border: none; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="checkout-card">
    <h1><?= htmlspecialchars($event['fldEventName']) ?></h1>
    <p>Preis: <strong>CHF <?= number_format($event['fldBasisPreis'], 2) ?></strong></p>

    <form method="POST">
        <label for="seat_id">Wähle deinen Sitzplatz:</label>
        <select name="seat_id" id="seat_id" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($seats as $seat): ?>
                <option value="<?= $seat['pkSeat'] ?>">
                    Reihe <?= htmlspecialchars($seat['fldReihe']) ?>, Platz <?= htmlspecialchars($seat['fldSeatNumber']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="confirm_selection" class="btn-confirm">
            ✅ Auswahl bestätigen & in Warenkorb
        </button>
    </form>
    <br>
    <a href="../index.php">Abbrechen</a>
</div>

</body>
</html>