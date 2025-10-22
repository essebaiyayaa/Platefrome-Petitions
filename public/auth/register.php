<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Inclure le fichier de configuration
require_once '../../config/config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($nom)) $errors[] = "Le nom est requis";
    if (empty($prenom)) $errors[] = "Le prénom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if (strlen($password) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";

    if (empty($errors)) {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT IDU FROM Utilisateur WHERE Email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé";
            } else {
                // Hash du mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insertion
                $stmt = $pdo->prepare("INSERT INTO Utilisateur (Nom, Prenom, Email, MotDePasse, Role, DateInscription, Actif) VALUES (?, ?, ?, ?, 'user', NOW(), 1)");
                $stmt->execute([$nom, $prenom, $email, $hashed_password]);

                // Récupérer l'ID du nouvel utilisateur
                $user_id = $pdo->lastInsertId();

                // Connexion automatique - Créer la session avec les bons noms de variables
                $_SESSION['IDU'] = $user_id;
                $_SESSION['Email'] = $email;
                $_SESSION['Nom'] = $nom;
                $_SESSION['Prenom'] = $prenom;
                $_SESSION['Role'] = 'user';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();

                // Redirection immédiate vers la page d'accueil
                header('Location: ../home.php?welcome=1');
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - PetitionHub</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .register-container {
            max-width: 600px;
            width: 100%;
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .back-home:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateX(-5px);
        }

        .register-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .register-logo {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            line-height: 0.9;
        }

        .petition-text {
            display: block;
            color: white;
        }

        .hub-text {
            display: block;
            color: #e6f3ff;
        }

        .register-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95rem;
            opacity: 0.95;
        }

        .register-body {
            padding: 2.5rem;
        }

        .form-label {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
            padding-left: 45px;
        }

        .form-control:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #0066cc;
            z-index: 10;
            font-size: 1rem;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            z-index: 10;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #0066cc;
        }

        .strength-meter {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: -0.75rem;
            margin-bottom: 1.25rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .strength-meter.weak {
            background: linear-gradient(90deg, #dc3545 0%, #dc3545 33%, #e0e0e0 33%);
        }

        .strength-meter.medium {
            background: linear-gradient(90deg, #ffc107 0%, #ffc107 66%, #e0e0e0 66%);
        }

        .strength-meter.strong {
            background: linear-gradient(90deg, #28a745 0%, #28a745 100%);
        }

        .alert {
            border-radius: 12px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
        }

        .alert-success {
            background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
            color: white;
        }

        .alert ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
        }

        .login-link {
            text-align: center;
            margin: 1.5rem 0;
            font-family: 'Montserrat', sans-serif;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .login-link a {
            color: #0066cc;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #0099ff;
            text-decoration: underline;
        }

        .btn-register {
            width: 100%;
            padding: 0.9rem;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.4);
        }

        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .register-body {
                padding: 2rem 1.5rem;
            }

            .register-header {
                padding: 1.5rem;
            }

            .register-logo {
                font-size: 1.6rem;
            }

            .back-home {
                position: static;
                margin-bottom: 1rem;
                display: inline-flex;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <a href="../home.php" class="back-home">
            <i class="fas fa-arrow-left"></i>
            Retour à l'accueil
        </a>

        <div class="register-card">
            <div class="register-header">
                <div class="register-logo">
                    <span class="petition-text">Petition</span>
                    <span class="hub-text">Hub</span>
                </div>
                <p class="register-subtitle">Créez votre compte pour lancer vos pétitions</p>
            </div>

            <div class="register-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Erreur !</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="nom" class="form-label">
                                <i class="fas fa-user me-1"></i> Nom
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="nom" name="nom" placeholder="Votre nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="prenom" class="form-label">
                                <i class="fas fa-user me-1"></i> Prénom
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Votre prénom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-1"></i> Email
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="votre.email@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i> Mot de passe
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        <span class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </span>
                    </div>
                    <div class="strength-meter" id="strengthMeter"></div>

                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-lock me-1"></i> Confirmer le mot de passe
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                        <span class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </span>
                    </div>

                    <div class="login-link">
                        Vous avez déjà un compte ? 
                        <a href="login.php">Connectez-vous</a>
                    </div>

                    <button type="submit" class="btn btn-register" id="submitBtn">
                        S'inscrire
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <small style="color: rgba(255, 255, 255, 0.9);">
                © 2025 PetitionHub. Tous droits réservés.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Password strength meter
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const meter = document.getElementById('strengthMeter');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            meter.className = 'strength-meter';
            if (strength >= 1 && strength <= 2) {
                meter.classList.add('weak');
            } else if (strength === 3) {
                meter.classList.add('medium');
            } else if (strength >= 4) {
                meter.classList.add('strong');
            }
        });

        // Animation au focus des inputs
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Désactiver le bouton pendant la soumission
        document.getElementById('registerForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Inscription en cours...';
        });
    </script>
</body>
</html>