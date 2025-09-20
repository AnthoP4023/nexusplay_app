<?php 
include '../../controladores/cont_profile.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
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
                            
                            <form method="POST" action="../../controladores/cont_password.php" class="password-form">
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

                <!-- Tabs de Navegación Desktop -->
                <div class="admin-tabs desktop-tabs">
                    <a href="admin.php" class="tab-btn active">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </a>
                    <a href="mis_pedidos.php" class="tab-btn">
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
                        <option value="admin.php" selected>📊 Estadísticas</option>
                        <option value="mis_pedidos.php">📦 Mis Pedidos</option>
                        <option value="mi_cartera.php">💰 Mi Cartera</option>
                        <option value="mis_tarjetas.php">💳 Mis Tarjetas</option>
                        <option value="mis_resenas.php">⭐ Mis Reseñas</option>
                    </select>
                </div>

                <div id="estadisticas" class="tab-content active">
                    <h2 class="section-title">Resumen de Actividad</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_pedidos'] ?? 0; ?></h3>
                                <p>Total Pedidos</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-info">
                                <h3>$<?php echo number_format($stats['total_gastado'] ?? 0, 2); ?></h3>
                                <p>Total Gastado</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['pedidos_completados'] ?? 0; ?></h3>
                                <p>Pedidos Completados</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="stat-info">
                                <h3>$<?php echo number_format($saldo_cartera, 2); ?></h3>
                                <p>Saldo en Cartera</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['pedidos_pendientes'] ?? 0; ?></h3>
                                <p>Pedidos Pendientes</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['pedidos_cancelados'] ?? 0; ?></h3>
                                <p>Pedidos Cancelados</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="stat-info">
                                <h3>$<?php echo $stats['total_pedidos'] > 0 ? number_format(($stats['total_gastado'] ?? 0) / $stats['total_pedidos'], 2) : '0.00'; ?></h3>
                                <p>Promedio por Pedido</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_pedidos'] > 0 ? round((($stats['pedidos_completados'] ?? 0) / $stats['total_pedidos']) * 100, 1) : 0; ?>%</h3>
                                <p>Tasa de Éxito</p>
                            </div>
                        </div>
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