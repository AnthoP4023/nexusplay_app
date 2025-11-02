<?php

function getAdminData($conn, $user_id) {
    $stmt = $conn->prepare("SELECT username, email, nombre, apellido, imagen_perfil, fecha_registro FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getAdminOrders($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT p.*, COUNT(dp.id) as total_items, GROUP_CONCAT(j.titulo SEPARATOR ', ') as juegos_comprados
        FROM pedidos p
        LEFT JOIN detalles_pedido dp ON p.id = dp.pedido_id
        LEFT JOIN juegos j ON dp.juego_id = j.id
        WHERE p.usuario_id = ?
        GROUP BY p.id
        ORDER BY p.fecha_pedido DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getAdminReviews($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT r.*, j.titulo as juego_titulo, j.imagen as juego_imagen
        FROM resenas r
        JOIN juegos j ON r.juego_id = j.id
        WHERE r.usuario_id = ?
        ORDER BY r.fecha_resena DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getAdminMovements($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT mc.tipo, mc.monto, mc.descripcion, mc.fecha
        FROM movimientos_cartera mc
        JOIN carteras c ON mc.cartera_id = c.id
        WHERE c.usuario_id = ?
        ORDER BY mc.fecha DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getAdminCards($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT id,
            RIGHT(AES_DECRYPT(numero_tarjeta, 'clave_cifrado_segura'), 4) AS ultimos_4,
            fecha_expiracion,
            CASE 
                WHEN alias IS NOT NULL AND alias != '' THEN alias
                ELSE CONCAT('Tarjeta ****', RIGHT(AES_DECRYPT(numero_tarjeta, 'clave_cifrado_segura'), 4))
            END AS display_name,
            fecha_registro
        FROM tarjetas
        WHERE usuario_id = ?
        ORDER BY fecha_registro DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getAdminStats($conn, $user_id) {
    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(p.id) as total_pedidos,
            SUM(CASE WHEN p.estado = 'completado' THEN p.total ELSE 0 END) as total_gastado,
            SUM(CASE WHEN p.estado = 'completado' THEN 1 ELSE 0 END) as pedidos_completados,
            SUM(CASE WHEN p.estado = 'pendiente' THEN 1 ELSE 0 END) as pedidos_pendientes,
            SUM(CASE WHEN p.estado = 'cancelado' THEN 1 ELSE 0 END) as pedidos_cancelados
        FROM pedidos p
        WHERE p.usuario_id = ?
    ");
    $stmt_stats->bind_param("i", $user_id);
    $stmt_stats->execute();
    $stats_result = $stmt_stats->get_result();
    $stats = $stats_result->fetch_assoc();

    $stmt_cartera = $conn->prepare("SELECT saldo FROM carteras WHERE usuario_id = ?");
    $stmt_cartera->bind_param("i", $user_id);
    $stmt_cartera->execute();
    $cartera_result = $stmt_cartera->get_result();
    $cartera = $cartera_result->fetch_assoc();
    $saldo_cartera = $cartera ? $cartera['saldo'] : 0;

    return ['stats' => $stats, 'saldo_cartera' => $saldo_cartera];
}
