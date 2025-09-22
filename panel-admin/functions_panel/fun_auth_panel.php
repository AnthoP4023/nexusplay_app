<?php
require_once __DIR__ . '/../../config_db/database.php';

function authenticateAdmin($username, $password) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT id, username, password, email FROM usuarios WHERE username = ? AND tipo_user_id = 2");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            $password_hash = md5($password);
            
            if ($password_hash === $user['password']) {
                return [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ];
            }
        }
        
        return false;
        
    } catch (mysqli_sql_exception $e) {
        error_log("Error en authenticateAdmin: " . $e->getMessage());
        return false;
    }
}

function isPanelAdminLoggedIn() {
    return isset($_SESSION['panel_admin_logged']) && 
           $_SESSION['panel_admin_logged'] === true &&
           isset($_SESSION['panel_admin_id']) &&
           isset($_SESSION['panel_admin_username']);
}

function loginPanelAdmin($admin_data) {
    $_SESSION['panel_admin_logged'] = true;
    $_SESSION['panel_admin_id'] = $admin_data['id'];
    $_SESSION['panel_admin_username'] = $admin_data['username'];
    $_SESSION['panel_admin_email'] = $admin_data['email'];
    $_SESSION['panel_login_time'] = time();
}


function logoutPanelAdmin() {
    unset($_SESSION['panel_admin_logged']);
    unset($_SESSION['panel_admin_id']);
    unset($_SESSION['panel_admin_username']);
    unset($_SESSION['panel_admin_email']);
    unset($_SESSION['panel_login_time']);
}

function isPanelSessionExpired($max_time_seconds = 7200) {
    if (!isPanelAdminLoggedIn()) {
        return true;
    }
    
    if (!isset($_SESSION['panel_login_time'])) {
        return true;
    }
    
    return (time() - $_SESSION['panel_login_time']) > $max_time_seconds;
}

function renewPanelSession() {
    if (isPanelAdminLoggedIn()) {
        $_SESSION['panel_login_time'] = time();
    }
}
?>