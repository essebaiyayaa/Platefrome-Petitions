<?php
require_once '../config/config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetitionHub - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <section class="hero-fullscreen d-flex align-items-center">
        <div class="container text-center">
            <h1 class="hero-title mb-3">
                <span class="d-block">Pétition Hub – L’application web de la</span>
                <span class="hero-highlight">GESTION DES PÉTITIONS ET DES SIGNATURES</span>
                <span class="d-block">qui facilite la participation citoyenne</span>
            </h1>

            <p class="hero-lead mx-auto mb-4">
                Créez, signez et partagez des pétitions pour défendre vos causes.
                Grâce à cette interface intuitive, PetitionHub vous garantit une gestion
                simple, rapide et efficace de vos actions citoyennes.
            </p>

            <a href="ListePetition.php" class="btn btn-hero btn-lg">
                Voir les <em>pétitions</em>
            </a>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-5 section-title">Comment ça marche</h2>
            <div class="row g-4">
                <div class="col-md-3 text-center">
                    <div class="step-card p-4 bg-white rounded shadow-sm">
                        <div class="step-number mx-auto mb-3 d-flex align-items-center justify-content-center">
                            <span class="h4 mb-0">1</span>
                        </div>
                        <div class="step-icon mb-3">
                            <i class="fas fa-pencil-alt fa-2x"></i>
                        </div>
                        <h5 class="step-title">Créer une pétition</h5>
                        <p class="text-muted">Lancez votre pétition en quelques clics et mobilisez des milliers de personnes</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="step-card p-4 bg-white rounded shadow-sm">
                        <div class="step-number mx-auto mb-3 d-flex align-items-center justify-content-center">
                            <span class="h4 mb-0">2</span>
                        </div>
                        <div class="step-icon mb-3">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                        <h5 class="step-title">Consulter les pétitions</h5>
                        <p class="text-muted">Découvrez toutes les causes qui comptent pour votre communauté</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="step-card p-4 bg-white rounded shadow-sm">
                        <div class="step-number mx-auto mb-3 d-flex align-items-center justify-content-center">
                            <span class="h4 mb-0">3</span>
                        </div>
                        <div class="step-icon mb-3">
                            <i class="fas fa-signature fa-2x"></i>
                        </div>
                        <h5 class="step-title">Signer une pétition</h5>
                        <p class="text-muted">Apportez votre soutien aux causes qui vous tiennent à cœur</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="step-card p-4 bg-white rounded shadow-sm">
                        <div class="step-number mx-auto mb-3 d-flex align-items-center justify-content-center">
                            <span class="h4 mb-0">4</span>
                        </div>
                        <div class="step-icon mb-3">
                            <i class="fas fa-bullhorn fa-2x"></i>
                        </div>
                        <h5 class="step-title">Partager et mobiliser</h5>
                        <p class="text-muted">Partagez les pétitions pour maximiser leur impact</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="why-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Pourquoi choisir PetitionHub ?</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-shield-alt feature-icon"></i>
                        </div>
                        <div class="feature-content">
                            <h5 class="feature-title">Signatures sécurisées</h5>
                            <p class="feature-text">Validation par email et protection contre les signatures frauduleuses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-chart-line feature-icon"></i>
                        </div>
                        <div class="feature-content">
                            <h5 class="feature-title">Suivi en temps réel</h5>
                            <p class="feature-text">Consultez le nombre de signatures et l'évolution de votre pétition</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-bell feature-icon"></i>
                        </div>
                        <div class="feature-content">
                            <h5 class="feature-title">Notifications automatiques</h5>
                            <p class="feature-text">Recevez des alertes sur les nouvelles pétitions et signatures</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fa-solid fa-users feature-icon"></i>
                        </div>
                        <div class="feature-content">
                            <h5 class="feature-title">Communauté engagée</h5>
                            <p class="feature-text">Rejoignez des milliers de citoyens mobilisés pour le changement</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>