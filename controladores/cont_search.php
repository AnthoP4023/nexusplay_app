<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config_db/database.php';
require_once 'functions/fun_auth.php';
require_once 'functions/fun_profile.php';

if (isset($_SESSION['user_id'])) {
    $perfil_img = loadUserProfileImage($conn, $_SESSION['user_id']);
    $_SESSION['imagen_perfil'] = $perfil_img;
}

$search_query = isset($_GET['q']) ? $_GET['q'] : '';
$plataforma_id = isset($_GET['plataforma']) ? $_GET['plataforma'] : '';
$categoria_id = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$precio = isset($_GET['precio']) ? $_GET['precio'] : '';

// Aquí se delega la búsqueda a la función (fun_search.php)
require_once 'functions/fun_search.php';
$juegos_result = searchJuegos($conn, $search_query, $plataforma_id, $categoria_id, $precio);

// $total_resultados es simplemente el conteo de elementos en el array
$total_resultados = is_array($juegos_result) ? count($juegos_result) : 0;

$plataformas = getPlataformas($conn); // función en fun_search
$categorias = getCategorias($conn);   // función en fun_search
