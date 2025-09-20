<?php 
    include("../controladores/cont_login.php"); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NexusPlay</title>
    <link rel="stylesheet" href="../css/auth.css">

</head>
<body>
    <div class="login-container">
      
        <a href="../index.php" class="close-btn">
            <span>&times;</span>
        </a>
        <div class="left-side">
            <div class="logo-section">
                <a href="../index.php" class="logo-top">
                    <img src="../images/Logo/img_logo.png" alt="Logo">
                    <span>NexusPlay</span>
                </a>
            </div>            
            <div class="login-section">
                <div class="form-center">
                    <h2>Iniciar Sesión</h2>
                    <?php if ($error): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="username" placeholder="👤 Usuario o Email" autocomplete="username" required>
                        <input type="password" name="password" placeholder="🔒 Contraseña">
                        <button type="submit">Iniciar Sesión</button>
                    </form>
                    <div class="links">
                        <a href="register.php">¿No tienes cuenta? Regístrate</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="right-side">
            <img src="../images/Logo/img_login.jpg" alt="Gaming">
        </div>
    </div>
</body>
</html> 