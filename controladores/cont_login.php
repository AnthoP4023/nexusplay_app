<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config_db/database.php';
require_once '../functions/fun_auth.php';
require_once '../functions/fun_login.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $login_result = checkLoginCredentials($conn, $username, $password);

    if ($login_result['success']) {
        $user = $login_result['user'];
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['user_type'] = $user['tipo_usuario'];

        unset($_SESSION['cart_loaded']);
        unset($_SESSION['carrito_count']);

        header('Location: ../index.php');
        exit();
    } else {
        $error = $login_result['message'];
    }
}
?>
