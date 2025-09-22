<?php
require_once __DIR__ . '/../../config_db/database.php';

function obtenerDatosAdmin($admin_id) {
    global $conn;

    if (!$conn) return false;

    $query = "SELECT id, username, email, nombre, apellido, imagen_perfil, fecha_registro, tipo_user_id 
              FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if ($admin['tipo_user_id'] != 2) return false; // Solo admin

        $imagen_bd = $admin['imagen_perfil'] ?? 'default-avatar.png';
        $ruta_imagen = '/nexusplay/images/users/' . $imagen_bd;
        $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $ruta_imagen;

        if (!file_exists($ruta_fisica) || $imagen_bd === 'default-avatar.png') {
            $ruta_imagen = '/nexusplay/images/users/default-avatar.png';
        }

        $admin['imagen_url'] = $ruta_imagen;
        return $admin;
    }

    return false;
}

function actualizarPerfilAdmin($admin_id, $username, $email, $nombre, $apellido) {
    global $conn;
    try {
        $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE (username = ? OR email = ?) AND id != ? AND tipo_user_id = 2");
        $check_stmt->bind_param("ssi", $username, $email, $admin_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) return false;

        $stmt = $conn->prepare("UPDATE usuarios SET username = ?, email = ?, nombre = ?, apellido = ? WHERE id = ? AND tipo_user_id = 2");
        $stmt->bind_param("ssssi", $username, $email, $nombre, $apellido, $admin_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error en actualizarPerfilAdmin: " . $e->getMessage());
        return false;
    }
}

function cambiarPasswordAdmin($admin_id, $current_password, $new_password) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ? AND tipo_user_id = 2");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (md5($current_password) === $admin['password']) {
                $new_hash = md5($new_password);
                $update_stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ? AND tipo_user_id = 2");
                $update_stmt->bind_param("si", $new_hash, $admin_id);
                return $update_stmt->execute();
            }
        }
        return false;
    } catch (Exception $e) {
        error_log("Error en cambiarPasswordAdmin: " . $e->getMessage());
        return false;
    }
}

function actualizarAvatarAdmin($admin_id, $file) {
    global $conn;

    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success'=>false,'message'=>'Error al subir el archivo'];
    }

    $allowed_types = ['image/jpeg','image/png','image/gif'];
    if (!in_array($file['type'],$allowed_types)) return ['success'=>false,'message'=>'Tipo de archivo no permitido.'];

    if ($file['size'] > 5*1024*1024) return ['success'=>false,'message'=>'Archivo muy grande (max 5MB).'];

    $upload_dir = $_SERVER['DOCUMENT_ROOT'].'/nexusplay/images/users/';
    if (!is_dir($upload_dir)) mkdir($upload_dir,0755,true);

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'admin_'.$admin_id.'_'.time().'.'.$ext;
    $upload_path = $upload_dir.$new_filename;

    if (!move_uploaded_file($file['tmp_name'],$upload_path)) {
        return ['success'=>false,'message'=>'Error al mover el archivo'];
    }

    try {
        $stmt = $conn->prepare("SELECT imagen_perfil FROM usuarios WHERE id = ? AND tipo_user_id = 2");
        $stmt->bind_param("i",$admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $old = $admin['imagen_perfil'] ?? '';
            if ($old && $old !== 'default-avatar.png' && file_exists($upload_dir.$old)) unlink($upload_dir.$old);
        }

        $update_stmt = $conn->prepare("UPDATE usuarios SET imagen_perfil=? WHERE id=? AND tipo_user_id=2");
        $update_stmt->bind_param("si",$new_filename,$admin_id);
        return ['success'=>$update_stmt->execute(),'message'=> $update_stmt->execute() ? 'Imagen actualizada correctamente' : 'Error al actualizar BD'];

    } catch (Exception $e) {
        error_log("Error en actualizarAvatarAdmin: ".$e->getMessage());
        return ['success'=>false,'message'=>'Error interno del servidor'];
    }
}
?>
