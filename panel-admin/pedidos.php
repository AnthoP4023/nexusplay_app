<?php
session_start();
require_once __DIR__ . '/controlador_panel/cont_pedidos.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Panel de Administrador - NexusPlay</title>
    <link rel="stylesheet" href="/nexusplay/assests/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css_panel/header.css">
    <link rel="stylesheet" href="css_panel/pedidos.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="main-content">
        <div class="panel-container">
            <div class="page-header">
                <h1><i class="fas fa-shopping-cart"></i> Gestión de Pedidos</h1>
            </div>

            <div class="filters-section">
                <div class="filter-group">
                    <div class="search-box">
                        <input type="text" id="search-user" placeholder="Buscar por usuario" onkeyup="filtrarPedidos()">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $estadisticas['total_pedidos']; ?></h3>
                        <p>Total Pedidos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $estadisticas['pedidos_pendientes']; ?></h3>
                        <p>Pendientes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $estadisticas['pedidos_completados']; ?></h3>
                        <p>Completados</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($estadisticas['ingresos_totales'], 2); ?></h3>
                        <p>Ingresos Totales</p>
                    </div>
                </div>
            </div>

            <div class="pedidos-container">
                <?php if (!empty($pedidos)): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <div class="pedido-card" data-status="<?php echo $pedido['estado']; ?>" data-user="<?php echo strtolower($pedido['username']); ?>" data-date="<?php echo $pedido['fecha_pedido']; ?>">
                            <div class="pedido-header">
                                <div class="pedido-info">
                                    <h3>Pedido #<?php echo $pedido['id']; ?></h3>
                                    <p class="pedido-usuario"><?php echo htmlspecialchars($pedido['username']); ?></p>
                                    <p class="pedido-fecha"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                                </div>
                                
                                <div class="pedido-status">
                                    <span class="status-badge <?php echo $pedido['estado']; ?>">
                                        <?php echo ucfirst($pedido['estado']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="pedido-details">
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido['email']); ?></p>
                                <p><strong>Método de pago:</strong> <?php echo ucfirst($pedido['metodo_pago']); ?></p>
                                <p><strong>Items:</strong> <?php echo $pedido['total_items']; ?> juego(s)</p>
                                <p><strong>Juegos:</strong> <?php echo htmlspecialchars($pedido['juegos_comprados']); ?></p>
                            </div>

                            <div class="pedido-actions">
                                <div class="pedido-total">
                                    <strong>$<?php echo number_format($pedido['total'], 2); ?></strong>
                                </div>
                                
                                <div class="action-buttons">
                                    <button class="btn-view" onclick="verDetalles(<?php echo $pedido['id']; ?>)">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                    
                                    <?php if ($pedido['estado'] === 'pendiente'): ?>
                                        <button class="btn-complete" onclick="completarPedido(<?php echo $pedido['id']; ?>)">
                                            <i class="fas fa-check"></i> Completar
                                        </button>
                                        <button class="btn-cancel" onclick="cancelarPedido(<?php echo $pedido['id']; ?>)">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>No hay pedidos registrados</h3>
                        <p>Los pedidos aparecerán aquí cuando los usuarios realicen compras</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div id="completeModal" class="action-modal">
        <div class="action-modal-content">
            <h3><i class="fas fa-check-circle"></i> Completar Pedido</h3>
            <p>¿Estás seguro de que deseas marcar este pedido como completado?</p>
            <div class="action-buttons">
                <button class="btn-cancel-action" onclick="closeCompleteModal()">Cancelar</button>
                <button class="btn-confirm-complete" onclick="confirmComplete()">Completar</button>
            </div>
        </div>
    </div>

    <div id="cancelModal" class="action-modal">
        <div class="action-modal-content">
            <h3><i class="fas fa-times-circle"></i> Cancelar Pedido</h3>
            <p>¿Estás seguro de que deseas cancelar este pedido?</p>
            <div class="action-buttons">
                <button class="btn-cancel-action" onclick="closeCancelModal()">No cancelar</button>
                <button class="btn-confirm-cancel" onclick="confirmCancel()">Sí, cancelar</button>
            </div>
        </div>
    </div>

    <script>
        let pedidoToComplete = null;
        let pedidoToCancel = null;

        function filtrarPedidos() {
            const searchFilter = document.getElementById('search-user').value.toLowerCase();
            const pedidos = document.querySelectorAll('.pedido-card');

            pedidos.forEach(pedido => {
                const user = pedido.getAttribute('data-user');
                pedido.style.display = user.includes(searchFilter) ? 'block' : 'none';
            });
        }

        function verDetalles(pedidoId) {
            window.location.href = `#${pedidoId}`;
        }

        function completarPedido(pedidoId) {
            pedidoToComplete = pedidoId;
            document.getElementById('completeModal').style.display = 'block';
        }

        function cancelarPedido(pedidoId) {
            pedidoToCancel = pedidoId;
            document.getElementById('cancelModal').style.display = 'block';
        }

        function closeCompleteModal() {
            document.getElementById('completeModal').style.display = 'none';
            pedidoToComplete = null;
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
            pedidoToCancel = null;
        }

        function confirmComplete() {
            if (pedidoToComplete) {
                fetch('functions_panel/fun_pedidos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=completar&pedido_id=${pedidoToComplete}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
            closeCompleteModal();
        }

        function confirmCancel() {
            if (pedidoToCancel) {
                fetch('functions_panel/fun_pedidos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=cancelar&pedido_id=${pedidoToCancel}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
            closeCancelModal();
        }

        window.onclick = function(event) {
            const completeModal = document.getElementById('completeModal');
            const cancelModal = document.getElementById('cancelModal');
            
            if (event.target == completeModal) {
                closeCompleteModal();
            }
            if (event.target == cancelModal) {
                closeCancelModal();
            }
        }
    </script>
</body>
</html>