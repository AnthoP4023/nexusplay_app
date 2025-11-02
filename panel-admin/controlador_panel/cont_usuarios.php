<?php
// /controlador_panel/cont_usuarios.php

require_once __DIR__ . '/../functions_panel/fun_auth_panel.php';
require_once __DIR__ . '/../functions_panel/fun_usuarios.php';

// Bloque de manejo de peticiones AJAX (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // Si la acción es 'delete_id', ya se manejó en usuarios.php, pero por si acaso.
    if (isset($_POST['delete_id'])) {
        exit;
    }
    
    // Verificación de sesión de seguridad
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
                    // El saldo se pasa como string/float
                    'saldo'          => floatval($_POST['saldo']), 
                ];
                
                // updateUsuario maneja la actualización en la tabla `usuarios` y el `saldo` en `carteras`.
                $result = updateUsuario((int)$_POST['id'], $datos_a_actualizar, $_FILES);
                
                echo json_encode(['success' => $result, 'message' => $result ? 'Usuario actualizado correctamente.' : 'Error al actualizar el usuario.']);
                break;
                
            case 'get':
                $usuario = getUsuarioById((int)$_POST['id']);
                
                // También necesitamos los tipos de usuario para el dropdown del modal
                $tipos = getTiposUsuario(); 
                
                if ($usuario) {
                     // Devolvemos el usuario y los tipos
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
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
    }
    exit;
}
// --- Fin del bloque AJAX ---

// Lógica de carga de datos para la vista principal (si no es una petición AJAX)
if (!isPanelAdminLoggedIn()) {
    header('Location: panel_login.php');
    exit();
}

renewPanelSession();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;

try {
    // También obtenemos los tipos de usuario para el modal (si no se carga por AJAX)
    $tipos_usuario = getTiposUsuario();
    
    $usuarios = getUsuarios($page, $limit);

    $total_usuarios_count = getTotalUsuariosCount();
    $total_pages = ceil($total_usuarios_count / $limit);

    $total_usuarios = getTotalUsuarios();
    $usuarios_mes = getUsuariosDelMes();
    $total_admins = getTotalAdministradores();
    
} catch (Exception $e) {
    error_log("Error en cont_usuarios.php: " . $e->getMessage());
    $total_usuarios = 0;
    $total_admins = 0;
    $usuarios_mes = 0;
    $usuarios = [];
    $total_pages = 1;
    $tipos_usuario = [];
}
?>