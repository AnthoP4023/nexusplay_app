<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_agg_cart.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = '';
$mensaje_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_tarjeta'])) {
    
    $numero_tarjeta = trim($_POST['numero_tarjeta'] ?? '');
    $fecha_expiracion = trim($_POST['fecha_expiracion'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');
    $nombre_titular = trim($_POST['nombre_titular'] ?? '');
    $alias_tarjeta = trim($_POST['alias_tarjeta'] ?? '');
    
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
        } elseif (!preg_match('/^\d+$/', $numero_tarjeta_limpio)) {
            $mensaje = 'El número de tarjeta solo puede contener números';
            $mensaje_tipo = 'error';
        } elseif (!preg_match('/^\d{2}\/\d{2}$/', $fecha_expiracion)) {
            $mensaje = 'Formato de fecha inválido (MM/YY)';
            $mensaje_tipo = 'error';
        } elseif (strlen($cvv) < 3 || strlen($cvv) > 4) {
            $mensaje = 'CVV inválido (3-4 dígitos)';
            $mensaje_tipo = 'error';
        } elseif (!preg_match('/^\d+$/', $cvv)) {
            $mensaje = 'El CVV solo puede contener números';
            $mensaje_tipo = 'error';
        } elseif (strlen($nombre_titular) < 3) {
            $mensaje = 'El nombre del titular debe tener al menos 3 caracteres';
            $mensaje_tipo = 'error';
        } else {
            $fecha_parts = explode('/', $fecha_expiracion);
            $mes = intval($fecha_parts[0]);
            $año = intval($fecha_parts[1]) + 2000; 
            
            if ($mes < 1 || $mes > 12) {
                $mensaje = 'Mes inválido en la fecha de expiración';
                $mensaje_tipo = 'error';
            } else {
                $fecha_actual = new DateTime();
                $fecha_tarjeta = new DateTime("$año-$mes-01");
                $fecha_tarjeta->modify('last day of this month');
                
                if ($fecha_tarjeta <= $fecha_actual) {
                    $mensaje = 'La tarjeta está vencida';
                    $mensaje_tipo = 'error';
                }
            }
        }
    }
    
    if (empty($mensaje)) {
        try {
            $ultimos_4 = substr($numero_tarjeta_limpio, -4);
            $stmt_check = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM tarjetas 
                WHERE usuario_id = ? AND RIGHT(AES_DECRYPT(numero_tarjeta, 'clave_cifrado_segura'), 4) = ?
            ");
            $stmt_check->bind_param("is", $user_id, $ultimos_4);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $check_data = $result_check->fetch_assoc();
            
            if ($check_data['count'] > 0) {
                $mensaje = 'Ya tienes una tarjeta registrada con esos últimos 4 dígitos';
                $mensaje_tipo = 'error';
            } else {
                if (empty($alias_tarjeta)) {
                    $alias_tarjeta = 'Tarjeta ****' . $ultimos_4;
                }
                
                $stmt_insert = $conn->prepare("
                    INSERT INTO tarjetas (usuario_id, numero_tarjeta, fecha_expiracion, nombre_titular, alias, fecha_registro) 
                    VALUES (?, AES_ENCRYPT(?, 'clave_cifrado_segura'), ?, ?, ?, NOW())
                ");
                $stmt_insert->bind_param("issss", $user_id, $numero_tarjeta_limpio, 
                                       $fecha_expiracion, $nombre_titular, $alias_tarjeta);
                
                if ($stmt_insert->execute()) {
                    $mensaje = '¡Tarjeta agregada exitosamente! Ya puedes usarla para tus compras.';
                    $mensaje_tipo = 'success';
                    
                    $redirect_url = isAdmin() ? 'profile/admin/mis_tarjetas.php' : 'profile/user/mis_tarjetas.php';
                    
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = '$redirect_url';
                        }, 3000);
                    </script>";
                } else {
                    throw new Exception("Error al insertar la tarjeta en la base de datos");
                }
            }
            
        } catch (Exception $e) {
            $mensaje = "Error al procesar la tarjeta: " . $e->getMessage();
            $mensaje_tipo = 'error';
            error_log("Error al agregar tarjeta: " . $e->getMessage());
        }
    }
}

function validarLuhn($numero) {
    $numero = preg_replace('/\D/', '', $numero);
    $longitud = strlen($numero);
    $suma = 0;
    
    for ($i = $longitud - 1; $i >= 0; $i--) {
        $digito = intval($numero[$i]);
        
        if (($longitud - $i) % 2 == 0) {
            $digito *= 2;
            if ($digito > 9) {
                $digito -= 9;
            }
        }
        
        $suma += $digito;
    }
    
    return ($suma % 10) == 0;
}

function detectarTipoTarjeta($numero) {
    $numero = preg_replace('/\D/', '', $numero);
    
    if (preg_match('/^4/', $numero)) {
        return 'Visa';
    } elseif (preg_match('/^5[1-5]/', $numero)) {
        return 'MasterCard';
    } elseif (preg_match('/^3[47]/', $numero)) {
        return 'American Express';
    } elseif (preg_match('/^6(?:011|5)/', $numero)) {
        return 'Discover';
    } else {
        return 'Desconocida';
    }
}
?>