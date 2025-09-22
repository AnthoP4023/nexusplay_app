<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}


function loadCartFromDatabase($conn, $user_id) {
    $_SESSION['carrito'] = [];

    $stmt = $conn->prepare("
        SELECT c.juego_id, c.cantidad, j.titulo, j.precio, j.imagen
        FROM carrito c
        JOIN juegos j ON c.juego_id = j.id
        WHERE c.usuario_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $_SESSION['carrito'][$row['juego_id']] = [
            'id'       => $row['juego_id'],
            'titulo'   => $row['titulo'],
            'precio'   => $row['precio'],
            'imagen'   => $row['imagen'],
            'cantidad' => $row['cantidad']
        ];
    }

    updateCartCount();
}

function refreshCarritoSession($conn, $user_id) {
    loadCartFromDatabase($conn, $user_id);
}

function updateCartCount() {
    $total_items = 0;
    if (!empty($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $total_items += $item['cantidad'];
        }
    }
    $_SESSION['carrito_count'] = $total_items;
}

function initializeCart($conn) {
    if (isset($_SESSION['user_id']) && !isset($_SESSION['cart_loaded'])) {
        loadCartFromDatabase($conn, $_SESSION['user_id']);
        $_SESSION['cart_loaded'] = true;
    } else {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
        updateCartCount();
    }
}

function getCartItemCount() {
    if (isset($_SESSION['carrito_count'])) {
        return $_SESSION['carrito_count'];
    }
    updateCartCount();
    return $_SESSION['carrito_count'];
}
?>