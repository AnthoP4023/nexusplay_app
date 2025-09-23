<?php
if (session_status() == PHP_SESSION_NONE) session_start();

require_once 'config_db/database.php';
require_once 'functions/fun_auth.php';
require_once 'functions/fun_profile.php';
require_once 'functions/fun_search.php';

if (isset($_SESSION['user_id'])) {
    $perfil_img = loadUserProfileImage($conn, $_SESSION['user_id']);
    $_SESSION['imagen_perfil'] = $perfil_img;
}

$search_query = isset($_GET['q']) ? $_GET['q'] : '';
$plataforma_id = isset($_GET['plataforma']) ? $_GET['plataforma'] : '';
$categoria_id = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$precio = isset($_GET['precio']) ? $_GET['precio'] : '';

$juegos_result = searchJuegos($conn, $search_query, $plataforma_id, $categoria_id, $precio);
$total_resultados = count($juegos_result);

$plataformas = $conn->query("SELECT * FROM plataformas");
$categorias = $conn->query("SELECT * FROM categorias");
?>
