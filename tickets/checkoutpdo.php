<?php
session_start();

// Prüfung: Ist der User eingeloggt?
if (!isset($_SESSION['user_id'])) {
    // Wenn nicht, schick ihn zum Login
    header("Location: login.php");
    exit;
}

echo "Willkommen beim Ticket-Kauf! Deine User-ID ist: " . $_SESSION['user_id'];
// Hier folgt der SQL-Code für tblticket und tblbestellung
?>
