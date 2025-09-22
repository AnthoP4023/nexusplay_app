<?php  
include 'controladores/cont_search.php'; 
include 'functions/fun_search.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo !empty($search_query) 
            ? 'Resultados para "' . htmlspecialchars($search_query) . '"' 
            : 'Todos los juegos'; ?> - NexusPlay
    </title>
    
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="search-filters-container">
        <form method="GET">
            <div class="filter-row">
                <select name="plataforma">
                    <option value="">Todas las plataformas</option>
                    <?php if ($plataformas): ?>
                        <?php while($plat = $plataformas->fetch_assoc()): ?>
                            <option value="<?= $plat['id'] ?>" <?= ($plataforma_id == $plat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($plat['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>

                <select name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php if ($categorias): ?>
                        <?php while($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($categoria_id == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>

                <select name="precio">
                    <option value="">Todos los precios</option>
                    <option value="1" <?= ($precio=='1') ? 'selected' : '' ?>>Menos de $20</option>
                    <option value="2" <?= ($precio=='2') ? 'selected' : '' ?>>$20 - $50</option>
                    <option value="3" <?= ($precio=='3') ? 'selected' : '' ?>>Más de $50</option>
                </select>
            </div>

            <div class="filter-buttons">
                <button type="submit" class="btn btn-search">Buscar</button>
                <a href="search.php" class="btn btn-clean">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="search-container">    
        <div class="search-results-count">
            <h2><?php echo $total_resultados; ?> Resultados</h2>
            <?php if ($total_resultados == 0): ?>
                <p>No se encontraron juegos que coincidan con tu búsqueda.</p>
            <?php endif; ?>
        </div>

        <?php if ($juegos_result && $total_resultados > 0): ?>
            <div class="games-grid<?php 
                if ($total_resultados === 1) echo ' single-result';
                elseif ($total_resultados === 2) echo ' few-results';
            ?>">
                <?php while ($juego = $juegos_result->fetch_assoc()): ?>
                    <div class="game-card">
                        <div class="game-image">
                            <img src="images/juegos/<?php echo $juego['imagen']?>" 
                                alt="<?php echo htmlspecialchars($juego['titulo']); ?>">
                            <div class="game-overlay">
                                <a href="game_view.php?id=<?php echo $juego['id']; ?>" class="btn-overlay">
                                    Ver detalles
                                </a>
                            </div>
                        </div>

                        <div class="game-info">
                            <h3><?php echo highlightSearchTerm($juego['titulo'], $search_query); ?></h3>
                            <p class="game-developer"><?php echo htmlspecialchars($juego['desarrollador'] ?? 'Sin desarrollador'); ?></p>
                            <p class="game-description">
                                <?php echo htmlspecialchars($juego['descripcion'] ?? ''); ?>
                            </p>

                            <div class="game-price">
                                <?php 
                                $precio_juego = (isset($juego['precio']) && is_numeric($juego['precio'])) 
                                    ? (float)$juego['precio'] 
                                    : 0;
                                ?>
                                <span class="current-price">
                                    $<?php echo number_format($precio_juego, 2); ?>
                                </span>

                                <form method="POST" action="/nexusplay/controladores/cont_cart.php" class="add-cart-form">
                                    <input type="hidden" name="juego_id" value="<?php echo $juego['id']; ?>">
                                    <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                    <button type="submit" class="btn btn-primary btn-cart">
                                        <i class="fas fa-shopping-cart"></i> Añadir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('click', (event) => {
            const profileMenu = document.querySelector('.profile-menu');
            const profileToggle = document.querySelector('#profile-toggle');
            if (profileMenu && profileToggle && !profileMenu.contains(event.target)) {
                profileToggle.checked = false;
            }
        });
    </script>
</body>
</html>