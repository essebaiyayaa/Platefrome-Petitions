<?php
require_once '../config/config.php';

header('Content-Type: application/json');
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'ID de pétition manquant']);
    exit();
}

$id_petition = $_GET['id'];
$query = "SELECT NomS, PrenomS, PaysS, DateS 
          FROM Signature 
          WHERE IDP = :id 
          ORDER BY DateS DESC 
          LIMIT 5";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id_petition]);
    $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'signatures' => $signatures
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Erreur lors de la récupération des signatures',
        'message' => $e->getMessage()
    ]);
}
?>