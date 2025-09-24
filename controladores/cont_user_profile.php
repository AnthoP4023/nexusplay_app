<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_user_profile.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit();
}

if (isAdmin()) {
    header('Location: /profile/admin/admin.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$password_message = $_SESSION['password_message'] ?? '';
$password_message_type = $_SESSION['password_message_type'] ?? '';
unset($_SESSION['password_message'], $_SESSION['password_message_type']);

// Datos del usuario
$user_data = getUserData($user_id);
$_SESSION['username'] = $user_data['username'] ?? '';
$imagen_bd = $user_data['imagen_perfil'] ?? '';

if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png') {
    $ruta_imagen = '/images/users/' . $imagen_bd;
    $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $ruta_imagen;
    $_SESSION['imagen_perfil'] = file_exists($ruta_fisica) ? $ruta_imagen : '/images/users/default-avatar.png';
} else {
    $_SESSION['imagen_perfil'] = '/images/users/default-avatar.png';
}

// Pedidos, reseñas, movimientos, tarjetas y estadísticas
$user_orders    = getUserOrders($user_id);
$user_reviews   = getUserReviews($user_id);
$user_movements = getUserMovements($user_id);
$user_cards     = getUserCards($user_id);
$user_stats     = getUserStats($user_id); // array con 'stats' y 'saldo_cartera'
