<?php 
include '../../controladores/cont_profile_admin.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tarjetas - Panel de Administrador</title>
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/footer.css">
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/profile_admin.css">
    <link rel="stylesheet" href="../../css/admin_panel.css">
    <link rel="stylesheet" href="../../assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="admin-profile-layout">
            <div class="profile-container">
                <div class="main-panel">
                    <div class="admin-info-container">
                        <div class="avatar-section">
                            <div class="admin-avatar">
                                <img src="<?php echo htmlspecialchars($perfil_img); ?>" alt="Perfil Admin" class="avatar-img">
                            </div>
                        </div>
                        
                        <div class="info-and-button">
                            <div class="admin-info">
                                <h1 class="admin"><?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                                <p class="admin-type">Administrador</p>
                                <p class="admin-email"><?php echo htmlspecialchars($admin_data['email']); ?></p>
                                <p class="join-date">
                                    Miembro desde: <?php echo date('d F Y', strtotime($admin_data['fecha_registro'])); ?>
                                </p>
                            </div>
                            
                            <div class="admin-actions">
                                <a href="/panel-admin/panel_login.php" class="btn-admin-panel" target="_blank">
                                    <i class="fas fa-cogs"></i> Panel de Control
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="password-container">
                        <div class="password-change">
                            <h3>Cambiar Contraseña</h3>
                            
                            <?php if (!empty($password_message)): ?>
                                <div class="password-message <?php echo $password_message_type; ?>">
                                    <?php echo htmlspecialchars($password_message); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="change_password.php" class="password-form">
                                <div class="password-inputs">
                                    <input type="password" name="current_password" placeholder="Contraseña actual" required>
                                    <input type="password" name="new_password" placeholder="Nueva contraseña" required>
                                    <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required>
                                </div>
                                <button type="submit" class="btn-change-password">
                                    <i class="fas fa-key"></i> Cambiar Contraseña
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="admin-tabs desktop-tabs">
                    <a href="admin.php" class="tab-btn">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </a>
                    <a href="mis_pedidos.php" class="tab-btn">
                        <i class="fas fa-box"></i> Mis Pedidos
                    </a>
                    <a href="mi_cartera.php" class="tab-btn">
                        <i class="fas fa-wallet"></i> Mi Cartera
                    </a>
                    <a href="mis_tarjetas.php" class="tab-btn active">
                        <i class="fas fa-credit-card"></i> Mis Tarjetas
                    </a>
                    <a href="mis_resenas.php" class="tab-btn">
                        <i class="fas fa-star"></i> Mis Reseñas
                    </a>
                </div>

                <div class="mobile-selector">
                    <select id="section-select" class="mobile-select" onchange="navigateToSection(this.value)">
                        <option value="admin.php">📊 Estadísticas</option>
                        <option value="mis_pedidos.php">📦 Mis Pedidos</option>
                        <option value="mi_cartera.php">💰 Mi Cartera</option>
                        <option value="mis_tarjetas.php" selected>💳 Mis Tarjetas</option>
                        <option value="mis_resenas.php">⭐ Mis Reseñas</option>
                    </select>
                </div>

                <div id="tarjetas" class="tab-content active">
                    <h2 class="section-title">Mis Tarjetas</h2>
                    <div class="tarjetas-container">
                        <?php if ($tarjetas_result && $tarjetas_result->num_rows > 0): ?>
                            <div class="tarjetas-grid">
                                <?php while ($tarjeta = $tarjetas_result->fetch_assoc()): ?>
                                    <div class="tarjeta-card">
                                        <div class="tarjeta-header">
                                            <div class="tarjeta-tipo">
                                                <i class="fas fa-credit-card"></i>
                                                <span><?php echo htmlspecialchars($tarjeta['display_name']); ?></span>
                                            </div>
                                        </div>
                                        <div class="tarjeta-number">
                                            **** **** **** <?php echo htmlspecialchars($tarjeta['ultimos_4']); ?>
                                        </div>
                                        <div class="tarjeta-footer">
                                            <span>Exp: <?php echo htmlspecialchars($tarjeta['fecha_expiracion']); ?></span>
                                            <span class="tarjeta-fecha">Agregada: <?php echo date('d/m/Y', strtotime($tarjeta['fecha_registro'])); ?></span>
                                        </div>
                                         <div class="tarjeta-actions">
                                            <form method="POST" action="../../controladores/cont_delete_card.php" style="display: inline;">
                                                <input type="hidden" name="tarjeta_id" value="<?php echo $tarjeta['id']; ?>">
                                                <button type="submit" name="eliminar_tarjeta" class="btn-delete" >
                                                    <i class="fas fa-trash"></i>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="add-card-btn">
                                <a href="../../agg_card.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Agregar Nueva Tarjeta
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-credit-card"></i>
                                <h3>No tienes tarjetas registradas</h3>
                                <p>Agrega una tarjeta para realizar compras más fácilmente</p>
                                <a href="../../agg_card.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Agregar Tarjeta
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>

    <script>
        function navigateToSection(url) {
            if (url) {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>