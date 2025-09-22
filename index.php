<?php
include 'controladores/cont_index.php'; 

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusPlay - Tienda de Videojuegos</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="game-carousel-section">
        <div class="container">
            <div class="carousel-container">
                <div class="carousel-track" id="gamesCarousel">
                    <?php if ($mejores_juegos_result && $mejores_juegos_result->num_rows > 0): ?>
                        <?php while ($juego = $mejores_juegos_result->fetch_assoc()): ?>
                            <div class="carousel-slide" style="background-image: url('images/juegos/<?php echo $juego['imagen'] ?: 'default.jpg'; ?>');">
                                <div class="slide-overlay"></div>
                                <div class="slide-content">
                                    <div class="slide-text">
                                        <span class="slide-category"><?php echo htmlspecialchars($juego['categoria_nombre']); ?></span>
                                        <h2><?php echo htmlspecialchars($juego['titulo']); ?></h2>
                                        <p>
                                            ⭐ <?php echo number_format($juego['promedio_rating'], 1); ?>/5 <br>
                                            $<?php echo number_format($juego['precio'], 2); ?>
                                        </p>
                                        <a href="game_view.php?id=<?php echo $juego['id']; ?>" class="btn btn-primary">Ver Juego</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
                <button class="carousel-btn carousel-btn-prev" onclick="moveCarousel(-1)">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="carousel-btn carousel-btn-next" onclick="moveCarousel(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="carousel-indicators" id="carouselIndicators"></div>
            </div>
        </div>
    </section>

    <main class="main-content">
        <section class="trending-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Tendencias</h2>
                    <a href="search.php?plataforma=&categoria=&precio=" class="view-all">Ver todos <i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="games-grid">
                    <?php if ($tendencias_result && $tendencias_result->num_rows > 0): ?>
                        <?php while ($juego = $tendencias_result->fetch_assoc()): ?>
                            <div class="game-card">
                                <div class="game-image">
                                    <img src="images/juegos/<?php echo $juego['imagen'] ?: 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($juego['titulo']); ?>">
                                    <div class="game-overlay">
                                        <a href="game_view.php?id=<?php echo $juego['id']; ?>" class="btn-overlay">Ver detalles</a>
                                    </div>
                                </div>
                                <div class="game-info">
                                    <h3><?php echo htmlspecialchars($juego['titulo']); ?></h3>
                                    <p class="game-platform"><?php echo htmlspecialchars($juego['categoria_nombre']); ?></p>
                                    <div class="game-price">
                                        <span class="current-price">$<?php echo number_format($juego['precio'], 2); ?></span>
                                        <form method="POST" action="controladores/cont_cart.php" class="add-cart-form">
                                            <input type="hidden" name="add_to_cart" value="1">
                                            <input type="hidden" name="juego_id" value="<?php echo $juego['id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-cart">
                                                <i class="fas fa-shopping-cart"></i> Añadir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="features-section">
            <div class="container">
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-download"></i></div>
                        <h3>Súper rápido</h3>
                        <p>Descarga digital instantánea</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <h3>Fiable y seguro</h3>
                        <p>Variedad de juegos</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-headset"></i></div>
                        <h3>Atención al cliente</h3>
                        <p>Agente disponible 24/7</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-star" style="color: #fbbf24;"></i></div>
                        <h3>Calificación</h3>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                            <span>4.0</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="recommended-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Te recomendamos</h2>
                    <a href="search.php?plataforma=&categoria=&precio=" class="view-all">Ver todos <i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="games-grid">
                    <?php if ($recomendados_result && $recomendados_result->num_rows > 0): ?>
                        <?php while ($juego = $recomendados_result->fetch_assoc()): ?>
                            <div class="game-card recommended">
                                <div class="game-image">
                                    <img src="images/juegos/<?php echo $juego['imagen'] ?: 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($juego['titulo']); ?>">
                                    <div class="recommended-badge">Recomendado</div>
                                    <div class="game-overlay">
                                        <a href="game_view.php?id=<?php echo $juego['id']; ?>" class="btn-overlay">Ver detalles</a>
                                    </div>
                                </div>
                                <div class="game-info">
                                    <h3><?php echo htmlspecialchars($juego['titulo']); ?></h3>
                                    <p class="game-platform"><?php echo htmlspecialchars($juego['categoria_nombre']); ?></p>
                                    <div class="game-rating">
                                        <?php 
                                        $rating = round($juego['promedio_rating']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                        }
                                        ?>
                                        <span>(<?php echo number_format($juego['promedio_rating'], 1); ?>)</span>
                                    </div>
                                    <div class="game-price">
                                        <span class="current-price">$<?php echo number_format($juego['precio'], 2); ?></span>
                                        <form method="POST" action="controladores/cont_cart.php" class="add-cart-form">
                                            <input type="hidden" name="juego_id" value="<?php echo $juego['id']; ?>">
                                            <input type="hidden" name="return_url" value="/nexusplay/index.php">
                                            <button type="submit" class="btn btn-primary btn-cart">
                                                <i class="fas fa-shopping-cart"></i> Añadir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="evento-banner-section">
            <div class="evento-banner">
                <div class="evento-content">
                    <div class="evento-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="evento-text">
                        <h3>Gran Evento Gamer</h3>
                        <p>No te pierdas nuestras ofertas especiales este fin de semana.</p>
                    </div>
                    <a href="evento.php" target="_blank" class="evento-btn">
                        <i class="fas fa-ticket-alt"></i> Ver Evento
                    </a>
                </div>
            </div>
        </section>

        <section class="reviews-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Últimas reseñas</h2>
                </div>
                <div class="reviews-grid">
                    <?php if ($resenas_result && $resenas_result->num_rows > 0): ?>
                        <?php while ($resena = $resenas_result->fetch_assoc()): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="review-game">
                                        <img src="images/juegos/<?php echo $resena['imagen'] ?: 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($resena['juego_titulo']); ?>">
                                        <div class="review-game-info">
                                            <h4><?php echo htmlspecialchars($resena['juego_titulo']); ?></h4>
                                            <p class="review-author">por <?php echo htmlspecialchars($resena['username']); ?></p>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php 
                                        $rating = round($resena['puntuacion']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p><?php echo htmlspecialchars(substr($resena['comentario'], 0, 150)) . (strlen($resena['comentario']) > 150 ? '...' : ''); ?></p>
                                </div>
                                <div class="review-date">
                                    <?php echo date('d/m/Y', strtotime($resena['fecha_resena'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="categories-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Explora por categorías</h2>
                </div>
                <div class="categories-grid">
                    <?php if ($categorias_result && $categorias_result->num_rows > 0): ?>
                        <?php while ($categoria = $categorias_result->fetch_assoc()): ?>
                            <a href="search.php?plataforma=&categoria=<?php echo $categoria['id']; ?>" class="category-card">
                                <div class="category-icon">
                                    <i class="fas fa-gamepad"></i>
                                </div>
                                <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                                <p><?php echo $categoria['total_juegos']; ?> juegos</p>
                            </a>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const totalSlides = slides.length;

        function showSlide(index) {
            const carousel = document.getElementById('gamesCarousel');
            currentSlide = (index + totalSlides) % totalSlides;
            carousel.style.transform = `translateX(-${currentSlide * 100}%)`;
            updateIndicators();
        }

        function moveCarousel(direction) {
            showSlide(currentSlide + direction);
        }

        function updateIndicators() {
            const indicators = document.getElementById('carouselIndicators');
            indicators.innerHTML = '';
            for (let i = 0; i < totalSlides; i++) {
                const indicator = document.createElement('button');
                indicator.className = `indicator ${i === currentSlide ? 'active' : ''}`;
                indicator.onclick = () => showSlide(i);
                indicators.appendChild(indicator);
            }
        }

        if (totalSlides > 1) {
            updateIndicators();
            setInterval(() => moveCarousel(1), 5000);
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>