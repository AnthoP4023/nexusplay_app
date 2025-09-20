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

$user_id = $_SESSION['user_id'];
$mensaje = '';
$mensaje_tipo = '';

$redirect_path = isAdmin() ? '../profile/admin/mis_tarjetas.php' : '../profile/user/mis_tarjetas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_tarjeta'])) {
    
    try {
        $tarjeta_id = intval($_POST['tarjeta_id'] ?? 0);
        
        if ($tarjeta_id <= 0) {
            throw new Exception('ID de tarjeta no válido');
        }
        
        $stmt_verify = $conn->prepare("SELECT id FROM tarjetas WHERE id = ? AND usuario_id = ?");
        $stmt_verify->bind_param("ii", $tarjeta_id, $user_id);
        $stmt_verify->execute();
        $result_verify = $stmt_verify->get_result();
        
        if ($result_verify->num_rows === 0) {
            throw new Exception('Tarjeta no encontrada o no tienes permisos para eliminarla');
        }
        
        $stmt_delete = $conn->prepare("DELETE FROM tarjetas WHERE id = ? AND usuario_id = ?");
        $stmt_delete->bind_param("ii", $tarjeta_id, $user_id);
        
        if ($stmt_delete->execute()) {
            $_SESSION['card_message'] = 'Tarjeta eliminada exitosamente';
            $_SESSION['card_message_type'] = 'success';
        } else {
            throw new Exception("Error al eliminar la tarjeta");
        }
        
    } catch (Exception $e) {
        $_SESSION['card_message'] = "Error: " . $e->getMessage();
        $_SESSION['card_message_type'] = 'error';
        error_log("Error al eliminar tarjeta: " . $e->getMessage());
    }
    
    header('Location: ' . $redirect_path);
    exit();
}

header('Location: ' . $redirect_path);
exit();
?>