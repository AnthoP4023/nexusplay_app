<?php
require_once __DIR__ . '/../config_db/database.php';

function getUserWallet($conn, $user_id) {
    try {
        $stmt = $conn->prepare("SELECT saldo FROM carteras WHERE usuario_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartera = $result->fetch_assoc();
        return $cartera ? floatval($cartera['saldo']) : 0;
    } catch (Exception $e) {
        error_log("Error en getUserWallet: " . $e->getMessage());
        return 0;
    }
}

function saveUserCard($conn, $user_id, $numero_tarjeta, $fecha_expiracion, $nombre_titular, $alias = null) {
    try {
        if (empty($alias)) {
            $ultimos4 = substr(preg_replace('/\D/', '', $numero_tarjeta), -4);
            $alias = "Tarjeta ****$ultimos4";
        }

        $stmt = $conn->prepare("
            INSERT INTO tarjetas (usuario_id, numero_tarjeta, fecha_expiracion, nombre_titular, alias, fecha_registro)
            VALUES (?, AES_ENCRYPT(?, 'clave_cifrado_segura'), ?, ?, ?, NOW())
        ");
        $stmt->bind_param("issss", $user_id, $numero_tarjeta, $fecha_expiracion, $nombre_titular, $alias);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Tarjeta guardada correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al guardar la tarjeta en la base de datos'];
        }
    } catch (Exception $e) {
        error_log("Error en saveUserCard: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error interno al guardar tarjeta'];
    }
}

function rechargeWallet($conn, $user_id, $monto, $descripcion) {
    try {
        $stmt = $conn->prepare("SELECT id, saldo FROM carteras WHERE usuario_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cartera = $stmt->get_result()->fetch_assoc();

        if (!$cartera) {
            $stmt_insert = $conn->prepare("INSERT INTO carteras (usuario_id, saldo) VALUES (?, ?)");
            $stmt_insert->bind_param("id", $user_id, $monto);
            $stmt_insert->execute();
            $cartera_id = $conn->insert_id;
        } else {
            $nuevo_saldo = $cartera['saldo'] + $monto;
            $stmt_update = $conn->prepare("UPDATE carteras SET saldo = ? WHERE id = ?");
            $stmt_update->bind_param("di", $nuevo_saldo, $cartera['id']);
            $stmt_update->execute();
            $cartera_id = $cartera['id'];
        }

        $stmt_mov = $conn->prepare("
            INSERT INTO movimientos_cartera (cartera_id, tipo, monto, descripcion, fecha)
            VALUES (?, 'recarga', ?, ?, NOW())
        ");
        $stmt_mov->bind_param("ids", $cartera_id, $monto, $descripcion);
        $stmt_mov->execute();

        return ['success' => true, 'message' => "Recarga exitosa: $" . number_format($monto, 2)];
    } catch (Exception $e) {
        error_log("Error en rechargeWallet: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al procesar la recarga'];
    }
}
?>
