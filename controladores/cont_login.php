<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config_db/database.php';
require_once '../functions/fun_auth.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Complete todos los campos';
    } else {
        $password_hash = md5($password);
        $query = "SELECT u.*, t.nombre as tipo_usuario
          FROM usuarios u
          INNER JOIN tipo_user t ON u.tipo_user_id = t.id
          WHERE u.username = '$username' AND u.password = '$password_hash'
          ORDER BY u.tipo_user_id ASC, u.id ASC"; 
        try {
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['user_type'] = $user['tipo_usuario'];
                
                unset($_SESSION['cart_loaded']);
                unset($_SESSION['carrito_count']);

                header('Location: ../index.php');
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } catch (mysqli_sql_exception $e) {
            $msg = $e->getMessage();
            $pos = strpos($msg, 'ORDER BY');
            if ($pos !== false) {
                $msg = substr($msg, 0, $pos);
            }
            die($msg);
        }
    }
}
?>