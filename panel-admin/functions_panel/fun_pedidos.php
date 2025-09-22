<?php
require_once __DIR__ . '/../../config_db/database.php';

function obtenerTodosPedidos() {
    global $conn;
    
    $sql = "SELECT p.*, u.username, u.email,
                   COUNT(dp.id) as total_items,
                   GROUP_CONCAT(j.titulo ORDER BY j.titulo SEPARATOR ', ') as juegos_comprados
            FROM pedidos p 
            JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN detalles_pedido dp ON p.id = dp.pedido_id 
            LEFT JOIN juegos j ON dp.juego_id = j.id 
            GROUP BY p.id 
            ORDER BY p.fecha_pedido DESC";
    
    $result = $conn->query($sql);
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return [];
}

function obtenerEstadisticasPedidos() {
    global $conn;
    
    $sql = "SELECT 
                COUNT(*) as total_pedidos,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pedidos_pendientes,
                SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as pedidos_completados,
                SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as pedidos_cancelados,
                SUM(CASE WHEN estado = 'completado' THEN total ELSE 0 END) as ingresos_totales
            FROM pedidos";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return [
        'total_pedidos' => 0,
        'pedidos_pendientes' => 0,
        'pedidos_completados' => 0,
        'pedidos_cancelados' => 0,
        'ingresos_totales' => 0
    ];
}

function actualizarEstadoPedido($pedido_id, $estado) {
    global $conn;
    
    $estados_validos = ['pendiente', 'completado', 'cancelado'];
    if (!in_array($estado, $estados_validos)) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $pedido_id);
    
    return $stmt->execute();
}

function obtenerDetallePedido($pedido_id) {
    global $conn;
    
    $sql = "SELECT p.*, u.username, u.email, u.nombre, u.apellido
            FROM pedidos p 
            JOIN usuarios u ON p.usuario_id = u.id 
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $pedido = $result->fetch_assoc();
        
        $sql_items = "SELECT dp.*, j.titulo, j.precio, j.imagen
                      FROM detalles_pedido dp
                      JOIN juegos j ON dp.juego_id = j.id
                      WHERE dp.pedido_id = ?";
        
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param("i", $pedido_id);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();
        
        $pedido['items'] = $items_result->fetch_all(MYSQLI_ASSOC);
        
        return $pedido;
    }
    
    return null;
}

if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $pedido_id = intval($_POST['pedido_id']);
    
    if ($action === 'completar') {
        $success = actualizarEstadoPedido($pedido_id, 'completado');
        echo json_encode(['success' => $success, 'message' => $success ? 'Pedido completado' : 'Error al completar']);
    } else if ($action === 'cancelar') {
        $success = actualizarEstadoPedido($pedido_id, 'cancelado');
        echo json_encode(['success' => $success, 'message' => $success ? 'Pedido cancelado' : 'Error al cancelar']);
    }
    
    exit();
}
?>