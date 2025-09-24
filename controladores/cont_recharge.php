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

    // Validaciones
    if (empty($monto_recarga)) {
        $mensaje = "Debes seleccionar un monto de recarga.";
        $mensaje_tipo = 'error';
    } elseif ($monto_recarga === 'custom' && empty($custom_amount)) {
        $mensaje = "Debes ingresar un monto personalizado.";
        $mensaje_tipo = 'error';
    } elseif (empty($metodo_pago)) {
        $mensaje = "Debes seleccionar un método de pago.";
        $mensaje_tipo = 'error';
    } elseif ($metodo_pago === 'nueva_tarjeta' &&
              (empty($_POST['numero_tarjeta']) || empty($_POST['fecha_expiracion']) || empty($_POST['cvv']) || empty($_POST['nombre_titular']))) {
        $mensaje = "Debes completar todos los datos de la nueva tarjeta.";
        $mensaje_tipo = 'error';
    } else {
        $monto_final = $monto_recarga === 'custom' ? floatval($custom_amount) : floatval($monto_recarga);

        if ($monto_final <= 0) {
            $mensaje = "Monto inválido para recarga.";
            $mensaje_tipo = 'error';
        } else {
            try {
                if ($metodo_pago === 'nueva_tarjeta' && !empty($_POST['guardar_tarjeta'])) {
                    $numero_tarjeta = $_POST['numero_tarjeta'];
                    $fecha_expiracion = $_POST['fecha_expiracion'];
                    $nombre_titular = $_POST['nombre_titular'];
                    $alias = $_POST['alias_tarjeta'] ?? null;

                    saveUserCard($conn, $user_id, $numero_tarjeta, $fecha_expiracion, $nombre_titular, $alias);
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
}
?>
