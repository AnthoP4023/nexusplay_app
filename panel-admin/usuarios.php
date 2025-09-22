<?php
session_start();
require_once __DIR__ . '/controlador_panel/cont_usuarios.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    deleteUsuario($delete_id);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;

$usuarios = getUsuarios($page, $limit);

$total_usuarios_count = getTotalUsuariosCount();
$total_pages = ceil($total_usuarios_count / $limit);

$total_usuarios = getTotalUsuarios();
$usuarios_mes = getUsuariosDelMes();
$total_admins = getTotalAdministradores();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Panel de Administrador - NexusPlay</title>
    <link rel="stylesheet" href="/nexusplay/assests/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css_panel/header.css">
    <link rel="stylesheet" href="css_panel/usuarios.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="main-content">
        <div class="usuarios-container">
            <div class="usuarios-header">
                <h1 class="usuarios-title">
                    <i class="fas fa-users"></i> Gestión de Usuarios
                </h1>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $total_usuarios; ?></span>
                        <span class="stat-label">Total Usuarios</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $usuarios_mes; ?></span>
                        <span class="stat-label">Este Mes</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $total_admins; ?></span>
                        <span class="stat-label">Administradores</span>
                    </div>
                </div>
            </div>

            <div class="usuarios-table-container">
                <table class="usuarios-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Saldo</th>
                            <th>Registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($usuarios && count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <img src="<?php echo htmlspecialchars($usuario['avatar']); ?>" alt="Avatar" class="user-avatar">
                                        <div class="user-details">
                                            <span class="username"><?php echo htmlspecialchars($usuario['username']); ?></span>
                                            <span class="user-id">#<?php echo $usuario['id']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <span class="user-type type-<?php echo $usuario['tipo_user_id']; ?>">
                                        <?php echo $usuario['tipo_user_id'] == 2 ? 'Admin' : 'Usuario'; ?>
                                    </span>
                                </td>
                                <td class="saldo"><?php echo formatCurrency($usuario['saldo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                                <td><span class="status-badge status-activo">Activo</span></td>
                                <td>
                                    <div class="actions">
                                        <button class="action-btn view" onclick="viewUser(<?php echo $usuario['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn edit" onclick="editUser(<?php echo $usuario['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if($usuario['tipo_user_id'] != 2):  ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" class="action-btn delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #888;">
                                    No se encontraron usuarios
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <button class="pagination-btn" onclick="loadPage(<?php echo $page - 1; ?>)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                <?php endif; ?>
                
                <span class="pagination-info">Página <?php echo $page; ?> de <?php echo $total_pages; ?></span>
                
                <?php if ($page < $total_pages): ?>
                    <button class="pagination-btn" onclick="loadPage(<?php echo $page + 1; ?>)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    function viewUser(id) { }
    function editUser(id) { }
    function loadPage(page) {
        window.location.href = '?page=' + page;
    }
    </script>
</body>
</html>
