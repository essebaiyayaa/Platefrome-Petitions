<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache');

require_once '../config/config.php';

try {
    $lastSeenId = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
    $query = "SELECT p.IDP, p.TitreP, p.NomPorteurP, p.DateAjoutP
              FROM Petition p 
              WHERE p.IDP > :lastId
              ORDER BY p.IDP DESC 
              LIMIT 1";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':lastId' => $lastSeenId]);
    $newPetition = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($newPetition) {
        echo json_encode([
            'success' => true,
            'hasNew' => true,
            'petition' => [
                'id' => intval($newPetition['IDP']),
                'titre' => $newPetition['TitreP'],
                'nomPorteur' => $newPetition['NomPorteurP'],
                'dateAjout' => $newPetition['DateAjoutP']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'hasNew' => false
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur',
        'message' => $e->getMessage()
    ]);
}
?>