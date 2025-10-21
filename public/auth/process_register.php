<?php
session_start();

// Inclure le fichier de configuration
require_once '../../config/config.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération et nettoyage des données
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        header('Location: register.php?error=empty_fields');
        exit();
    }
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: register.php?error=invalid_email');
        exit();
    }
    
    // Validation des mots de passe
    if ($password !== $confirm_password) {
        header('Location: register.php?error=passwords_mismatch');
        exit();
    }
    
    // Validation de la force du mot de passe
    if (strlen($password) < 8) {
        header('Location: register.php?error=weak_password');
        exit();
    }
    
    try {
        // La connexion PDO ($pdo) est déjà disponible via config.php
        
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT IDU FROM Utilisateur WHERE Email = :email");
        $stmt->execute(['email' => $email]);
        
        if ($stmt->fetch()) {
            header('Location: register.php?error=email_exists');
            exit();
        }
        
        // Hasher le mot de passe
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertion du nouvel utilisateur
        $stmt = $pdo->prepare("
            INSERT INTO Utilisateur (
                Nom, 
                Prenom, 
                Email, 
                MotDePasse, 
                Role,
                DateInscription,
                Actif
            ) VALUES (
                :nom, 
                :prenom, 
                :email, 
                :mot_de_passe, 
                'user',
                NOW(),
                1
            )
        ");
        
        $result = $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $password_hash
        ]);
        
        if ($result) {
            // Récupérer l'ID du nouvel utilisateur
            $user_id = $pdo->lastInsertId();
            
            // ✅ Connexion automatique après inscription avec les bonnes variables
            $_SESSION['IDU'] = $user_id;
            $_SESSION['Email'] = $email;
            $_SESSION['Nom'] = $nom;
            $_SESSION['Prenom'] = $prenom;
            $_SESSION['Role'] = 'user';
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // ✅ Redirection vers la page d'accueil (home.php)
            header('Location: ../home.php?welcome=1');
            exit();
            
        } else {
            header('Location: register.php?error=registration_failed');
            exit();
        }
        
    } catch (PDOException $e) {
        // Gestion des erreurs de base de données
        error_log("Erreur d'inscription : " . $e->getMessage());
        header('Location: register.php?error=system');
        exit();
    }
    
} else {
    // Accès direct au fichier sans POST
    header('Location: register.php');
    exit();
}
?>