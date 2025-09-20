<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config_db/database.php';
require_once __DIR__ . '/../functions/fun_auth.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

if (isAdmin()) {
    header('Location: /nexusplay/profile/admin/admin.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$password_message = '';
$password_message_type = '';
$profile_message = '';
$profile_message_type = '';
$image_message = '';
$image_message_type = '';

if (isset($_SESSION['password_message'])) {
    $password_message = $_SESSION['password_message'];
    $password_message_type = $_SESSION['password_message_type'];
    
    unset($_SESSION['password_message']);
    unset($_SESSION['password_message_type']);
}

if (isset($_SESSION['profile_message'])) {
    $profile_message = $_SESSION['profile_message'];
    $profile_message_type = $_SESSION['profile_message_type'];
    
    unset($_SESSION['profile_message']);
    unset($_SESSION['profile_message_type']);
}

if (isset($_SESSION['image_message'])) {
    $image_message = $_SESSION['image_message'];
    $image_message_type = $_SESSION['image_message_type'];
    
    unset($_SESSION['image_message']);
    unset($_SESSION['image_message_type']);
}

try {
    $stmt = $conn->prepare("SELECT username, email, nombre, apellido, imagen_perfil, fecha_registro FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $_SESSION['username'] = $user_data['username'];

        $imagen_bd = $user_data['imagen_perfil'];
        
        if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png') {
            $ruta_imagen = '/nexusplay/images/users/' . $imagen_bd;
            $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $ruta_imagen;
            
            if (file_exists($ruta_fisica)) {
                $perfil_img = $ruta_imagen;
            } else {
                $perfil_img = '/nexusplay/images/users/default-avatar.png';
            }
        } else {
            $perfil_img = '/nexusplay/images/users/default-avatar.png';
        }

        $_SESSION['imagen_perfil'] = $perfil_img;

    } else {
        die("Usuario no encontrado.");
    }

    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(p.id) as total_pedidos,
            SUM(CASE WHEN p.estado = 'completado' THEN p.total ELSE 0 END) as total_gastado
        FROM pedidos p 
        WHERE p.usuario_id = ?
    ");
    $stmt_stats->bind_param("i", $user_id);
    $stmt_stats->execute();
    $stats_result = $stmt_stats->get_result();
    $stats = $stats_result->fetch_assoc();

    $stmt_cartera = $conn->prepare("SELECT saldo FROM carteras WHERE usuario_id = ?");
    $stmt_cartera->bind_param("i", $user_id);
    $stmt_cartera->execute();
    $cartera_result = $stmt_cartera->get_result();
    $cartera = $cartera_result->fetch_assoc();
    $saldo_cartera = $cartera ? $cartera['saldo'] : 0;

} catch (mysqli_sql_exception $e) {
    die("Error en la consulta: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $password_message = 'Todos los campos de contraseña son obligatorios';
            $password_message_type = 'error';
        } elseif ($new_password !== $confirm_password) {
            $password_message = 'Las contraseñas nuevas no coinciden';
            $password_message_type = 'error';
        } elseif (strlen($new_password) < 6) {
            $password_message = 'La nueva contraseña debe tener al menos 6 caracteres';
            $password_message_type = 'error';
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
                            $_SESSION['password_message'] = 'Contraseña cambiada exitosamente';
                            $_SESSION['password_message_type'] = 'success';
                        } else {
                            $_SESSION['password_message'] = 'Error al actualizar la contraseña';
                            $_SESSION['password_message_type'] = 'error';
                        }
                    } else {
                        $_SESSION['password_message'] = 'La contraseña actual es incorrecta';
                        $_SESSION['password_message_type'] = 'error';
                    }
                } else {
                    $_SESSION['password_message'] = 'Usuario no encontrado';
                    $_SESSION['password_message_type'] = 'error';
                }
            } catch (mysqli_sql_exception $e) {
                $_SESSION['password_message'] = 'Error en la base de datos';
                $_SESSION['password_message_type'] = 'error';
            }
        }
        
        header('Location: configuracion.php');
        exit();
    }
    
    if (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['username']);
        $new_email    = trim($_POST['email']);
        $new_nombre   = trim($_POST['nombre']);
        $new_apellido = trim($_POST['apellido']);
        
        if (empty($new_username) || empty($new_email) || empty($new_nombre) || empty($new_apellido)) {
            $_SESSION['profile_message'] = 'Todos los campos del perfil son obligatorios';
            $_SESSION['profile_message_type'] = 'error';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['profile_message'] = 'El email no es válido';
            $_SESSION['profile_message_type'] = 'error';
        } else {
            $sql = "UPDATE usuarios SET 
                        username = '$new_username', 
                        email = '$new_email', 
                        nombre = '$new_nombre', 
                        apellido = '$new_apellido'
                    WHERE id = $user_id";

            try {
                $conn->query($sql);

                $_SESSION['profile_message'] = 'Perfil actualizado exitosamente';
                $_SESSION['profile_message_type'] = 'success';

                $_SESSION['username'] = $new_username;
                $user_data['username'] = $new_username;
                $user_data['email'] = $new_email;
                $user_data['nombre'] = $new_nombre;
                $user_data['apellido'] = $new_apellido;

            } catch (mysqli_sql_exception $e) {
                $_SESSION['profile_message'] = $e->getMessage();
                $_SESSION['profile_message_type'] = 'error';
            }
        }

        header('Location: configuracion.php');
        exit();
    }


    
    if (isset($_POST['update_profile_image'])) {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/nexusplay/images/users/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file = $_FILES['profile_image'];
            $file_size = $file['size'];
            $file_tmp = $file['tmp_name'];
            $file_type = $file['type'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 5 * 1024 * 1024;
            
            if (!in_array($file_ext, $allowed_extensions)) {
                $_SESSION['image_message'] = 'Solo se permiten archivos JPG, JPEG, PNG y GIF';
                $_SESSION['image_message_type'] = 'error';
            } elseif ($file_size > $max_size) {
                $_SESSION['image_message'] = 'El archivo es demasiado grande. Máximo 5MB';
                $_SESSION['image_message_type'] = 'error';
            } else {
                try {
                    $stmt_old = $conn->prepare("SELECT imagen_perfil FROM usuarios WHERE id = ?");
                    $stmt_old->bind_param("i", $user_id);
                    $stmt_old->execute();
                    $result_old = $stmt_old->get_result();
                    
                    if ($result_old && $result_old->num_rows > 0) {
                        $old_data = $result_old->fetch_assoc();
                        $old_image = $old_data['imagen_perfil'];
                        
                        if (!empty($old_image) && $old_image !== 'default-avatar.png') {
                            $old_path = $upload_dir . $old_image;
                            if (file_exists($old_path)) {
                                unlink($old_path);
                            }
                        }
                    }
                } catch (Exception $e) {
                }
                
                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    try {
                        $stmt_update = $conn->prepare("UPDATE usuarios SET imagen_perfil = ? WHERE id = ?");
                        $stmt_update->bind_param("si", $new_filename, $user_id);
                        
                        if ($stmt_update->execute()) {
                            $_SESSION['image_message'] = 'Imagen de perfil actualizada exitosamente';
                            $_SESSION['image_message_type'] = 'success';
                            
                            $new_image_path = '/nexusplay/images/users/' . $new_filename;
                            $_SESSION['imagen_perfil'] = $new_image_path;
                            $perfil_img = $new_image_path;
                            $user_data['imagen_perfil'] = $new_filename;
                        } else {
                            $_SESSION['image_message'] = 'Error al actualizar la imagen en la base de datos';
                            $_SESSION['image_message_type'] = 'error';
                            
                            if (file_exists($upload_path)) {
                                unlink($upload_path);
                            }
                        }
                    } catch (mysqli_sql_exception $e) {
                        $_SESSION['image_message'] = 'Error en la base de datos';
                        $_SESSION['image_message_type'] = 'error';
                        
                        if (file_exists($upload_path)) {
                            unlink($upload_path);
                        }
                    }
                } else {
                    $_SESSION['image_message'] = 'Error al subir la imagen';
                    $_SESSION['image_message_type'] = 'error';
                }
            }
        } else {
            $_SESSION['image_message'] = 'Por favor selecciona una imagen válida';
            $_SESSION['image_message_type'] = 'error';
        }
        
        header('Location: configuracion.php');
        exit();
    }
}
?>