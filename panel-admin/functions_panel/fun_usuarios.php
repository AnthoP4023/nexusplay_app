<?php
require_once __DIR__ . '/../../config_db/database.php';

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

function getUsuarioById($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            $usuario['avatar'] = !empty($usuario['imagen_perfil']) && $usuario['imagen_perfil'] !== 'default-avatar.png'
                ? '../../images/users/' . $usuario['imagen_perfil']
                : '../../images/users/default-avatar.png';
            return $usuario;
        }
        return null;
    } catch (Exception $e) {
        error_log("Error en getUsuarioById: " . $e->getMessage());
        return null;
    }
}

function updateUsuario($id, $datos) {
    global $conn;
    try {
        $fields = [];
        $params = [];
        $types = "";

        foreach ($datos as $field => $value) {
            $fields[] = "$field = ?";
            $params[] = $value;
            $types .= is_int($value) ? "i" : "s";
        }

        $params[] = $id;
        $types .= "i";

        $sql = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error en updateUsuario: " . $e->getMessage());
        return false;
    }
}

function deleteUsuario($id) {
    global $conn;
    try {
        $usuario = getUsuarioById($id);
        if ($usuario && $usuario['tipo_user_id'] == 2) {
            return ['success' => false, 'message' => 'No se permite eliminar administradores.'];
        }

        $conn->begin_transaction();

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
            $stmt_mov = $conn->prepare("DELETE FROM movimientos_cartera WHERE cartera_id = ?");
            $stmt_mov->bind_param("i", $cartera_id);
            $stmt_mov->execute();
            $stmt_mov->close();
        }
        
        $stmt_resena = $conn->prepare("DELETE FROM resenas WHERE usuario_id = ?");
        $stmt_resena->bind_param("i", $id);
        $stmt_resena->execute();
        $stmt_resena->close();

        $stmt_carrito = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt_carrito->bind_param("i", $id);
        $stmt_carrito->execute();
        $stmt_carrito->close();

        $stmt_tarjetas = $conn->prepare("DELETE FROM tarjetas WHERE usuario_id = ?");
        $stmt_tarjetas->bind_param("i", $id);
        $stmt_tarjetas->execute();
        $stmt_tarjetas->close();

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
            $stmt_detalles->bind_param($types, ...$pedido_ids);
            $stmt_detalles->execute();
            $stmt_detalles->close();
        }

        $stmt_carteras = $conn->prepare("DELETE FROM carteras WHERE usuario_id = ?");
        $stmt_carteras->bind_param("i", $id);
        $stmt_carteras->execute();
        $stmt_carteras->close();
        
        $stmt_pedidos = $conn->prepare("DELETE FROM pedidos WHERE usuario_id = ?");
        $stmt_pedidos->bind_param("i", $id);
        $stmt_pedidos->execute();
        $stmt_pedidos->close();

        $stmt_user = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt_user->bind_param("i", $id);
        
        if ($stmt_user->execute()) {
            $stmt_user->close();
            $conn->commit();
            return ['success' => true, 'message' => 'Usuario y todos los datos asociados eliminados correctamente.'];
        } else {
            $stmt_user->close();
            $conn->rollback(); 
            return ['success' => false, 'message' => 'Error al eliminar el registro principal del usuario.'];
        }
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Error de la base de datos: ' . $e->getMessage()];
    }
}
function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}
