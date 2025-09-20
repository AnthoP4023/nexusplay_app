<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config_db/database.php';
require_once __DIR__ . '/../functions/fun_auth.php';


if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$password_message = '';
$password_message_type = '';

if (isset($_SESSION['password_message'])) {
    $password_message = $_SESSION['password_message'];
    $password_message_type = $_SESSION['password_message_type'];
    
    unset($_SESSION['password_message']);
    unset($_SESSION['password_message_type']);
}

try {
    $stmt = $conn->prepare("SELECT username, email, nombre, apellido, imagen_perfil, fecha_registro FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $admin_data = $result->fetch_assoc();
        $_SESSION['username'] = $admin_data['username'];

        $imagen_bd = $admin_data['imagen_perfil'];
        
        if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png') {
            $ruta_imagen = '/nexusplay/images/users/' . $imagen_bd;
            $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $ruta_imagen;
            
            if (file_exists($ruta_fisica)) {
                $perfil_img = $ruta_imagen;
            } else {
                $perfil_img = '/nexusplay/images/users/default-avatar.png';
            }
        } else {
            $perfil_img = '/nexusplay/images/users/default-avatar.png';
        }

        $_SESSION['imagen_perfil'] = $perfil_img;

    } else {
        die("Usuario no encontrado.");
    }

    $stmt_pedidos = $conn->prepare("
        SELECT p.*, 
               COUNT(dp.id) as total_items,
               GROUP_CONCAT(j.titulo SEPARATOR ', ') as juegos_comprados
        FROM pedidos p 
        LEFT JOIN detalles_pedido dp ON p.id = dp.pedido_id 
        LEFT JOIN juegos j ON dp.juego_id = j.id 
        WHERE p.usuario_id = ? 
        GROUP BY p.id 
        ORDER BY p.fecha_pedido DESC
    ");
    $stmt_pedidos->bind_param("i", $user_id);
    $stmt_pedidos->execute();
    $pedidos_result = $stmt_pedidos->get_result();

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

    $stmt_movimientos = $conn->prepare("
        SELECT mc.tipo, mc.monto, mc.descripcion, mc.fecha
        FROM movimientos_cartera mc
        JOIN carteras c ON mc.cartera_id = c.id
        WHERE c.usuario_id = ?
        ORDER BY mc.fecha DESC
        LIMIT 10
    ");
    $stmt_movimientos->bind_param("i", $user_id);
    $stmt_movimientos->execute();
    $movimientos_result = $stmt_movimientos->get_result();

    $stmt_tarjetas = $conn->prepare("
        SELECT id, RIGHT(AES_DECRYPT(numero_tarjeta, 'clave_cifrado_segura'), 4) as ultimos_4,
               fecha_expiracion, alias, fecha_registro
        FROM tarjetas 
        WHERE usuario_id = ?
        ORDER BY fecha_registro DESC
    ");
    $stmt_tarjetas->bind_param("i", $user_id);
    $stmt_tarjetas->execute();
    $tarjetas_result = $stmt_tarjetas->get_result();

    $stmt_resenas = $conn->prepare("
        SELECT r.*, j.titulo as juego_titulo, j.imagen as juego_imagen
        FROM resenas r
        JOIN juegos j ON r.juego_id = j.id
        WHERE r.usuario_id = ?
        ORDER BY r.fecha_resena DESC
        LIMIT 5
    ");
    $stmt_resenas->bind_param("i", $user_id);
    $stmt_resenas->execute();
    $resenas_result = $stmt_resenas->get_result();

} catch (mysqli_sql_exception $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?> 