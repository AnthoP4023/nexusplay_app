<?php
require_once __DIR__ . '/../../config_db/database.php';

function obtenerDatosAdmin($admin_id) {
    global $conn;

    if (!$conn) {
        error_log("No hay conexión a la BD");
        return false;
    }

    $query = "SELECT id, username, email, nombre, apellido, imagen_perfil, fecha_registro, tipo_user_id FROM usuarios WHERE id = $admin_id";
    $result = $conn->query($query);

    if (!$result || $result->num_rows === 0) {
        error_log("No se encontraron filas para ID: $admin_id o error en query");
        return false;
    }

    $admin = $result->fetch_assoc();

    if ($admin['tipo_user_id'] != 2) return false;

    $imagen_bd = $admin['imagen_perfil'] ?? '';
    $ruta_fisica = __DIR__ . '/../../images/users/' . $imagen_bd;

    if (!empty($imagen_bd) && file_exists($ruta_fisica)) {
        $admin['imagen_url'] = '/images/users/' . $imagen_bd;
    } else {
        $admin['imagen_url'] = '/images/users/default-avatar.png';
    }

    return $admin;
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
        return ['success' => false, 'message' => 'Error al subir el archivo'];
    }

    $upload_dir = __DIR__ . '/../../images/users/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $file_name = $file['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $file_type = strtolower($file['type'] ?? '');
    $max_size = 5 * 1024 * 1024;

    // Lista "normal" de tipos MIME que esperarías para imágenes.
    $allowed_types = ['image/jpeg', 'image/pjpeg', 'image/jpg', 'image/png', 'image/gif'];

    // Extensiones de imagen que aceptamos por su extensión (fallback si el client MIME falla)
    $image_exts = ['jpg','jpeg','png','gif'];

    // --- Regla principal de la vulnerabilidad: bloquear solo ".php" ---
    if ($file_ext === 'php') {
        return ['success' => false, 'message' => 'No se permiten archivos con extensión .php'];
    }

    // --- Validación que permite fotos (por MIME o por extensión) ---
    // Si el MIME enviado está en la whitelist, OK.
    // O si la extensión indica imagen, OK (esto evita que falles al subir fotos cuando el client MIME es raro).
    if (!in_array($file_type, $allowed_types, true) && !in_array($file_ext, $image_exts, true)) {
        // En este punto: no es un image/* reconocido y la extensión no parece imagen -> rechazar
        return ['success' => false, 'message' => 'Tipo de archivo no permitido. Solo JPG, PNG y GIF'];
    }

    // Tamaño
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Archivo demasiado grande'];
    }

    // --- Mantengo tu esquema de nombre pero puedes optar por guardar el nombre original si quieres más "vulnerabilidad" ---
    $new_filename = 'admin_' . $admin_id . '_' . time() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        try {
            $stmt = $conn->prepare("UPDATE usuarios SET imagen_perfil = ? WHERE id = ? AND tipo_user_id = 2");
            $stmt->bind_param("si", $new_filename, $admin_id);
            $stmt->execute();
            return ['success' => true, 'message' => 'Archivo subido correctamente'];
        } catch (Exception $e) {
            error_log("Error en actualizarAvatarAdmin: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    } else {
        return ['success' => false, 'message' => 'Error al mover el archivo'];
    }
}
