<?php
require_once __DIR__ . '/../config_db/database.php';

function validateRegistration($data) {
    $errors = [];

    if (empty($data['username']) || empty($data['email']) || empty($data['nombre']) || empty($data['apellido']) || empty($data['password']) || empty($data['confirm_password'])) {
        $errors[] = 'Complete todos los campos';
    }

    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = 'Las contraseñas no coinciden';
    }

    if (strlen($data['password']) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    }

    if (empty($data['terms']) || !$data['terms']) {
        $errors[] = 'Debe aceptar los términos y condiciones';
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido';
    }

    return $errors;
}

function isUserExists($conn, $username, $email) {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result && $result->num_rows > 0;
}

function registerUser($conn, $data) {
    $password_hash = md5($data['password']);
    $stmt = $conn->prepare("INSERT INTO usuarios (email, username, password, nombre, apellido, imagen_perfil, tipo_user_id, fecha_registro) VALUES (?, ?, ?, ?, ?, 'default-avatar.png', 1, NOW())");
    $stmt->bind_param("sssss", $data['email'], $data['username'], $password_hash, $data['nombre'], $data['apellido']);
    return $stmt->execute();
}
?>
