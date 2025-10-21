<?php
session_start();
require_once '../config/config.php';

// Récupérer toutes les pétitions, les plus récentes en premier
$query = "SELECT p.*, COUNT(DISTINCT s.IDS) as nb_signatures 
          FROM Petition p 
          LEFT JOIN Signature s ON p.IDP = s.IDP 
          GROUP BY p.IDP 
          ORDER BY p.DateAjoutP DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$petitions = $stmt->fetchAll();

// Vérifier si l'utilisateur est connecté
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
    <style>
        .petition-list-hero {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 80px 0 60px;
            text-align: center;
        }

        .petition-list-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .petition-list-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.1rem;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Bannière pétition la plus populaire */
        .top-petition-banner {
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 102, 204, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .top-petition-icon {
            font-size: 2.5rem;
            margin-right: 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        .top-petition-content {
            flex: 1;
        }

        .top-petition-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 0.3rem;
        }

        .top-petition-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
        }

        .top-petition-signatures {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .top-petition-signatures .number {
            font-size: 1.5rem;
            font-weight: 800;
        }

        /* Toast notification */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
        }

        .custom-toast {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            border-left: 4px solid #0066cc;
            min-width: 350px;
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast-header {
           
            color: white;
            border-radius: 8px 8px 0 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }

        .toast-body {
            font-family: 'Montserrat', sans-serif;
            padding: 1rem;
        }

        .petition-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .petition-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .petition-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 102, 204, 0.2);
        }

        .petition-card:hover::before {
            transform: scaleY(1);
        }

        .petition-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .petition-description {
            font-family: 'Montserrat', sans-serif;
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .petition-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            color: #6c757d;
        }

        .meta-item i {
            color: #0066cc;
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .petition-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .signature-count {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .signature-count .number {
            color: #0066cc;
            font-size: 1.3rem;
        }

        .btn-sign {
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 0.7rem 2rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
            text-decoration: none;
            display: inline-block;
        }

        .btn-sign:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.3);
            color: #fff;
        }

        .no-petitions {
            text-align: center;
            padding: 4rem 2rem;
        }

        .no-petitions-icon {
            font-size: 4rem;
            color: #0066cc;
            margin-bottom: 1.5rem;
        }

        .no-petitions-text {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .btn-create-hero {
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.9rem 2.5rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.3);
            text-decoration: none;
            display: inline-block;
        }

        .btn-create-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 102, 204, 0.4);
            color: white;
        }

        @media (max-width: 767px) {
            .petition-list-title {
                font-size: 1.8rem;
            }

            .petition-card {
                padding: 1.5rem;
            }

            .petition-title {
                font-size: 1.2rem;
            }

            .petition-footer {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-sign {
                width: 100%;
            }

            .top-petition-banner {
                flex-direction: column;
                text-align: center;
            }

            .top-petition-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .custom-toast {
                min-width: 300px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Toast Container for Notifications -->
    <div class="toast-container"></div>
    
    <!-- Hero Section -->
    <div class="petition-list-hero">
        <div class="container">
            <h1 class="petition-list-title">Découvrez les Pétitions</h1>
            <p class="petition-list-subtitle">
                Explorez toutes les causes et faites entendre votre voix pour le changement
            </p>
            <?php if ($is_logged_in): ?>
                <a href="creer_petition.php" class="btn btn-create-hero mt-3">
                    <i class="fas fa-plus-circle me-2"></i>Créer une pétition
                </a>
            <?php else: ?>
                <a href="auth/login.php?redirect=<?php echo urlencode('../creer_petition.php'); ?>" class="btn btn-create-hero mt-3">
                    <i class="fas fa-plus-circle me-2"></i>Créer une pétition
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Liste des Pétitions -->
    <section class="py-5">
        <div class="container">
            <!-- Bannière pétition la plus populaire -->
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
    <script>
        // ========================================
        // VARIABLES GLOBALES
        // ========================================
        let lastSeenPetitionId = <?php 
            if (count($petitions) > 0) {
                echo intval($petitions[0]['IDP']);
            } else {
                echo '0';
            }
        ?>;
        
        // ========================================
        // FONCTION 1: Afficher une notification toast
        // ========================================
        function showToast(title, message) {
            const toastContainer = document.querySelector('.toast-container');
            const toastId = 'toast-' + Date.now();
            
            const toastHTML = `
                <div id="${toastId}" class="toast custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <i class="fas fa-bell me-2"></i>
                        <strong class="me-auto">${title}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            const toastElement = document.getElementById(toastId);
            
            const toast = new bootstrap.Toast(toastElement, { 
                autohide: false  // Ne disparaît pas automatiquement
            });
            
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });
        }

        // ========================================
        // FONCTION 2: Charger la pétition la plus populaire (AJAX)
        // ========================================
        function loadTopPetition() {
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success && response.petition) {
                                document.getElementById('topPetitionTitle').textContent = response.petition.titre;
                                document.getElementById('topPetitionCount').textContent = response.petition.signatures;
                                document.getElementById('topPetitionBanner').style.display = 'flex';
                            }
                        } catch (e) {
                            console.error('Erreur parsing JSON (loadTopPetition):', e);
                        }
                    } else {
                        console.error('Erreur AJAX loadTopPetition:', xhr.status);
                    }
                }
            };
            
            xhr.open('GET', 'get_top_petition.php', true);
            xhr.send();
        }

        // ========================================
        // FONCTION 3: Vérifier les nouvelles pétitions (AJAX Polling)
        // ========================================
        function checkNewPetitions() {
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success && response.hasNew) {
                                const petition = response.petition;
                                
                                console.log('Nouvelle pétition détectée: ID ' + petition.id);
                                
                                // Afficher la notification immédiatement
                                showToast(
                                    'Nouvelle Pétition !',
                                    '<strong>' + escapeHtml(petition.titre) + '</strong><br>' +
                                    '<small>Par ' + escapeHtml(petition.nomPorteur) + '</small><br>' +
                                    '<em class="text-muted" style="font-size: 0.85rem;">La page va se recharger dans 5 secondes...</em>'
                                );
                                
                                // Mettre à jour l'ID pour éviter les doublons
                                lastSeenPetitionId = petition.id;
                                
                                // Recharger la page après 5 secondes
                                setTimeout(function() {
                                    location.reload();
                                }, 5000);
                            }
                        } catch (e) {
                            console.error('Erreur parsing JSON (checkNewPetitions):', e);
                        }
                    } else {
                        console.error('Erreur AJAX checkNewPetitions:', xhr.status);
                    }
                }
            };
            
            xhr.open('GET', 'check_new_petitions.php?last_id=' + lastSeenPetitionId, true);
            xhr.send();
        }

        // ========================================
        // FONCTION UTILITAIRE: Échapper le HTML
        // ========================================
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // ========================================
        // INITIALISATION AU CHARGEMENT DE LA PAGE
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Système AJAX initialisé');
            console.log('Dernier ID pétition vu: ' + lastSeenPetitionId);
            
            // Charger la pétition la plus populaire immédiatement
            loadTopPetition();
            
            // Premier check après 3 secondes
            setTimeout(checkNewPetitions, 3000);
            
            // Vérifier les nouvelles pétitions toutes les 10 secondes
            setInterval(checkNewPetitions, 10000);
            
            // Actualiser la pétition populaire toutes les 30 secondes
            setInterval(loadTopPetition, 30000);
        });
    </script>
</body>
</html>