<?php 
include '../../controladores/cont_profile.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Panel de Administrador</title>
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
                                <a href="/panel-control/login.php" class="btn-admin-panel" target="_blank">
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
                    <a href="mis_pedidos.php" class="tab-btn active">
                        <i class="fas fa-box"></i> Mis Pedidos
                    </a>
                    <a href="mi_cartera.php" class="tab-btn">
                        <i class="fas fa-wallet"></i> Mi Cartera
                    </a>
                    <a href="mis_tarjetas.php" class="tab-btn">
                        <i class="fas fa-credit-card"></i> Mis Tarjetas
                    </a>
                    <a href="mis_resenas.php" class="tab-btn">
                        <i class="fas fa-star"></i> Mis Reseñas
                    </a>
                </div>

                <div class="mobile-selector">
                    <select id="section-select" class="mobile-select" onchange="navigateToSection(this.value)">
                        <option value="admin.php">📊 Estadísticas</option>
                        <option value="mis_pedidos.php" selected>📦 Mis Pedidos</option>
                        <option value="mi_cartera.php">💰 Mi Cartera</option>
                        <option value="mis_tarjetas.php">💳 Mis Tarjetas</option>
                        <option value="mis_resenas.php">⭐ Mis Reseñas</option>
                    </select>
                </div>

                <div id="pedidos" class="tab-content active">
                    <h2 class="section-title">Historial de Pedidos</h2>
                    <div class="pedidos-container">
                        <?php if ($pedidos_result && $pedidos_result->num_rows > 0): ?>
                            <?php while ($pedido = $pedidos_result->fetch_assoc()): ?>
                                <div class="pedido-card">
                                    <div class="pedido-header">
                                        <div class="pedido-info">
                                            <h3>Pedido #<?php echo $pedido['id']; ?></h3>
                                            <p class="pedido-fecha"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                                        </div>
                                        <div class="pedido-status">
                                            <span class="status-badge <?php echo $pedido['estado']; ?>">
                                                <?php echo ucfirst($pedido['estado']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="pedido-details">
                                        <p><strong>Juegos:</strong> <?php echo htmlspecialchars($pedido['juegos_comprados']); ?></p>
                                        <p><strong>Items:</strong> <?php echo $pedido['total_items']; ?></p>
                                        <p><strong>Método de pago:</strong> <?php echo htmlspecialchars($pedido['metodo_pago']); ?></p>
                                    </div>
                                    <div class="pedido-total">
                                        <strong>$<?php echo number_format($pedido['total'], 2); ?></strong>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h3>No tienes pedidos aún</h3>
                                <p>Explora nuestra tienda y realiza tu primera compra</p>
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