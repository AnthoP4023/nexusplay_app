<?php
// /functions_panel/fun_usuarios.php
require_once __DIR__ . '/../../config_db/database.php';

// --- Funciones de Estadística y Consulta ---

function getTotalUsuarios() {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE tipo_user_id = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        error_log("Error en getTotalUsuarios: " . $e->getMessage());
        return 0;
    }
}

function getTotalAdministradores() {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE tipo_user_id = 2");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        error_log("Error en getTotalAdministradores: " . $e->getMessage());
        return 0;
    }
}

function getUsuariosDelMes() {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE MONTH(fecha_registro) = MONTH(CURDATE()) AND YEAR(fecha_registro) = YEAR(CURDATE())");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        error_log("Error en getUsuariosDelMes: " . $e->getMessage());
        return 0;
    }
}

function getUsuarios($page = 1, $limit = 20) {
    global $conn;
    $offset = ($page - 1) * $limit;

    try {
        $sql = "SELECT u.id, u.username, u.email, u.tipo_user_id, u.fecha_registro, u.imagen_perfil,
                IFNULL(c.saldo, 0) AS saldo
        FROM usuarios u
        LEFT JOIN carteras c ON u.id = c.usuario_id
        ORDER BY u.fecha_registro DESC
        LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $row['avatar'] = !empty($row['imagen_perfil']) && $row['imagen_perfil'] !== 'default-avatar.png'
                ? '../../images/users/' . $row['imagen_perfil']
                : '../../images/users/default-avatar.png';
            $usuarios[] = $row;
        }

        return $usuarios;
    } catch (Exception $e) {
        error_log("Error en getUsuarios: " . $e->getMessage());
        return [];
    }
}

function getTotalUsuariosCount() {
    global $conn;
    try {
        $sql = "SELECT COUNT(*) as total FROM usuarios";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        error_log("Error en getTotalUsuariosCount: " . $e->getMessage());
        return 0;
    }
}

function getTiposUsuario() {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, nombre FROM tipo_user ORDER BY id ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tipos = [];
        while ($row = $result->fetch_assoc()) {
            $tipos[] = $row;
        }
        return $tipos;
    } catch (Exception $e) {
        error_log("Error en getTiposUsuario: " . $e->getMessage());
        return [];
    }
}

// --- Funciones de Edición (CRUD) ---

function getUsuarioById($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT 
                u.id, u.username, u.email, u.tipo_user_id, u.imagen_perfil, u.nombre, u.apellido,
                IFNULL(c.saldo, 0) AS saldo
            FROM usuarios u
            LEFT JOIN carteras c ON u.id = c.usuario_id
            WHERE u.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            $usuario['imagen_perfil_nombre'] = $usuario['imagen_perfil'] ?? 'default-avatar.png'; 
            unset($usuario['imagen_perfil']);
            unset($usuario['password']); 
            return $usuario;
        }
        return null;
    } catch (Exception $e) {
        error_log("Error en getUsuarioById: " . $e->getMessage());
        return null;
    }
}

function uploadAvatar($file) {
    try {
        $upload_dir = __DIR__ . '/../../images/users/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . rand(1000, 9999) . '.' . $extension;
        $upload_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $filename;
        }
    } catch (Exception $e) {
        error_log("Error en uploadAvatar: " . $e->getMessage());
    }
    
    return null; 
}

function updateSaldo($usuario_id, $nuevo_saldo) {
    global $conn;
    try {
        // Verifica si la cartera existe
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM carteras WHERE usuario_id = ?");
        $stmt_check->bind_param("i", $usuario_id);
        $stmt_check->execute();
        $exists = $stmt_check->get_result()->fetch_row()[0];
        $stmt_check->close();

        if ($exists) {
            $sql = "UPDATE carteras SET saldo = ? WHERE usuario_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $nuevo_saldo, $usuario_id); 
        } else {
            // Si no existe, inserta. Podrías querer manejar esto mejor si la cartera siempre debe existir.
            $sql = "INSERT INTO carteras (usuario_id, saldo) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("id", $usuario_id, $nuevo_saldo);
        }
        
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error en updateSaldo: " . $e->getMessage());
        return false;
    }
}

