<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_recharge.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = '';
$mensaje_tipo = '';

try {
    $saldo_cartera = getUserWallet($conn, $user_id);

    $stmt = $conn->prepare("
        SELECT id, RIGHT(AES_DECRYPT(numero_tarjeta, 'clave_cifrado_segura'), 4) AS ultimos_4, alias
        FROM tarjetas
        WHERE usuario_id = ?
        ORDER BY fecha_registro DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $tarjetas = $stmt->get_result();

} catch (Exception $e) {
    die("Error al cargar datos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['realizar_recarga'])) {
    $monto_recarga = $_POST['monto_recarga'] ?? '';
    $custom_amount = $_POST['custom_amount'] ?? '';
    $metodo_pago = $_POST['metodo_pago'] ?? '';

    if ($monto_recarga === 'custom') {
        $monto_final = floatval($custom_amount);
    } else {
        $monto_final = floatval($monto_recarga);
    }

    if ($monto_final <= 0) {
        $mensaje = "Monto inválido para recarga.";
        $mensaje_tipo = 'error';
    } else {
        try {
            if ($metodo_pago === 'nueva_tarjeta' && !empty($_POST['guardar_tarjeta'])) {
                $numero_tarjeta = $_POST['numero_tarjeta'];
                $fecha_expiracion = $_POST['fecha_expiracion'];
                $cvv = $_POST['cvv'];
                $nombre_titular = $_POST['nombre_titular'];
                $alias = $_POST['alias_tarjeta'] ?? null;

                saveUserCard($conn, $user_id, $numero_tarjeta, $fecha_expiracion, $cvv, $nombre_titular, $alias);
            }

            $descripcion = "Recarga de cartera - $" . number_format($monto_final, 2);
            rechargeWallet($conn, $user_id, $monto_final, $descripcion);

            $mensaje = "¡Recarga exitosa! Se han agregado $" . number_format($monto_final, 2) . " a tu cartera.";
            $mensaje_tipo = 'success';

            $saldo_cartera += $monto_final;

        } catch (Exception $e) {
            $mensaje = "Error al procesar la recarga: " . $e->getMessage();
            $mensaje_tipo = 'error';
        }
    }
}
?>
