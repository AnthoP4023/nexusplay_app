<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_id = $_SESSION['user_id'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'Todos los campos son obligatorios';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Las contraseñas nuevas no coinciden';
        $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'La nueva contraseña debe tener al menos 6 caracteres';
        $message_type = 'error';
    } else {
        try {
            $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $current_password_hash = md5($current_password);
                
                if ($current_password_hash === $user['password']) {
                    $new_password_hash = md5($new_password);
                    $update_stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $new_password_hash, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $message = 'Contraseña cambiada exitosamente';
                        $message_type = 'success';
                    } else {
                        $message = 'Error al actualizar la contraseña';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'La contraseña actual es incorrecta';
                    $message_type = 'error';
                }
            } else {
                $message = 'Usuario no encontrado';
                $message_type = 'error';
            }
        } catch (mysqli_sql_exception $e) {
            $message = 'Error en la base de datos: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
    
    $_SESSION['password_message'] = $message;
    $_SESSION['password_message_type'] = $message_type;
}

header('Location: ../profile/admin/admin.php');
exit();
?>