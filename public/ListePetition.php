<?php
session_start();
require_once '../config/config.php';

$query = "SELECT p.*, COUNT(DISTINCT s.IDS) as nb_signatures 
          FROM Petition p 
          LEFT JOIN Signature s ON p.IDP = s.IDP 
          GROUP BY p.IDP 
          ORDER BY p.DateAjoutP DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$petitions = $stmt->fetchAll();

$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetitionHub - Liste des Pétitions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800;900&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="toast-container"></div>
    <div class="petition-list-hero">
        <div class="container">
            <h1 class="petition-list-title">Découvrez les Pétitions</h1>
            <p class="petition-list-subtitle">
                Explorez toutes les causes et faites entendre votre voix pour le changement
            </p>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <div id="topPetitionBanner" class="top-petition-banner" style="display: none;">
                <div class="top-petition-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="top-petition-content">
                    <div class="top-petition-label">La plus populaire</div>
                    <div class="top-petition-title" id="topPetitionTitle">-</div>
                    <div class="top-petition-signatures">
                        <span class="number" id="topPetitionCount">0</span> signatures
                    </div>
                </div>
            </div>

            <?php if (count($petitions) > 0): ?>
                <div class="row">
                    <div class="col-12">
                        <?php foreach ($petitions as $petition): ?>
                            <div class="petition-card">
                                <h2 class="petition-title"><?php echo htmlspecialchars($petition['TitreP']); ?></h2>
                                
                                <p class="petition-description">
                                    <?php echo nl2br(htmlspecialchars($petition['DescriptionP'])); ?>
                                </p>

                                <div class="petition-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo htmlspecialchars($petition['NomPorteurP']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?php echo date('d/m/Y', strtotime($petition['DateAjoutP'])); ?></span>
                                    </div>
                                    <?php if ($petition['DateFinP']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-flag-checkered"></i>
                                            <span>Fin: <?php echo date('d/m/Y', strtotime($petition['DateFinP'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="petition-footer">
                                    <div class="signature-count">
                                        <span class="number"><?php echo $petition['nb_signatures']; ?></span>
                                        <span>signature<?php echo $petition['nb_signatures'] > 1 ? 's' : ''; ?></span>
                                    </div>
                                    <?php if ($is_logged_in): ?>
                                        <a href="signer_petition.php?id=<?php echo $petition['IDP']; ?>" class="btn btn-sign">
                                            Signer cette pétition
                                        </a>
                                    <?php else: ?>
                                        <a href="auth/login.php?redirect=<?php echo urlencode('../signer_petition.php?id=' . $petition['IDP']); ?>" class="btn btn-sign">
                                            Signer cette pétition
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-petitions">
                    <div class="no-petitions-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p class="no-petitions-text">Aucune pétition disponible pour le moment</p>
                    <?php if ($is_logged_in): ?>
                        <a href="creer_petition.php" class="btn btn-create-hero">
                            <i class="fas fa-plus me-2"></i>Créer une pétition
                        </a>
                    <?php else: ?>
                        <a href="auth/login.php?redirect=<?php echo urlencode('../creer_petition.php'); ?>" class="btn btn-create-hero">
                            <i class="fas fa-plus me-2"></i>Créer une pétition
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const initialLastId = <?php 
            if (count($petitions) > 0) {
                echo intval($petitions[0]['IDP']);
            } else {
                echo '0';
            }
        ?>;
        initPetitionList(initialLastId);
    });
</script>
</body>
</html>