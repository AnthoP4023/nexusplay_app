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

function getUsuarios($page = 1, $limit = 20, $search = '', $tipo = '') {
    global $conn;
    $offset = ($page - 1) * $limit;
    
    try {
        $where = "WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $where .= " AND (username LIKE ? OR email LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }
        
        if (!empty($tipo)) {
            $where .= " AND tipo_user_id = ?";
            $params[] = $tipo;
            $types .= "i";
        }
        
        $sql = "SELECT id, username, email, tipo_user_id, saldo, fecha_registro, imagen_perfil 
        FROM usuarios $where 
        ORDER BY fecha_registro DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2)); 
}
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

function getTotalUsuariosCount($search = '', $tipo = '') {
    global $conn;
    
    try {
        $where = "WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $where .= " AND (username LIKE ? OR email LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }
        
        if (!empty($tipo)) {
            $where .= " AND tipo_user_id = ?";
            $params[] = $tipo;
            $types .= "i";
        }
        
        $sql = "SELECT COUNT(*) as total FROM usuarios $where";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
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

function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}
?>