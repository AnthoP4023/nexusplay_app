<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';

/**
 * Obtiene el saldo actual de la cartera del usuario
 */
function getUserWallet($conn, $user_id) {
    $stmt = $conn->prepare("SELECT saldo FROM carteras WHERE usuario_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartera = $result->fetch_assoc();
    return $cartera ? $cartera['saldo'] : 0;
}

/**
 * Guarda una nueva tarjeta del usuario
 */
function saveUserCard($conn, $user_id, $numero_tarjeta, $fecha_expiracion, $cvv, $nombre_titular, $alias = null) {
    $stmt = $conn->prepare("
        INSERT INTO tarjetas (usuario_id, numero_tarjeta, fecha_expiracion, cvv, nombre_titular, alias, fecha_registro)
        VALUES (?, AES_ENCRYPT(?, 'clave_cifrado_segura'), ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("isssss", $user_id, $numero_tarjeta, $fecha_expiracion, $cvv, $nombre_titular, $alias);
    $stmt->execute();
}

/**
 * Realiza una recarga en la cartera del usuario
 */
function rechargeWallet($conn, $user_id, $monto, $descripcion) {
    // Obtener cartera actual
    $stmt = $conn->prepare("SELECT id, saldo FROM carteras WHERE usuario_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cartera = $stmt->get_result()->fetch_assoc();

    if (!$cartera) {
        // Crear cartera si no existe
        $stmt_insert = $conn->prepare("INSERT INTO carteras (usuario_id, saldo) VALUES (?, ?)");
        $stmt_insert->bind_param("id", $user_id, $monto);
        $stmt_insert->execute();
        $cartera_id = $conn->insert_id;
    } else {
        // Actualizar saldo
        $nuevo_saldo = $cartera['saldo'] + $monto;
        $stmt_update = $conn->prepare("UPDATE carteras SET saldo = ? WHERE id = ?");
        $stmt_update->bind_param("di", $nuevo_saldo, $cartera['id']);
        $stmt_update->execute();
        $cartera_id = $cartera['id'];
    }

    // Registrar movimiento
    $stmt_mov = $conn->prepare("
        INSERT INTO movimientos_cartera (cartera_id, tipo, monto, descripcion, fecha)
        VALUES (?, 'recarga', ?, ?, NOW())
    ");
    $stmt_mov->bind_param("ids", $cartera_id, $monto, $descripcion);
    $stmt_mov->execute();
}
?>
