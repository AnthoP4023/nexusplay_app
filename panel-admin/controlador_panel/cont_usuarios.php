<?php
// /controlador_panel/cont_usuarios.php

require_once __DIR__ . '/../functions_panel/fun_auth_panel.php';
require_once __DIR__ . '/../functions_panel/fun_usuarios.php';

// Bloque de manejo de peticiones AJAX (POST para 'get' y 'edit')
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!isPanelAdminLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Sesión expirada o no autorizada.']);
        exit;
    }
    
    try {
        switch($_POST['action']) {
            case 'edit':
                $datos_a_actualizar = [
                    'username'       => trim($_POST['username']),
                    'email'          => trim($_POST['email']),
                    'tipo_user_id'   => (int)$_POST['tipo_user_id'],
                    'nombre'         => trim($_POST['nombre']),
                    'apellido'       => trim($_POST['apellido']),
                    'saldo'          => floatval($_POST['saldo']), 
                ];
                
                $result = updateUsuario((int)$_POST['id'], $datos_a_actualizar, $_FILES);
                
                echo json_encode(['success' => $result, 'message' => $result ? 'Usuario actualizado correctamente.' : 'Error al actualizar el usuario.']);
                break;
                
            case 'get':
                $usuario = getUsuarioById((int)$_POST['id']);
                $tipos = getTiposUsuario(); 
                
                if ($usuario) {
                    // Devolvemos el usuario y los tipos para llenar el modal
                    echo json_encode(['usuario' => $usuario, 'tipos' => $tipos]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    } catch (Exception $e) {
        error_log("Error en cont_usuarios.php (AJAX): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
    }
    exit;
}

// Lógica de carga de datos para la vista principal
if (!isPanelAdminLoggedIn()) {
    header('Location: panel_login.php');
    exit();
}

renewPanelSession();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$tipos_usuario = []; // Inicializar antes del try

try {
    // Estas funciones son necesarias para la tabla y las estadísticas
    $total_usuarios = getTotalUsuarios();
    $total_admins = getTotalAdministradores();
    $usuarios_mes = getUsuariosDelMes();
    
    // Función para rellenar el dropdown del modal de forma estática si no es AJAX
    $tipos_usuario = getTiposUsuario();
    
    $usuarios = getUsuarios($page, $limit);
    $total_usuarios_count = getTotalUsuariosCount();
    $total_pages = ceil($total_usuarios_count / $limit);
    
} catch (Exception $e) {
    error_log("Error en cont_usuarios.php: " . $e->getMessage());
    $total_usuarios = 0;
    $total_admins = 0;
    $usuarios_mes = 0;
    $usuarios = [];
    $total_pages = 1;
}
?>