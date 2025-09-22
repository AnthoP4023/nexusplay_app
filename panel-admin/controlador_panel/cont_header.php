<?php
require_once __DIR__ . '/../functions_panel/fun_auth_panel.php';
require_once __DIR__ . '/../../config_db/database.php';

if (!isPanelAdminLoggedIn()) {
    header('Location: panel_login.php');
    exit();
}

renewPanelSession();

$admin_id = $_SESSION['panel_admin_id'];
$admin_name = $_SESSION['panel_admin_username'] ?? 'Administrador';

try {
    $stmt = $conn->prepare("SELECT imagen_perfil FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $admin_data = $result->fetch_assoc();
        $imagen_bd = $admin_data['imagen_perfil'];
        
        if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png') {
            $admin_avatar = '/nexusplay/images/users/' . $imagen_bd;
        } else {
            $admin_avatar = '/nexusplay/images/users/default-avatar.png';
        }
    } else {
        $admin_avatar = '/nexusplay/images/users/default-avatar.png';
    }
} catch (Exception $e) {
    $admin_avatar = '/nexusplay/images/users/default-avatar.png';
}
?>