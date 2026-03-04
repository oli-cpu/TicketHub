<?php
session_start();
require_once '../connectpdo.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Zugriff verweigert.");

$id = $_GET['id'] ?? null;
if (!$id) die("Keine ID angegeben.");

// Update-Logik
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['event_name'];
    $datum = $_POST['datum'];
    $preis = $_POST['preis'];

    $stmt = $pdo->prepare("UPDATE tblevent SET fldEventName = ?, fldEventDatum = ?, fldBasisPreis = ? WHERE pkEvent = ?");
    $stmt->execute([$name, $datum, $preis, $id]);
    header("Location: admin_dashboard.php");
    exit;
}

// Aktuelle Daten laden
$stmt = $pdo->prepare("SELECT * FROM tblevent WHERE pkEvent = ?");
$stmt->execute([$id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Event bearbeiten</title>
    <style>
        body { font-family: sans-serif; padding: 40px; }
        .form-card { max-width: 400px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; }
    </style>
</head>
<body>
    <div class="form-card">
        <h3>Event bearbeiten</h3>
        <form method="POST">
            <label>Event Name:</label>
            <input type="text" name="event_name" value="<?= htmlspecialchars($event['fldEventName']) ?>" required>
            <label>Datum:</label>
            <input type="datetime-local" name="datum" value="<?= date('Y-m-d\TH:i', strtotime($event['fldEventDatum'])) ?>" required>
            <label>Preis:</label>
            <input type="number" step="0.01" name="preis" value="<?= $event['fldBasisPreis'] ?>" required>
            <button type="submit">Änderungen speichern</button>
        </form>
        <br>
        <a href="admin_dashboard.php">Abbrechen</a>
    </div>
</body>
</html>