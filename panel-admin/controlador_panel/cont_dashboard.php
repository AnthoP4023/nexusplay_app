<?php
require_once __DIR__ . '/../functions_panel/fun_auth_panel.php';
require_once __DIR__ . '/../functions_panel/fun_dashboard.php';

if (!isPanelAdminLoggedIn()) {
    header('Location: panel_login.php');
    exit();
}

renewPanelSession();

try {
    $dashboard_stats = getDashboardStats();
    $recent_orders = getRecentOrders(8);
    $sales_by_category = getSalesByCategory();
    $top_games = getTopSellingGames(5);
    $top_users = getTopUsers(5);
    $growth_percentage = getGrowthPercentage();
    
    $stats_widgets = [
        'usuarios' => [
            'total' => $dashboard_stats['total_usuarios'],
            'mes' => $dashboard_stats['usuarios_mes'],
            'crecimiento' => $growth_percentage,
            'icon' => 'fas fa-users',
            'color' => '#2563eb'
        ],
        'juegos' => [
            'total' => $dashboard_stats['total_juegos'],
            'activos' => $dashboard_stats['total_juegos'], 
            'icon' => 'fas fa-gamepad',
            'color' => '#10b981'
        ],
        'pedidos' => [
            'total' => $dashboard_stats['total_pedidos'],
            'mes' => $dashboard_stats['pedidos_mes'],
            'icon' => 'fas fa-shopping-cart',
            'color' => '#f59e0b'
        ],
        'ingresos' => [
            'total' => $dashboard_stats['ingresos_totales'],
            'mes' => 0, 
            'icon' => 'fas fa-dollar-sign',
            'color' => '#ef4444'
        ]
    ];
    
    $chart_data = [
        'category_labels' => is_array($sales_by_category) ? array_column($sales_by_category, 'nombre') : [],
        'category_sales' => is_array($sales_by_category) ? array_column($sales_by_category, 'ventas') : []
    ];
    
} catch (Exception $e) {
    error_log("Error en cont_dashboard.php: " . $e->getMessage());
    
    $dashboard_stats = [
        'total_usuarios' => 0,
        'total_juegos' => 0,
        'total_pedidos' => 0,
        'ingresos_totales' => 0,
        'pedidos_mes' => 0,
        'usuarios_mes' => 0
    ];
    
    $recent_orders = [];
    $sales_by_category = [];
    $top_games = [];
    $top_users = [];
    $growth_percentage = 0;
    
    $stats_widgets = [
        'usuarios' => ['total' => 0, 'mes' => 0, 'crecimiento' => 0, 'icon' => 'fas fa-users', 'color' => '#2563eb'],
        'juegos' => ['total' => 0, 'activos' => 0, 'icon' => 'fas fa-gamepad', 'color' => '#10b981'],
        'pedidos' => ['total' => 0, 'mes' => 0, 'icon' => 'fas fa-shopping-cart', 'color' => '#f59e0b'],
        'ingresos' => ['total' => 0, 'mes' => 0, 'icon' => 'fas fa-dollar-sign', 'color' => '#ef4444']
    ];
    
    $chart_data = [
        'category_labels' => [],
        'category_sales' => []
    ];
}

function getGrowthClass($growth) {
    if ($growth > 0) return 'positive';
    if ($growth < 0) return 'negative';
    return 'neutral';
}

function getTrendIcon($growth) {
    if ($growth > 0) return 'fas fa-arrow-up';
    if ($growth < 0) return 'fas fa-arrow-down';
    return 'fas fa-minus';
}

function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Hace unos segundos';
    if ($time < 3600) return 'Hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'Hace ' . floor($time/3600) . ' horas';
    if ($time < 2592000) return 'Hace ' . floor($time/86400) . ' días';
    if ($time < 31536000) return 'Hace ' . floor($time/2592000) . ' meses';
    return 'Hace ' . floor($time/31536000) . ' años';
}
?>
