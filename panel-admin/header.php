<?php
require_once __DIR__ . '/controlador_panel/cont_header.php';
?>

<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <span></span>
    <span></span>
    <span></span>
</button>

<div class="mobile-overlay" id="mobileOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-profile-section">
        <div class="admin-avatar">
            <img src="<?php echo htmlspecialchars($admin_avatar); ?>" alt="Avatar Administrador" class="avatar-img">
        </div>
        <div class="admin-badge">Administrador</div>
        <h3 class="admin-name"><?php echo htmlspecialchars($admin_name); ?></h3>
    </div>

    <nav class="admin-navigation">
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a></li>
            
            <li><a href="usuarios.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a></li>
            
            <li><a href="transacciones.php" class="nav-item">
                <i class="fas fa-exchange-alt"></i>
                <span>Transacciones</span>
            </a></li>
            
            <li><a href="pedidos.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Pedidos</span>
            </a></li>
            
            <li><a href="productos.php" class="nav-item">
                <i class="fas fa-gamepad"></i>
                <span>Productos</span>
            </a></li>
            
            <li class="nav-divider"></li>
            
            <li><a href="configuraciones.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a></li>
            
            <li><a href="panel_logout.php" class="nav-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a></li>
        </ul>
    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');
        
        function toggleMenu() {
            mobileMenuToggle.classList.toggle('active');
            adminSidebar.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
        }
        
        mobileMenuToggle.addEventListener('click', toggleMenu);
        mobileOverlay.addEventListener('click', toggleMenu);
        
        const navItems = document.querySelectorAll('.nav-item');
        const currentPage = window.location.pathname.split('/').pop();
        
        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href === currentPage) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    });
</script>