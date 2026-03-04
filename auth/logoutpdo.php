<?php
session_start();
session_destroy(); // Löscht alle Session-Daten
header("Location: ../index.php"); // Zurück zur Startseite
exit();
?>