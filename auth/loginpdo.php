<?php
// Fehleranzeige für die Entwicklung
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../connectpdo.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!empty($user) && !empty($pass)) {
        try {
            $stmt = $pdo->prepare("SELECT pkUser, fldPasswordHash, fldUserRole FROM tbluser WHERE fldUsername = ?");
            $stmt->execute([$user]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData && password_verify($pass, $userData['fldPasswordHash'])) {
                $_SESSION['user_id'] = $userData['pkUser'];
                $_SESSION['role']    = $userData['fldUserRole']; 

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TicketHub - Login</title>
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

        .login-container { 
            background: #121212; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            width: 350px; 
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

        .register-link { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 14px; 
            color: #888;
            line-height: 1.6;
        }

        .register-link a { 
            color: #ff9900; 
            text-decoration: none; 
            font-weight: bold;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
            color: #555;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-home:hover {
            color: #888;
        }

        /* Fehler-Box */
        .error { 
            color: #ff4444; 
            background: rgba(255, 68, 68, 0.1); 
            padding: 12px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
            border: 1px solid #ff4444;
            text-align: center;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand">
            Ticket<span class="highlight">Hub</span>
        </div>
        
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
        
        <a href="../index.php" class="back-home">Zurück zur Startseite</a>
    </div>
</body>
</html>