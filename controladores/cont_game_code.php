<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config_db/database.php';
require_once __DIR__ . '/../functions/fun_auth.php';
require_once __DIR__ . '/../functions/fun_game_code.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$pedido_id = isset($_GET['pedido_id']) ? intval($_GET['pedido_id']) : 0;

if ($pedido_id <= 0) {
    header('Location: index.php');
    exit();
}

if (!puedeAccederCodigos($conn, $pedido_id, $user_id)) {
    header('Location: profile/user/mis_pedidos.php');
    exit();
}

$pedido = obtenerPedidoConVerificacion($conn, $pedido_id, $user_id);
if (!$pedido) {
    header('Location: profile/user/mis_pedidos.php');
    exit();
}

$juegos_comprados = obtenerJuegosConCodigos($conn, $pedido_id);

if (empty($juegos_comprados)) {
    header('Location: profile/user/mis_pedidos.php');
    exit();
}

$estadisticas = calcularEstadisticasPedido($juegos_comprados, $pedido);

registrarActividadCodigos($conn, $pedido_id, $user_id, 'view');

$hay_codigos_pendientes = tieneCodigosPendientes($juegos_comprados);

$total_juegos = $estadisticas['total_juegos'];
$fecha_formateada = $estadisticas['fecha_formateada'];
$numero_pedido = $estadisticas['numero_pedido'];
$total_formateado = $estadisticas['total_formateado'];
?>