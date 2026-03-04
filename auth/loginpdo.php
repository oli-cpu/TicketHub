<?php
// Fehleranzeige für die Entwicklung (hilft bei weißem Bildschirm)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../connectpdo.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!empty($user) && !empty($pass)) {
        try {
            // Abfrage basierend auf deinem ER-Modell (fld-Präfixe)
            $stmt = $pdo->prepare("SELECT pkUser, fldPasswordHash, fldUserRole FROM tbluser WHERE fldUsername = ?");
            $stmt->execute([$user]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            // Überprüfung des Passwort-Hashes
            if ($userData && password_verify($pass, $userData['fldPasswordHash'])) {
                $_SESSION['user_id'] = $userData['pkUser'];
                $_SESSION['role']    = $userData['fldUserRole']; // Speichert 'admin' oder 'user'

                // Rollenbasierte Weiterleitung
                if ($_SESSION['role'] === 'admin') {
                    header("Location: ../admin/admin_dashboard.php");
                } else {
                    header("Location: ../index.php");
                }
                exit;
            } else {
                $error = "Ungültiger Benutzername oder Passwort.";
            }
        } catch (PDOException $e) {
            $error = "Datenbankfehler: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>TicketHub - Login</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 320px; }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .register-link { text-align: center; margin-top: 15px; font-size: 14px; }
        .register-link a { color: #007bff; text-decoration: none; font-weight: bold; }
        .error { color: #d9534f; background: #f2dede; padding: 10px; border-radius: 4px; margin-bottom: 10px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if(isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Benutzername" required>
            <input type="password" name="password" placeholder="Passwort" required>
            <button type="submit">Einloggen</button>
        </form>

        <div class="register-link">
            Noch kein Konto? <br>
            <a href="registerpdo.php">Jetzt hier registrieren</a>
        </div>
        
        <div style="text-align: center; margin-top: 10px;">
            <a href="../index.php" style="font-size: 12px; color: #666;">Zurück zur Startseite</a>
        </div>
    </div>
</body>
</html>