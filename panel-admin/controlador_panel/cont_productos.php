<?php
require_once 'functions_panel/fun_productos.php';

if ($_POST && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch($_POST['action']) {
            case 'add':
                $result = addProduct($_POST);
                echo json_encode($result);
                break;
                
            case 'edit':
                $result = updateProduct($_POST);
                echo json_encode($result);
                break;
                
            case 'delete':
                $result = deleteProduct($_POST['id']);
                echo json_encode($result);
                break;
                
            case 'get':
                $product = getProduct($_POST['id']);
                echo json_encode($product);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

$categorias = getCategorias();
$productos = getProductos($_GET['categoria'] ?? null, $_GET['buscar'] ?? null);
?>