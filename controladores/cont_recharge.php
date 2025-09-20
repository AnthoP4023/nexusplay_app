<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = '';
$mensaje_tipo = '';

$saldo_cartera = 0;
try {
    $stmt_user = $conn->prepare("
        SELECT u.*, c.saldo as saldo_cartera
        FROM usuarios u
        LEFT JOIN carteras c ON u.id = c.usuario_id
        WHERE u.id = ?
    ");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    $user = $user_result->fetch_assoc();
    
    $saldo_cartera = $user['saldo_cartera'] ?? 0;
    
    if ($user['saldo_cartera'] === null) {
        $stmt_create_wallet = $conn->prepare("INSERT INTO carteras (usuario_id, saldo) VALUES (?, 0.00)");
        $stmt_create_wallet->bind_param("i", $user_id);
        $stmt_create_wallet->execute();
        $saldo_cartera = 0;
    }
} catch (mysqli_sql_exception $e) {
    die("Error al obtener datos del usuario: " . $e->getMessage());
}

$tarjetas = [];
try {
    $stmt_cards = $conn->prepare("
        SELECT id, RIGHT(AES_DECRYPT(numero_tarjeta, 'clave_cifrado_segura'), 4) as ultimos_4,
               fecha_expiracion, alias
        FROM tarjetas 
        WHERE usuario_id = ?
        ORDER BY fecha_registro DESC
    ");
    $stmt_cards->bind_param("i", $user_id);
    $stmt_cards->execute();
    $cards_result = $stmt_cards->get_result();
    
    while ($card = $cards_result->fetch_assoc()) {
        $tarjetas[] = $card;
    }
} catch (mysqli_sql_exception $e) {
    die("Error al obtener tarjetas: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['realizar_recarga'])) {
    
    $monto_recarga = $_POST['monto_recarga'] ?? '';
    $custom_amount = $_POST['custom_amount'] ?? '';
    
    if (empty($monto_recarga)) {
        $mensaje = 'Por favor selecciona un monto a recargar';
        $mensaje_tipo = 'error';
    } else {
        if ($monto_recarga === 'custom') {
            if (empty($custom_amount) || !is_numeric($custom_amount)) {
                $mensaje = 'Por favor ingresa un monto personalizado válido';
                $mensaje_tipo = 'error';
            } else {
                $monto_final = floatval($custom_amount);
                if ($monto_final < 1 || $monto_final > 1000) {
                    $mensaje = 'El monto debe estar entre $1.00 y $1,000.00';
                    $mensaje_tipo = 'error';
                }
            }
        } else {
            $monto_final = floatval($monto_recarga);
            if ($monto_final <= 0) {
                $mensaje = 'Monto de recarga inválido';
                $mensaje_tipo = 'error';
            }
        }
    }
    
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    if (empty($mensaje) && empty($metodo_pago)) {
        $mensaje = 'Por favor selecciona un método de pago';
        $mensaje_tipo = 'error';
    }
    
    if (empty($mensaje) && $metodo_pago === 'nueva_tarjeta') {
        $numero_tarjeta = trim($_POST['numero_tarjeta'] ?? '');
        $fecha_expiracion = trim($_POST['fecha_expiracion'] ?? '');
        $cvv = trim($_POST['cvv'] ?? '');
        $nombre_titular = trim($_POST['nombre_titular'] ?? '');
        
        if (empty($numero_tarjeta)) {
            $mensaje = 'El número de tarjeta es requerido';
            $mensaje_tipo = 'error';
        } elseif (empty($fecha_expiracion)) {
            $mensaje = 'La fecha de expiración es requerida';
            $mensaje_tipo = 'error';
        } elseif (empty($cvv)) {
            $mensaje = 'El CVV es requerido';
            $mensaje_tipo = 'error';
        } elseif (empty($nombre_titular)) {
            $mensaje = 'El nombre del titular es requerido';
            $mensaje_tipo = 'error';
        } else {
            $numero_tarjeta_limpio = preg_replace('/\s/', '', $numero_tarjeta);
            
            if (strlen($numero_tarjeta_limpio) < 13 || strlen($numero_tarjeta_limpio) > 19) {
                $mensaje = 'Número de tarjeta inválido';
                $mensaje_tipo = 'error';
            } elseif (!preg_match('/^\d{2}\/\d{2}$/', $fecha_expiracion)) {
                $mensaje = 'Fecha de expiración inválida (MM/YY)';
                $mensaje_tipo = 'error';
            } elseif (strlen($cvv) < 3 || strlen($cvv) > 4) {
                $mensaje = 'CVV inválido';
                $mensaje_tipo = 'error';
            }
        }
    }
    
    if (empty($mensaje)) {
        try {
            $conn->autocommit(FALSE);
            
            if ($metodo_pago === 'nueva_tarjeta' && isset($_POST['guardar_tarjeta'])) {
                $alias_tarjeta = trim($_POST['alias_tarjeta'] ?? '');
                if (empty($alias_tarjeta)) {
                    $alias_tarjeta = 'Tarjeta ****' . substr($numero_tarjeta_limpio, -4);
                }
                
                $stmt_insert_card = $conn->prepare("
                    INSERT INTO tarjetas (usuario_id, numero_tarjeta, fecha_expiracion, nombre_titular, alias) 
                    VALUES (?, AES_ENCRYPT(?, 'clave_cifrado_segura'), ?, ?, ?)
                ");
                $stmt_insert_card->bind_param("issss", $user_id, $numero_tarjeta_limpio, 
                                            $fecha_expiracion, $nombre_titular, $alias_tarjeta);
                $stmt_insert_card->execute();
            }
            
            $nuevo_saldo = $saldo_cartera + $monto_final;
            $stmt_update_wallet = $conn->prepare("
                UPDATE carteras 
                SET saldo = ? 
                WHERE usuario_id = ?
            ");
            $stmt_update_wallet->bind_param("di", $nuevo_saldo, $user_id);
            $stmt_update_wallet->execute();
            
            $descripcion = "Recarga de cartera - $" . number_format($monto_final, 2);
            $stmt_movement = $conn->prepare("
                INSERT INTO movimientos_cartera (cartera_id, tipo, monto, descripcion)
                SELECT id, 'recarga', ?, ?
                FROM carteras WHERE usuario_id = ?
            ");
            $stmt_movement->bind_param("dsi", $monto_final, $descripcion, $user_id);
            $stmt_movement->execute();
            
            $conn->commit();
            
            $mensaje = "¡Recarga exitosa! Se han agregado $" . number_format($monto_final, 2) . " a tu cartera.";
            $mensaje_tipo = 'success';
            
            $saldo_cartera = $nuevo_saldo;
            
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "Error al procesar la recarga: " . $e->getMessage();
            $mensaje_tipo = 'error';
            error_log("Error en recarga: " . $e->getMessage());
        } finally {
            $conn->autocommit(TRUE);
        }
    }
}
?>