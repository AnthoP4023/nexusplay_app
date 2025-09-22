<?php
require_once __DIR__ . '../../../config_db/database.php';

function getDashboardStats() {
    global $conn;
    $stats = [];
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_usuarios'] = $result->fetch_assoc()['total'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM juegos");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_juegos'] = $result->fetch_assoc()['total'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_pedidos'] = $result->fetch_assoc()['total'];
        
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as ingresos FROM pedidos WHERE estado = 'completado'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['ingresos_totales'] = $result->fetch_assoc()['ingresos'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE MONTH(fecha_pedido) = MONTH(CURRENT_DATE()) AND YEAR(fecha_pedido) = YEAR(CURRENT_DATE())");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['pedidos_mes'] = $result->fetch_assoc()['total'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE MONTH(fecha_registro) = MONTH(CURRENT_DATE()) AND YEAR(fecha_registro) = YEAR(CURRENT_DATE())");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['usuarios_mes'] = $result->fetch_assoc()['total'];
        
    } catch (Exception $e) {
        error_log("Error en getDashboardStats: " . $e->getMessage());
        $stats = [
            'total_usuarios' => 0,
            'total_juegos' => 0,
            'total_pedidos' => 0,
            'ingresos_totales' => 0,
            'pedidos_mes' => 0,
            'usuarios_mes' => 0
        ];
    }
    
    return $stats;
}

function getRecentOrders($limit = 5) {
    global $conn;
    $orders = [];
    
    try {
        $stmt = $conn->prepare("
            SELECT p.id, p.total, p.estado, p.metodo_pago, p.fecha_pedido, 
                   u.username, u.nombre, u.apellido
            FROM pedidos p 
            JOIN usuarios u ON p.usuario_id = u.id 
            ORDER BY p.fecha_pedido DESC 
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
    } catch (Exception $e) {
        error_log("Error en getRecentOrders: " . $e->getMessage());
    }
    
    return $orders;
}

function getSalesByCategory() {
    global $conn;
    $categories = [];
    
    try {
        $stmt = $conn->prepare("
            SELECT c.nombre, COUNT(dp.id) as ventas, SUM(dp.precio_unitario * dp.cantidad) as total_ventas
            FROM categorias c
            LEFT JOIN juegos j ON c.id = j.categoria_id
            LEFT JOIN detalles_pedido dp ON j.id = dp.juego_id
            LEFT JOIN pedidos p ON dp.pedido_id = p.id AND p.estado = 'completado'
            GROUP BY c.id, c.nombre
            ORDER BY total_ventas DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'nombre' => $row['nombre'],
                'ventas' => $row['ventas'] ?? 0,
                'total_ventas' => $row['total_ventas'] ?? 0
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error en getSalesByCategory: " . $e->getMessage());
    }
    
    return $categories;
}

function getTopSellingGames($limit = 5) {
    global $conn;
    $games = [];
    
    try {
        $stmt = $conn->prepare("
            SELECT j.titulo, j.imagen, j.precio, 
                   COUNT(dp.id) as ventas, 
                   SUM(dp.precio_unitario * dp.cantidad) as ingresos
            FROM juegos j
            LEFT JOIN detalles_pedido dp ON j.id = dp.juego_id
            LEFT JOIN pedidos p ON dp.pedido_id = p.id AND p.estado = 'completado'
            GROUP BY j.id, j.titulo, j.imagen, j.precio
            ORDER BY ventas DESC, ingresos DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $games[] = [
                'titulo' => $row['titulo'],
                'imagen' => $row['imagen'],
                'precio' => $row['precio'],
                'ventas' => $row['ventas'] ?? 0,
                'ingresos' => $row['ingresos'] ?? 0
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error en getTopSellingGames: " . $e->getMessage());
    }
    
    return $games;
}

function getTopUsers($limit = 5) {
    global $conn;
    $users = [];
    
    try {
        $stmt = $conn->prepare("
            SELECT u.username, u.nombre, u.apellido, u.imagen_perfil, u.fecha_registro,
                   COUNT(p.id) as total_pedidos,
                   COALESCE(SUM(p.total), 0) as total_gastado
            FROM usuarios u
            LEFT JOIN pedidos p ON u.id = p.usuario_id AND p.estado = 'completado'
            WHERE u.tipo_user_id = 1
            GROUP BY u.id, u.username, u.nombre, u.apellido, u.imagen_perfil, u.fecha_registro
            ORDER BY total_gastado DESC, total_pedidos DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'username' => $row['username'],
                'nombre' => $row['nombre'],
                'apellido' => $row['apellido'],
                'imagen_perfil' => $row['imagen_perfil'],
                'fecha_registro' => $row['fecha_registro'],
                'total_pedidos' => $row['total_pedidos'] ?? 0,
                'total_gastado' => $row['total_gastado'] ?? 0
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error en getTopUsers: " . $e->getMessage());
    }
    
    return $users;
}

function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2, ',', '.');
}

function getStatusColor($status) {
    switch($status) {
        case 'completado':
            return '#10b981';
        case 'pendiente':
            return '#f59e0b';
        case 'cancelado':
            return '#ef4444';
        default:
            return '#6b7280';
    }
}

function getGrowthPercentage() {
    global $conn;
    $growth = 0;
    
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as current_month 
            FROM usuarios 
            WHERE MONTH(fecha_registro) = MONTH(CURRENT_DATE()) 
            AND YEAR(fecha_registro) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc()['current_month'];
        
        $stmt = $conn->prepare("
            SELECT COUNT(*) as last_month 
            FROM usuarios 
            WHERE MONTH(fecha_registro) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
            AND YEAR(fecha_registro) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
        ");
        $stmt->execute();
        $last = $stmt->get_result()->fetch_assoc()['last_month'];
        
        if ($last > 0) {
            $growth = (($current - $last) / $last) * 100;
        }
        
    } catch (Exception $e) {
        error_log("Error en getGrowthPercentage: " . $e->getMessage());
    }
    
    return round($growth, 1);
}
?>
