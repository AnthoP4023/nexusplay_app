<?php
    session_start();
    require_once '../config_db/database.php';
    require_once '../functions/fun_auth.php';

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

        if (empty($username) || empty($email) || empty($nombre) || empty($apellido) || empty($password) || empty($confirm_password)) {
            $error = 'Complete todos los campos';
        } elseif ($password !== $confirm_password) {
            $error = 'Las contraseñas no coinciden';
        } elseif (strlen($password) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres';
        } elseif (!$terms_accepted) {
            $error = 'Debe aceptar los términos y condiciones';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El email no es válido';
        } else {
            $check_query = "SELECT * FROM usuarios WHERE username = '$username' OR email = '$email'";
            
            try {
                $check_result = $conn->query($check_query);
                
                if ($check_result && $check_result->num_rows > 0) {
                    $error = 'El usuario o email ya están registrados';
                } else {
                    $password_hash = md5($password);
                    $insert_query = "INSERT INTO usuarios (email, username, password, nombre, apellido, imagen_perfil, tipo_user_id, fecha_registro) 
                                VALUES ('$email', '$username', '$password_hash', '$nombre', '$apellido', 'default-avatar.png', '1', NOW())";
                    
                    if ($conn->query($insert_query)) {
                        $success = 'Usuario registrado exitosamente. Puedes iniciar sesión ahora.';
                    } else {
                        $error = 'Error al registrar usuario';
                    }
                }
            } catch (mysqli_sql_exception $e) {
                die("Error en la consulta: " . $e->getMessage());
            }
        }
    }  
?>
