<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
        $redirect = $_GET['redirect'];
        if (!preg_match('#^https?://#i', $redirect)) {
            header('Location: ' . $redirect);
            exit();
        }
    }
    if ($_SESSION['Role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: ../home.php');
    }
    exit();
}
$redirect_url = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '';
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
    <link rel="stylesheet" href="../assets/css/styles.css">
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <a href="../home.php" class="back-home">
            <i class="fas fa-arrow-left"></i>
            Retour à l'accueil
        </a>
            <h1><i class="fas fa-sign-in-alt me-2"></i>Connexion</h1>
            <p>Accédez à votre espace PetitionHub</p>
        </div>
        <div class="login-body">
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="process_login.php">
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
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </button>
            </form>

            <div class="login-footer">
                <p class="mb-0">Pas encore de compte ? <a href="register.php<?php echo $redirect_url ? '?redirect=' . urlencode($redirect_url) : ''; ?>">Créer un compte</a></p>
            </div>
        </div>
    </div>
    <script>
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