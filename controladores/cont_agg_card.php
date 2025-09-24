<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_agg_card.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = '';
$mensaje_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_tarjeta'])) {
    $numero_tarjeta   = trim($_POST['numero_tarjeta'] ?? '');
    $fecha_expiracion = trim($_POST['fecha_expiracion'] ?? '');
    $cvv              = trim($_POST['cvv'] ?? '');
    $nombre_titular   = trim($_POST['nombre_titular'] ?? '');
    $alias_tarjeta    = trim($_POST['alias_tarjeta'] ?? '');

    if (empty($numero_tarjeta)) {
        $mensaje = 'El número de tarjeta es requerido';
    } elseif (strlen($numero_tarjeta) < 13) {
        $mensaje = 'El número de tarjeta es demasiado corto';
    } elseif (empty($fecha_expiracion) || !preg_match('/^\d{2}\/\d{2}$/', $fecha_expiracion)) {
        $mensaje = 'Formato de fecha inválido (MM/YY)';
    } elseif (empty($cvv) || strlen($cvv) < 3 || strlen($cvv) > 4) {
        $mensaje = 'CVV inválido (3-4 dígitos)';
    } elseif (strlen($nombre_titular) < 3) {
        $mensaje = 'El nombre del titular debe tener al menos 3 caracteres';
    }

    if (empty($mensaje)) {
        $fecha_parts = explode('/', $fecha_expiracion);
        $mes = intval($fecha_parts[0]);
        $año = intval($fecha_parts[1]) + 2000;
        $fecha_actual = new DateTime();
        $fecha_tarjeta = new DateTime("$año-$mes-01");
        $fecha_tarjeta->modify('last day of this month');

        if ($mes < 1 || $mes > 12) {
            $mensaje = 'Mes inválido en la fecha de expiración';
        } elseif ($fecha_tarjeta <= $fecha_actual) {
            $mensaje = 'La tarjeta está vencida';
        }
    }

    if (empty($mensaje)) {
        $numero_tarjeta_limpio = preg_replace('/\s/', '', $numero_tarjeta);
        $ultimos_4 = substr($numero_tarjeta_limpio, -4);

        if (cardExists($conn, $user_id, $ultimos_4)) {
            $mensaje = 'Ya tienes una tarjeta registrada con esos últimos 4 dígitos';
            $mensaje_tipo = 'error';
        } else {
            if (empty($alias_tarjeta)) {
                $alias_tarjeta = 'Tarjeta ****' . $ultimos_4;
            }

            $resultado = insertCard($conn, $user_id, $numero_tarjeta_limpio, $fecha_expiracion, $nombre_titular, $alias_tarjeta);

            if ($resultado['success']) {
                $mensaje = $resultado['message'];
                $mensaje_tipo = 'success';
                $redirect_url = isAdmin() ? 'profile/admin/mis_tarjetas.php' : 'profile/user/mis_tarjetas.php';
                header("Refresh:3; url=$redirect_url");
            } else {
                $mensaje = $resultado['message'];
                $mensaje_tipo = 'error';
            }
        }
    } else {
        $mensaje_tipo = 'error';
    }
}
?>
