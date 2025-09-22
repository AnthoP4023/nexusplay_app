<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_buy_game.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = '';
$mensaje_tipo = '';

$carrito_items = [];
$total_carrito = 0;

if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    $carrito_items = $_SESSION['carrito'];
    foreach ($carrito_items as $item) {
        $total_carrito += $item['precio'] * $item['cantidad'];
    }
} else {
    header('Location: cart.php');
    exit();
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['realizar_compra'])) {
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    
    if (empty($metodo_pago)) {
        $mensaje = 'Por favor selecciona un método de pago';
        $mensaje_tipo = 'error';
    }
    elseif ($metodo_pago === 'tarjeta') {
        $tarjeta_seleccionada = $_POST['tarjeta_seleccionada'] ?? '';
        if (empty($tarjeta_seleccionada)) {
            $mensaje = 'Por favor selecciona una tarjeta';
            $mensaje_tipo = 'error';
        }
    }
    elseif ($metodo_pago === 'nueva_tarjeta') {
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
            
            if ($metodo_pago === 'cartera') {
                if ($saldo_cartera < $total_carrito) {
                    throw new Exception("Saldo insuficiente en la cartera");
                }
                
                $nuevo_saldo = $saldo_cartera - $total_carrito;
                $stmt_update_wallet = $conn->prepare("UPDATE carteras SET saldo = ? WHERE usuario_id = ?");
                $stmt_update_wallet->bind_param("di", $nuevo_saldo, $user_id);
                $stmt_update_wallet->execute();
                
                $cantidad_juegos = count($carrito_items);
                $stmt_movement = $conn->prepare("
                    INSERT INTO movimientos_cartera (cartera_id, tipo, monto, descripcion)
                    SELECT id, 'compra', ?, CONCAT('Compra de ', ?, ' juego(s)')
                    FROM carteras WHERE usuario_id = ?
                ");
                $stmt_movement->bind_param("dii", $total_carrito, $cantidad_juegos, $user_id);
                $stmt_movement->execute();
                
                $metodo_pago_texto = 'Cartera NexusPlay';
                
            } elseif ($metodo_pago === 'tarjeta') {
                $tarjeta_id = $_POST['tarjeta_seleccionada'] ?? 0;
                
                if ($tarjeta_id <= 0) {
                    throw new Exception("Debe seleccionar una tarjeta");
                }
                
                $stmt_verify_card = $conn->prepare("SELECT alias FROM tarjetas WHERE id = ? AND usuario_id = ?");
                $stmt_verify_card->bind_param("ii", $tarjeta_id, $user_id);
                $stmt_verify_card->execute();
                $card_result = $stmt_verify_card->get_result();
                
                if ($card_result->num_rows === 0) {
                    throw new Exception("Tarjeta no válida");
                }
                
                $card_info = $card_result->fetch_assoc();
                $metodo_pago_texto = 'Tarjeta: ' . $card_info['alias'];
                
            } elseif ($metodo_pago === 'nueva_tarjeta') {
                $numero_tarjeta = preg_replace('/\s/', '', $_POST['numero_tarjeta'] ?? '');
                $fecha_expiracion = $_POST['fecha_expiracion'] ?? '';
                $cvv = $_POST['cvv'] ?? '';
                $nombre_titular = $_POST['nombre_titular'] ?? '';
                $alias_tarjeta = $_POST['alias_tarjeta'] ?? 'Mi Tarjeta';
                
                if (strlen($numero_tarjeta) < 13 || strlen($numero_tarjeta) > 19) {
                    throw new Exception("Número de tarjeta inválido");
                }
                
                if (!preg_match('/^\d{2}\/\d{2}$/', $fecha_expiracion)) {
                    throw new Exception("Fecha de expiración inválida (MM/YY)");
                }
                
                if (strlen($cvv) < 3 || strlen($cvv) > 4) {
                    throw new Exception("CVV inválido");
                }
                
                if (empty($nombre_titular)) {
                    throw new Exception("Nombre del titular requerido");
                }
                
                if (isset($_POST['guardar_tarjeta']) && $_POST['guardar_tarjeta'] === 'on') {
                    $stmt_save_card = $conn->prepare("
                        INSERT INTO tarjetas (usuario_id, numero_tarjeta, fecha_expiracion, alias)
                        VALUES (?, AES_ENCRYPT(?, 'clave_cifrado_segura'), ?, ?)
                    ");
                    $stmt_save_card->bind_param("isss", $user_id, $numero_tarjeta, $fecha_expiracion, $alias_tarjeta);
                    $stmt_save_card->execute();
                }
                
                $metodo_pago_texto = 'Tarjeta terminada en ' . substr($numero_tarjeta, -4);
                
            } else {
                throw new Exception("Método de pago no válido");
            }
            
            $stmt_pedido = $conn->prepare("
                INSERT INTO pedidos (usuario_id, total, estado, metodo_pago, fecha_pedido)
                VALUES (?, ?, 'completado', ?, NOW())
            ");
            $stmt_pedido->bind_param("ids", $user_id, $total_carrito, $metodo_pago_texto);
            $stmt_pedido->execute();
            $pedido_id = $conn->insert_id;
            
            foreach ($carrito_items as $item) {
                for ($i = 0; $i < $item['cantidad']; $i++) {
                    $stmt_codigo = $conn->prepare("
                        SELECT codigo FROM codigos_juegos 
                        WHERE juego_id = ? AND estado = 'disponible' 
                        LIMIT 1
                    ");
                    $stmt_codigo->bind_param("i", $item['id']);
                    $stmt_codigo->execute();
                    $codigo_result = $stmt_codigo->get_result();
                    
                    $codigo = null;
                    if ($codigo_result->num_rows > 0) {
                        $codigo_row = $codigo_result->fetch_assoc();
                        $codigo = $codigo_row['codigo'];
                        
                        $stmt_update_codigo = $conn->prepare("
                            UPDATE codigos_juegos SET estado = 'vendido' 
                            WHERE codigo = ?
                        ");
                        $stmt_update_codigo->bind_param("s", $codigo);
                        $stmt_update_codigo->execute();
                    } else {
                        $codigo = 'GAME-' . strtoupper(substr(md5(uniqid()), 0, 12));
                    }
                    
                    $stmt_detalle = $conn->prepare("
                        INSERT INTO detalles_pedido (pedido_id, juego_id, cantidad, precio_unitario, codigo_entregado)
                        VALUES (?, ?, 1, ?, ?)
                    ");
                    $stmt_detalle->bind_param("iids", $pedido_id, $item['id'], $item['precio'], $codigo);
                    $stmt_detalle->execute();
                }
            }
            
            unset($_SESSION['carrito']);
            if (isLoggedIn()) {
                $stmt_clear_cart = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
                $stmt_clear_cart->bind_param("i", $user_id);
                $stmt_clear_cart->execute();
            }
            
            $conn->commit();
            header('Location: game_code.php?pedido_id=' . $pedido_id);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback(); 
            $mensaje = $e->getMessage();
            $mensaje_tipo = 'error';
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            $mensaje = 'Error en el procesamiento de la compra';
            $mensaje_tipo = 'error';
        }
        
        $conn->autocommit(TRUE); 
    }
}
?>