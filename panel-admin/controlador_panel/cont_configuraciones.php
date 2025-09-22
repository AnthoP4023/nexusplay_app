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
error_log("Debug: ID del admin desde sesión: " . $admin_id);
error_log("Debug: Username desde sesión: " . ($_SESSION['panel_admin_username'] ?? 'No definido'));

$success_message = '';
$error_message = '';

if (isset($_SESSION['config_success'])) {
    $success_message = $_SESSION['config_success'];
    unset($_SESSION['config_success']);
}

if (isset($_SESSION['config_error'])) {
    $error_message = $_SESSION['config_error'];
    unset($_SESSION['config_error']);
}

try {
    error_log("Debug: Antes de llamar obtenerDatosAdmin");
    $admin_data = obtenerDatosAdmin($admin_id);
    error_log("Debug: Después de llamar obtenerDatosAdmin, resultado: " . ($admin_data ? 'Datos obtenidos' : 'Sin datos'));
    
    // Debug adicional para verificar la variable
    echo "<!-- DEBUG CONTROLADOR: admin_data = " . print_r($admin_data, true) . " -->";
    echo "<!-- DEBUG: Es array? " . (is_array($admin_data) ? 'SI' : 'NO') . " -->";
    echo "<!-- DEBUG: Está vacío? " . (empty($admin_data) ? 'SI' : 'NO') . " -->";
    echo "<!-- DEBUG: Es false? " . ($admin_data === false ? 'SI' : 'NO') . " -->";
    
    if ($admin_data === false || empty($admin_data)) {
        error_log("Debug: admin_data es false o vacío, usando datos por defecto");
        $error_message = 'Error al cargar los datos del administrador';
        $admin_data = [
            'username' => '',
            'email' => '',
            'nombre' => '',
            'apellido' => '',
            'imagen_url' => '/nexusplay/images/users/default-avatar.png',
            'fecha_registro' => date('Y-m-d H:i:s')
        ];
    } else {
        error_log("Debug: admin_data cargado correctamente con " . count($admin_data) . " elementos");
    }
    
} catch (Exception $e) {
    error_log("Error en cont_configuraciones.php: " . $e->getMessage());
    $error_message = 'Error interno del servidor';
    $admin_data = [
        'username' => '',
        'email' => '',
        'nombre' => '',
        'apellido' => '',
        'imagen_url' => '/nexusplay/images/users/default-avatar.png',
        'fecha_registro' => date('Y-m-d H:i:s')
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        
        if (empty($username) || empty($email) || empty($nombre) || empty($apellido)) {
            $_SESSION['config_error'] = 'Todos los campos son obligatorios';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['config_error'] = 'El email no es válido';
        } else {
            if (actualizarPerfilAdmin($admin_id, $username, $email, $nombre, $apellido)) {
                $_SESSION['panel_admin_username'] = $username;
                $_SESSION['config_success'] = 'Perfil actualizado correctamente';
            } else {
                $_SESSION['config_error'] = 'Error al actualizar el perfil';
            }
        }
        
        header('Location: configuraciones.php');
        exit();
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['config_error'] = 'Todos los campos de contraseña son obligatorios';
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['config_error'] = 'Las nuevas contraseñas no coinciden';
        } elseif (strlen($new_password) < 6) {
            $_SESSION['config_error'] = 'La nueva contraseña debe tener al menos 6 caracteres';
        } else {
            if (cambiarPasswordAdmin($admin_id, $current_password, $new_password)) {
                $_SESSION['config_success'] = 'Contraseña actualizada correctamente';
            } else {
                $_SESSION['config_error'] = 'La contraseña actual es incorrecta';
            }
        }
        
        header('Location: configuraciones.php');
        exit();
    }
    
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar'])) {
        $upload_result = actualizarAvatarAdmin($admin_id, $_FILES['avatar']);
        
        if ($upload_result['success']) {
            $_SESSION['config_success'] = 'Foto de perfil actualizada correctamente';
        } else {
            $_SESSION['config_error'] = $upload_result['message'];
        }
        
        header('Location: configuraciones.php');
        exit();
    }
}
?>