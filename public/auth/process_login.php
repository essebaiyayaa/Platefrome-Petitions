<?php
session_start();
require_once '../../config/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
        header('Location: login.php?error=empty' . $redirectParam);
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
        header('Location: login.php?error=invalid_email' . $redirectParam);
        exit();
    }
    
    try {
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
        if (!$user) {
            sleep(1); 
            $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
            header('Location: login.php?error=invalid' . $redirectParam);
            exit();
        }
        if (!$user['Actif']) {
            $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
            header('Location: login.php?error=inactive' . $redirectParam);
            exit();
        }
        if (!password_verify($password, $user['MotDePasse'])) {
            sleep(1); 
            $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
            header('Location: login.php?error=invalid' . $redirectParam);
            exit();
        }

        session_regenerate_id(true);
        $_SESSION['IDU'] = $user['IDU'];
        $_SESSION['Email'] = $user['Email'];
        $_SESSION['Nom'] = $user['Nom'];
        $_SESSION['Prenom'] = $user['Prenom'];
        $_SESSION['Role'] = $user['Role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
            $redirect = $_POST['redirect'];
            if (!preg_match('#^https?://#i', $redirect)) {
                header('Location: ' . $redirect);
                exit();
            }
        }
        if ($user['Role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: ../home.php');
        }
        exit();
        
    } catch (PDOException $e) {
        error_log("Erreur de connexion : " . $e->getMessage());
        $redirectParam = isset($_POST['redirect']) ? '&redirect=' . urlencode($_POST['redirect']) : '';
        header('Location: login.php?error=system' . $redirectParam);
        exit();
    }
    
} else {
    header('Location: login.php');
    exit();
}
?>