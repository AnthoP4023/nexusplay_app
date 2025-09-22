<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_cart.php';

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

$user_id = $_SESSION['user_id'] ?? null;

$redirect_url = $_POST['return_url'] ?? ($_SERVER['HTTP_REFERER'] ?? '/cart.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['juego_id']) && !isset($_POST['update_quantity']) && !isset($_POST['remove_from_cart']) && !isset($_POST['clear_cart'])) {
        $juego_id = intval($_POST['juego_id']);
        $cantidad = max(intval($_POST['cantidad'] ?? 1), 1);

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
                        'id' => $juego['id'],
                        'titulo' => $juego['titulo'],
                        'precio' => $juego['precio'],
                        'imagen' => $juego['imagen'],
                        'cantidad' => $cantidad
                    ];
                }
            }
            updateCartCount(); 
        }

        header("Location: $redirect_url");
        exit();
    }

    if (isset($_POST['update_quantity'], $_POST['juego_id'], $_POST['nueva_cantidad'])) {
        $juego_id = intval($_POST['juego_id']);
        $cantidad = intval($_POST['nueva_cantidad']);

        if ($user_id) {
            if ($cantidad <= 0) {
                $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id=? AND juego_id=?");
                $stmt->bind_param("ii", $user_id, $juego_id);
                $stmt->execute();
            } else {
                $stmt = $conn->prepare("UPDATE carrito SET cantidad=? WHERE usuario_id=? AND juego_id=?");
                $stmt->bind_param("iii", $cantidad, $user_id, $juego_id);
                $stmt->execute();
            }
            refreshCarritoSession($conn, $user_id);
        } else {
            if ($cantidad <= 0) {
                unset($_SESSION['carrito'][$juego_id]);
            } else {
                $_SESSION['carrito'][$juego_id]['cantidad'] = $cantidad;
            }
            updateCartCount();
        }

        header("Location: $redirect_url");
        exit();
    }

    if (isset($_POST['remove_from_cart'], $_POST['juego_id'])) {
        $juego_id = intval($_POST['juego_id']);
        if ($user_id) {
            $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id=? AND juego_id=?");
            $stmt->bind_param("ii", $user_id, $juego_id);
            $stmt->execute();
            refreshCarritoSession($conn, $user_id);
        } else {
            unset($_SESSION['carrito'][$juego_id]);
            updateCartCount();
        }

        header("Location: $redirect_url");
        exit();
    }

    if (isset($_POST['clear_cart'])) {
        if ($user_id) {
            $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id=?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            refreshCarritoSession($conn, $user_id);
        } else {
            $_SESSION['carrito'] = [];
            updateCartCount();
        }

        header("Location: $redirect_url");
        exit();
    }
}
?>
