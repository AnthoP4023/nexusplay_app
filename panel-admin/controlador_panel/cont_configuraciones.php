<?php
require_once __DIR__ . '/../functions_panel/fun_auth_panel.php';
require_once __DIR__ . '/../functions_panel/fun_configuraciones.php';
require_once __DIR__ . '/../../config_db/database.php';

if (!isPanelAdminLoggedIn()) {
    header('Location: panel_login.php');
    exit();
}
renewPanelSession();

$admin_id = $_SESSION['panel_admin_id'];

$success_message = $_SESSION['config_success'] ?? '';
$error_message = $_SESSION['config_error'] ?? '';
unset($_SESSION['config_success'], $_SESSION['config_error']);

$admin_data = obtenerDatosAdmin($admin_id) ?: [
    'username' => '',
    'email' => '',
    'nombre' => '',
    'apellido' => '',
    'imagen_url' => '../../images/users/default-avatar.png',
    'fecha_registro' => date('Y-m-d H:i:s')
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $nombre   = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);

        if (!$username || !$email || !$nombre || !$apellido) {
            $_SESSION['config_error'] = 'Todos los campos son obligatorios';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['config_error'] = 'Email no válido';
        } elseif (actualizarPerfilAdmin($admin_id, $username, $email, $nombre, $apellido)) {
            $_SESSION['panel_admin_username'] = $username;
            $_SESSION['config_success'] = 'Perfil actualizado correctamente';
        } else {
            $_SESSION['config_error'] = 'Error al actualizar el perfil';
        }

        header('Location: configuraciones.php');
        exit();
    }

    if (isset($_POST['change_password'])) {
        $current = trim($_POST['current_password']);
        $new     = trim($_POST['new_password']);
        $confirm = trim($_POST['confirm_password']);

        if (!$current || !$new || !$confirm) {
            $_SESSION['config_error'] = 'Todos los campos de contraseña son obligatorios';
        } elseif ($new !== $confirm) {
            $_SESSION['config_error'] = 'Las nuevas contraseñas no coinciden';
        } elseif (strlen($new) < 6) {
            $_SESSION['config_error'] = 'La nueva contraseña debe tener al menos 6 caracteres';
        } elseif (cambiarPasswordAdmin($admin_id, $current, $new)) {
            $_SESSION['config_success'] = 'Contraseña actualizada correctamente';
        } else {
            $_SESSION['config_error'] = 'Contraseña actual incorrecta';
        }

        header('Location: configuraciones.php');
        exit();
    }

    if (isset($_POST['update_avatar']) && isset($_FILES['avatar'])) {
        $result = actualizarAvatarAdmin($admin_id, $_FILES['avatar']);
        $_SESSION['config_success'] = $result['success'] ? 'Foto de perfil actualizada correctamente' : '';
        $_SESSION['config_error']   = $result['success'] ? '' : $result['message'];
        header('Location: configuraciones.php');
        exit();
    }
}
?>
