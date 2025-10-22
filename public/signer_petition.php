<?php
session_start();
require_once '../config/config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ListePetition.php');
    exit();
}

$id_petition = $_GET['id'];

$query = "SELECT * FROM Petition WHERE IDP = :id";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $id_petition]);
$petition = $stmt->fetch();

if (!$petition) {
    header('Location: ListePetition.php');
    exit();
}

$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
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

$nomS = $is_logged_in ? $_SESSION['Nom'] : '';
$prenomS = $is_logged_in ? $_SESSION['Prenom'] : '';
$emailS = $is_logged_in ? $_SESSION['Email'] : '';
$paysS = '';

$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

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
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="toast-container"></div>
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

    <section class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <a href="ListePetition.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>

            <div class="form-signature-card">

                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($already_signed): ?>
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
                <form method="POST" action="AjouterSignature.php" id="signatureForm">
                    <input type="hidden" name="id_petition" value="<?php echo $id_petition; ?>">
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

                    <div class="captcha-container">
                        <div class="g-recaptcha" data-sitekey="6LeSHvMrAAAAANAytvs46-abyJL1UL8e2DahvmFM"></div>
                    </div>

                    <button type="submit" class="btn btn-submit">
                        Envoyer ma signature
                    </button>
                </form>
                <?php endif; ?>

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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
<script>
        document.addEventListener('DOMContentLoaded', function() {
        initSignaturePage(<?php echo $id_petition; ?>);
        });
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