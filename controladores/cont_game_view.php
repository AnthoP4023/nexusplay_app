<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config_db/database.php';
require_once __DIR__ . '/../functions/fun_auth.php';
require_once __DIR__ . '/../functions/fun_profile.php';
require_once __DIR__ . '/../functions/fun_game_view.php';

if (isset($_SESSION['user_id'])) {
    $perfil_img = loadUserProfileImage($conn, $_SESSION['user_id']);
    $_SESSION['imagen_perfil'] = $perfil_img;
}

$game_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($game_id <= 0) {
    header('Location: ../index.php');
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    if (isset($_POST['add_review'])) {
        $user_id = $_SESSION['user_id'];
        $puntuacion = isset($_POST['puntuacion']) ? intval($_POST['puntuacion']) : 0;
        $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
        
        $validation_errors = validateGameReview($puntuacion, $comentario);
        
        if (empty($validation_errors)) {
            try {
                $stmt_check = $conn->prepare("SELECT id FROM resenas WHERE usuario_id = ? AND juego_id = ?");
                $stmt_check->bind_param("ii", $user_id, $game_id);
                $stmt_check->execute();
                $check_result = $stmt_check->get_result();
                
                if ($check_result->num_rows > 0) {
                    $stmt_update = $conn->prepare("UPDATE resenas SET puntuacion = ?, comentario = ?, fecha_resena = NOW() WHERE usuario_id = ? AND juego_id = ?");
                    $stmt_update->bind_param("isii", $puntuacion, $comentario, $user_id, $game_id);
                    $stmt_update->execute();
                } else {
                    $stmt_insert = $conn->prepare("INSERT INTO resenas (usuario_id, juego_id, puntuacion, comentario) VALUES (?, ?, ?, ?)");
                    $stmt_insert->bind_param("iiis", $user_id, $game_id, $puntuacion, $comentario);
                    $stmt_insert->execute();
                }
                
                $message = "Tu reseña ha sido publicada exitosamente";
                $message_type = "success";
            } catch (mysqli_sql_exception $e) {
                $message = "Error al procesar la reseña. Inténtalo de nuevo.";
                $message_type = "error";
            }
        } else {
            $message = implode(' ', $validation_errors);
            $message_type = "error";
        }
    }
}

try {
    $stmt_game = $conn->prepare("
        SELECT j.*, p.nombre as plataforma_nombre, c.nombre as categoria_nombre,
               COALESCE(AVG(r.puntuacion), 0) as promedio_rating,
               COUNT(r.id) as total_resenas
        FROM juegos j
        LEFT JOIN plataformas p ON j.plataforma_id = p.id
        LEFT JOIN categorias c ON j.categoria_id = c.id
        LEFT JOIN resenas r ON j.id = r.juego_id
        WHERE j.id = ?
        GROUP BY j.id
    ");
    $stmt_game->bind_param("i", $game_id);
    $stmt_game->execute();
    $game_result = $stmt_game->get_result();
    
    if ($game_result->num_rows === 0) {
        header('Location: ../index.php');
        exit();
    }
    
    $game = $game_result->fetch_assoc();
    
    $stmt_reviews = $conn->prepare("
        SELECT r.*, u.username, u.imagen_perfil
        FROM resenas r
        JOIN usuarios u ON r.usuario_id = u.id
        WHERE r.juego_id = ?
        ORDER BY r.fecha_resena DESC
        LIMIT 15
    ");
    $stmt_reviews->bind_param("i", $game_id);
    $stmt_reviews->execute();
    $reviews_result = $stmt_reviews->get_result();
    
    $stmt_related = $conn->prepare("
        SELECT j.*, COALESCE(AVG(r.puntuacion), 0) as promedio_rating
        FROM juegos j
        LEFT JOIN resenas r ON j.id = r.juego_id
        WHERE j.categoria_id = ? AND j.id != ?
        GROUP BY j.id
        ORDER BY promedio_rating DESC
        LIMIT 4
    ");
    $stmt_related->bind_param("ii", $game['categoria_id'], $game_id);
    $stmt_related->execute();
    $related_result = $stmt_related->get_result();
    
    $user_review = null;
    if (isset($_SESSION['user_id'])) {
        $stmt_user_review = $conn->prepare("SELECT * FROM resenas WHERE usuario_id = ? AND juego_id = ?");
        $stmt_user_review->bind_param("ii", $_SESSION['user_id'], $game_id);
        $stmt_user_review->execute();
        $user_review_result = $stmt_user_review->get_result();
        if ($user_review_result->num_rows > 0) {
            $user_review = $user_review_result->fetch_assoc();
        }
    }
    
    $stmt_rating_stats = $conn->prepare("
        SELECT 
            puntuacion,
            COUNT(*) as cantidad,
            (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM resenas WHERE juego_id = ?)) as porcentaje
        FROM resenas 
        WHERE juego_id = ?
        GROUP BY puntuacion
        ORDER BY puntuacion DESC
    ");
    $stmt_rating_stats->bind_param("ii", $game_id, $game_id);
    $stmt_rating_stats->execute();
    $rating_stats_result = $stmt_rating_stats->get_result();
    
    $rating_stats = [];
    while ($stat = $rating_stats_result->fetch_assoc()) {
        $rating_stats[$stat['puntuacion']] = $stat;
    }
    
} catch (mysqli_sql_exception $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$total_items_carrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $total_items_carrito += $item['cantidad'];
    }
}
?>