<?php

$servername= 'localhost:3306';
$username='root';
<<<<<<< HEAD
$passwort='121007';
=======
$passwort='CbiN1iCb1..1';
>>>>>>> e75057534ced04282ffc6d3fbc4b41b30d3b4229
$db='TicketHub';

try{
    $conn = new pdo ("mysql:host=$servername;dbname=$db",$username,$passwort);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Verbindung mit DB stu141 steht!!";
}

catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>
