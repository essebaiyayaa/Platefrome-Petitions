<?php
session_start();
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        header('Location: register.php?error=empty_fields');
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: register.php?error=invalid_email');
        exit();
    }
    if ($password !== $confirm_password) {
        header('Location: register.php?error=passwords_mismatch');
        exit();
    }
    if (strlen($password) < 8) {
        header('Location: register.php?error=weak_password');
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("SELECT IDU FROM Utilisateur WHERE Email = :email");
        $stmt->execute(['email' => $email]);
        
        if ($stmt->fetch()) {
            header('Location: register.php?error=email_exists');
            exit();
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
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
            $user_id = $pdo->lastInsertId();
            $_SESSION['IDU'] = $user_id;
            $_SESSION['Email'] = $email;
            $_SESSION['Nom'] = $nom;
            $_SESSION['Prenom'] = $prenom;
            $_SESSION['Role'] = 'user';
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            header('Location: ../home.php?welcome=1');
            exit();
        } else {
            header('Location: register.php?error=registration_failed');
            exit();
        }
        
    } catch (PDOException $e) {
        error_log("Erreur d'inscription : " . $e->getMessage());
        header('Location: register.php?error=system');
        exit();
    }  
} else {
    header('Location: register.php');
    exit();
}
?>