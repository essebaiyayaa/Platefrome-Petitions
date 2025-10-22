<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['IDU'];
$query = "SELECT p.*, COUNT(DISTINCT s.IDS) as nb_signatures 
          FROM Petition p 
          LEFT JOIN Signature s ON p.IDP = s.IDP 
          WHERE p.IDU = :user_id
          GROUP BY p.IDP 
          ORDER BY p.DateAjoutP DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$mes_petitions = $stmt->fetchAll();

$success_message = '';
$error_message = '';

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetitionHub - Mes Pétitions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800;900&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="toast-container"></div>
    
    <div class="my-petitions-hero">
        <div class="container">
            <h1 class="my-petitions-title">Mes Pétitions</h1>
            <p class="mb-4">Gérez toutes vos pétitions en un seul endroit</p>
            <?php if ($is_logged_in): ?>
                <a href="creer_petition.php" class="btn btn-create-hero mt-3">
                    <i class="fas fa-plus-circle me-2"></i>Créer une nouvelle pétition
                </a>
            <?php else: ?>
                <a href="auth/login.php?redirect=<?php echo urlencode('../creer_petition.php'); ?>" class="btn btn-create-hero mt-3">
                    <i class="fas fa-plus-circle me-2"></i>Créer une pétition
                </a>
            <?php endif; ?>   
        </div>
    </div>
    
    <div class="container">
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <section class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <?php if (count($mes_petitions) > 0): ?>
                <div class="row">
                    <div class="col-12">
                        <?php foreach ($mes_petitions as $petition): ?>
                            <div class="petition-card-my">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap">
                                    <h2 class="petition-title-my mb-0">
                                        <?php echo htmlspecialchars($petition['TitreP']); ?>
                                    </h2>
                                    <span class="signature-badge">
                                        <i class="fas fa-users"></i>
                                        <?php echo $petition['nb_signatures']; ?> signature<?php echo $petition['nb_signatures'] > 1 ? 's' : ''; ?>
                                    </span>
                                </div>
                                
                                <p class="petition-description-my">
                                    <?php 
                                    $description = $petition['DescriptionP'];
                                    echo nl2br(htmlspecialchars(strlen($description) > 200 ? substr($description, 0, 200) . '...' : $description)); 
                                    ?>
                                </p>

                                <div class="petition-meta-my">
                                    <div class="meta-item-my">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Créée le <?php echo date('d/m/Y', strtotime($petition['DateAjoutP'])); ?></span>
                                    </div>
                                    <?php if ($petition['DateFinP']): ?>
                                        <div class="meta-item-my">
                                            <i class="fas fa-flag-checkered"></i>
                                            <span>Fin: <?php echo date('d/m/Y', strtotime($petition['DateFinP'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="meta-item-my">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo htmlspecialchars($petition['NomPorteurP']); ?></span>
                                    </div>
                                </div>

                                <div class="petition-actions">
                                    <a href="modifier_petition.php?id=<?php echo $petition['IDP']; ?>" class="btn btn-edit btn-sm">
                                        <i class="fas fa-edit me-1"></i>Modifier
                                    </a>
                                    <button class="btn btn-delete btn-sm" onclick="confirmDelete(<?php echo $petition['IDP']; ?>, '<?php echo htmlspecialchars(addslashes($petition['TitreP'])); ?>')">
                                        <i class="fas fa-trash-alt me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-petitions-box">
                    <div class="no-petitions-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p class="no-petitions-text">Vous n'avez créé aucune pétition pour le moment</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer la pétition :</p>
                    <p class="fw-bold" id="petitionTitleToDelete"></p>
                    <p class="text-danger">
                        <i class="fas fa-info-circle me-1"></i>
                        Cette action est irréversible et supprimera également toutes les signatures associées.
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" action="supprimer_petition.php" id="deleteForm">
                        <input type="hidden" name="id_petition" id="petitionIdToDelete">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i>Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        function confirmDelete(petitionId, petitionTitle) {
            document.getElementById('petitionIdToDelete').value = petitionId;
            document.getElementById('petitionTitleToDelete').textContent = petitionTitle;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
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