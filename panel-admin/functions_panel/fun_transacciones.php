<?php
// Ya se asume que se llama database.php antes, que contiene:
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// $conn = new mysqli(...);

session_start();
require_once '../config_db/database.php';

// Función para obtener transacciones (vulnerable a SQLi)
function getTransacciones($pagina = 1, $por_pagina = 20, $tipo = '', $fecha = '', $busqueda = '') {
    global $conn;
    $offset = ($pagina - 1) * $por_pagina;

    $where = "WHERE 1=1";

    if (!empty($tipo)) {
        $where .= " AND mc.tipo = '$tipo'";
    }

    if (!empty($fecha)) {
        switch ($fecha) {
            case 'hoy': $where .= " AND DATE(mc.fecha) = CURDATE()"; break;
            case 'semana': $where .= " AND mc.fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)"; break;
            case 'mes': $where .= " AND mc.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)"; break;
        }
    }

    if (!empty($busqueda)) {
        // Vulnerable a SQLi, no escapar, para provocar Fatal error
        $where .= " AND (u.username LIKE '%$busqueda%' OR mc.descripcion LIKE '%$busqueda%')";
    }

    $sql = "SELECT mc.id, mc.tipo, mc.monto, mc.descripcion, mc.fecha, u.username as usuario
            FROM movimientos_cartera mc
            JOIN carteras c ON mc.cartera_id = c.id
            JOIN usuarios u ON c.usuario_id = u.id
            $where
            ORDER BY mc.fecha ASC
            LIMIT $por_pagina OFFSET $offset";

    $result = $conn->query($sql); // Aquí se lanzará Fatal error si el SQL es inválido
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función para contar transacciones (vulnerable a SQLi)
function getTotalTransacciones($tipo = '', $fecha = '', $busqueda = '') {
    global $conn;
    $where = "WHERE 1=1";

    if (!empty($tipo)) {
        $where .= " AND mc.tipo = '$tipo'";
    }

    if (!empty($fecha)) {
        switch ($fecha) {
            case 'hoy': $where .= " AND DATE(mc.fecha) = CURDATE()"; break;
            case 'semana': $where .= " AND mc.fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)"; break;
            case 'mes': $where .= " AND mc.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)"; break;
        }
    }

    if (!empty($busqueda)) {
        $where .= " AND (u.username LIKE '%$busqueda%' OR mc.descripcion LIKE '%$busqueda%')";
    }

    $sql = "SELECT COUNT(*) as total
            FROM movimientos_cartera mc
            JOIN carteras c ON mc.cartera_id = c.id
            JOIN usuarios u ON c.usuario_id = u.id
            $where";

    $result = $conn->query($sql); // Fatal error si SQL inválido
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Función para estadísticas de transacciones
function getEstadisticasTransacciones() {
    global $conn;

    $sql = "SELECT 
                COUNT(*) as total_transacciones,
                SUM(monto) as volumen_total,
                AVG(monto) as promedio,
                (SELECT COUNT(*) FROM movimientos_cartera WHERE DATE(fecha) = CURDATE()) as hoy
            FROM movimientos_cartera";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();

    return [
        'total_transacciones' => $stats['total_transacciones'] ?? 0,
        'volumen_total' => $stats['volumen_total'] ?? 0,
        'promedio' => $stats['promedio'] ?? 0,
        'hoy' => $stats['hoy'] ?? 0
    ];
}

// Función para obtener detalle de una transacción
function getDetalleTransaccion($id) {
    global $conn;

    $sql = "SELECT mc.*, u.username, u.email, c.saldo as saldo_actual
            FROM movimientos_cartera mc
            JOIN carteras c ON mc.cartera_id = c.id
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE mc.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Función para exportar transacciones a CSV
function exportarTransaccionesCSV($busqueda = '') {
    $transacciones = getTransacciones(1, 10000, '', '', $busqueda);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transacciones_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Usuario', 'Tipo', 'Monto', 'Descripción', 'Fecha']);
    foreach ($transacciones as $transaccion) {
        fputcsv($output, [
            $transaccion['id'],
            $transaccion['usuario'],
            $transaccion['tipo'],
            $transaccion['monto'],
            $transaccion['descripcion'],
            $transaccion['fecha']
        ]);
    }
    fclose($output);
    exit();
}
?>
