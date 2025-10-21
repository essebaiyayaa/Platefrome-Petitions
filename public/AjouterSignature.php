<?php
session_start();
require_once '../config/config.php';

// Vérifier si la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ListePetition.php');
    exit();
}

// Vérifier si un ID de pétition est fourni
if (!isset($_POST['id_petition']) || empty($_POST['id_petition'])) {
    $_SESSION['error_message'] = "ID de pétition manquant.";
    header('Location: ListePetition.php');
    exit();
}

$id_petition = $_POST['id_petition'];

// Récupérer les données du formulaire
$nomS = trim($_POST['nomS']);
$prenomS = trim($_POST['prenomS']);
$emailS = trim($_POST['emailS']);
$paysS = trim($_POST['paysS']);

// Validation des données
if (empty($nomS) || empty($prenomS) || empty($emailS) || empty($paysS)) {
    $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
    header("Location: signature.php?id=" . $id_petition);
    exit();
}

if (!filter_var($emailS, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "L'adresse email n'est pas valide.";
    header("Location: signature.php?id=" . $id_petition);
    exit();
}

// Vérifier si la pétition existe
$petition_query = "SELECT * FROM Petition WHERE IDP = :id";
$petition_stmt = $pdo->prepare($petition_query);
$petition_stmt->execute(['id' => $id_petition]);
$petition = $petition_stmt->fetch();

if (!$petition) {
    $_SESSION['error_message'] = "La pétition n'existe pas.";
    header('Location: ListePetition.php');
    exit();
}

// Vérifier si l'email a déjà signé cette pétition
$check_query = "SELECT * FROM Signature WHERE IDP = :idp AND EmailS = :email";
$check_stmt = $pdo->prepare($check_query);
$check_stmt->execute(['idp' => $id_petition, 'email' => $emailS]);

if ($check_stmt->fetch()) {
    $_SESSION['error_message'] = "Vous avez déjà signé cette pétition avec cet email.";
    header("Location: signature.php?id=" . $id_petition);
    exit();
}

// Insérer la signature
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
    
    // Message de succès
    $_SESSION['success_message'] = "Merci ! Votre signature a été enregistrée avec succès.";
    
    // Redirection vers la liste des pétitions
    header('Location: ListePetition.php');
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Une erreur est survenue lors de l'enregistrement de votre signature : " . $e->getMessage();
    header("Location: signature.php?id=" . $id_petition);
    exit();
}
?>