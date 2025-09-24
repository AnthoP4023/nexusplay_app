<?php include '../../controladores/cont_config_user.php'; ?>

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

        <?php
        // Mensajes unificados
        $success_message = $_SESSION['config_success'] ?? '';
        $error_message = $_SESSION['config_error'] ?? '';
        unset($_SESSION['config_success'], $_SESSION['config_error']);
        ?>

        <?php if ($success_message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="user-tabs desktop-tabs">
            <a href="user.php" class="tab-btn"><i class="fas fa-chart-line"></i> Resumen</a>
            <a href="mis_pedidos.php" class="tab-btn"><i class="fas fa-box"></i> Mis Pedidos</a>
            <a href="mi_cartera.php" class="tab-btn"><i class="fas fa-wallet"></i> Mi Cartera</a>
            <a href="mis_tarjetas.php" class="tab-btn"><i class="fas fa-credit-card"></i> Mis Tarjetas</a>
            <a href="mis_resenas.php" class="tab-btn"><i class="fas fa-star"></i> Mis Reseñas</a>
            <a href="configuracion.php" class="tab-btn active"><i class="fa-solid fa-gear"></i> Configuraciones</a>
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
            
            <div class="config-container">

                <!-- Información Personal -->
                <div class="config-section">
                    <div class="config-header">
                        <h3><i class="fas fa-user-edit"></i> Información Personal</h3>
                        <p>Actualiza tus datos personales</p>
                    </div>
                    <form method="POST" class="config-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username"><i class="fas fa-user"></i> Nombre de Usuario</label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre"><i class="fas fa-id-badge"></i> Nombre</label>
                                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user_data['nombre']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="apellido"><i class="fas fa-id-badge"></i> Apellido</label>
                                <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($user_data['apellido']); ?>" required>
                            </div>
                        </div>
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
                    <div class="profile-image-section">
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($user_data['perfil_img']); ?>" alt="Imagen actual" id="currentProfileImage" class="profile-preview">
                        </div>
                        <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                            <input type="file" id="profileImageInput" name="profile_image" accept="image/*" required>
                            <button type="submit" name="update_profile_image" class="btn btn-green" id="uploadBtn" disabled>
                                <i class="fas fa-save"></i> Actualizar Imagen
                            </button>
                            <div id="imagePreviewContainer" style="display:none;">
                                <img id="imagePreview" alt="Vista previa">
                                <button type="button" id="cancelPreview" class="btn btn-secondary">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Seguridad de la Cuenta -->
                <div class="config-section">
                    <div class="config-header">
                        <h3><i class="fas fa-lock"></i> Seguridad de la Cuenta</h3>
                        <p>Cambia tu contraseña</p>
                    </div>
                    <form method="POST" class="config-form">
                        <div class="form-group">
                            <label for="current_password"><i class="fas fa-key"></i> Contraseña Actual</label>
                            <input type="password" id="current_password" name="current_password" required>
                            <button type="button" onclick="togglePassword('current_password')"><i class="fas fa-eye" id="current_password_icon"></i></button>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password"><i class="fas fa-lock"></i> Nueva Contraseña</label>
                                <input type="password" id="new_password" name="new_password" required>
                                <button type="button" onclick="togglePassword('new_password')"><i class="fas fa-eye" id="new_password_icon"></i></button>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password"><i class="fas fa-lock"></i> Confirmar Contraseña</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" onclick="togglePassword('confirm_password')"><i class="fas fa-eye" id="confirm_password_icon"></i></button>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-secondary">
                            <i class="fas fa-shield-alt"></i> Cambiar Contraseña
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
</main>

<?php include '../../includes/footer.php'; ?>

<script>
function navigateToSection(url){if(url){window.location.href=url;}}

function togglePassword(fieldId){
    const field=document.getElementById(fieldId);
    const icon=document.getElementById(fieldId+'_icon');
    if(field.type==='password'){field.type='text'; icon.classList.replace('fa-eye','fa-eye-slash');} 
    else {field.type='password'; icon.classList.replace('fa-eye-slash','fa-eye');}
}

// Preview imagen
document.addEventListener('DOMContentLoaded',function(){
    const fileInput=document.getElementById('profileImageInput');
    const previewContainer=document.getElementById('imagePreviewContainer');
    const imagePreview=document.getElementById('imagePreview');
    const uploadBtn=document.getElementById('uploadBtn');
    const cancelBtn=document.getElementById('cancelPreview');

    fileInput.addEventListener('change',function(e){
        const file=e.target.files[0];
        if(file){
            const allowed=['image/jpeg','image/jpg','image/png','image/gif'];
            if(!allowed.includes(file.type)){alert('Formato inválido'); reset(); return;}
            if(file.size>5*1024*1024){alert('Archivo demasiado grande'); reset(); return;}
            const reader=new FileReader();
            reader.onload=function(e){imagePreview.src=e.target.result; previewContainer.style.display='block'; uploadBtn.disabled=false;}
            reader.readAsDataURL(file);
        }
    });

    cancelBtn.addEventListener('click',reset);

    function reset(){
        fileInput.value=''; previewContainer.style.display='none'; uploadBtn.disabled=true;
    }
});
</script>
</body>
</html>
