<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = isset($_SESSION['IDU']) && isset($_SESSION['Email']);
$user_name = $is_logged_in ? ($_SESSION['Prenom'] ?? $_SESSION['Nom'] ?? 'Utilisateur') : null;
$user_role = $is_logged_in ? ($_SESSION['Role'] ?? 'user') : null;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container position-relative">
        <div class="logo">
            <div class="logo-container">
                <div class="logo-text">
                    <span class="petition">Petition</span>
                    <br>
                    <span class="hub">Hub</span>
                </div>
            </div>
        </div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav nav-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'home.php') ? 'active' : ''; ?>" href="home.php">
                        Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'ListePetition.php') ? 'active' : ''; ?>" href="ListePetition.php">
                        Pétitions
                    </a>
                </li>
                <?php if ($is_logged_in): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'mes_petitions.php') ? 'active' : ''; ?>" href="mes_petitions.php">
                Mes Pétitions
            </a>
        </li>
    <?php else: ?>
        <li class="nav-item">
            <a class="nav-link" href="auth/login.php?redirect=<?php echo urlencode('../creer_petition.php'); ?>">
                Créer une pétition
            </a>
        </li>
    <?php endif; ?>
            </ul>
            <div class="d-flex ms-auto align-items-center">
                <?php if ($is_logged_in): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($user_name); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user me-2"></i>Mon profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="ListePetition.php">
                                    <i class="fas fa-file-signature me-2"></i>Pétitions
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="creer_petition.php">
                                    <i class="fas fa-plus me-2"></i>Créer une pétition
                                </a>
                            </li>
                            <?php if ($user_role === 'admin'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="admin/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Administration
                                </a>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Se déconnecter
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-outline-primary me-2">
                       Se connecter
                    </a>
                    <a href="auth/register.php" class="btn btn-outline-primary">
                       S'inscrire
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
