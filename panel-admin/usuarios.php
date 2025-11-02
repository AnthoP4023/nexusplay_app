<?php
// /usuarios.php
session_start();
require_once __DIR__ . '/controlador_panel/cont_usuarios.php';

// Manejo de la acción de ELIMINAR (POST no AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && !isset($_POST['action'])) {
    $delete_id = (int)$_POST['delete_id'];
    $result = deleteUsuario($delete_id);
    
    // Podrías añadir un mensaje de sesión aquí para feedback
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Variables inicializadas por cont_usuarios.php
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;

// Estas variables se cargan desde cont_usuarios.php
if (!isset($usuarios)) {
    // Si la carga falló en el controlador, las inicializamos
    $usuarios = [];
    $total_usuarios_count = 0;
    $total_pages = 1;
    $total_usuarios = 0;
    $usuarios_mes = 0;
    $total_admins = 0;
    $tipos_usuario = [];
}
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
                                        <button class="action-btn view" onclick="viewUser(<?php echo $usuario['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn edit" onclick="editUser(<?php echo $usuario['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if($usuario['tipo_user_id'] != 2):  ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" class="action-btn delete" onclick="return confirm('¿Está seguro de que desea eliminar a este usuario y todos sus datos?');">
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
            <h2 id="modalTitle"><i class="fas fa-user-edit"></i> Editar Usuario</h2>
            
            <form id="userForm" enctype="multipart/form-data">
                <input type="hidden" id="userId" name="id">
                
                <div class="form-group">
                    <label>Nombre de Usuario (Login):</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Nombre Real:</label>
                    <input type="text" id="nombre" name="nombre">
                </div>

                <div class="form-group">
                    <label>Apellido:</label>
                    <input type="text" id="apellido" name="apellido">
                </div>

                <div class="form-group">
                    <label>Saldo:</label>
                    <input type="number" id="saldo" name="saldo" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Tipo de Usuario:</label>
                    <select id="tipo_user_id" name="tipo_user_id" required>
                        <?php foreach($tipos_usuario as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Avatar (imagen_perfil):</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*">
                    <p id="currentAvatar" class="note">Avatar actual: </p>
                </div>

                <p class="note">Nota: La edición de contraseña debe gestionarse por separado.</p>
                
                <div class="form-actions">
                    <button type="button" onclick="closeModal()">Cancelar</button>
                    <button type="submit">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
    .modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 500px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    .close:hover, .close:focus { color: #000; text-decoration: none; cursor: pointer; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
    .form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .note { font-size: 0.85em; color: #666; margin-top: 5px; }
    </style>

    <script>
    // Función para cerrar el modal
    function closeModal() {
        document.getElementById('userModal').style.display = 'none';
        document.getElementById('userForm').reset();
    }
    
    // Función editUser: Carga los datos del usuario por AJAX y abre el modal
    function editUser(id) {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Editando Usuario #' + id;
        
        // 1. Petición para obtener los datos del usuario
        fetch('usuarios.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get&id=' + id
        })
        .then(response => response.json())
        .then(response => {
            if (response && response.usuario) {
                const data = response.usuario;
                
                // 2. Rellenar el dropdown de Tipos de Usuario
                const tipoSelect = document.getElementById('tipo_user_id');
                tipoSelect.innerHTML = ''; // Limpiar opciones anteriores

                response.tipos.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = tipo.id;
                    option.textContent = tipo.nombre;
                    tipoSelect.appendChild(option);
                });

                // 3. Rellenar el resto del formulario
                document.getElementById('userId').value = data.id;
                document.getElementById('username').value = data.username;
                document.getElementById('email').value = data.email;
                document.getElementById('nombre').value = data.nombre || '';
                document.getElementById('apellido').value = data.apellido || '';
                document.getElementById('saldo').value = parseFloat(data.saldo).toFixed(2); 
                document.getElementById('tipo_user_id').value = data.tipo_user_id;
                
                // Mostrar info del Avatar actual
                document.getElementById('currentAvatar').textContent = 'Avatar actual: ' + (data.imagen_perfil_nombre || 'default-avatar.png'); 
                document.getElementById('avatar').value = ''; 
                
                document.getElementById('userModal').style.display = 'block';
            } else {
                alert(response.message || 'Error al cargar los datos del usuario.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al cargar el usuario.');
        });
    }

    // Manejador del formulario para enviar la actualización por AJAX
    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';
        
        // Usamos FormData para poder enviar el archivo de imagen (avatar)
        const formData = new FormData(this);
        formData.append('action', 'edit'); 
        
        fetch('usuarios.php', {
            method: 'POST',
            body: formData 
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario actualizado correctamente.');
                location.reload(); 
            } else {
                alert(data.message || 'Error al actualizar el usuario. Verifique los logs del servidor.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Guardar Cambios';
            }
            closeModal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al guardar los datos.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar Cambios';
            closeModal();
        });
    });
    
    function viewUser(id) { 
        alert('Visualizando detalles del usuario #' + id);
    }
    
    function loadPage(page) {
        window.location.href = '?page=' + page;
    }
    
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