<?php
session_start();

require_once __DIR__ . '/../functions_panel/fun_auth_panel.php';

if (isPanelAdminLoggedIn()) {
    header('Location: ../dashboard.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error_message = 'Por favor, completa todos los campos';
    } else {
        $admin_data = authenticateAdmin($username, $password);
        
        if ($admin_data) {
            loginPanelAdmin($admin_data);
            
            header('Location: ../dashboard.php');
            exit();
            
        } else {
            $error_message = 'Usuario o contraseña incorrectos';
        }
    }
    
    if (!empty($error_message)) {
        $_SESSION['panel_error'] = $error_message;
        header('Location: ../panel_login.php');
        exit();
    }
    
} else {
    header('Location: ../panel_login.php');
    exit();
}
?>