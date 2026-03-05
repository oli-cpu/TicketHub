<?php
session_start();
require_once '../connectpdo.php';

// Sicherheits-Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert. Nur Administratoren haben hier Zutritt.");
}

$id = $_GET['id'] ?? null;
if (!$id) die("Keine ID angegeben.");

// Update-Logik
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['event_name'];
    $datum = $_POST['datum'];
    $preis = $_POST['preis'];

    try {
        $stmt = $pdo->prepare("UPDATE tblevent SET fldEventName = ?, fldEventDatum = ?, fldBasisPreis = ? WHERE pkEvent = ?");
        $stmt->execute([$name, $datum, $preis, $id]);
        header("Location: admin_dashboard.php");
        exit;
    } catch (PDOException $e) {
        $error = "Fehler beim Aktualisieren: " . $e->getMessage();
    }
}

// Aktuelle Daten laden
$stmt = $pdo->prepare("SELECT * FROM tblevent WHERE pkEvent = ?");
$stmt->execute([$id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) die("Event nicht gefunden.");
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event bearbeiten - Admin Panel</title>
    <style>
        /* Globaler Dark Look */
        body { 
            font-family: Arial, sans-serif; 
            background-color: #000000; 
            color: #ffffff; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }

        .form-card { 
            width: 100%;
            max-width: 450px; 
            background: #121212; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.6); 
            border: 1px solid #222;
        }

        /* Logo/Branding */
        .brand {
            text-align: center;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .brand span.highlight {
            background: #ff9900;
            color: #000;
            padding: 2px 8px;
            border-radius: 4px;
        }

        h3 { 
            font-size: 1.2rem; 
            text-align: center; 
            color: #888; 
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Formular Elemente */
        label {
            display: block;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        input { 
            width: 100%; 
            padding: 12px; 
            margin-bottom: 20px; 
            background: #282828; 
            border: 1px solid #333; 
            border-radius: 4px; 
            color: #fff; 
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #ff9900;
        }

        /* Button Styling */
        .btn-submit { 
            background: #ff9900; 
            color: #000; 
            border: none; 
            padding: 14px; 
            width: 100%; 
            border-radius: 4px; 
            font-weight: bold; 
            font-size: 1rem;
            cursor: pointer; 
            transition: background 0.2s, transform 0.1s;
        }

        .btn-submit:hover { 
            background: #e68a00; 
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .cancel-link { 
            display: block; 
            text-align: center; 
            margin-top: 20px; 
            color: #555; 
            text-decoration: none; 
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .cancel-link:hover { 
            color: #ff9900; 
        }

        /* Error Message */
        .error {
            background: rgba(255, 0, 0, 0.1);
            color: #ff4444;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #ff4444;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-card">
        <div class="brand">
            Ticket<span class="highlight">Hub</span>
        </div>
        
        <h3>Event bearbeiten</h3>

        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Event Name</label>
            <input type="text" name="event_name" value="<?= htmlspecialchars($event['fldEventName']) ?>" required>
            
            <label>Datum & Uhrzeit</label>
            <input type="datetime-local" name="datum" value="<?= date('Y-m-d\TH:i', strtotime($event['fldEventDatum'])) ?>" required>
            
            <label>Basispreis (CHF)</label>
            <input type="number" step="0.01" name="preis" value="<?= $event['fldBasisPreis'] ?>" required>
            
            <button type="submit" class="btn-submit">Änderungen speichern</button>
        </form>
        
        <a href="admin_dashboard.php" class="cancel-link">Abbrechen & zurück</a>
    </div>
</body>
</html>