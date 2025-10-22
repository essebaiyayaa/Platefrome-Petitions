<?php
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ListePetition.php');
    exit();
}
if (!isset($_POST['id_petition']) || empty($_POST['id_petition'])) {
    $_SESSION['error_message'] = "ID de pétition manquant.";
    header('Location: ListePetition.php');
    exit();
}

$id_petition = $_POST['id_petition'];
if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
    $_SESSION['error_message'] = "Veuillez compléter le CAPTCHA.";
    header("Location: signer_petition.php?id=" . $id_petition);
    exit();
}
$recaptcha_secret = '6LeSHvMrAAAAAAfTEoyKs96MTB1h3HmOKawV80Nv'; 
$recaptcha_response = $_POST['g-recaptcha-response'];
$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';

$recaptcha_data = [
    'secret' => $recaptcha_secret,
    'response' => $recaptcha_response,
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($recaptcha_data)
    ]
];

$context = stream_context_create($options);
$verify = file_get_contents($recaptcha_url, false, $context);
$captcha_success = json_decode($verify);

if (!$captcha_success->success) {
    $_SESSION['error_message'] = "La vérification CAPTCHA a échoué. Veuillez réessayer.";
    header("Location: signer_petition.php?id=" . $id_petition);
    exit();
}
$nomS = trim($_POST['nomS']);
$prenomS = trim($_POST['prenomS']);
$emailS = trim($_POST['emailS']);
$paysS = trim($_POST['paysS']);
if (empty($nomS) || empty($prenomS) || empty($emailS) || empty($paysS)) {
    $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
    header("Location: signer_petition.php?id=" . $id_petition);
    exit();
}

if (!filter_var($emailS, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "L'adresse email n'est pas valide.";
    header("Location: signer_petition.php?id=" . $id_petition);
    exit();
}
$petition_query = "SELECT * FROM Petition WHERE IDP = :id";
$petition_stmt = $pdo->prepare($petition_query);
$petition_stmt->execute(['id' => $id_petition]);
$petition = $petition_stmt->fetch();

if (!$petition) {
    $_SESSION['error_message'] = "La pétition n'existe pas.";
    header('Location: ListePetition.php');
    exit();
}
$check_query = "SELECT * FROM Signature WHERE IDP = :idp AND EmailS = :email";
$check_stmt = $pdo->prepare($check_query);
$check_stmt->execute(['idp' => $id_petition, 'email' => $emailS]);

if ($check_stmt->fetch()) {
    $_SESSION['error_message'] = "Vous avez déjà signé cette pétition avec cet email.";
    header("Location: signer_petition.php?id=" . $id_petition);
    exit();
}
$insert_query = "INSERT INTO Signature (IDP, NomS, PrenomS, EmailS, PaysS) 
               VALUES (:idp, :nom, :prenom, :email, :pays)";
$insert_stmt = $pdo->prepare($insert_query);

try {
    $insert_stmt->execute([
        'idp' => $id_petition,
        'nom' => $nomS,
        'prenom' => $prenomS,
        'email' => $emailS,
        'pays' => $paysS
    ]);
    $_SESSION['success_message'] = "Merci ! Votre signature a été enregistrée avec succès.";
    header('Location: ListePetition.php');
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Une erreur est survenue lors de l'enregistrement de votre signature : " . $e->getMessage();
    header("Location: signer_petition.php?id=" . $id_petition);
    exit();
}
?>