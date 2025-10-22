<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: auth/login.php');
    exit();
}
if (!isset($_POST['id_petition']) || empty($_POST['id_petition'])) {
    $_SESSION['error_message'] = "ID de pétition manquant.";
    header('Location: mes_petitions.php');
    exit();
}

$id_petition = intval($_POST['id_petition']);
$user_id = $_SESSION['IDU'];

try {
    $check_query = "SELECT * FROM Petition WHERE IDP = :id AND IDU = :user_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([
        'id' => $id_petition,
        'user_id' => $user_id
    ]);
    $petition = $check_stmt->fetch();
    
    if (!$petition) {
        $_SESSION['error_message'] = "Vous n'êtes pas autorisé à supprimer cette pétition.";
        header('Location: mes_petitions.php');
        exit();
    }
    $pdo->beginTransaction();
    $delete_signatures = "DELETE FROM Signature WHERE IDP = :id";
    $stmt_signatures = $pdo->prepare($delete_signatures);
    $stmt_signatures->execute(['id' => $id_petition]);
    
    $delete_petition = "DELETE FROM Petition WHERE IDP = :id";
    $stmt_petition = $pdo->prepare($delete_petition);
    $stmt_petition->execute(['id' => $id_petition]);
    
    $pdo->commit();
    
    $_SESSION['success_message'] = "La pétition a été supprimée avec succès.";
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Erreur lors de la suppression : " . $e->getMessage();
}

header('Location: mes_petitions.php');
exit();
?>