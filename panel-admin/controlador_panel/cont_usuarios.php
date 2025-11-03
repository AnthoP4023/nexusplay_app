<?php
require_once __DIR__ . '/../functions_panel/fun_auth_panel.php';
require_once __DIR__ . '/../functions_panel/fun_usuarios.php';

if (!isPanelAdminLoggedIn()) {
    header('Location: panel_login.php');
    exit();
}

renewPanelSession();

// Bloque de manejo de peticiones AJAX (GET_USER y EDIT_USER)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Si la acción es AJAX, respondemos en JSON y detenemos la ejecución PHP
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    switch($_POST['action']) {
        
        case 'get_user':
            // 1. Obtener datos del usuario para rellenar el modal
            if (!isset($_POST['id'])) {
                echo json_encode(['error' => 'ID no proporcionado']);
                exit;
            }
            $id = (int)$_POST['id'];
            $usuario = getUsuarioById($id);
            
            // Devolvemos el array del usuario o un array vacío si no se encuentra
            echo json_encode($usuario ?? []); 
            exit; 

        case 'edit_user':
            // 2. Procesar la actualización del usuario
            if (!isset($_POST['id'], $_POST['username'], $_POST['email'], $_POST['tipo_user_id'])) {
                $response['message'] = 'Datos incompletos para la edición.';
                echo json_encode($response);
                exit;
            }

            $id = (int)$_POST['id'];
            
            // Datos a actualizar
            $datos_a_actualizar = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'tipo_user_id' => (int)$_POST['tipo_user_id']
            ];

            // Implementar validación de datos (ej. email válido, nombre no vacío)

            if (updateUsuario($id, $datos_a_actualizar)) {
                $response['success'] = true;
                $response['message'] = 'Usuario actualizado correctamente.';
            } else {
                $response['message'] = 'Error al actualizar el usuario en la base de datos o no hubo cambios.';
            }
            
            echo json_encode($response);
            exit;
            
        default:
            // Si la acción no es reconocida, seguimos con el código normal.
            break;
    }
}

// Lógica normal de carga de página (PAGINACIÓN Y ESTADÍSTICAS)
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$limit = 20;

try {
    $total_usuarios = getTotalUsuarios();
    $total_admins = getTotalAdministradores();
    $usuarios_mes = getUsuariosDelMes();
    
    // Aquí podrías necesitar modificar getUsuarios y getTotalUsuariosCount 
    // si quieres que consideren $search y $tipo, pero por ahora usamos la versión simple.
    $usuarios = getUsuarios($page, $limit); 
    $total_records = getTotalUsuariosCount();
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