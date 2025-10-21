<?php
session_start();
require_once '../config/config.php';

// Vérifier si l'utilisateur est connecté
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
            $query = "INSERT INTO Petition (TitreP, DescriptionP, NomPorteurP, DateAjoutP, DateFinP) 
                      VALUES (:titre, :description, :nomPorteur, NOW(), :dateFin)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':titre' => $titre,
                ':description' => $description,
                ':nomPorteur' => $nomPorteur,
                ':dateFin' => $dateFin ?: null
            ]);
            
            $petitionId = $pdo->lastInsertId();
            
            $success = 'Pétition créée avec succès !';
            
            // Redirection après 2 secondes
            header("refresh:2;url=ListePetition.php");
        } catch (PDOException $e) {
            $error = 'Erreur lors de la création de la pétition.';
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
    <style>
        .create-petition-container {
            max-width: 800px;
            margin: 4rem auto;
            padding: 0 1rem;
        }

        .create-card {
            background: #fff;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .create-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .create-subtitle {
            font-family: 'Montserrat', sans-serif;
            color: #6c757d;
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-label {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.1);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .btn-create {
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.3);
        }

        .alert {
            border-radius: 12px;
            border: none;
            font-family: 'Montserrat', sans-serif;
        }

        .required {
            color: #dc3545;
        }

        @media (max-width: 767px) {
            .create-card {
                padding: 2rem 1.5rem;
            }

            .create-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="create-petition-container">
        <div class="create-card">
            <h1 class="create-title">Créer une Pétition</h1>
            <p class="create-subtitle">Lancez votre mouvement pour le changement</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
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
</body>
</html>