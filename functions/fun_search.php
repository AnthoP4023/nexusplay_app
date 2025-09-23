<?php
require_once 'config_db/database.php';

// Función para obtener juegos según filtros
function getJuegos($conn, $search_query = '', $plataforma_id = '', $categoria_id = '', $precio = '') {
    $sql = "SELECT j.id, j.titulo, j.descripcion, j.imagen, j.precio, j.desarrollador 
            FROM juegos j 
            WHERE 1=1";

    $params = [];

    if (!empty($search_query)) {
        $sql .= " AND j.titulo LIKE ?";
        $params[] = "%$search_query%";
    }

    if (!empty($plataforma_id)) {
        $sql .= " AND j.plataforma_id = ?";
        $params[] = $plataforma_id;
    }

    if (!empty($categoria_id)) {
        $sql .= " AND j.categoria_id = ?";
        $params[] = $categoria_id;
    }

    if (!empty($precio)) {
        switch ($precio) {
            case '1': $sql .= " AND j.precio < 20"; break;
            case '2': $sql .= " AND j.precio BETWEEN 20 AND 50"; break;
            case '3': $sql .= " AND j.precio > 50"; break;
        }
    }

    $stmt = $conn->prepare($sql);

    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función para resaltar término de búsqueda
if (!function_exists('highlightSearchTerm')) {
    function highlightSearchTerm($text, $term) {
        if (empty($term)) return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $escapedTerm = htmlspecialchars($term, ENT_QUOTES, 'UTF-8');

        return preg_replace(
            "/(" . preg_quote($escapedTerm, "/") . ")/i",
            '<span class="highlight">$1</span>',
            $escapedText
        );
    }
}
?>
