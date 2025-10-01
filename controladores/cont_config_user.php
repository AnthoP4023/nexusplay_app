<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_config_user.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

if (isAdmin()) {
    header('Location: ../profile/admin/admin.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Mensajes
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

// Datos del usuario
$user_data = getUserData($user_id);
$user_stats = getUserStats($user_id);
$perfil_img = $user_data['perfil_img'];
$saldo_cartera = $user_stats['saldo_cartera'];

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Cambiar contraseña
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $_SESSION['password_message'] = 'Todos los campos son obligatorios';
            $_SESSION['password_message_type'] = 'error';
        } elseif ($new !== $confirm) {
            $_SESSION['password_message'] = 'Las nuevas contraseñas no coinciden';
            $_SESSION['password_message_type'] = 'error';
        } elseif (strlen($new) < 6) {
            $_SESSION['password_message'] = 'La nueva contraseña debe tener al menos 6 caracteres';
            $_SESSION['password_message_type'] = 'error';
        } elseif (changeUserPassword($user_id, $current, $new)) {
            $_SESSION['password_message'] = 'Contraseña cambiada exitosamente';
            $_SESSION['password_message_type'] = 'success';
        } else {
            $_SESSION['password_message'] = 'Contraseña actual incorrecta';
            $_SESSION['password_message_type'] = 'error';
        }

        header('Location: configuracion.php');
        exit();
    }

    // Actualizar perfil
    if (isset($_POST['update_profile'])) {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';

        if (empty($username) || empty($email) || empty($nombre) || empty($apellido)) {
            $_SESSION['profile_message'] = 'Todos los campos son obligatorios';
            $_SESSION['profile_message_type'] = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['profile_message'] = 'Email inválido';
            $_SESSION['profile_message_type'] = 'error';
        } else {
            updateUserProfile($user_id, $username, $email, $nombre, $apellido);
            $_SESSION['profile_message'] = 'Perfil actualizado exitosamente';
            $_SESSION['profile_message_type'] = 'success';
        }

        header('Location: configuracion.php');
        exit();
    }

    // Actualizar imagen de perfil
    if (isset($_POST['update_profile_image']) && isset($_FILES['profile_image'])) {
        $result = updateUserProfileImage($user_id, $_FILES['profile_image']);
        $_SESSION['image_message'] = $result['success'] ? 'Imagen actualizada' : $result['message'];
        $_SESSION['image_message_type'] = $result['success'] ? 'success' : 'error';

        header('Location: configuracion.php');
        exit();
    }
}
?>
