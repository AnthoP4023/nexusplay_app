<?php 
include '../../controladores/cont_config_user.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - NexusPlay</title>
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/footer.css">
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/profile_user.css">
    <link rel="stylesheet" href="../../css/user_config.css">
    <link rel="stylesheet" href="../../assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="user-profile-layout">
            <div class="profile-container">
                <div class="main-panel">
                    <div class="user-info-container">
                        <div class="avatar-section">
                            <div class="user-avatar">
                                <img src="<?php echo htmlspecialchars($perfil_img); ?>" alt="Mi Perfil" class="avatar-img">
                            </div>
                        </div>
                        
                        <div class="user-info">
                            <h1 class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                            <span class="user-type">Usuario</span>
                            <p class="user-email"><?php echo htmlspecialchars($user_data['email']); ?></p>
                            <p class="join-date">
                                Miembro desde: <?php echo date('d F Y', strtotime($user_data['fecha_registro'])); ?>
                            </p>
                        </div>
                        
                        <div class="user-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $stats['total_pedidos'] ?? 0; ?></div>
                                <div class="stat-label">Pedidos</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">$<?php echo number_format($stats['total_gastado'] ?? 0, 0); ?></div>
                                <div class="stat-label">Gastado</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">$<?php echo number_format($saldo_cartera, 0); ?></div>
                                <div class="stat-label">Cartera</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="user-tabs desktop-tabs">
                    <a href="user.php" class="tab-btn">📊 Resumen</a>
                    <a href="mis_pedidos.php" class="tab-btn">📦 Mis Pedidos</a>
                    <a href="mi_cartera.php" class="tab-btn">💰 Mi Cartera</a>
                    <a href="mis_tarjetas.php" class="tab-btn">💳 Mis Tarjetas</a>
                    <a href="mis_resenas.php" class="tab-btn">⭐ Mis Reseñas</a>
                    <a href="configuracion.php" class="tab-btn active">⚙️ Configuraciones</a>
                </div>

                <div class="mobile-selector">
                    <select id="section-select" class="mobile-select" onchange="navigateToSection(this.value)">
                        <option value="user.php">📊 Resumen</option>
                        <option value="mis_pedidos.php">📦 Mis Pedidos</option>
                        <option value="mi_cartera.php">💰 Mi Cartera</option>
                        <option value="mis_tarjetas.php">💳 Mis Tarjetas</option>
                        <option value="mis_resenas.php">⭐ Mis Reseñas</option>
                        <option value="configuracion.php" selected>⚙️ Configuraciones</option>
                    </select>
                </div>

                <div id="configuraciones" class="tab-content active">
                    <h2 class="section-title">Configuraciones de Cuenta</h2>

                    <!-- Información Personal -->
                    <div class="config-section">
                        <div class="config-header">
                            <h3><i class="fas fa-user-edit"></i> Información Personal</h3>
                            <p>Actualiza tus datos personales</p>
                        </div>
                        <?php if (!empty($profile_message)): ?>
                            <div class="message <?php echo $profile_message_type; ?>">
                                <i class="fas <?php echo $profile_message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                                <?php echo htmlspecialchars($profile_message); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" class="config-form">
                            <!-- campos de usuario, email, nombre, apellido -->
                            <button type="submit" name="update_profile" class="btn btn-green">
                                <i class="fas fa-save"></i> Actualizar Información
                            </button>
                        </form>
                    </div>

                    <!-- Imagen de Perfil -->
                    <div class="config-section">
                        <div class="config-header">
                            <h3><i class="fas fa-camera"></i> Imagen de Perfil</h3>
                            <p>Cambia tu foto de perfil</p>
                        </div>
                        <?php if (!empty($image_message)): ?>
                            <div class="message <?php echo $image_message_type; ?>">
                                <i class="fas <?php echo $image_message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                                <?php echo htmlspecialchars($image_message); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                            <input type="file" id="profileImageInput" name="profile_image" accept="image/*" required>
                            <button type="submit" name="update_profile_image" id="uploadBtn" class="btn btn-green" disabled>
                                <i class="fas fa-save"></i> Actualizar Imagen
                            </button>
                        </form>
                    </div>

                    <!-- Seguridad de la Cuenta -->
                    <div class="config-section">
                        <div class="config-header">
                            <h3><i class="fas fa-lock"></i> Seguridad de la Cuenta</h3>
                            <p>Cambia tu contraseña para mantener tu cuenta segura</p>
                        </div>
                        <?php if (!empty($password_message)): ?>
                            <div class="message <?php echo $password_message_type; ?>">
                                <i class="fas <?php echo $password_message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                                <?php echo htmlspecialchars($password_message); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" class="config-form">
                            <!-- campos de contraseña -->
                            <button type="submit" name="change_password" class="btn btn-secondary">
                                <i class="fas fa-shield-alt"></i> Cambiar Contraseña
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <script>
        const fileInput = document.getElementById('profileImageInput');
        const uploadBtn = document.getElementById('uploadBtn');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            const allowedTypes = ['image/jpeg','image/jpg','image/png','image/gif'];
            if (file && allowedTypes.includes(file.type) && file.size <= 5*1024*1024) {
                uploadBtn.disabled = false;
            } else {
                uploadBtn.disabled = true;
            }
        });

        function navigateToSection(url) {
            if(url) window.location.href = url;
        }

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');
            if(field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye','fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash','fa-eye');
            }
        }
    </script>
</body>
</html>
