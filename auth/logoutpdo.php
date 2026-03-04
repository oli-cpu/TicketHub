<?php
session_start();
session_destroy(); // Löscht alle Session-Daten
header("Location: /prj-TicketHub/index.php"); // Zurück zur Startseite
exit();
?>