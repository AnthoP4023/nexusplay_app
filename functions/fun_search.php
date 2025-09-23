<?php
require_once __DIR__ . '/../config_db/database.php';

if (!function_exists('highlightSearchTerm')) {
    function highlightSearchTerm($text, $term) {
        if (empty($term)) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $escapedTerm = htmlspecialchars($term, ENT_QUOTES, 'UTF-8');

        return preg_replace(
            "/(" . preg_quote($escapedTerm, "/") . ")/i",
            '<span class="highlight">$1</span>',
            $escapedText
        );
    }
}

if (!function_exists('searchJuegos')) {
    function searchJuegos($conn, $search_query = '', $plataforma_id = '', $categoria_id = '', $precio = '') {
        $sql = "SELECT j.id, j.titulo, j.descripcion, j.imagen, j.precio, j.desarrollador 
                FROM juegos j 
                WHERE 1=1";

        if (!empty($search_query)) {
            $sql .= " AND j.titulo LIKE '%$search_query%'";
        }

        if (!empty($plataforma_id)) {
            $sql .= " AND j.plataforma_id = $plataforma_id";
        }

        if (!empty($categoria_id)) {
            $sql .= " AND j.categoria_id = $categoria_id";
        }

        if (!empty($precio)) {
            switch ($precio) {
                case '1':
                    $sql .= " AND j.precio < 20";
                    break;
                case '2':
                    $sql .= " AND j.precio BETWEEN 20 AND 50";
                    break;
                case '3':
                    $sql .= " AND j.precio > 50";
                    break;
            }
        }

        $result = $conn->query($sql);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }
}
?>
