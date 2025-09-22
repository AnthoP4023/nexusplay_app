<?php
session_start();
require_once '../config_db/database.php';
require_once 'controlador_panel/cont_productos.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Panel Admin</title>
    <link rel="stylesheet" href="../../assests/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css_panel/header.css">
    <link rel="stylesheet" href="css_panel/productos.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1><i class="fas fa-gamepad"></i> Gestión de Productos</h1>
            <button class="btn-add" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Agregar Producto
            </button>
        </div>

        <div class="filters">
            <form method="GET">
                <select name="categoria" onchange="this.form.submit()">
                    <option value="">Todas las categorías</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($_GET['categoria'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="buscar" placeholder="Buscar productos..." 
                       value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="products-grid">
            <?php foreach($productos as $producto): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="../../images/juegos/<?php echo $producto['imagen']; ?>" alt="<?php echo htmlspecialchars($producto['titulo']); ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($producto['titulo']); ?></h3>
                        <p class="category"><?php echo htmlspecialchars($producto['categoria']); ?></p>
                        <p class="price">$<?php echo number_format($producto['precio'], 2); ?></p>
                        <p class="stock">Stock: <?php echo $producto['stock']; ?></p>
                    </div>
                    <div class="product-actions">
                        <button class="btn-edit" onclick="editProduct(<?php echo $producto['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete" onclick="deleteProduct(<?php echo $producto['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Agregar Producto</h2>
            
            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" id="productId" name="id">
                
                <div class="form-group">
                    <label>Título:</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>
                
                <div class="form-group">
                    <label>Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Precio:</label>
                    <input type="number" id="precio" name="precio" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Stock:</label>
                    <input type="number" id="stock" name="stock" required>
                </div>
                
                <div class="form-group">
                    <label>Categoría:</label>
                    <select id="categoria_id" name="categoria_id" required>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Imagen:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeModal()">Cancelar</button>
                    <button type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="delete-modal">
        <div class="delete-modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
            <p>¿Estás seguro de que deseas eliminar este producto?</p>
            <div class="delete-actions">
                <button class="btn-cancel" onclick="closeDeleteModal()">Cancelar</button>
                <button class="btn-confirm" onclick="confirmDelete()">Eliminar</button>
            </div>
        </div>
    </div>

    <script>
        let productToDelete = null;

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Agregar Producto';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('productModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            productToDelete = null;
        }

        function editProduct(id) {
            document.getElementById('modalTitle').textContent = 'Editar Producto';
            
            fetch('productos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('productId').value = data.id;
                    document.getElementById('titulo').value = data.titulo;
                    document.getElementById('descripcion').value = data.descripcion || '';
                    document.getElementById('precio').value = data.precio;
                    document.getElementById('stock').value = data.stock;
                    document.getElementById('categoria_id').value = data.categoria_id;
                    document.getElementById('productModal').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar el producto');
            });
        }

        function deleteProduct(id) {
            productToDelete = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function confirmDelete() {
            if (productToDelete) {
                const deleteBtn = document.querySelector('.btn-confirm');
                deleteBtn.disabled = true;
                deleteBtn.textContent = 'Eliminando...';
                
                fetch('productos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete&id=' + productToDelete
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error al eliminar');
                        deleteBtn.disabled = false;
                        deleteBtn.textContent = 'Eliminar';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                    deleteBtn.disabled = false;
                    deleteBtn.textContent = 'Eliminar';
                });
            }
            closeDeleteModal();
        }

        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Guardando...';
            
            const formData = new FormData(this);
            const isEdit = document.getElementById('productId').value !== '';
            formData.append('action', isEdit ? 'edit' : 'add');
            
            fetch('productos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al procesar la solicitud');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Guardar';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Guardar';
            });
        });

        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target == modal) {
                closeModal();
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>