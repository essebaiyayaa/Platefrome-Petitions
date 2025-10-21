<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');
require_once '../config/config.php';

try {
    $query = "SELECT p.IDP, p.TitreP, COUNT(DISTINCT s.IDS) as nb_signatures 
              FROM Petition p 
              LEFT JOIN Signature s ON p.IDP = s.IDP 
              GROUP BY p.IDP 
              HAVING nb_signatures > 0
              ORDER BY nb_signatures DESC 
              LIMIT 1";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $topPetition = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($topPetition) {
        echo json_encode([
            'success' => true,
            'petition' => [
                'id' => $topPetition['IDP'],
                'titre' => $topPetition['TitreP'],
                'signatures' => intval($topPetition['nb_signatures'])
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucune pétition avec signatures'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}
?>