<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_admin_profile.php'; 

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$password_message = '';
$password_message_type = '';

if (isset($_SESSION['password_message'])) {
    $password_message = $_SESSION['password_message'];
    $password_message_type = $_SESSION['password_message_type'];
    unset($_SESSION['password_message'], $_SESSION['password_message_type']);
}

$admin_data = getAdminData($conn, $user_id);
$_SESSION['username'] = $admin_data['username'] ?? '';

$imagen_bd = $admin_data['imagen_perfil'] ?? '';
if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png') {
    $ruta_imagen = '/images/users/' . $imagen_bd;
    $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $ruta_imagen;
    $perfil_img = file_exists($ruta_fisica) ? $ruta_imagen : '/images/users/default-avatar.png';
} else {
    $perfil_img = '/images/users/default-avatar.png';
}
$_SESSION['imagen_perfil'] = $perfil_img;

$pedidos_result = getAdminOrders($conn, $user_id);
$resenas_result = getAdminReviews($conn, $user_id);
$movimientos_result = getAdminMovements($conn, $user_id);
$tarjetas_result = getAdminCards($conn, $user_id);

$stats_data = getAdminStats($conn, $user_id);
$stats = $stats_data['stats'];
$saldo_cartera = $stats_data['saldo_cartera'];
