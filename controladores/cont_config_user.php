<?php
if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../functions/fun_auth.php';
require_once __DIR__ . '/../functions/fun_config_user.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

if (isAdmin()) {
    header('Location: /nexusplay/profile/admin/admin.php');
    exit();
}

$user_id = $_SESSION['user_id'];

/* Mensajes de sesión */
$password_message = $_SESSION['password_message'] ?? '';
$password_message_type = $_SESSION['password_message_type'] ?? '';
$profile_message = $_SESSION['profile_message'] ?? '';
$profile_message_type = $_SESSION['profile_message_type'] ?? '';
$image_message = $_SESSION['image_message'] ?? '';
$image_message_type = $_SESSION['image_message_type'] ?? '';

unset($_SESSION['password_message'], $_SESSION['password_message_type']);
unset($_SESSION['profile_message'], $_SESSION['profile_message_type']);
unset($_SESSION['image_message'], $_SESSION['image_message_type']);

/* Datos del usuario */
$user_data = getUserData($user_id);
$stats_data = getUserStats($user_id);
$stats = $stats_data['stats'];
$saldo_cartera = $stats_data['saldo_cartera'];

$_SESSION['imagen_perfil'] = $user_data['perfil_img'];

/* Procesar formularios */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['change_password'])) {
        $success = changeUserPassword($user_id, $_POST['current_password'], $_POST['new_password']);
        $_SESSION['password_message'] = $success ? 'Contraseña cambiada exitosamente' : 'Error al cambiar la contraseña';
        $_SESSION['password_message_type'] = $success ? 'success' : 'error';
        header('Location: configuracion.php');
        exit();
    }

    if (isset($_POST['update_profile'])) {
        $success = updateUserProfile($user_id, $_POST['username'], $_POST['email'], $_POST['nombre'], $_POST['apellido']);
        $_SESSION['profile_message'] = $success ? 'Perfil actualizado exitosamente' : 'Error al actualizar el perfil';
        $_SESSION['profile_message_type'] = $success ? 'success' : 'error';
        header('Location: configuracion.php');
        exit();
    }

    if (isset($_POST['update_profile_image'])) {
        $result = updateUserProfileImage($user_id, $_FILES['profile_image']);
        $_SESSION['image_message'] = $result['success'] ? 'Imagen de perfil actualizada' : $result['message'];
        $_SESSION['image_message_type'] = $result['success'] ? 'success' : 'error';
        if ($result['success']) $_SESSION['imagen_perfil'] = '/images/users/' . $result['filename'];
        header('Location: configuracion.php');
        exit();
    }
}
?>
