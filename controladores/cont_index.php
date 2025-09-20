<?php
session_start();
require_once __DIR__ . '../../config_db/database.php';
require_once __DIR__ . '../../functions/fun_auth.php';
require_once __DIR__ . '../../functions/fun_profile.php';

if (isset($_SESSION['user_id'])) {
    $perfil_img = loadUserProfileImage($conn, $_SESSION['user_id']);
    $_SESSION['imagen_perfil'] = $perfil_img;
}

$mejores_juegos_query = "SELECT j.*, c.nombre as categoria_nombre, 
                        COALESCE(AVG(r.puntuacion), 0) as promedio_rating
                        FROM juegos j
                        LEFT JOIN categorias c ON j.categoria_id = c.id
                        LEFT JOIN resenas r ON j.id = r.juego_id
                        GROUP BY j.id
                        ORDER BY promedio_rating DESC, j.fecha_agregado DESC
                        LIMIT 5";
$mejores_juegos_result = $conn->query($mejores_juegos_query);

$tendencias_query = "SELECT j.*, c.nombre as categoria_nombre FROM juegos j 
                    LEFT JOIN categorias c ON j.categoria_id = c.id 
                    ORDER BY j.fecha_agregado DESC LIMIT 8";
$tendencias_result = $conn->query($tendencias_query);

$recomendados_query = "SELECT j.*, c.nombre as categoria_nombre, 
                      COALESCE(AVG(r.puntuacion), 0) as promedio_rating
                      FROM juegos j 
                      LEFT JOIN categorias c ON j.categoria_id = c.id
                      LEFT JOIN resenas r ON j.id = r.juego_id
                      GROUP BY j.id 
                      ORDER BY promedio_rating DESC, j.fecha_agregado DESC 
                      LIMIT 8";
$recomendados_result = $conn->query($recomendados_query);

$resenas_query = "SELECT r.*, j.titulo as juego_titulo, u.username, j.imagen
                 FROM resenas r 
                 JOIN juegos j ON r.juego_id = j.id 
                 JOIN usuarios u ON r.usuario_id = u.id 
                 ORDER BY r.fecha_resena DESC 
                 LIMIT 4";
$resenas_result = $conn->query($resenas_query);

$mas_vendidos_query = "SELECT j.*, p.nombre as plataforma_nombre, 
                      COUNT(dp.juego_id) as total_vendidos
                      FROM juegos j 
                      LEFT JOIN plataformas p ON j.plataforma_id = p.id
                      LEFT JOIN detalles_pedido dp ON j.id = dp.juego_id
                      LEFT JOIN pedidos pe ON dp.pedido_id = pe.id
                      WHERE pe.estado = 'completado' OR pe.estado IS NULL
                      GROUP BY j.id 
                      ORDER BY total_vendidos DESC, j.fecha_agregado DESC 
                      LIMIT 6";
$mas_vendidos_result = $conn->query($mas_vendidos_query);

$categorias_query = "SELECT c.*, COUNT(j.id) as total_juegos 
                    FROM categorias c 
                    LEFT JOIN juegos j ON c.id = j.categoria_id 
                    GROUP BY c.id 
                    ORDER BY total_juegos DESC";
$categorias_result = $conn->query($categorias_query);
?>