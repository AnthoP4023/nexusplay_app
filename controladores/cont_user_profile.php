<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_profile_user.php';

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

// Obtener datos del usuario
$user_data = getUserData($user_id);
$user_orders = getUserOrders($user_id);
$user_stats = getUserStats($user_id);
$user_movements = getUserMovements($user_id);
$user_cards = getUserCards($user_id);
$user_reviews = getUserReviews($user_id);

// Guardar imagen y username en sesión
$_SESSION['username'] = $user_data['username'] ?? '';
$_SESSION['imagen_perfil'] = $user_data['perfil_img'] ?? '/images/users/default-avatar.png';