function updateUsuario($id, $datos, $file_data = null) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        // 1. Manejo del Saldo (tabla `carteras`)
        if (isset($datos['saldo'])) {
            $saldo_actualizado = updateSaldo($id, $datos['saldo']);
            if (!$saldo_actualizado) {
                 $conn->rollback();
                 return false;
            }
            unset($datos['saldo']); 
        }

        // 2. Manejo del Avatar (tabla `usuarios`)
        if (isset($file_data['avatar']) && $file_data['avatar']['error'] == 0) {
            $avatar_nombre = uploadAvatar($file_data['avatar']);
            if ($avatar_nombre) {
                $datos['imagen_perfil'] = $avatar_nombre; 
            }
        }
        
        // 3. Actualización de la tabla `usuarios`
        $fields = [];
        $params = [];
        $types = "";

        foreach ($datos as $field => $value) {
            $fields[] = "$field = ?";
            $params[] = $value;
            $types .= is_int($value) ? "i" : (is_float($value) ? "d" : "s");
        }

        if (!empty($fields)) {
            $params[] = $id;
            $types .= "i";

            $sql = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            // Usamos call_user_func_array para bind_param
            call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
            
            if (!$stmt->execute()) {
                 $conn->rollback();
                 return false;
            }
        }
        
        // Si todo salió bien (incluyendo el saldo y los campos de usuario)
        $conn->commit();
        return true;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en updateUsuario: " . $e->getMessage());
        return false;
    }
}

// --- Función de Eliminación (Completa) ---

function deleteUsuario($id) {
    global $conn;
    try {
        $usuario = getUsuarioById($id);
        if ($usuario && $usuario['tipo_user_id'] == 2) {
            return false; // No se permite eliminar administradores
        }

        $conn->begin_transaction();

        // 1. Eliminar datos de tablas relacionadas
        $cartera_id = null;
        $stmt_cartera = $conn->prepare("SELECT id FROM carteras WHERE usuario_id = ?");
        $stmt_cartera->bind_param("i", $id);
        $stmt_cartera->execute();
        $result_cartera = $stmt_cartera->get_result();
        if ($row = $result_cartera->fetch_assoc()) {
            $cartera_id = $row['id'];
        }
        $stmt_cartera->close();

        if ($cartera_id) {
            $conn->prepare("DELETE FROM movimientos_cartera WHERE cartera_id = ?")->bind_param("i", $cartera_id)->execute();
        }
        
        $conn->prepare("DELETE FROM resenas WHERE usuario_id = ?")->bind_param("i", $id)->execute();
        $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?")->bind_param("i", $id)->execute();
        $conn->prepare("DELETE FROM tarjetas WHERE usuario_id = ?")->bind_param("i", $id)->execute();

        // Eliminar detalles de pedidos antes de los pedidos
        $pedido_ids = [];
        $stmt_pedidos_ids = $conn->prepare("SELECT id FROM pedidos WHERE usuario_id = ?");
        $stmt_pedidos_ids->bind_param("i", $id);
        $stmt_pedidos_ids->execute();
        $result_pedidos = $stmt_pedidos_ids->get_result();
        while ($row = $result_pedidos->fetch_assoc()) {
            $pedido_ids[] = $row['id'];
        }
        $stmt_pedidos_ids->close();

        if (!empty($pedido_ids)) {
            $in_clause = str_repeat('?,', count($pedido_ids) - 1) . '?';
            $types = str_repeat('i', count($pedido_ids));
            $stmt_detalles = $conn->prepare("DELETE FROM detalles_pedido WHERE pedido_id IN ($in_clause)");
            call_user_func_array([$stmt_detalles, 'bind_param'], array_merge([$types], $pedido_ids));
            $stmt_detalles->execute();
        }

        // 2. Eliminar registros principales
        $conn->prepare("DELETE FROM carteras WHERE usuario_id = ?")->bind_param("i", $id)->execute();
        $conn->prepare("DELETE FROM pedidos WHERE usuario_id = ?")->bind_param("i", $id)->execute();
        
        $stmt_user = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt_user->bind_param("i", $id);
        
        if ($stmt_user->execute()) {
            $conn->commit();
            return true;
        } else {
            $conn->rollback(); 
            return false;
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en deleteUsuario: " . $e->getMessage());
        return false;
    }
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}