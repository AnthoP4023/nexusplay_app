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
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ? AND tipo_user_id = 1");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error en deleteUsuario: " . $e->getMessage());
        return false;
    }
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}
