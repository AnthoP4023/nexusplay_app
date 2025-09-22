<?php
require_once __DIR__ . '/../functions_panel/fun_auth_panel.php';
require_once __DIR__ . '/../functions_panel/fun_pedidos.php';

if (!isPanelAdminLoggedIn()) {
    header('Location: panel_login.php');
    exit();
}

renewPanelSession();

try {
    $pedidos = obtenerTodosPedidos();
    $estadisticas = obtenerEstadisticasPedidos();
    
} catch (Exception $e) {
    error_log("Error en cont_pedidos.php: " . $e->getMessage());
    $pedidos = [];
    $estadisticas = [
        'total_pedidos' => 0,
        'pedidos_pendientes' => 0,
        'pedidos_completados' => 0,
        'pedidos_cancelados' => 0,
        'ingresos_totales' => 0
    ];
}
?>