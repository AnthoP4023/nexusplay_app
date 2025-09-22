<?php
session_start();
require_once '../config_db/database.php';
require_once 'controlador_panel/cont_transacciones.php';

$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$por_pagina = 20;

$transacciones = getTransacciones($pagina_actual, $por_pagina, '', '', $busqueda);
$transacciones = array_reverse($transacciones);

$stats = getEstadisticasTransacciones();
$total_transacciones = getTotalTransacciones('', '', $busqueda);
$total_paginas = ceil($total_transacciones / $por_pagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transacciones - Panel Admin</title>
    <link rel="stylesheet" href="/nexusplay/assests/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css_panel/header.css">
    <link rel="stylesheet" href="css_panel/transacciones.css">
</head>
<body>

    <?php include 'header.php'; ?> 

    <main class="main-content">
        <div class="transacciones-container">
            <div class="transacciones-header">
                <h1 class="transacciones-title">
                    <i class="fas fa-exchange-alt"></i> Transacciones
                </h1>
                <div class="filter-section">
                    <div class="search-container">
                        <i class="fas fa-search"></i>
                        <form method="GET">
                            <input type="text" name="buscar" placeholder="Buscar por usuario..." 
                                   value="<?php echo htmlspecialchars($busqueda); ?>">
                            <button type="submit" style="display:none;"></button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['total_transacciones']; ?></span>
                        <span class="stat-label">Total Transacciones</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-info">
                        <span class="stat-number">$<?php echo number_format($stats['volumen_total'],2); ?></span>
                        <span class="stat-label">Volumen Total</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-info">
                        <span class="stat-number">$<?php echo number_format($stats['promedio'],2); ?></span>
                        <span class="stat-label">Promedio</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['hoy']; ?></span>
                        <span class="stat-label">Hoy</span>
                    </div>
                </div>
            </div>

            <div class="transacciones-table-container">
                <div class="table-header">
                    <h2>Lista de Transacciones</h2>
                    <button class="btn-export"><i class="fas fa-download"></i> Exportar</button>
                </div>

                <table class="transacciones-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transacciones as $t): ?>
                            <tr>
                                <td>#<?php echo $t['id']; ?></td>
                                <td><?php echo htmlspecialchars($t['usuario']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $t['tipo']; ?>">
                                        <?php echo ucfirst($t['tipo']); ?>
                                    </span>
                                </td>
                                <td class="amount <?php echo $t['tipo']; ?>">
                                    $<?php echo number_format($t['monto'],2); ?>
                                </td>
                                <td><?php echo htmlspecialchars($t['descripcion']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($t['fecha'])); ?></td>
                                <td><button class="btn-view"><i class="fas fa-eye"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php $params = $_GET; ?>
                    <a href="?<?php echo http_build_query(array_merge($params,['pagina'=>1])); ?>" class="btn-page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($params,['pagina'=>max(1,$pagina_actual-1)])); ?>" class="btn-page">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    <span class="page-info">Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?></span>
                    <a href="?<?php echo http_build_query(array_merge($params,['pagina'=>min($total_paginas,$pagina_actual+1)])); ?>" class="btn-page">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($params,['pagina'=>$total_paginas])); ?>" class="btn-page">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
