<?php 
include '../../controladores/cont_user_profile.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - NexusPlay</title>
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

                <div class="user-tabs desktop-tabs">
                    <a href="user.php" class="tab-btn">
                        <i class="fas fa-chart-line"></i> Resumen
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
                    <a href="configuracion.php" class="tab-btn">
                        <i class="fa-solid fa-gear"></i> Configuraciones
                    </a>
                </div>

                <div class="mobile-selector">
                    <select id="section-select" class="mobile-select" onchange="navigateToSection(this.value)">
                        <option value="user.php">📊 Resumen</option>
                        <option value="mis_pedidos.php" selected>📦 Mis Pedidos</option>
                        <option value="mi_cartera.php">💰 Mi Cartera</option>
                        <option value="mis_tarjetas.php">💳 Mis Tarjetas</option>
                        <option value="mis_resenas.php">⭐ Mis Reseñas</option>
                        <option value="configuracion.php">⚙️ Configuraciones</option>
                    </select>
                </div>

                <div id="pedidos" class="tab-content active">
                    <h2 class="section-title">Historial de Pedidos</h2>
                    <div class="pedidos-container">
                        <?php if ($pedidos_result && $pedidos_result->num_rows > 0): ?>
                            <?php 
                            $pedidos_result->data_seek(0);
                            while ($pedido = $pedidos_result->fetch_assoc()): 
                            ?>
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
                                <a href="../../index.php" class="btn btn-primary">
                                    <i class="fas fa-gamepad"></i> Explorar Juegos
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