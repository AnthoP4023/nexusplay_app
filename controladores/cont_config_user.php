<?php
if (session_status() == PHP_SESSION_NONE) session_start();

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

$user_data = getUserData($user_id);
if (!$user_data) die("Usuario no encontrado.");

$_SESSION['username'] = $user_data['username'];
$_SESSION['imagen_perfil'] = $user_data['perfil_img'];

$user_stats = getUserStats($user_id);
$stats = $user_stats['stats'];
$saldo_cartera = $user_stats['saldo_cartera'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!$current_password || !$new_password || !$confirm_password) {
            $_SESSION['config_error'] = 'Todos los campos de contraseña son obligatorios';
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['config_error'] = 'Las contraseñas nuevas no coinciden';
        } elseif (strlen($new_password) < 6) {
            $_SESSION['config_error'] = 'La nueva contraseña debe tener al menos 6 caracteres';
        } else {
            if (changeUserPassword($user_id, $current_password, $new_password)) {
                $_SESSION['config_success'] = 'Contraseña cambiada exitosamente';
            } else {
                $_SESSION['config_error'] = 'Contraseña actual incorrecta o error en la base de datos';
            }
        }
        header('Location: configuracion.php'); exit();
    }

    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');

        if (!$username || !$email || !$nombre || !$apellido) {
            $_SESSION['config_error'] = 'Todos los campos del perfil son obligatorios';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['config_error'] = 'El email no es válido';
        } else {
            if (updateUserProfile($user_id, $username, $email, $nombre, $apellido)) {
                $_SESSION['config_success'] = 'Perfil actualizado exitosamente';
                $_SESSION['username'] = $username;
            } else {
                $_SESSION['config_error'] = 'Error al actualizar el perfil';
            }
        }
        header('Location: configuracion.php'); exit();
    }

    if (isset($_POST['update_profile_image']) && isset($_FILES['profile_image'])) {
        $result = updateUserProfileImage($user_id, $_FILES['profile_image']);
        if ($result['success']) {
            $_SESSION['config_success'] = 'Imagen de perfil actualizada exitosamente';
            $_SESSION['imagen_perfil'] = '/images/users/' . $result['filename'];
        } else {
            $_SESSION['config_error'] = $result['message'];
        }
        header('Location: configuracion.php'); exit();
    }
}
