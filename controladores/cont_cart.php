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
    $_SESSION['carrito'] = [];
}

$message = '';
$message_type = '';
$carrito_items = [];
$total_carrito = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;

    if (isset($_POST['add_to_cart'], $_POST['juego_id'])) {
        $juego_id = intval($_POST['juego_id']);
        $cantidad = intval($_POST['cantidad'] ?? 1);
        if ($cantidad <= 0) $cantidad = 1;

        if ($user_id) {
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

            refreshCarritoSession($conn, $user_id);
        } else {
            if (isset($_SESSION['carrito'][$juego_id])) {
                $_SESSION['carrito'][$juego_id]['cantidad'] += $cantidad;
            } else {
                $stmt = $conn->prepare("SELECT id, titulo, precio, imagen FROM juegos WHERE id=?");
                $stmt->bind_param("i", $juego_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $juego = $result->fetch_assoc();
                    $_SESSION['carrito'][$juego_id] = [
                        'id'       => $juego['id'],
                        'titulo'   => $juego['titulo'],
                        'precio'   => $juego['precio'],
                        'imagen'   => $juego['imagen'],
                        'cantidad' => $cantidad
                    ];
                }
            }
        }

        $message = "Juego agregado al carrito";
        $message_type = "success";
    }

    if ($user_id && isset($_POST['update_quantity'], $_POST['juego_id'], $_POST['nueva_cantidad'])) {
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
        refreshCarritoSession($conn, $user_id);
    }

    if ($user_id && isset($_POST['remove_from_cart'], $_POST['juego_id'])) {
        $juego_id = intval($_POST['juego_id']);
        $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id=? AND juego_id=?");
        $stmt->bind_param("ii", $user_id, $juego_id);
        $stmt->execute();
        $message = "Juego eliminado del carrito";
        $message_type = "info";
        refreshCarritoSession($conn, $user_id);
    }

    if ($user_id && isset($_POST['clear_cart'])) {
        $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $message = "Carrito vaciado";
        $message_type = "info";
        refreshCarritoSession($conn, $user_id);
    }

    header("Location: ../index.php?added=1");
exit();
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
