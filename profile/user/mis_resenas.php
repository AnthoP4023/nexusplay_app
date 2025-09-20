<?php 
include '../../controladores/cont_user_profile.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reseñas - NexusPlay</title>
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
                    <a href="mis_pedidos.php" class="tab-btn">
                        <i class="fas fa-box"></i> Mis Pedidos
                    </a>
                    <a href="mi_cartera.php" class="tab-btn">
                        <i class="fas fa-wallet"></i> Mi Cartera
                    </a>
                    <a href="mis_tarjetas.php" class="tab-btn">
                        <i class="fas fa-credit-card"></i> Mis Tarjetas
                    </a>
                    <a href="mis_resenas.php" class="tab-btn active">
                        <i class="fas fa-star"></i> Mis Reseñas
                    </a>
                    <a href="configuracion.php" class="tab-btn">
                        <i class="fa-solid fa-gear"></i> Configuraciones
                    </a>
                </div>

                <div class="mobile-selector">
                    <select id="section-select" class="mobile-select" onchange="navigateToSection(this.value)">
                        <option value="user.php">📊 Resumen</option>
                        <option value="mis_pedidos.php">📦 Mis Pedidos</option>
                        <option value="mi_cartera.php">💰 Mi Cartera</option>
                        <option value="mis_tarjetas.php">💳 Mis Tarjetas</option>
                        <option value="mis_resenas.php" selected>⭐ Mis Reseñas</option>
                        <option value="configuracion.php">⚙️ Configuraciones</option>
                    </select>
                </div>

                <div id="resenas" class="tab-content active">
                    <h2 class="section-title">Mis Reseñas</h2>
                    <div class="resenas-container">
                        <?php if ($resenas_result && $resenas_result->num_rows > 0): ?>
                            <?php while ($resena = $resenas_result->fetch_assoc()): ?>
                                <div class="resena-card">
                                    <div class="resena-game">
                                        <img src="../../images/juegos/<?php echo $resena['juego_imagen'] ?: 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($resena['juego_titulo']); ?>">
                                        <div class="resena-game-info">
                                            <h4><?php echo htmlspecialchars($resena['juego_titulo']); ?></h4>
                                            <div class="resena-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $resena['puntuacion'] ? 'filled' : ''; ?>"></i>
                                                <?php endfor; ?>
                                                <span>(<?php echo $resena['puntuacion']; ?>/5)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="resena-content">
                                        <p><?php echo htmlspecialchars($resena['comentario']); ?></p>
                                        <span class="resena-fecha"><?php echo date('d/m/Y', strtotime($resena['fecha_resena'])); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-star"></i>
                                <h3>No has escrito reseñas</h3>
                                <p>Compra juegos y comparte tu experiencia con otros usuarios</p>
                                <a href="/nexusplay/index.php" class="btn btn-primary">
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