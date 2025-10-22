<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: auth/login.php?redirect=' . urlencode('../creer_petition.php'));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $nomPorteur = trim($_POST['nomPorteur'] ?? '');
    $dateFin = $_POST['dateFin'] ?? null;
    
    if (empty($titre) || empty($description) || empty($nomPorteur)) {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    } else {
        try {
            $query = "INSERT INTO Petition (TitreP, DescriptionP, NomPorteurP, Email, DateFinP, IDU) 
                      VALUES (:titre, :description, :nom_porteur, :email, :date_fin, :user_id)";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':titre' => $titre,
                ':description' => $description,
                ':nom_porteur' => $nomPorteur,           
                ':email' => $_SESSION['Email'],          
                ':date_fin' => $dateFin ?: null,        
                ':user_id' => $_SESSION['IDU']          
            ]);
            $petitionId = $pdo->lastInsertId();
            $_SESSION['success_message'] = 'Pétition créée avec succès !';
            header("Location: mes_petitions.php");
            exit();
            
        } catch (PDOException $e) {
            $error = 'Erreur lors de la création de la pétition : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetitionHub - Créer une Pétition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800;900&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="toast-container"></div>
    <div class="create-petition-container">
        <div class="create-card">
            <h1 class="create-title">Créer une Pétition</h1>
            <p class="create-subtitle">Lancez votre mouvement pour le changement</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <br><small>Redirection en cours...</small>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="titre" class="form-label">
                        Titre de la pétition <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="titre" 
                           name="titre" 
                           placeholder="Ex: Pour un environnement plus propre"
                           required
                           value="<?php echo htmlspecialchars($_POST['titre'] ?? ''); ?>">
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">
                        Description <span class="required">*</span>
                    </label>
                    <textarea class="form-control" 
                              id="description" 
                              name="description" 
                              placeholder="Décrivez votre cause et pourquoi elle est importante..."
                              required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="nomPorteur" class="form-label">
                        Nom du porteur <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="nomPorteur" 
                           name="nomPorteur" 
                           placeholder="Votre nom ou le nom de votre organisation"
                           required
                           value="<?php echo htmlspecialchars($_POST['nomPorteur'] ?? ''); ?>">
                </div>

                <div class="mb-4">
                    <label for="dateFin" class="form-label">
                        Date de fin (optionnel)
                    </label>
                    <input type="date" 
                           class="form-control" 
                           id="dateFin" 
                           name="dateFin"
                           min="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo htmlspecialchars($_POST['dateFin'] ?? ''); ?>">
                    <small class="text-muted">Laissez vide pour une pétition sans date limite</small>
                </div>

                <button type="submit" class="btn btn-create">
                    <i class="fas fa-paper-plane me-2"></i>Créer la Pétition
                </button>
            </form>
        </div>
    </div>
    
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
            initPetitionList(<?php echo $last_id; ?>);
        });
    </script>
</body>
</html>