<?php
// Fehleranzeige für die Entwicklung
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../connectpdo.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user  = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $fname = $_POST['firstname'] ?? '';
    $lname = $_POST['lastname'] ?? '';
    // Passwort sicher hashen
    $passHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Standardrolle für neue Registrierungen
    $role = 'user';

    // SQL-Statement mit Spaltennamen aus deinem ER-Modell
    $sql = "INSERT INTO tbluser (fldUsername, fldEmail, fldPasswordHash, fldFirstName, fldLastName, fldUserRole) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([$user, $email, $passHash, $fname, $lname, $role]);
        $success = "Registrierung erfolgreich. <a href='loginpdo.php'>Hier einloggen</a>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate Entry
            $error = "Benutzername oder Email bereits vergeben.";
        } else {
            $error = "Fehler: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>TicketHub - Registrierung</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; margin: 0; }
        .register-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #333; margin-top: 0; }
        input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background-color: #218838; }
        .login-link { text-align: center; margin-top: 15px; font-size: 14px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 10px; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Konto erstellen</h2>

        <?php if(isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="username" placeholder="Benutzername" required>
            <input type="email" name="email" placeholder="E-Mail Adresse" required>
            <input type="text" name="firstname" placeholder="Vorname" required>
            <input type="text" name="lastname" placeholder="Nachname" required>
            <input type="password" name="password" placeholder="Passwort" required>
            <button type="submit">Registrieren</button>
        </form>

        <div class="login-link">
            Bereits ein Konto? <a href="loginpdo.php">Zum Login</a>
        </div>
    </div>
</body>
</html>