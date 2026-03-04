<?php
include 'connectpdo.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    // Passwort sicher hashen
    $passHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO tbluser (Username, Email, PasswordHash, FirstName, LastName) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([$user, $email, $passHash, $fname, $lname]);
        echo "Registrierung erfolgreich. <a href='login.php'>Hier einloggen</a>";
    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }
}
?>

<form method="post">
    Username: <input type="text" name="username" required><br>
    Email: <input type="email" name="email" required><br>
    Vorname: <input type="text" name="firstname" required><br>
    Nachname: <input type="text" name="lastname" required><br>
    Passwort: <input type="password" name="password" required><br>
    <button type="submit">Registrieren</button>
</form>
