<?php
require_once __DIR__ . '../../config_db/database.php';

function getUserData($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT username, email, nombre, apellido, imagen_perfil, fecha_registro FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $imagen_bd = $data['imagen_perfil'];
        if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png') {
            $ruta_imagen = '/images/users/' . $imagen_bd;
            $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $ruta_imagen;
            $data['perfil_img'] = file_exists($ruta_fisica) ? $ruta_imagen : '/images/users/default-avatar.png';
        } else {
            $data['perfil_img'] = '/images/users/default-avatar.png';
        }
    }

    return $data;
}

function getUserStats($user_id) {
    global $conn;

    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(p.id) as total_pedidos,
            SUM(CASE WHEN p.estado='completado' THEN p.total ELSE 0 END) as total_gastado
        FROM pedidos p
        WHERE p.usuario_id = ?
    ");
    $stmt_stats->bind_param("i", $user_id);
    $stmt_stats->execute();
    $stats = $stmt_stats->get_result()->fetch_assoc();

    $stmt_cartera = $conn->prepare("SELECT saldo FROM carteras WHERE usuario_id = ?");
    $stmt_cartera->bind_param("i", $user_id);
    $stmt_cartera->execute();
    $cartera = $stmt_cartera->get_result()->fetch_assoc();

    return [
        'stats' => $stats,
        'saldo_cartera' => $cartera ? $cartera['saldo'] : 0
    ];
}

function changeUserPassword($user_id, $current_password, $new_password) {
    global $conn;

    $current_hash = md5($current_password);
    $new_hash = md5($new_password);

    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && $user['password'] === $current_hash) {
        $update = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $update->bind_param("si", $new_hash, $user_id);
        return $update->execute();
    }
    return false;
}

function updateUserProfile($user_id, $username, $email, $nombre, $apellido) {
    global $conn;

    $sql = "UPDATE usuarios SET 
                username = '$username', 
                email = '$email', 
                nombre = '$nombre', 
                apellido = '$apellido'
            WHERE id = $user_id";

    return $conn->query($sql);
}

function updateUserProfileImage($user_id, $file) {
    global $conn;

    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/images/users/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 5 * 1024 * 1024;

    if (!in_array($file_ext, $allowed_extensions) || $file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Archivo inválido o demasiado grande'];
    }

    $stmt_old = $conn->prepare("SELECT imagen_perfil FROM usuarios WHERE id = ?");
    $stmt_old->bind_param("i", $user_id);
    $stmt_old->execute();
    $old_data = $stmt_old->get_result()->fetch_assoc();

    if (!empty($old_data['imagen_perfil']) && $old_data['imagen_perfil'] !== 'default-avatar.png') {
        $old_path = $upload_dir . $old_data['imagen_perfil'];
        if (file_exists($old_path)) unlink($old_path);
    }

    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $stmt_update = $conn->prepare("UPDATE usuarios SET imagen_perfil = ? WHERE id = ?");
        $stmt_update->bind_param("si", $new_filename, $user_id);
        if ($stmt_update->execute()) {
            return ['success' => true, 'filename' => $new_filename];
        } else {
            unlink($upload_path);
            return ['success' => false, 'message' => 'Error al actualizar la base de datos'];
        }
    }

    return ['success' => false, 'message' => 'Error al subir la imagen'];
} 