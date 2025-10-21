<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['IDU']) && isset($_SESSION['Email']);
$user_name = $is_logged_in ? ($_SESSION['Prenom'] ?? $_SESSION['Nom'] ?? 'Utilisateur') : null;
$user_role = $is_logged_in ? ($_SESSION['Role'] ?? 'user') : null;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container position-relative">
        <!-- Logo stylisé -->
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
                <!-- NOUVEAU: Lien Créer une pétition -->
                <?php if ($is_logged_in): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'creer_petition.php') ? 'active' : ''; ?>" href="creer_petition.php">
                            Créer une pétition
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
                    <!-- Menu déroulant pour utilisateur connecté -->
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
                    <!-- Boutons de connexion/inscription pour utilisateurs non connectés -->
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

<style>
/* Styles pour le dropdown utilisateur */
.dropdown-menu {
    min-width: 220px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    border: none;
    margin-top: 10px;
}

.dropdown-item {
    padding: 0.7rem 1.2rem;
    transition: all 0.2s ease;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.95rem;
}

.dropdown-item:hover {
    background-color: #f0f7ff;
    color: #0066cc;
    padding-left: 1.5rem;
}

.dropdown-item i {
    width: 20px;
    color: #0066cc;
}

.dropdown-item.text-danger:hover {
    background-color: #fff5f5;
    color: #dc3545;
}

.dropdown-item.text-danger i {
    color: #dc3545;
}

.dropdown-divider {
    margin: 0.5rem 0;
    border-color: #e9ecef;
}

/* Animation du dropdown */
.dropdown-menu {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 991px) {
    .nav-center {
        position: static;
        transform: none;
        margin-top: 1rem;
    }
    
    .navbar-nav {
        text-align: center;
    }
    
    .d-flex.ms-auto {
        margin-top: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .dropdown-menu {
        text-align: left;
    }
}
</style>