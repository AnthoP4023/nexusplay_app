<?php
require_once __DIR__ . '/../functions_panel/fun_auth_panel.php';
require_once __DIR__ . '/../functions_panel/fun_usuarios.php';

if (!isPanelAdminLoggedIn()) {
    header('Location: panel_login.php');
    exit();
}

renewPanelSession();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$limit = 20;

try {
    $total_usuarios = getTotalUsuarios();
    $total_admins = getTotalAdministradores();
    $usuarios_mes = getUsuariosDelMes();
    
    $usuarios = getUsuarios($page, $limit, $search, $tipo);
    $total_records = getTotalUsuariosCount($search, $tipo);
    $total_pages = ceil($total_records / $limit);
    
} catch (Exception $e) {
    error_log("Error en cont_usuarios.php: " . $e->getMessage());
    $total_usuarios = 0;
    $total_admins = 0;
    $usuarios_mes = 0;
    $usuarios = [];
    $total_pages = 1;
}
?>