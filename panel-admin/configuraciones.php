<?php
session_start();
require_once __DIR__ . '/controlador_panel/cont_configuraciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuraciones - Panel Admin - NexusPlay</title>
<link rel="stylesheet" href="../../assests/fontawesome/css/all.min.css">
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

    <?php if ($success_message): ?>
        <div class="message success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="message error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="config-sections">

        <!-- PERFIL -->
        <div class="config-section">
            <div class="section-header"><i class="fas fa-user-circle"></i><h2>Perfil</h2></div>

            <form method="POST" enctype="multipart/form-data">
                <div class="avatar-section">
                    <img src="<?= htmlspecialchars($admin_data['imagen_url']) ?>" alt="Avatar" id="avatarPreview">
                    <input type="file" name="avatar" accept="image/*" onchange="previewAvatar(this)">
                    <button type="submit" name="update_avatar">Actualizar Foto</button>
                </div>
            </form>

            <form method="POST">
                <label>Usuario</label>
                <input type="text" name="username" value="<?= htmlspecialchars($admin_data['username']) ?>" required>
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($admin_data['email']) ?>" required>
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($admin_data['nombre']) ?>" required>
                <label>Apellido</label>
                <input type="text" name="apellido" value="<?= htmlspecialchars($admin_data['apellido']) ?>" required>
                <button type="submit" name="update_profile">Guardar Cambios</button>
            </form>
        </div>

        <!-- CONTRASEÑA -->
        <div class="config-section">
            <div class="section-header"><i class="fas fa-lock"></i><h2>Cambiar Contraseña</h2></div>
            <form method="POST">
                <label>Contraseña Actual</label>
                <input type="password" name="current_password" required>
                <label>Nueva Contraseña</label>
                <input type="password" name="new_password" required minlength="6">
                <label>Confirmar Nueva Contraseña</label>
                <input type="password" name="confirm_password" required minlength="6">
                <button type="submit" name="change_password">Cambiar Contraseña</button>
            </form>
        </div>

        <!-- INFO -->
        <div class="config-section">
            <div class="section-header"><i class="fas fa-info-circle"></i><h2>Información de la Cuenta</h2></div>
            <label>Tipo de Usuario</label>
            <input type="text" value="Administrador" readonly>
            <label>Fecha de Registro</label>
            <input type="text" value="<?= date('d/m/Y H:i', strtotime($admin_data['fecha_registro'])) ?>" readonly>
        </div>

    </div>
</div>
</main>

<script>
function previewAvatar(input){
    if(input.files && input.files[0]){
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>

