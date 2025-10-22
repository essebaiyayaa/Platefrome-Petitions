<?php
session_start();

// Si l'utilisateur est déjà connecté, rediriger
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Vérifier s'il y a une redirection demandée
    if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
        $redirect = $_GET['redirect'];
        if (!preg_match('#^https?://#i', $redirect)) {
            header('Location: ' . $redirect);
            exit();
        }
    }
    
    // Sinon, redirection par défaut
    if ($_SESSION['Role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: ../home.php');
    }
    exit();
}

// Récupérer le paramètre de redirection
$redirect_url = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '';

// Récupérer les messages d'erreur
$error = isset($_GET['error']) ? $_GET['error'] : '';

$error_messages = [
    'empty' => 'Veuillez remplir tous les champs.',
    'invalid_email' => 'Adresse email invalide.',
    'invalid' => 'Email ou mot de passe incorrect.',
    'inactive' => 'Votre compte est désactivé. Contactez l\'administrateur.',
    'system' => 'Une erreur système est survenue. Réessayez plus tard.'
];

$error_message = isset($error_messages[$error]) ? $error_messages[$error] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetitionHub - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800;900&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
        }

        .login-header h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .login-body {
            padding: 2.5rem 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #0066cc;
        }

        .btn-login {
            background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 0.9rem;
            font-weight: 700;
            font-size: 1.05rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .login-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
            margin-top: 1.5rem;
        }

        .login-footer a {
            color: #0066cc;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #0099ff;
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

        .back-link i {
            margin-right: 0.5rem;
        }

        @media (max-width: 576px) {
            .login-header h1 {
                font-size: 1.6rem;
            }

            .login-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <a href="../home.php" class="back-home">
            <i class="fas fa-arrow-left"></i>
            Retour à l'accueil
        </a>
            <h1>Connexion</h1>
            <p>Accédez à votre espace PetitionHub</p>
        </div>

        <div class="login-body">
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="process_login.php">
                <!-- Champ caché pour la redirection -->
                <?php if ($redirect_url): ?>
                    <input type="hidden" name="redirect" value="<?php echo $redirect_url; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-1" style="color: #0066cc;"></i>Adresse Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="votre.email@exemple.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1" style="color: #0066cc;"></i>Mot de passe
                    </label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="••••••••" required>
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Se connecter
                </button>
            </form>

            <div class="login-footer">
                <p class="mb-0">Pas encore de compte ? <a href="register.php<?php echo $redirect_url ? '?redirect=' . urlencode($redirect_url) : ''; ?>">Créer un compte</a></p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>