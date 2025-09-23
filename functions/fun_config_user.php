<?php
require_once __DIR__ . '/../config_db/database.php';

/**
 * Obtener datos del usuario
 */
function getUserData($user_id) {
    global $conn;

    $result = $conn->query("SELECT username, email, nombre, apellido, imagen_perfil, fecha_registro 
                            FROM usuarios WHERE id = $user_id");

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();

        $imagen_bd = $user_data['imagen_perfil'];
        if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/images/users/' . $imagen_bd)) {
            $user_data['perfil_img'] = '/images/users/' . $imagen_bd;
        } else {
            $user_data['perfil_img'] = '/images/users/default-avatar.png';
        }

        return $user_data;
    }

    return false;
}

/**
 * Obtener estadísticas del usuario
 */
function getUserStats($user_id) {
    global $conn;

    $stats_result = $conn->query("SELECT COUNT(p.id) as total_pedidos,
                                         SUM(CASE WHEN p.estado='completado' THEN p.total ELSE 0 END) as total_gastado
                                  FROM pedidos p 
                                  WHERE p.usuario_id = $user_id");
    $stats = $stats_result->fetch_assoc();

    $cartera_result = $conn->query("SELECT saldo FROM carteras WHERE usuario_id = $user_id");
    $cartera = $cartera_result->fetch_assoc();
    $saldo_cartera = $cartera ? $cartera['saldo'] : 0;

    return [
        'stats' => $stats,
        'saldo_cartera' => $saldo_cartera
    ];
}

/**
 * Cambiar contraseña del usuario
 */
function changeUserPassword($user_id, $current_password, $new_password) {
    global $conn;

    $current_hash = md5($current_password);
    $new_hash = md5($new_password);

    $result = $conn->query("SELECT password FROM usuarios WHERE id = $user_id");
    $user = $result->fetch_assoc();

    if ($user && $current_hash === $user['password']) {
        return $conn->query("UPDATE usuarios SET password='$new_hash' WHERE id=$user_id");
    }

    return false;
}

/**
 * Actualizar perfil del usuario (Vulnerable a SQLi intencional)
 */
function updateUserProfile($user_id, $username, $email, $nombre, $apellido) {
    global $conn;

    $sql = "UPDATE usuarios SET 
                username='$username', 
                email='$email', 
                nombre='$nombre', 
                apellido='$apellido' 
            WHERE id=$user_id";

    return $conn->query($sql);
}

/**
 * Actualizar imagen de perfil del usuario
 */
function updateUserProfileImage($user_id, $file) {
    global $conn;

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Archivo inválido'];
    }

    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/images/users/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_size = $file['size'];
    $allowed_ext = ['jpg','jpeg','png','gif'];
    $max_size = 5 * 1024 * 1024;

    if (!in_array($file_ext, $allowed_ext)) return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    if ($file_size > $max_size) return ['success' => false, 'message' => 'Archivo demasiado grande'];

    $new_filename = 'user_'.$user_id.'_'.time().'.'.$file_ext;
    $upload_path = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $conn->query("UPDATE usuarios SET imagen_perfil='$new_filename' WHERE id=$user_id");
        return ['success' => true, 'filename' => $new_filename];
    }

    return ['success' => false, 'message' => 'Error al subir archivo'];
}
