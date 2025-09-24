<?php
session_start();
require_once '../functions/fun_auth.php';
require_once '../functions/fun_register.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['correo_elect']);
    $username = trim($_POST['nombre_usuario']);
    $nombre = trim($_POST['nombres']);
    $apellido = trim($_POST['apellidos']);
    $password = trim($_POST['clave']);
    $confirm_password = trim($_POST['confirm_clave']);
    $terms_accepted = isset($_POST['terms']);

    $result = registerUser($email, $username, $nombre, $apellido, $password, $confirm_password, $terms_accepted);

    $error = $result['error'];
    $success = $result['success'];
}
?>
