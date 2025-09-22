<?php
require_once __DIR__ . '/../../config_db/database.php';

function obtenerDatosAdmin($admin_id) {
    global $conn;
    
    error_log("Debug: Iniciando obtenerDatosAdmin con ID: " . $admin_id);
    error_log("Debug: Conexión disponible: " . ($conn ? 'SÍ' : 'NO'));
    
    if (!$conn) {
        error_log("Debug: No hay conexión a BD");
        return false;
    }
    
    $query = "SELECT id, username, email, nombre, apellido, imagen_perfil, fecha_registro, tipo_user_id FROM usuarios WHERE id = $admin_id";
    error_log("Debug: Query a ejecutar: " . $query);
    
    $result = $conn->query($query);
    
    if (!$result) {
        error_log("Debug: Error en query: " . $conn->error);
        return false;
    }
    
    error_log("Debug: Query ejecutada exitosamente, filas: " . $result->num_rows);
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        error_log("Debug: Datos raw obtenidos: " . print_r($admin, true));
        
        error_log("Debug: username = '" . ($admin['username'] ?? 'NULL') . "'");
        error_log("Debug: email = '" . ($admin['email'] ?? 'NULL') . "'");
        error_log("Debug: nombre = '" . ($admin['nombre'] ?? 'NULL') . "'");
        error_log("Debug: apellido = '" . ($admin['apellido'] ?? 'NULL') . "'");
        error_log("Debug: tipo_user_id = '" . ($admin['tipo_user_id'] ?? 'NULL') . "'");
        
        if ($admin['tipo_user_id'] != 2) {
            error_log("Warning: Usuario no es admin, tipo_user_id = " . $admin['tipo_user_id']);
            return false;
        }
        
        $imagen_bd = isset($admin['imagen_perfil']) ? $admin['imagen_perfil'] : 'default-avatar.png';
        if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png') {
            $ruta_imagen = '/nexusplay/images/users/' . $imagen_bd;
            $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $ruta_imagen;
            
            if (file_exists($ruta_fisica)) {
                $admin['imagen_url'] = $ruta_imagen;
            } else {
                $admin['imagen_url'] = '/nexusplay/images/users/default-avatar.png';
            }
        } else {
            $admin['imagen_url'] = '/nexusplay/images/users/default-avatar.png';
        }
        
        error_log("Debug: Datos finales a retornar: " . print_r($admin, true));
        return $admin;
    } else {
        error_log("Debug: No se encontraron filas para ID: " . $admin_id);
        return false;
    }
}

function actualizarPerfilAdmin($admin_id, $username, $email, $nombre, $apellido) {
    global $conn;
    
    try {
        $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE (username = ? OR email = ?) AND id != ? AND tipo_user_id = 2");
        $check_stmt->bind_param("ssi", $username, $email, $admin_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            return false;
        }
        
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
            $current_password_hash = md5($current_password);
            
            if ($current_password_hash === $admin['password']) {
                $new_password_hash = md5($new_password);
                $update_stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ? AND tipo_user_id = 2");
                $update_stmt->bind_param("si", $new_password_hash, $admin_id);
                
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
        return ['success' => false, 'message' => 'Error al subir el archivo'];
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024;
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido. Solo JPG, PNG y GIF'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'El archivo es muy grande. Máximo 5MB'];
    }
    
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/nexusplay/images/users/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'admin_' . $admin_id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        try {
            $stmt = $conn->prepare("SELECT imagen_perfil FROM usuarios WHERE id = ? AND tipo_user_id = 2");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                $old_image = $admin['imagen_perfil'];
                
                if ($old_image && $old_image !== 'default-avatar.png') {
                    $old_path = $upload_dir . $old_image;
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
            }
            
            $update_stmt = $conn->prepare("UPDATE usuarios SET imagen_perfil = ? WHERE id = ? AND tipo_user_id = 2");
            $update_stmt->bind_param("si", $new_filename, $admin_id);
            
            if ($update_stmt->execute()) {
                return ['success' => true, 'message' => 'Imagen actualizada correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar la base de datos'];
            }
            
        } catch (Exception $e) {
            error_log("Error en actualizarAvatarAdmin: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    } else {
        return ['success' => false, 'message' => 'Error al mover el archivo'];
    }
}
?>