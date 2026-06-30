<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true){
    header("location: index.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $ontvangerNaam = trim($_POST['ontvanger'] ?? '');
    $bedrag = filter_var($_POST['bedrag'] ?? '', FILTER_VALIDATE_FLOAT);
    $omschrijving = trim($_POST['omschrijving'] ?? '');

    if ($ontvangerNaam === '' || strlen($ontvangerNaam) > 50) {
        $error = "Voer een geldige ontvanger in.";
    } elseif ($bedrag === false || $bedrag <= 0) {
        $error = "Voer een geldig bedrag in.";
    } elseif ($omschrijving === '' || strlen($omschrijving) > 255) {
        $error = "Voer een geldige omschrijving in.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->execute([$ontvangerNaam]);
        $ontvanger = $stmt->fetch();

        if($stmt->rowCount() == 1) {
            if($_SESSION['user']['balance'] >= $bedrag) {
                $stmt = $pdo->prepare("INSERT INTO transaction (sender, receiver, amount, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user']['id'], $ontvanger['id'], $bedrag, $omschrijving]);

                $stmt = $pdo->prepare("UPDATE user SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$bedrag, $ontvanger['id']]);

                $stmt = $pdo->prepare("UPDATE user SET balance = balance - ? WHERE id = ?");
                $stmt->execute([$bedrag, $_SESSION['user']['id']]);

                $success = "Het bedrag is succesvol overgemaakt";
            } else {
                $error = "Je hebt niet genoeg saldo om dit bedrag over te maken";
            }
        } else {
            $error = "Deze gebruiker bestaat niet";
        }
    }
}

$stmt = $pdo->prepare("SELECT balance FROM user WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$saldo = $stmt->fetchColumn();
?>