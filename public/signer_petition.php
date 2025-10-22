<?php
session_start();
require_once '../config/config.php';

// Vérifier si un ID de pétition est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ListePetition.php');
    exit();
}

$id_petition = $_GET['id'];

// Récupérer les informations de la pétition
$query = "SELECT * FROM Petition WHERE IDP = :id";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $id_petition]);
$petition = $stmt->fetch();

// Si la pétition n'existe pas, rediriger
if (!$petition) {
    header('Location: ListePetition.php');
    exit();
}

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Vérifier si l'utilisateur connecté a déjà signé cette pétition
$already_signed = false;
if ($is_logged_in) {
    $check_query = "SELECT * FROM Signature WHERE IDP = :idp AND EmailS = :email";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([
        'idp' => $id_petition, 
        'email' => $_SESSION['Email']
    ]);
    $already_signed = $check_stmt->fetch();
}

// Pré-remplir les champs si l'utilisateur est connecté
$nomS = $is_logged_in ? $_SESSION['Nom'] : '';
$prenomS = $is_logged_in ? $_SESSION['Prenom'] : '';
$emailS = $is_logged_in ? $_SESSION['Email'] : '';
$paysS = '';

// Afficher les messages d'erreur depuis la session
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Compter les signatures
$count_query = "SELECT COUNT(*) as total FROM Signature WHERE IDP = :id";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute(['id' => $id_petition]);
$count_result = $count_stmt->fetch();
$total_signatures = $count_result['total'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetitionHub - Signer la Pétition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800;900&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .signature-hero {
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            padding: 60px 0 40px;
            color: white;
            text-align: center;
        }

        .signature-hero-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .signature-count-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1rem;
        }

        .form-signature-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-signature-card:hover {
            border-color: #0066cc;
            box-shadow: 0 8px 25px rgba(0, 102, 204, 0.15);
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            border-radius: 2px;
        }

        .petition-info-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .petition-title-display {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.4rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            text-align: center;
        }

        .petition-description-display {
            font-family: 'Montserrat', sans-serif;
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 1rem;
            text-align: center;
        }

        .petition-meta-display {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
        }

        .meta-item-display {
            display: flex;
            align-items: center;
            color: #6c757d;
        }

        .meta-item-display i {
            color: #0066cc;
            margin-right: 0.5rem;
        }

        .recent-signatures-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 2px solid #e9ecef;
        }

        .recent-signatures-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            text-align: center;
        }

        .signature-item {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid white;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .signature-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0, 102, 204, 0.1);
        }

        .signature-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .signature-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
        }

        .signature-details {
            font-family: 'Montserrat', sans-serif;
        }

        .signature-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.2rem;
        }

        .signature-location {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .signature-date {
            font-size: 0.8rem;
            color: #adb5bd;
            font-family: 'Montserrat', sans-serif;
        }

        .no-signatures {
            text-align: center;
            color: #6c757d;
            font-family: 'Montserrat', sans-serif;
            padding: 2rem;
            font-style: italic;
        }

        .loading-spinner {
            text-align: center;
            padding: 2rem;
            color: #0066cc;
        }

        .personal-info-section {
            margin-top: 2rem;
        }

        .form-label {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
        }

        .form-control:read-only {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
        }

        .form-control:read-only:focus {
            border-color: #dee2e6;
            box-shadow: none;
        }

        .captcha-container {
            display: flex;
            justify-content: center;
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 0.9rem 2.5rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
            margin-top: 1.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 102, 204, 0.4);
        }

        .btn-back {
            color: #0066cc;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            color: #0099ff;
            transform: translateX(-5px);
        }

        .btn-back i {
            margin-right: 0.5rem;
        }

        .alert {
            border-radius: 12px;
            font-family: 'Montserrat', sans-serif;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .already-signed-section {
            text-align: center;
            padding: 3rem 2rem;
        }

        .already-signed-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1.5rem;
        }

        .already-signed-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .already-signed-message {
            font-family: 'Montserrat', sans-serif;
            color: #6c757d;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        @media (max-width: 767px) {
            .signature-hero-title {
                font-size: 1.5rem;
            }

            .form-signature-card {
                padding: 1.5rem;
            }

            .section-title {
                font-size: 1.3rem;
            }

            .petition-title-display {
                font-size: 1.2rem;
            }

            .petition-meta-display {
                flex-direction: column;
                gap: 0.8rem;
                align-items: center;
            }

            .signature-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .captcha-container {
                transform: scale(0.85);
                transform-origin: center;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Hero Section -->
    <div class="signature-hero">
        <div class="container">
            <h1 class="signature-hero-title">
                <?php echo $already_signed ? 'Pétition déjà signée' : 'Signez cette Pétition'; ?>
            </h1>
            <div class="signature-count-badge">
                <i class="fas fa-users me-2"></i><?php echo $total_signatures; ?> signature<?php echo $total_signatures > 1 ? 's' : ''; ?>
            </div>
        </div>
    </div>

    <!-- Contenu Principal -->
    <section class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <a href="ListePetition.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>

            <!-- Formulaire Centré -->
            <div class="form-signature-card">

                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($already_signed): ?>
                    <!-- Section pour utilisateur ayant déjà signé -->
                    <div class="already-signed-section">
                        <div class="already-signed-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="already-signed-title">Merci pour votre signature !</h2>
                        <p class="already-signed-message">
                            Vous avez déjà signé la pétition <strong>"<?php echo htmlspecialchars($petition['TitreP']); ?>"</strong>.<br>
                            Votre soutien a été enregistré et compte parmi les <strong><?php echo $total_signatures; ?> signature(s)</strong> collectées.
                        </p>
                    </div>

                <?php else: ?>
                <!-- Formulaire de signature normal -->
                <form method="POST" action="AjouterSignature.php" id="signatureForm">
                    <!-- Champ caché pour l'ID de la pétition -->
                    <input type="hidden" name="id_petition" value="<?php echo $id_petition; ?>">
                    
                    <!-- Section 1: Informations de la Pétition -->
                    <h3 class="section-title">
                        Pétition
                    </h3>

                    <div class="petition-info-section">
                        <h4 class="petition-title-display"><?php echo htmlspecialchars($petition['TitreP']); ?></h4>
                        
                        <p class="petition-description-display">
                            <?php echo nl2br(htmlspecialchars($petition['DescriptionP'])); ?>
                        </p>

                        <div class="petition-meta-display">
                            <div class="meta-item-display">
                                <i class="fas fa-user"></i>
                                <span><strong>Porteur:</strong> <?php echo htmlspecialchars($petition['NomPorteurP']); ?></span>
                            </div>
                            <div class="meta-item-display">
                                <i class="fas fa-calendar-alt"></i>
                                <span><strong>Créée le:</strong> <?php echo date('d/m/Y', strtotime($petition['DateAjoutP'])); ?></span>
                            </div>
                            <?php if ($petition['DateFinP']): ?>
                                <div class="meta-item-display">
                                    <i class="fas fa-flag-checkered"></i>
                                    <span><strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($petition['DateFinP'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Section 2: Informations Personnelles -->
                    <div class="personal-info-section">
                        <h3 class="section-title">
                            Vos Informations
                        </h3>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nomS" class="form-label">
                                    <i class="fas fa-user me-1" style="color: #0066cc;"></i>Nom *
                                </label>
                                <input type="text" class="form-control" id="nomS" name="nomS" 
                                       placeholder="Votre nom" required
                                       value="<?php echo htmlspecialchars($nomS); ?>"
                                       <?php echo $is_logged_in ? 'readonly' : ''; ?>>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="prenomS" class="form-label">
                                    <i class="fas fa-user me-1" style="color: #0066cc;"></i>Prénom *
                                </label>
                                <input type="text" class="form-control" id="prenomS" name="prenomS" 
                                       placeholder="Votre prénom" required
                                       value="<?php echo htmlspecialchars($prenomS); ?>"
                                       <?php echo $is_logged_in ? 'readonly' : ''; ?>>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="emailS" class="form-label">
                                    <i class="fas fa-envelope me-1" style="color: #0066cc;"></i>Email *
                                </label>
                                <input type="email" class="form-control" id="emailS" name="emailS" 
                                       placeholder="votre.email@exemple.com" required
                                       value="<?php echo htmlspecialchars($emailS); ?>"
                                       <?php echo $is_logged_in ? 'readonly' : ''; ?>>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="paysS" class="form-label">
                                    <i class="fas fa-globe me-1" style="color: #0066cc;"></i>Pays *
                                </label>
                                <input type="text" class="form-control" id="paysS" name="paysS" 
                                       placeholder="Votre pays" required
                                       value="<?php echo htmlspecialchars($paysS); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Section CAPTCHA -->
                    <div class="captcha-container">
                        <div class="g-recaptcha" data-sitekey="6LeSHvMrAAAAANAytvs46-abyJL1UL8e2DahvmFM"></div>
                    </div>

                    <button type="submit" class="btn btn-submit">
                        Envoyer ma signature
                    </button>
                </form>
                <?php endif; ?>

                <!-- Section des Signatures Récentes -->
                <div class="recent-signatures-section">
                    <h3 class="recent-signatures-title">
                        Dernières Signatures
                    </h3>
                    <div id="recentSignaturesContainer">
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Chargement des signatures...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validation du formulaire avec CAPTCHA
        document.getElementById('signatureForm')?.addEventListener('submit', function(e) {
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                e.preventDefault();
                alert('Veuillez compléter le CAPTCHA avant de continuer.');
                return false;
            }
        });

        // Fonction pour charger les signatures récentes
        function loadRecentSignatures() {
            const petitionId = <?php echo $id_petition; ?>;
            const container = document.getElementById('recentSignaturesContainer');
            
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success && response.signatures.length > 0) {
                                let html = '';
                                
                                response.signatures.forEach(function(signature) {
                                    const initial = signature.PrenomS.charAt(0).toUpperCase();
                                    const date = new Date(signature.DateS);
                                    const dateFormatted = date.toLocaleDateString('fr-FR', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                    
                                    html += `
                                        <div class="signature-item">
                                            <div class="signature-info">
                                                <div class="signature-avatar">${initial}</div>
                                                <div class="signature-details">
                                                    <div class="signature-name">
                                                        ${signature.PrenomS} ${signature.NomS}
                                                    </div>
                                                    <div class="signature-location">
                                                        <i class="fas fa-map-marker-alt me-1"></i>${signature.PaysS}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="signature-date">
                                                ${dateFormatted}
                                            </div>
                                        </div>
                                    `;
                                });
                                
                                container.innerHTML = html;
                            } else {
                                container.innerHTML = '<div class="no-signatures"><i class="fas fa-inbox fa-2x mb-2"></i><p>Aucune signature pour le moment. Soyez le premier à signer !</p></div>';
                            }
                        } catch (e) {
                            container.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des signatures</div>';
                        }
                    } else {
                        container.innerHTML = '<div class="alert alert-danger">Erreur de connexion au serveur</div>';
                    }
                }
            };
            
            xhr.open('GET', 'get_recent_signatures.php?id=' + petitionId, true);
            xhr.send();
        }
        
        // Charger les signatures au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadRecentSignatures();
            setInterval(loadRecentSignatures, 10000);
        });
    </script>
</body>
</html>