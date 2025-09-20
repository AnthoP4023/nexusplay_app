<?php
$trustpilot_score = 4.0;
$trustpilot_reviews = 200000;
$current_year = date('Y');
?>
<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>NexusPlay</h4>
                    <div class="stars-container">
                        <div class="stars">
                            <?php
                            $full_stars = floor($trustpilot_score);
                            $half_star = ($trustpilot_score - $full_stars) >= 0.5 ? 1 : 0;
                            $empty_stars = 5 - $full_stars - $half_star;

                            for ($i = 0; $i < $full_stars; $i++) echo '<i class="fas fa-star"></i>';
                            if ($half_star) echo '<i class="fas fa-star-half-alt"></i>';
                            for ($i = 0; $i < $empty_stars; $i++) echo '<i class="far fa-star"></i>';
                            ?>
                        </div>
                        <div class="stars-text">
                            <?php echo $trustpilot_score; ?> | <?php echo number_format($trustpilot_reviews); ?> reseñas
                        </div>
                    </div>
                <p>Tu tienda de videojuegos favorita. Encuentra los mejores juegos para todas las plataformas.</p>
            </div>

            <div class="footer-section">
                <h4>Enlaces Rápidos</h4>
                <ul>
                    <li><a href="#">Términos y condiciones</a></li>
                    <li><a href="#">Política de privacidad</a></li>
                    <li><a href="#">Programa de afiliación</a></li>
                    <li><a href="#">Contacto</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Plataformas</h4>
                <ul>
                    <li><a href="search.php?plataforma=1&categoria=&precio=">PC Gaming</a></li>
                    <li><a href="search.php?plataforma=2&categoria=&precio=">PlayStation</a></li>
                    <li><a href="search.php?plataforma=3&categoria=&precio=">Xbox</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Síguenos</h4>
                <div class="social-icons">
                    <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon x">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>
                    <a href="#" class="social-icon twitch"><i class="fab fa-twitch"></i></a>
                    <a href="#" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon youtube"><i class="fab fa-youtube"></i></a>                  
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo $current_year; ?> NexusPlay. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>
