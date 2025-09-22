<?php
session_start();
require_once __DIR__ . '/controlador_panel/cont_configuraciones.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuraciones - Panel de Administrador - NexusPlay</title>
    <link rel="stylesheet" href="/nexusplay/assests/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css_panel/header.css">
    <link rel="stylesheet" href="css_panel/configuraciones.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="main-content">
        <div class="config-container">
            <div class="config-header">
                <h1><i class="fas fa-cog"></i> Configuraciones</h1>
                <p>Administra tu perfil y configuraciones de cuenta</p>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="config-sections">
                <div class="config-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h2>Información del Perfil</h2>
                    </div>
                    
                    <?php if ($admin_data): ?>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <?php $avatar_data = obtenerDatosAdmin($_SESSION['panel_admin_id']); ?>
                            <div class="avatar-section">
                                <div class="current-avatar">
                                    <img src="<?php echo $avatar_data ? htmlspecialchars($avatar_data['imagen_url']) : '/nexusplay/images/users/default-avatar.png'; ?>" alt="Avatar Admin" id="avatarPreview">
                                </div>
                                <div class="avatar-info">
                                    <h3>Foto de Perfil</h3>
                                    <p>Formatos permitidos: JPG, PNG, GIF (máx. 5MB)</p>
                                    <div class="file-input-wrapper">
                                        <input type="file" id="avatar" name="avatar" class="file-input" accept="image/*" onchange="previewAvatar(this)">
                                        <label for="avatar" class="file-input-label">
                                            <i class="fas fa-camera"></i>
                                            Cambiar Foto
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="update_avatar" class="btn">
                                    <i class="fas fa-save"></i>
                                    Actualizar Foto
                                </button>
                            </div>
                        </form>
                        
                        <form method="POST" action="">
                            <?php
                            $form_data = obtenerDatosAdmin($_SESSION['panel_admin_id']);
                            ?>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="username">Nombre de Usuario</label>
                                    <input type="text" id="username" name="username" value="<?php echo $form_data ? htmlspecialchars($form_data['username']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Correo Electrónico</label>
                                    <input type="email" id="email" name="email" value="<?php echo $form_data ? htmlspecialchars($form_data['email']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" id="nombre" name="nombre" value="<?php echo $form_data ? htmlspecialchars($form_data['nombre']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="apellido">Apellido</label>
                                    <input type="text" id="apellido" name="apellido" value="<?php echo $form_data ? htmlspecialchars($form_data['apellido']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="update_profile" class="btn">
                                    <i class="fas fa-save"></i>
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
                
                <div class="config-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h2>Cambiar Contraseña</h2>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="password-grid">
                            <div class="form-group">
                                <label for="current_password">Contraseña Actual</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Nueva Contraseña</label>
                                <input type="password" id="new_password" name="new_password" required minlength="6">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Nueva Contraseña</label>
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="change_password" class="btn">
                                <i class="fas fa-key"></i>
                                Cambiar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="config-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h2>Información de la Cuenta</h2>
                    </div>
                    
                    <?php if ($admin_data): ?>
                        <?php $info_data = obtenerDatosAdmin($_SESSION['panel_admin_id']); ?>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Tipo de Usuario</label>
                                <input type="text" value="Administrador" readonly style="background: rgba(74, 74, 74, 0.3); cursor: not-allowed;">
                            </div>
                            
                            <div class="form-group">
                                <label>Fecha de Registro</label>
                                <input type="text" value="<?php echo $info_data ? date('d/m/Y H:i', strtotime($info_data['fecha_registro'])) : 'No disponible'; ?>" readonly style="background: rgba(74, 74, 74, 0.3); cursor: not-allowed;">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('new_password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value !== '') {
                if (this.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Las contraseñas no coinciden');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
        });
    </script>
</body>
</html>