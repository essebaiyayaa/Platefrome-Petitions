<?php
session_start();

// Inclure le fichier de configuration
require_once '../../config/config.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération et nettoyage des données
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation des champs obligatoires
    if (empty($email) || empty($password)) {
        $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
        header('Location: login.php?error=empty' . $redirectParam);
        exit();
    }
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
        header('Location: login.php?error=invalid_email' . $redirectParam);
        exit();
    }
    
    try {
        // La connexion PDO ($pdo) est déjà disponible via config.php
        
        // Recherche de l'utilisateur dans la base de données
        $stmt = $pdo->prepare("
            SELECT 
                IDU,
                Nom,
                Prenom,
                Email,
                MotDePasse,
                Role,
                Actif
            FROM Utilisateur 
            WHERE Email = :email
            LIMIT 1
        ");
        
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérification si l'utilisateur existe
        if (!$user) {
            // L'utilisateur n'existe pas
            sleep(1); // Protection contre brute force
            $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
            header('Location: login.php?error=invalid' . $redirectParam);
            exit();
        }
        
        // Vérifier si le compte est actif
        if (!$user['Actif']) {
            $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
            header('Location: login.php?error=inactive' . $redirectParam);
            exit();
        }
        
        // Vérification du mot de passe avec password_verify
        if (!password_verify($password, $user['MotDePasse'])) {
            // Mot de passe incorrect
            sleep(1); // Protection contre brute force
            $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
            header('Location: login.php?error=invalid' . $redirectParam);
            exit();
        }
        
        // ✅ Connexion réussie - Créer la session
        session_regenerate_id(true);
        
        $_SESSION['IDU'] = $user['IDU'];
        $_SESSION['Email'] = $user['Email'];
        $_SESSION['Nom'] = $user['Nom'];
        $_SESSION['Prenom'] = $user['Prenom'];
        $_SESSION['Role'] = $user['Role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        // 🔒 Vérifier si une redirection personnalisée a été demandée
        if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
            $redirect = $_POST['redirect'];
            
            // Protection : éviter une URL externe (redirection malveillante)
            if (!preg_match('#^https?://#i', $redirect)) {
                header('Location: ' . $redirect);
                exit();
            }
        }
        
        // Redirection par défaut selon le rôle de l'utilisateur
        if ($user['Role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: ../home.php');
        }
        exit();
        
    } catch (PDOException $e) {
        // Erreur de base de données
        error_log("Erreur de connexion : " . $e->getMessage());
        $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
        header('Location: login.php?error=system' . $redirectParam);
        exit();
    }
    
} else {
    // Accès direct au fichier sans POST
    header('Location: login.php');
    exit();
}
?>