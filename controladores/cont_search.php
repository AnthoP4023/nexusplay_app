<?php
if (session_status() == PHP_SESSION_NONE) session_start();

require_once 'functions/fun_auth.php';
require_once 'functions/fun_profile.php';
require_once 'functions/fun_search.php';

if (isset($_SESSION['user_id'])) {
    $_SESSION['imagen_perfil'] = loadUserProfileImage($conn, $_SESSION['user_id']);
}

$search_query = $_GET['q'] ?? '';
$plataforma_id = $_GET['plataforma'] ?? '';
$categoria_id = $_GET['categoria'] ?? '';
$precio = $_GET['precio'] ?? '';

// Llamada a la función que obtiene los juegos
$juegos_result = getJuegos($conn, $search_query, $plataforma_id, $categoria_id, $precio);
$total_resultados = count($juegos_result);

// Consultas auxiliares para filtros
$plataformas = $conn->query("SELECT * FROM plataformas");
$categorias = $conn->query("SELECT * FROM categorias");
?>
