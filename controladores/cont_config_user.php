<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../functions/fun_auth.php';
require_once __DIR__ . '/../functions/fun_config_user.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

if (isAdmin()) {
    header('Location: ../profile/admin/admin.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Mensajes de sesión
$password_message = $_SESSION['password_message'] ?? '';
$password_message_type = $_SESSION['password_message_type'] ?? '';
$profile_message = $_SESSION['profile_message'] ?? '';
$profile_message_type = $_SESSION['profile_message_type'] ?? '';
$image_message = $_SESSION['image_message'] ?? '';
$image_message_type = $_SESSION['image_message_type'] ?? '';

// Limpiar mensajes
unset($_SESSION['password_message'], $_SESSION['password_message_type']);
unset($_SESSION['profile_message'], $_SESSION['profile_message_type']);
unset($_SESSION['image_message'], $_SESSION['image_message_type']);

// Obtener datos del usuario
$user_data = getUserData($conn, $user_id);
if (!$user_data) die("Usuario no encontrado");

// Imagen de perfil
$imagen_bd = $user_data['imagen_perfil'] ?? 'default-avatar.png';
if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png' && file_exists($_SERVER['DOCUMENT_ROOT'].'/images/users/'.$imagen_bd)) {
    $perfil_img = '/images/users/'.$imagen_bd;
} else {
    $perfil_img = '/images/users/default-avatar.png';
}
$_SESSION['imagen_perfil'] = $perfil_img;

// Estadísticas y saldo
$stats = getUserStats($conn, $user_id);
$saldo_cartera = getUserWallet($conn, $user_id);

// Procesar formularios POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Cambiar contraseña
    if (isset($_POST['change_password'])) {
        $res = updateUserPassword($conn, $user_id, $_POST['current_password'], $_POST['new_password']);
        $_SESSION['password_message'] = $res['message'];
        $_SESSION['password_message_type'] = $res['success'] ? 'success' : 'error';
        header('Location: configuracion.php');
        exit();
    }

    // Actualizar perfil
    if (isset($_POST['update_profile'])) {
        $res = updateUserProfile($conn, $user_id, $_POST['username'], $_POST['email'], $_POST['nombre'], $_POST['apellido']);
        $_SESSION['profile_message'] = $res['message'];
        $_SESSION['profile_message_type'] = $res['success'] ? 'success' : 'error';

        if ($res['success']) {
            $_SESSION['username'] = $_POST['username'];
            $user_data['username'] = $_POST['username'];
            $user_data['email'] = $_POST['email'];
            $user_data['nombre'] = $_POST['nombre'];
            $user_data['apellido'] = $_POST['apellido'];
        }

        header('Location: configuracion.php');
        exit();
    }

    // Actualizar imagen de perfil
    if (isset($_POST['update_profile_image'])) {
        $res = updateUserProfileImage($conn, $user_id, $_FILES['profile_image'] ?? null);
        $_SESSION['image_message'] = $res['message'];
        $_SESSION['image_message_type'] = $res['success'] ? 'success' : 'error';

        if ($res['success']) {
            $_SESSION['imagen_perfil'] = '/images/users/'.$res['filename'];
            $perfil_img = '/images/users/'.$res['filename'];
            $user_data['imagen_perfil'] = $res['filename'];
        }

        header('Location: configuracion.php');
        exit();
    }
}
?>
