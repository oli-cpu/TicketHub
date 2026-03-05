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
    $passHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    $sql = "INSERT INTO tbluser (fldUsername, fldEmail, fldPasswordHash, fldFirstName, fldLastName, fldUserRole) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([$user, $email, $passHash, $fname, $lname, $role]);
        $success = "Registrierung erfolgreich! <a href='loginpdo.php' style='color:#ff9900; font-weight:bold;'>Hier einloggen</a>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TicketHub - Registrierung</title>
    <style>
        /* Globales Dark Design */
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

        .register-container { 
            background: #121212; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            width: 380px; 
            border: 1px solid #222;
        }

        /* Branding Look */
        .brand {
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            letter-spacing: -1px;
        }
        .brand span.highlight {
            background: #ff9900;
            color: #000;
            padding: 2px 8px;
            border-radius: 4px;
            margin-left: 3px;
        }

        h2 { 
            font-size: 1.1rem; 
            text-align: center; 
            color: #ccc; 
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Formular Styling */
        input { 
            width: 100%; 
            padding: 12px; 
            margin: 10px 0; 
            background: #282828;
            border: 1px solid #333; 
            border-radius: 4px; 
            color: #fff;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #ff9900;
        }

        button { 
            width: 100%; 
            padding: 14px; 
            background-color: #ff9900; 
            color: #000; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            margin-top: 15px; 
            transition: background 0.2s;
        }

        button:hover { 
            background-color: #e68a00; 
        }

        .login-link { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 14px; 
            color: #888;
        }

        .login-link a { 
            color: #ff9900; 
            text-decoration: none; 
            font-weight: bold;
        }

        /* Feedback-Boxen */
        .success { 
            color: #ff9900; 
            background: rgba(255, 153, 0, 0.1); 
            padding: 15px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
            border: 1px solid #ff9900;
            text-align: center;
            font-size: 14px;
        }
        .error { 
            color: #ff4444; 
            background: rgba(255, 68, 68, 0.1); 
            padding: 15px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
            border: 1px solid #ff4444;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            Ticket<span class="highlight">Hub</span>
        </div>
        
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