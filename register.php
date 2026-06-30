<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordcheck = $_POST['passwordcheck'] ?? '';

    if ($username === '' || strlen($username) < 3 || strlen($username) > 50) {
        $error = "Voer een geldige gebruikersnaam in.";
    } elseif (strlen($password) < 8) {
        $error = "Het wachtwoord moet minimaal 8 tekens lang zijn.";
    } elseif ($password !== $passwordcheck) {
        $error = "De wachtwoorden komen niet overeen";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() == 0) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO user (username, password, balance, isAdmin) VALUES (?, ?, 100, 0)");
            $stmt->execute([$username, $hashedPassword]);

            $success = "Je account is aangemaakt, je kunt nu inloggen";
        } else {
            $error = "Deze gebruikersnaam is al in gebruik";
        }
    }
}
?>