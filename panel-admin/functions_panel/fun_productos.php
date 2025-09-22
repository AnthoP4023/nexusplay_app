<?php
require_once '../config_db/database.php';

function getCategorias() {
    global $conn;
    $categorias = [];
    
    try {
        $query = "SELECT * FROM categorias ORDER BY nombre";
        $result = $conn->query($query);
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error en getCategorias: " . $e->getMessage());
    }
    
    return $categorias;
}

function getProductos($categoria_filter = null, $search = null) {
    global $conn;
    $productos = [];
    
    try {
        $query = "SELECT j.*, c.nombre as categoria FROM juegos j 
                  JOIN categorias c ON j.categoria_id = c.id WHERE 1=1";
        
        if ($categoria_filter) {
            $query .= " AND j.categoria_id = " . intval($categoria_filter);
        }
        
        if ($search) {
            $search = $conn->real_escape_string($search);
            $query .= " AND j.titulo LIKE '%" . $search . "%'";
        }
        
        $query .= " ORDER BY j.titulo";
        
        $result = $conn->query($query);
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error en getProductos: " . $e->getMessage());
    }
    
    return $productos;
}

function getProduct($id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM juegos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            return $result->fetch_assoc();
        }
    } catch (Exception $e) {
        error_log("Error en getProduct: " . $e->getMessage());
    }
    
    return null;
}

function addProduct($data) {
    global $conn;
    
    try {
        $imagen = 'default.jpg';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $imagen = uploadImage($_FILES['imagen']);
        }
        
        $stmt = $conn->prepare("INSERT INTO juegos (titulo, descripcion, precio, stock, categoria_id, imagen, fecha_lanzamiento) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssdiss", $data['titulo'], $data['descripcion'], $data['precio'], $data['stock'], $data['categoria_id'], $imagen);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Producto agregado correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al agregar producto'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function updateProduct($data) {
    global $conn;
    
    try {
        $current = getProduct($data['id']);
        if (!$current) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }
        
        $imagen = $current['imagen'];
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $imagen = uploadImage($_FILES['imagen']);
        }
        
        $stmt = $conn->prepare("UPDATE juegos SET titulo=?, descripcion=?, precio=?, stock=?, categoria_id=?, imagen=? WHERE id=?");
        $stmt->bind_param("ssdissi", $data['titulo'], $data['descripcion'], $data['precio'], $data['stock'], $data['categoria_id'], $imagen, $data['id']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Producto actualizado correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar producto'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function deleteProduct($id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM juegos WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Producto eliminado correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar producto'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function uploadImage($file) {
    try {
        $upload_dir = '../images/juegos/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . rand(1000, 9999) . '.' . $extension;
        $upload_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $filename;
        }
    } catch (Exception $e) {
        error_log("Error en uploadImage: " . $e->getMessage());
    }
    
    return 'default.jpg';
}
?>