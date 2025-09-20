<?php 
include 'controladores/cont_game_view.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['titulo']); ?> - NexusPlay</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/game_view.css">
    <link rel="stylesheet" href="assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="game-container">
            <div class="game-main-content">
                <div class="game-hero-compact">
                    <div class="game-hero-grid">
                        <div class="game-image-compact">
                            <img src="<?php echo getGameImagePath($game['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($game['titulo']); ?>">
                        </div>
                        
                        <div class="game-info-compact">
                            <div class="game-header">
                                <h1><?php echo htmlspecialchars($game['titulo']); ?></h1>
                                <p class="game-developer"><?php echo htmlspecialchars($game['desarrollador'] ?: 'Desarrollador no especificado'); ?></p>
                            </div>
                            
                            <div class="game-meta">
                                <span>
                                    <i class="fas fa-gamepad"></i> 
                                    <?php echo htmlspecialchars($game['plataforma_nombre'] ?: 'Multiplataforma'); ?>
                                </span>
                                <span>
                                    <i class="fas fa-tag"></i> 
                                    <?php echo htmlspecialchars($game['categoria_nombre'] ?: 'Sin categoría'); ?>
                                </span>
                            </div>
                            
                            <?php if ($game['total_resenas'] > 0): ?>
                            <div class="game-rating">
                                <div class="stars">
                                    <?php echo generateStars(round($game['promedio_rating'], 1)); ?>
                                </div>
                                <span class="rating-text"><?php echo round($game['promedio_rating'], 1); ?> (<?php echo $game['total_resenas']; ?> reseñas)</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="game-description-section">
                    <h2>Descripción del Juego</h2>
                    <div class="game-description">
                        <?php echo nl2br(htmlspecialchars($game['descripcion'] ?: 'No hay descripción disponible para este juego.')); ?>
                    </div>
                </div>

                <div class="reviews-section">
                    <div class="reviews-header">
                        <h2>Reseñas de Usuarios</h2>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="btn-toggle-review" id="toggleReviewForm">
                                <i class="fas fa-plus"></i> 
                                Escribir reseña
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="review-form-container" id="reviewFormContainer" style="display: none;">
                        <?php if (!empty($message)): ?>
                            <div class="review-message <?php echo $message_type; ?>">
                                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="review-form">
                            <h3>Escribir tu reseña</h3>
                                                        
                            <div class="form-group">
                                <span>Puntuación:</span>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="puntuacion" value="<?php echo $i; ?>" id="star<?php echo $i; ?>">
                                        <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                    
                            <div class="form-group">
                                <label for="comentario">Tu comentario:</label>
                                <textarea name="comentario" id="comentario" 
                                          placeholder="Comparte tu experiencia con este juego..." 
                                          required></textarea>
                            </div>
                            
                            <button type="submit" name="add_review" class="btn-submit-review">
                                <i class="fas fa-paper-plane"></i>
                                Publicar reseña
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div class="reviews-list">
                        <?php if ($reviews_result && $reviews_result->num_rows > 0): ?>
                            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="review-user">
                                            <img src="<?php echo getUserAvatarPath($review['imagen_perfil']); ?>" 
                                                 alt="<?php echo htmlspecialchars($review['username']); ?>" 
                                                 class="review-avatar">
                                            <div class="review-user-info">
                                                <h4><?php echo htmlspecialchars($review['username']); ?></h4>
                                                <p class="review-date"><?php echo formatReviewDate($review['fecha_resena']); ?></p>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            <?php echo generateStars($review['puntuacion']); ?>
                                        </div>
                                    </div>
                                    <div class="review-content">
                                        <?php echo nl2br(htmlspecialchars($review['comentario'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-reviews">
                                <i class="fas fa-comments"></i>
                                <h3>No hay reseñas todavía</h3>
                                <p>Sé el primero en compartir tu opinión sobre este juego</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="game-sidebar">
                <div class="purchase-card">
                    <div class="game-price"><?php echo formatGamePrice($game['precio']); ?></div>
                    <form method="POST" action="controladores/carrito_add.php" class="add-to-cart-form">
                        <input type="hidden" name="juego_id" value="<?php echo $game['id']; ?>">
                        <button type="submit" class="btn-add-cart">
                            <i class="fas fa-shopping-cart"></i>
                            Agregar al Carrito
                        </button>
                    </form>
                </div>

                <?php if ($game['total_resenas'] > 0): ?>
                <div class="rating-stats-card">
                    <h3>Calificaciones</h3>
                    
                    <div class="overall-rating">
                        <div class="overall-score"><?php echo round($game['promedio_rating'], 1); ?></div>
                        <div class="overall-stars">
                            <?php echo generateStars(round($game['promedio_rating'], 1)); ?>
                        </div>
                        <div class="total-reviews-text"><?php echo $game['total_resenas']; ?> reseñas</div>
                    </div>
                    
                    <div class="rating-breakdown">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <div class="rating-bar">
                            <span class="rating-label"><?php echo $i; ?> estrella<?php echo $i > 1 ? 's' : ''; ?></span>
                            <div class="rating-progress">
                                <?php 
                                $count = isset($rating_stats[$i]) ? $rating_stats[$i]['cantidad'] : 0;
                                $percentage = $game['total_resenas'] > 0 ? ($count / $game['total_resenas']) * 100 : 0;
                                ?>
                                <div class="rating-fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="rating-count"><?php echo $count; ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <?php echo getGameViewJavaScript($message, $message_type, isset($_SESSION['user_id'])); ?>
</body>
</html>