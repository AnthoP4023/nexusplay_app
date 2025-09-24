<?php
session_start();
require_once '../config_db/database.php';
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
    $data = [
        'email' => trim($_POST['correo_elect']),
        'username' => trim($_POST['nombre_usuario']),
        'nombre' => trim($_POST['nombres']),
        'apellido' => trim($_POST['apellidos']),
        'password' => trim($_POST['clave']),
        'confirm_password' => trim($_POST['confirm_clave']),
        'terms' => isset($_POST['terms'])
    ];

    $errors = validateRegistration($data);

    if (empty($errors)) {
        if (isUserExists($conn, $data['username'], $data['email'])) {
            $error = 'El usuario o email ya están registrados';
        } else {
            if (registerUser($conn, $data)) {
                $success = 'Usuario registrado exitosamente. Puedes iniciar sesión ahora.';
            } else {
                $error = 'Error al registrar usuario';
            }
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
