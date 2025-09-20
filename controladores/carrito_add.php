<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config_db/database.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

function syncCarritoWithDB($conn, $user_id) {
    $_SESSION['carrito'] = array();
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
            'id' => $row['juego_id'],
            'titulo' => $row['titulo'],
            'precio' => $row['precio'],
            'imagen' => $row['imagen'],
            'cantidad' => $row['cantidad']
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['juego_id'])) {
    $juego_id = intval($_POST['juego_id']);
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
    
    if ($cantidad <= 0) $cantidad = 1;
    
    try {
        $stmt = $conn->prepare("SELECT id, titulo, precio, imagen FROM juegos WHERE id = ?");
        $stmt->bind_param("i", $juego_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $juego = $result->fetch_assoc();
            
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                
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
                
                syncCarritoWithDB($conn, $user_id);
            } else {
                if (isset($_SESSION['carrito'][$juego_id])) {
                    $_SESSION['carrito'][$juego_id]['cantidad'] += $cantidad;
                } else {
                    $_SESSION['carrito'][$juego_id] = array(
                        'id' => $juego['id'],
                        'titulo' => $juego['titulo'],
                        'precio' => $juego['precio'],
                        'imagen' => $juego['imagen'],
                        'cantidad' => $cantidad
                    );
                }
            }
            
            $_SESSION['cart_message'] = 'Juego agregado al carrito exitosamente';
            $_SESSION['cart_message_type'] = 'success';
        } else {
            $_SESSION['cart_message'] = 'El juego no existe';
            $_SESSION['cart_message_type'] = 'error';
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['cart_message'] = 'Error al agregar el juego al carrito';
        $_SESSION['cart_message_type'] = 'error';
    }
}

$redirect_url = '/nexusplay/index.php';

if (isset($_POST['from_cart']) && $_POST['from_cart'] == '1') {
    $redirect_url = '/nexusplay/cart.php';
} elseif (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    
    if (strpos($referer, $site_url) === 0) {
        $redirect_url = $referer;
    }
}

header("Location: $redirect_url");
exit();
?>