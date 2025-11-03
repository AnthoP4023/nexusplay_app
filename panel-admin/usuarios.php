<?php
session_start();
require_once __DIR__ . '/controlador_panel/cont_usuarios.php';

// Manejo de la eliminación por POST (lo que ya tenías)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    deleteUsuario($delete_id);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;

// Estas variables se inicializan en cont_usuarios.php
// $usuarios = getUsuarios($page, $limit); 
// $total_usuarios_count = getTotalUsuariosCount();
// $total_pages = ceil($total_usuarios_count / $limit);
// $total_usuarios = getTotalUsuarios();
// $usuarios_mes = getUsuariosDelMes();
// $total_admins = getTotalAdministradores();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Panel de Administrador - NexusPlay</title>
    <link rel="stylesheet" href="../../assests/fontawesome/css/all.min.css">
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
                                        <button class="action-btn edit" onclick="editUser(<?php echo $usuario['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if($usuario['tipo_user_id'] != 2): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este usuario? Esto eliminará todos sus datos asociados.');">
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

    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Editar Usuario</h2>
            
            <form id="userForm">
                <input type="hidden" id="userId" name="id">
                
                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre">
                </div>
                
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="tipo_user_id">Tipo de Usuario:</label>
                    <select id="tipo_user_id" name="tipo_user_id" required>
                        <option value="1">Usuario Estándar</option>
                        <option value="2">Administrador</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="save-btn">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function loadPage(page) {
        window.location.href = '?page=' + page;
    }

    function closeModal() {
        document.getElementById('userModal').style.display = 'none';
    }

    /**
     * Carga los datos de un usuario en el modal para su edición.
     * Incluye nombre y apellido.
     */
    function editUser(id) {
        document.getElementById('modalTitle').textContent = 'Editar Usuario #' + id;
        
        // 1. Obtener los datos del usuario por AJAX
        fetch('usuarios.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_user&id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.id) {
                // 2. Rellenar el formulario del modal
                document.getElementById('userId').value = data.id;
                document.getElementById('username').value = data.username;
                // Campos nuevos
                document.getElementById('nombre').value = data.nombre || ''; // Usar '' si es null
                document.getElementById('apellido').value = data.apellido || ''; // Usar '' si es null
                // Fin campos nuevos
                document.getElementById('email').value = data.email;
                document.getElementById('tipo_user_id').value = data.tipo_user_id;

                // 3. Mostrar el modal
                document.getElementById('userModal').style.display = 'block';
            } else {
                alert('Error: Usuario no encontrado o error en la respuesta del servidor.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del usuario. Verifica la consola para más detalles.');
        });
    }

    // ----------------------------------------------------
    // MANEJO DEL ENVÍO DEL FORMULARIO DE EDICIÓN
    // (Este bloque no necesita cambios, ya que usa FormData que incluye los nuevos campos)
    // ----------------------------------------------------
    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('.save-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';
        
        const formData = new FormData(this);
        formData.append('action', 'edit_user'); 
        
        fetch('usuarios.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario actualizado correctamente.');
                closeModal();
                location.reload(); 
            } else {
                alert(data.message || 'Error desconocido al actualizar el usuario.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Guardar Cambios';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al servidor al intentar actualizar.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar Cambios';
        });
    });

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modal = document.getElementById('userModal');
        if (event.target == modal) {
            closeModal();
        }
    }
    </script>
</body>
</html>