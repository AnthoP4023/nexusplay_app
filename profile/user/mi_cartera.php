<?php 
include '../../controladores/cont_user_profile.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cartera - NexusPlay</title>
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/footer.css">
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/profile_user.css">
    <link rel="stylesheet" href="../../css/user_panel.css">
    <link rel="stylesheet" href="../../assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="user-profile-layout">
            <div class="profile-container">
                <!-- Panel Principal del Usuario -->
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
                        
                        <!-- Stats del usuario -->
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

                <!-- Tabs de Navegación Desktop -->
                <div class="user-tabs desktop-tabs">
                    <a href="user.php" class="tab-btn">
                        <i class="fas fa-chart-line"></i> Resumen
                    </a>
                    <a href="mis_pedidos.php" class="tab-btn">
                        <i class="fas fa-box"></i> Mis Pedidos
                    </a>
                    <a href="mi_cartera.php" class="tab-btn active">
                        <i class="fas fa-wallet"></i> Mi Cartera
                    </a>
                    <a href="mis_tarjetas.php" class="tab-btn">
                        <i class="fas fa-credit-card"></i> Mis Tarjetas
                    </a>
                    <a href="mis_resenas.php" class="tab-btn">
                        <i class="fas fa-star"></i> Mis Reseñas
                    </a>
                    <a href="configuracion.php" class="tab-btn">
                        <i class="fa-solid fa-gear"></i> Configuraciones
                    </a>
                </div>

                <!-- Selector Móvil -->
                <div class="mobile-selector">
                    <select id="section-select" class="mobile-select" onchange="navigateToSection(this.value)">
                        <option value="user.php">📊 Resumen</option>
                        <option value="mis_pedidos.php">📦 Mis Pedidos</option>
                        <option value="mi_cartera.php" selected>💰 Mi Cartera</option>
                        <option value="mis_tarjetas.php">💳 Mis Tarjetas</option>
                        <option value="mis_resenas.php">⭐ Mis Reseñas</option>
                        <option value="configuracion.php">⚙️ Configuraciones</option>
                    </select>
                </div>

                <div id="cartera" class="tab-content active">
                    <h2 class="section-title">Mi Cartera Digital</h2>
                    <div class="cartera-section">
                        <div class="saldo-card">
                            <div class="saldo-info">
                                <h3>Saldo Disponible</h3>
                                <div class="saldo-amount">$<?php echo number_format($saldo_cartera, 2); ?></div>
                            </div>
                            <div class="saldo-actions">
                                <a href="../../recharge.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Recargar
                                </a>
                            </div>
                        </div>
                        
                        <div class="movimientos-container">
                            <h3>Últimos Movimientos</h3>
                            <?php if ($movimientos_result && $movimientos_result->num_rows > 0): ?>
                                <div class="movimientos-list">
                                    <?php while ($movimiento = $movimientos_result->fetch_assoc()): ?>
                                        <div class="movimiento-item">
                                            <div class="movimiento-icon">
                                                <i class="fas <?php 
                                                    echo $movimiento['tipo'] == 'recarga' ? 'fa-plus-circle' : 
                                                        ($movimiento['tipo'] == 'compra' ? 'fa-shopping-cart' : 'fa-undo'); 
                                                ?>"></i>
                                            </div>
                                            <div class="movimiento-details">
                                                <p class="movimiento-desc"><?php echo htmlspecialchars($movimiento['descripcion']); ?></p>
                                                <p class="movimiento-fecha"><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha'])); ?></p>
                                            </div>
                                            <div class="movimiento-amount <?php echo $movimiento['tipo']; ?>">
                                                <?php echo $movimiento['tipo'] == 'recarga' ? '+' : '-'; ?>$<?php echo number_format($movimiento['monto'], 2); ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-receipt"></i>
                                    <h3>Sin movimientos</h3>
                                    <p>No hay transacciones registradas en tu cartera</p>
                                    <button class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Hacer primera recarga
                                    </button>
                                </div>
                            <?php endif; ?>
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