<?php 
include '../../controladores/cont_user_profile.php';

$card_message = '';
$card_message_type = '';

if (isset($_SESSION['card_message'])) {
    $card_message = $_SESSION['card_message'];
    $card_message_type = $_SESSION['card_message_type'];
    
    unset($_SESSION['card_message']);
    unset($_SESSION['card_message_type']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tarjetas - NexusPlay</title>
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
                    <a href="mis_tarjetas.php" class="tab-btn active">
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
                        <option value="mis_pedidos.php">📦 Mis Pedidos</option>
                        <option value="mi_cartera.php">💰 Mi Cartera</option>
                        <option value="mis_tarjetas.php" selected>💳 Mis Tarjetas</option>
                        <option value="mis_resenas.php">⭐ Mis Reseñas</option>
                        <option value="configuracion.php">⚙️ Configuraciones</option>
                    </select>
                </div>

                <div id="tarjetas" class="tab-content active">
                    <h2 class="section-title">Mis Tarjetas</h2>
                    
                    <?php if (!empty($card_message)): ?>
                        <div class="alert <?php echo $card_message_type; ?>">
                            <i class="fas <?php echo $card_message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <?php echo htmlspecialchars($card_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="tarjetas-container">
                        <?php if ($tarjetas_result && $tarjetas_result->num_rows > 0): ?>
                            <div class="tarjetas-grid">
                                <?php while ($tarjeta = $tarjetas_result->fetch_assoc()): ?>
                                    <div class="tarjeta-card">
                                        <div class="tarjeta-header">
                                            <div class="tarjeta-tipo">
                                                <i class="fas fa-credit-card"></i>
                                                <span><?php echo htmlspecialchars($tarjeta['alias'] ?: 'Tarjeta'); ?></span>
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