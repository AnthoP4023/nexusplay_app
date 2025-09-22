<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_profile.php';
require_once __DIR__ . '../../functions/fun_cart.php';


if (isset($_SESSION['user_id'])) {
    $perfil_img = loadUserProfileImage($conn, $_SESSION['user_id']);
    $_SESSION['imagen_perfil'] = $perfil_img;
    
    refreshCarritoSession($conn, $_SESSION['user_id']);
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

$message = '';
$message_type = '';
$carrito_items = array();
$total_carrito = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['add_to_cart']) && isset($_POST['juego_id'])) {
        $juego_id = intval($_POST['juego_id']);
        $cantidad = intval($_POST['cantidad'] ?? 1);
        if ($cantidad <= 0) $cantidad = 1;

        $stmt_check = $conn->prepare("SELECT cantidad FROM carrito WHERE usuario_id=? AND juego_id=?");
        $stmt_check->bind_param("ii", $user_id, $juego_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $stmt_update = $conn->prepare("UPDATE carrito SET cantidad = cantidad + ? WHERE usuario_id=? AND juego_id=?");
            $stmt_update->bind_param("iii", $cantidad, $user_id, $juego_id);
            $stmt_update->execute();
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO carrito(usuario_id, juego_id, cantidad) VALUES(?,?,?)");
            $stmt_insert->bind_param("iii", $user_id, $juego_id, $cantidad);
            $stmt_insert->execute();
        }

        $message = "Juego agregado al carrito";
        $message_type = "success";
    }

    if (isset($_POST['update_quantity']) && isset($_POST['juego_id'], $_POST['nueva_cantidad'])) {
        $juego_id = intval($_POST['juego_id']);
        $cantidad = intval($_POST['nueva_cantidad']);

        if ($cantidad <= 0) {
            $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id=? AND juego_id=?");
            $stmt->bind_param("ii", $user_id, $juego_id);
            $stmt->execute();
            $message = "Juego eliminado del carrito";
            $message_type = "info";
        } else {
            $stmt = $conn->prepare("UPDATE carrito SET cantidad=? WHERE usuario_id=? AND juego_id=?");
            $stmt->bind_param("iii", $cantidad, $user_id, $juego_id);
            $stmt->execute();
            $message = "Cantidad actualizada";
            $message_type = "success";
        }
    }

    if (isset($_POST['remove_from_cart']) && isset($_POST['juego_id'])) {
        $juego_id = intval($_POST['juego_id']);
        $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id=? AND juego_id=?");
        $stmt->bind_param("ii", $user_id, $juego_id);
        $stmt->execute();
        $message = "Juego eliminado del carrito";
        $message_type = "info";
    }

    if (isset($_POST['clear_cart'])) {
        $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $message = "Carrito vaciado";
        $message_type = "info";
    }

    refreshCarritoSession($conn, $user_id);
}

if (!empty($_SESSION['carrito'])) {
    $carrito_items = $_SESSION['carrito'];
    foreach ($carrito_items as $item) {
        $total_carrito += $item['precio'] * $item['cantidad'];
    }
}

$total_items_carrito = 0;
foreach ($carrito_items as $item) {
    $total_items_carrito += $item['cantidad'];
}

$_SESSION['carrito_count'] = $total_items_carrito;
?>