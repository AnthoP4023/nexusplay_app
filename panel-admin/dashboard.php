<?php
session_start();

require_once __DIR__ . '/controlador_panel/cont_dashboard.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel de Administrador - NexusPlay</title>
    <link rel="stylesheet" href="/nexusplay/assests/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css_panel/header.css">
    <link rel="stylesheet" href="css_panel/dashboard.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="main-content">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </h1>
                <p class="dashboard-subtitle">
                    Resumen general de NexusPlay
                </p>
            </div>

            <div class="stats-grid">
                <div class="stat-card" style="--stat-color: <?php echo $stats_widgets['usuarios']['color']; ?>">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="<?php echo $stats_widgets['usuarios']['icon']; ?>"></i>
                        </div>
                        <div class="stat-trend <?php echo getGrowthClass($stats_widgets['usuarios']['crecimiento']); ?>">
                            <i class="<?php echo getTrendIcon($stats_widgets['usuarios']['crecimiento']); ?>"></i>
                            <?php echo abs($stats_widgets['usuarios']['crecimiento']); ?>%
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>Total Usuarios</h3>
                        <div class="stat-number"><?php echo formatNumber($stats_widgets['usuarios']['total']); ?></div>
                        <p class="stat-subtitle"><?php echo formatNumber($stats_widgets['usuarios']['mes']); ?> nuevos este mes</p>
                    </div>
                </div>

                <div class="stat-card" style="--stat-color: <?php echo $stats_widgets['juegos']['color']; ?>">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="<?php echo $stats_widgets['juegos']['icon']; ?>"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>Catálogo de Juegos</h3>
                        <div class="stat-number"><?php echo formatNumber($stats_widgets['juegos']['total']); ?></div>
                        <p class="stat-subtitle"><?php echo formatNumber($stats_widgets['juegos']['activos']); ?> juegos activos</p>
                    </div>
                </div>

                <div class="stat-card" style="--stat-color: <?php echo $stats_widgets['pedidos']['color']; ?>">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="<?php echo $stats_widgets['pedidos']['icon']; ?>"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>Total Pedidos</h3>
                        <div class="stat-number"><?php echo formatNumber($stats_widgets['pedidos']['total']); ?></div>
                        <p class="stat-subtitle"><?php echo formatNumber($stats_widgets['pedidos']['mes']); ?> este mes</p>
                    </div>
                </div>

                <div class="stat-card" style="--stat-color: <?php echo $stats_widgets['ingresos']['color']; ?>">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="<?php echo $stats_widgets['ingresos']['icon']; ?>"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3>Ingresos Totales</h3>
                        <div class="stat-number"><?php echo formatCurrency($stats_widgets['ingresos']['total']); ?></div>
                        <p class="stat-subtitle"><?php echo formatCurrency($stats_widgets['ingresos']['mes']); ?> este mes</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="recent-orders">
                    <h3 class="section-title">
                        <i class="fas fa-shopping-cart"></i>
                        Últimos Pedidos
                    </h3>
                    <div class="orders-list">
                        <?php if (!empty($recent_orders)): ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <h4>#<?php echo $order['id']; ?> - <?php echo htmlspecialchars($order['username']); ?></h4>
                                        <p class="order-meta">
                                            <?php echo getTimeAgo($order['fecha_pedido']); ?> • 
                                            <?php echo ucfirst($order['metodo_pago'] ?? 'N/A'); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <div class="order-amount"><?php echo formatCurrency($order['total']); ?></div>
                                        <span class="order-status status-<?php echo $order['estado']; ?>">
                                            <?php echo ucfirst($order['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <h4>No hay pedidos recientes</h4>
                                    <p class="order-meta">Aún no se han realizado pedidos</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="category-stats">
                    <h3 class="section-title">
                        <i class="fas fa-tags"></i>
                        Ventas por Categoría
                    </h3>
                    <div class="category-list">
                        <?php if (!empty($sales_by_category)): ?>
                            <?php foreach ($sales_by_category as $category): ?>
                                <div class="category-item">
                                    <div class="category-info">
                                        <h4><?php echo htmlspecialchars($category['nombre']); ?></h4>
                                        <p class="category-meta"><?php echo formatNumber($category['ventas']); ?> ventas</p>
                                    </div>
                                    <div class="category-revenue">
                                        <?php echo formatCurrency($category['total_ventas']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="category-item">
                                <div class="category-info">
                                    <h4>No hay datos de categorías</h4>
                                    <p class="category-meta">Aún no se han registrado ventas</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="bottom-section">
                <div class="top-games">
                    <h3 class="section-title">
                        <i class="fas fa-trophy"></i>
                        Juegos Más Vendidos
                    </h3>
                    <div class="games-list">
                        <?php if (!empty($top_games)): ?>
                            <?php foreach (array_slice($top_games, 0, 5) as $game): ?>
                                <div class="game-item">
                                    <img src="/nexusplay/images/juegos/<?php echo htmlspecialchars($game['imagen'] ?? 'default-game.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($game['titulo']); ?>" 
                                         class="game-image">
                                    <div class="game-details">
                                        <h4><?php echo htmlspecialchars($game['titulo']); ?></h4>
                                        <p class="game-stats">
                                            <?php echo formatNumber($game['ventas']); ?> ventas • 
                                            <?php echo formatCurrency($game['precio']); ?>
                                        </p>
                                    </div>
                                    <div class="game-revenue">
                                        <?php echo formatCurrency($game['ingresos']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="game-item">
                                <div class="game-details">
                                    <h4>No hay datos de ventas</h4>
                                    <p class="game-stats">Aún no se han registrado ventas</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="top-users">
                    <h3 class="section-title">
                        <i class="fas fa-users"></i>
                        Usuarios Más Activos
                    </h3>
                    <div class="users-list">
                        <?php if (!empty($top_users)): ?>
                            <?php foreach (array_slice($top_users, 0, 5) as $user): ?>
                                <div class="user-item">
                                    <img src="/nexusplay/images/users/<?php echo htmlspecialchars($user['imagen_perfil'] ?? 'default-avatar.png'); ?>" 
                                         alt="<?php echo htmlspecialchars($user['username']); ?>" 
                                         class="user-avatar">
                                    <div class="user-details">
                                        <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                                        <p class="user-stats">
                                            <?php echo formatNumber($user['total_pedidos']); ?> pedidos • 
                                            Miembro desde <?php echo date('M Y', strtotime($user['fecha_registro'])); ?>
                                        </p>
                                    </div>
                                    <div class="user-spent">
                                        <?php echo formatCurrency($user['total_gastado']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="user-item">
                                <div class="user-details">
                                    <h4>No hay datos de usuarios</h4>
                                    <p class="user-stats">Aún no hay usuarios activos</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .recent-orders, .category-stats, .top-games, .top-users');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
            
            setInterval(updateTime, 1000);

            const hoverElements = document.querySelectorAll('.stat-card, .order-item, .category-item, .game-item, .user-item');
            
            hoverElements.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                    this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
                });
                
                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'none';
                });
            });

            function animateNumber(element, finalValue) {
                let currentValue = 0;
                const increment = finalValue / 100;
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    element.textContent = Math.floor(currentValue).toLocaleString('es-ES');
                }, 20);
            }

            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const finalValue = parseInt(stat.textContent.replace(/[^\d]/g, ''));
                if (!isNaN(finalValue) && finalValue > 0) {
                    stat.textContent = '0';
                    setTimeout(() => {
                        animateNumber(stat, finalValue);
                    }, 500);
                }
            });

            console.log('Dashboard NexusPlay cargado correctamente');
        });
    </script>
</body>
</html>