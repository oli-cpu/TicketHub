<?php
$servername = 'localhost';
$username = 'root';
$passwort = 'Passwort123.';
$db = 'tickethub';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$db;charset=utf8mb4", $username, $passwort);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn = $pdo;
} catch(PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}
?>
