<?php
require_once __DIR__ . '/../config_db/database.php';

if (!function_exists('getUserData')) {
    function getUserData($conn, $user_id) {
        try {
            $stmt = $conn->prepare("SELECT username, email, nombre, apellido, imagen_perfil, fecha_registro FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : false;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}

if (!function_exists('getUserStats')) {
    function getUserStats($conn, $user_id) {
        try {
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(p.id) as total_pedidos,
                    SUM(CASE WHEN p.estado = 'completado' THEN p.total ELSE 0 END) as total_gastado
                FROM pedidos p
                WHERE p.usuario_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return ($result) ? $result->fetch_assoc() : ['total_pedidos'=>0,'total_gastado'=>0];
        } catch (mysqli_sql_exception $e) {
            return ['total_pedidos'=>0,'total_gastado'=>0];
        }
    }
}

if (!function_exists('getUserWallet')) {
    function getUserWallet($conn, $user_id) {
        try {
            $stmt = $conn->prepare("SELECT saldo FROM carteras WHERE usuario_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['saldo'] : 0;
        } catch (mysqli_sql_exception $e) {
            return 0;
        }
    }
}

if (!function_exists('updateUserPassword')) {
    function updateUserPassword($conn, $user_id, $current_password, $new_password) {
        try {
            $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (md5($current_password) === $user['password']) {
                    $new_hash = md5($new_password);
                    $update_stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $new_hash, $user_id);
                    return $update_stmt->execute() ? ['success'=>true,'message'=>'Contraseña cambiada'] : ['success'=>false,'message'=>'Error al actualizar contraseña'];
                } else {
                    return ['success'=>false,'message'=>'Contraseña actual incorrecta'];
                }
            }
            return ['success'=>false,'message'=>'Usuario no encontrado'];
        } catch (mysqli_sql_exception $e) {
            return ['success'=>false,'message'=>'Error en la base de datos'];
        }
    }
}

if (!function_exists('updateUserProfile')) {
    function updateUserProfile($conn, $user_id, $username, $email, $nombre, $apellido) {
        try {
            $stmt = $conn->prepare("UPDATE usuarios SET username = ?, email = ?, nombre = ?, apellido = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $username, $email, $nombre, $apellido, $user_id);
            return $stmt->execute() ? ['success'=>true,'message'=>'Perfil actualizado'] : ['success'=>false,'message'=>'Error al actualizar perfil'];
        } catch (mysqli_sql_exception $e) {
            return ['success'=>false,'message'=>'Error en la base de datos'];
        }
    }
}

if (!function_exists('updateUserProfileImage')) {
    function updateUserProfileImage($conn, $user_id, $file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success'=>false,'message'=>'Archivo no válido'];
        }

        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/images/users/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg','jpeg','png','gif'];
        $max_size = 5*1024*1024;

        if (!in_array($file_ext, $allowed_ext)) return ['success'=>false,'message'=>'Formato no permitido'];
        if ($file['size'] > $max_size) return ['success'=>false,'message'=>'Archivo demasiado grande'];

        $new_filename = 'user_'.$user_id.'_'.time().'.'.$file_ext;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'],$upload_path)) {
            try {
                $stmt = $conn->prepare("UPDATE usuarios SET imagen_perfil = ? WHERE id = ?");
                $stmt->bind_param("si",$new_filename,$user_id);
                if ($stmt->execute()) {
                    return ['success'=>true,'message'=>'Imagen actualizada','filename'=>$new_filename];
                } else {
                    unlink($upload_path);
                    return ['success'=>false,'message'=>'Error al actualizar BD'];
                }
            } catch (mysqli_sql_exception $e) {
                unlink($upload_path);
                return ['success'=>false,'message'=>'Error en la base de datos'];
            }
        }
        return ['success'=>false,'message'=>'Error al subir archivo'];
    }
}
?>
