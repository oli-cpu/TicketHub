<?php
session_start();
require_once '../connectpdo.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Abfrage von ID, Passwort-Hash UND Rolle laut deinem Modell
    $stmt = $pdo->prepare("SELECT pkUser, fldPasswordHash, fldUserRole FROM tbluser WHERE fldUsername = ?");
    $stmt->execute([$user]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Passwort-Check
    if ($userData && password_verify($pass, $userData['fldPasswordHash'])) {
        
        // WICHTIG: Hier werden die Daten in der Session für spätere Checks gespeichert
        $_SESSION['user_id'] = $userData['pkUser'];
        $_SESSION['role']    = $userData['fldUserRole']; // Speichert 'admin' oder 'user'

        // Weiterleitung basierend auf der Rolle
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/admin_dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        $error = "Falsche Zugangsdaten.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>TicketHub - Login</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Login</h2>
        <?php if(isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Benutzername" required>
            <input type="password" name="password" placeholder="Passwort" required>
            <button type="submit">Einloggen</button>
        </form>
        <p><small><a href="../index.php">Zurück zur Startseite</a></small></p>
    </div>
</body>
</html>