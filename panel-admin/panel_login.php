<?php
session_start();

if (isset($_SESSION['panel_admin_logged']) && $_SESSION['panel_admin_logged'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';
if (isset($_SESSION['panel_error'])) {
    $error_message = $_SESSION['panel_error'];
    unset($_SESSION['panel_error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Login</title>
    <link rel="stylesheet" href="css_panel/panel_login.css">
    <link rel="stylesheet" href="../../assests/fontawesome/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fa-solid fa-shield-halved"></i>
                <h2>Panel de Administrador</h2>
                <p>Acceso restringido solo para administradores</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="controlador_panel/cont_pan_login.php" class="login-form">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Usuario" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Ingresar al Panel
                </button>
            </form>
            
            <div class="back-link">
                <a href="../profile/admin/admin.php">
                    <i class="fas fa-arrow-left"></i>
                    Volver al sitio principal
                </a>
            </div>
        </div>
    </div>
</body>
</html>