<?php

$servername= 'localhost:3306';
$username='root';
$passwort='Passwort123.';
$db='stu141';

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
