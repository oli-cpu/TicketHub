<?php
session_start();
include 'connectpdo.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT pkUser, PasswordHash, UserRole FROM tbluser WHERE Username = ?");
    $stmt->execute([$user]);
    $userData = $stmt->fetch();

    if ($userData && password_verify($pass, $userData['PasswordHash'])) {
        $_SESSION['user_id'] = $userData['pkUser'];
        $_SESSION['role'] = $userData['UserRole'];

        // Rollenbasierte Weiterleitung
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        echo "Login fehlgeschlagen.";
    }
}
?>
