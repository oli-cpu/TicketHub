<?php
session_start();
require_once '../connectpdo.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

try {
    $stmt = $pdo->prepare("SELECT fldUsername, fldEmail, fldFirstName, fldLastName FROM tbluser WHERE pkUser = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Fehler beim Laden des Profils: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];

    try {
        $updateSql = "UPDATE tbluser SET fldEmail = ?, fldFirstName = ?, fldLastName = ? WHERE pkUser = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$email, $fname, $lname, $user_id]);
        
        $message = "Profil erfolgreich aktualisiert!";
        $user['fldEmail'] = $email;
        $user['fldFirstName'] = $fname;
        $user['fldLastName'] = $lname;
    } catch (PDOException $e) {
        $message = "Fehler beim Speichern: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mein Profil - TicketHub</title>
    <style>
        /* Globales Dark Design */
        body { 
            font-family: Arial, sans-serif; 
            background-color: #000000; 
            color: #ffffff; 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0; 
        }

        /* Container Style */
        .profile-container { 
            width: 100%;
            max-width: 450px; 
            background: #121212; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid #222;
        }

        /* Branding Look */
        .brand-header {
            text-align: center;
            font-size: 1.8rem;
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

        h2 { 
            font-size: 1.2rem; 
            margin-bottom: 25px; 
            text-align: center; 
            color: #eee;
        }

        /* Formular Styling */
        .form-group { margin-bottom: 20px; }
        
        label { 
            display: block; 
            font-size: 0.85rem; 
            color: #999; 
            margin-bottom: 8px; 
            text-transform: uppercase;
            font-weight: bold;
        }

        input { 
            width: 100%; 
            padding: 12px; 
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

        input[readonly] { 
            background: #1b1b1b; 
            color: #666; 
            border-color: #222;
            cursor: not-allowed; 
        }

        /* Buttons */
        .btn-save { 
            background: #ff9900; 
            color: #000; 
            border: none; 
            padding: 14px; 
            border-radius: 4px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 1rem; 
            font-weight: bold; 
            margin-top: 10px;
            transition: background 0.2s;
        }

        .btn-save:hover { background: #e68a00; }

        .btn-back { 
            display: block;
            text-align: center;
            margin-top: 20px; 
            text-decoration: none; 
            color: #ff9900; 
            font-size: 0.9rem; 
        }

        /* Feedback Messages */
        .msg { 
            padding: 12px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
            text-align: center; 
            font-size: 0.9rem;
            font-weight: bold;
        }
        .success { background: rgba(255, 153, 0, 0.1); color: #ff9900; border: 1px solid #ff9900; }

    </style>
</head>
<body>

<div class="profile-container">
    <div class="brand-header">
        Ticket<span class="highlight">Hub</span>
    </div>

    <h2>Profil-Einstellungen</h2>

    <?php if ($message): ?>
        <div class="msg success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Benutzername</label>
            <input type="text" value="<?= htmlspecialchars($user['fldUsername']) ?>" readonly>
        </div>

        <div class="form-group">
            <label>Vorname</label>
            <input type="text" name="firstname" value="<?= htmlspecialchars($user['fldFirstName']) ?>" required>
        </div>

        <div class="form-group">
            <label>Nachname</label>
            <input type="text" name="lastname" value="<?= htmlspecialchars($user['fldLastName']) ?>" required>
        </div>

        <div class="form-group">
            <label>E-Mail Adresse</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['fldEmail']) ?>" required>
        </div>

        <button type="submit" class="btn-save">Speichern</button>
    </form>

    <a href="../index.php" class="btn-back">Zurück zur Übersicht</a>
</div>

</body>
</html>