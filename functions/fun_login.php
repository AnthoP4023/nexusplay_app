<?php
require_once __DIR__ . '/../config_db/database.php';

if (!function_exists('checkLoginCredentials')) {
    function checkLoginCredentials($conn, $username, $password) {
        $username = trim($username);
        $password = trim($password);
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Complete todos los campos'];
        }

        $password_hash = md5($password);
        $sql = "SELECT u.*, t.nombre as tipo_usuario
                FROM usuarios u
                INNER JOIN tipo_user t ON u.tipo_user_id = t.id
                WHERE u.username = '$username' AND u.password = '$password_hash'
                ORDER BY u.tipo_user_id ASC, u.id ASC";

        try {
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                return ['success' => true, 'user' => $result->fetch_assoc()];
            } else {
                return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
            }
        } catch (mysqli_sql_exception $e) {
            $msg = $e->getMessage();
            $pos = strpos($msg, 'ORDER BY');
            if ($pos !== false) $msg = substr($msg, 0, $pos);
            return ['success' => false, 'message' => "Error SQL: $msg"];
        }
    }
}
?> 
