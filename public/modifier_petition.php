<?php
session_start();
require_once '../config/config.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: auth/login.php');
    exit();
}
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID de pétition manquant.";
    header('Location: mes_petitions.php');
    exit();
}

$id_petition = intval($_GET['id']);
$user_id = $_SESSION['IDU'];
$query = "SELECT * FROM Petition WHERE IDP = :id AND IDU = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'id' => $id_petition,
    'user_id' => $user_id
]);
$petition = $stmt->fetch();
if (!$petition) {
    $_SESSION['error_message'] = "Pétition introuvable ou vous n'avez pas l'autorisation de la modifier.";
    header('Location: mes_petitions.php');
    exit();
}
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $nom_porteur = trim($_POST['nom_porteur'] ?? '');
    $date_fin = !empty($_POST['date_fin']) ? $_POST['date_fin'] : null;
    
    // Validation
    if (empty($titre) || empty($description) || empty($nom_porteur)) {
        $error_message = "Tous les champs obligatoires doivent être remplis.";
    } else {
        try {
            $update_query = "UPDATE Petition 
                           SET TitreP = :titre, 
                               DescriptionP = :description, 
                               NomPorteurP = :nom_porteur, 
                               DateFinP = :date_fin 
                           WHERE IDP = :id AND IDU = :user_id";
            
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->execute([
                'titre' => $titre,
                'description' => $description,
                'nom_porteur' => $nom_porteur,
                'date_fin' => $date_fin,
                'id' => $id_petition,
                'user_id' => $user_id
            ]);
            
            $_SESSION['success_message'] = "La pétition a été modifiée avec succès !";
            header('Location: mes_petitions.php');
            exit();
            
        } catch (Exception $e) {
            $error_message = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetitionHub - Modifier la Pétition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800;900&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="toast-container"></div>
    
    <div class="edit-hero">
        <div class="container">
            <h1 class="edit-title">Modifier la Pétition</h1>
            <p class="mb-0">Mettez à jour les informations de votre pétition</p>
        </div>
    </div>

    <section class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <a href="mes_petitions.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour à mes pétitions
            </a>

            <div class="form-edit-card">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="titre" class="form-label">
                            <i class="fas fa-heading me-1" style="color: #3498db;"></i>
                            Titre de la pétition *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="titre" 
                               name="titre" 
                               value="<?php echo htmlspecialchars($petition['TitreP']); ?>"
                               required 
                               maxlength="200">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-1" style="color: #3498db;"></i>
                            Description *
                        </label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="8" 
                                  required><?php echo htmlspecialchars($petition['DescriptionP']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="nom_porteur" class="form-label">
                            <i class="fas fa-user me-1" style="color: #3498db;"></i>
                            Nom du porteur de la pétition *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nom_porteur" 
                               name="nom_porteur" 
                               value="<?php echo htmlspecialchars($petition['NomPorteurP']); ?>"
                               required 
                               maxlength="100">
                    </div>

                    <div class="mb-4">
                        <label for="date_fin" class="form-label">
                            <i class="fas fa-calendar-alt me-1" style="color: #3498db;"></i>
                            Date de fin (optionnelle)
                        </label>
                        <input type="date" 
                               class="form-control" 
                               id="date_fin" 
                               name="date_fin"
                               value="<?php echo $petition['DateFinP'] ? date('Y-m-d', strtotime($petition['DateFinP'])) : ''; ?>"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="mes_petitions.php" class="btn btn-secondary">
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            $query = "SELECT MAX(IDP) as last_id FROM Petition";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $last_id = $result['last_id'] ?? 0;
            ?>
            
            initPetitionList(<?php echo $last_id; ?>, 'public/');
        });
    </script>
</body>
</html>