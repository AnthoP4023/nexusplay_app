<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

$password_message = $_SESSION['password_message'] ?? '';
$password_message_type = $_SESSION['password_message_type'] ?? '';
$profile_message = $_SESSION['profile_message'] ?? '';
$profile_message_type = $_SESSION['profile_message_type'] ?? '';
$image_message = $_SESSION['image_message'] ?? '';
$image_message_type = $_SESSION['image_message_type'] ?? '';

unset($_SESSION['password_message'], $_SESSION['password_message_type']);
unset($_SESSION['profile_message'], $_SESSION['profile_message_type']);
unset($_SESSION['image_message'], $_SESSION['image_message_type']);

$user_data = getUserData($user_id);
if (!$user_data) {
    die("Usuario no encontrado.");
}
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

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['password_message'] = 'Todos los campos de contraseña son obligatorios';
            $_SESSION['password_message_type'] = 'error';
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['password_message'] = 'Las contraseñas nuevas no coinciden';
            $_SESSION['password_message_type'] = 'error';
        } elseif (strlen($new_password) < 6) {
            $_SESSION['password_message'] = 'La nueva contraseña debe tener al menos 6 caracteres';
            $_SESSION['password_message_type'] = 'error';
        } else {
            if (changeUserPassword($user_id, $current_password, $new_password)) {
                $_SESSION['password_message'] = 'Contraseña cambiada exitosamente';
                $_SESSION['password_message_type'] = 'success';
            } else {
                $_SESSION['password_message'] = 'Contraseña actual incorrecta o error en la base de datos';
                $_SESSION['password_message_type'] = 'error';
            }
        }

        header('Location: configuracion.php');
        exit();
    }

    if (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['username'] ?? '');
        $new_email = trim($_POST['email'] ?? '');
        $new_nombre = trim($_POST['nombre'] ?? '');
        $new_apellido = trim($_POST['apellido'] ?? '');

        if (empty($new_username) || empty($new_email) || empty($new_nombre) || empty($new_apellido)) {
            $_SESSION['profile_message'] = 'Todos los campos del perfil son obligatorios';
            $_SESSION['profile_message_type'] = 'error';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['profile_message'] = 'El email no es válido';
            $_SESSION['profile_message_type'] = 'error';
        } else {
            if (updateUserProfile($user_id, $new_username, $new_email, $new_nombre, $new_apellido)) {
                $_SESSION['profile_message'] = 'Perfil actualizado exitosamente';
                $_SESSION['profile_message_type'] = 'success';

                $_SESSION['username'] = $new_username;
                $user_data['username'] = $new_username;
                $user_data['email'] = $new_email;
                $user_data['nombre'] = $new_nombre;
                $user_data['apellido'] = $new_apellido;
            } else {
                $_SESSION['profile_message'] = 'Error al actualizar el perfil';
                $_SESSION['profile_message_type'] = 'error';
            }
        }

        header('Location: configuracion.php');
        exit();
    }

    if (isset($_POST['update_profile_image'])) {
        if (isset($_FILES['profile_image'])) {
            $result = updateUserProfileImage($user_id, $_FILES['profile_image']);
            if ($result['success']) {
                $_SESSION['image_message'] = 'Imagen de perfil actualizada exitosamente';
                $_SESSION['image_message_type'] = 'success';
                $_SESSION['imagen_perfil'] = '/images/users/' . $result['filename'];
                $user_data['imagen_perfil'] = $result['filename'];
            } else {
                $_SESSION['image_message'] = $result['message'];
                $_SESSION['image_message_type'] = 'error';
            }
        } else {
            $_SESSION['image_message'] = 'Por favor selecciona una imagen válida';
            $_SESSION['image_message_type'] = 'error';
        }

        header('Location: configuracion.php');
        exit();
    }
} 