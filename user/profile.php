<?php
session_start();
require_once '../connectpdo.php';

// Zugriffsschutz: Nur eingeloggte User
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginpdo.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. Aktuelle Benutzerdaten laden
try {
    $stmt = $pdo->prepare("SELECT fldUsername, fldEmail, fldFirstName, fldLastName FROM tbluser WHERE pkUser = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Fehler beim Laden des Profils: " . $e->getMessage());
}

// 2. Profil aktualisieren (POST-Logik)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];

    try {
        $updateSql = "UPDATE tbluser SET fldEmail = ?, fldFirstName = ?, fldLastName = ? WHERE pkUser = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$email, $fname, $lname, $user_id]);
        
        $message = "Profil erfolgreich aktualisiert!";
        // Daten neu laden für die Anzeige
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
    <title>Mein Profil - TicketHub</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 40px; margin: 0; }
        .profile-container { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; color: #555; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        input[readonly] { background: #f9f9f9; color: #888; cursor: not-allowed; }
        .btn-save { background: #007bff; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; }
        .btn-save:hover { background: #0056b3; }
        .msg { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .btn-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; font-weight: 600; }
    </style>
</head>
<body>

<div class="profile-container">
    <a href="../index.php" class="btn-back">← Zurück zum Shop</a>
    <h2>Profil einrichten</h2>

    <?php if ($message): ?>
        <div class="msg success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Benutzername (nicht änderbar)</label>
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

        <button type="submit" class="btn-save">Änderungen speichern</button>
    </form>
</div>

</body>
</html>