<?php 
include 'controladores/cont_cart.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - NexusPlay</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="cart-container">
            <div class="cart-header">
                <h1><i class="fas fa-shopping-cart"></i> Mi Carrito</h1>
                <div class="cart-summary">
                    <span class="cart-count"><?php echo count($carrito_items); ?> productos</span>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="cart-message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php 
            if (isset($_SESSION['cart_message'])): ?>
                <div class="cart-message <?php echo $_SESSION['cart_message_type']; ?>">
                    <?php 
                    echo htmlspecialchars($_SESSION['cart_message']); 
                    unset($_SESSION['cart_message'], $_SESSION['cart_message_type']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (empty($carrito_items)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2>Tu carrito está vacío</h2>
                    <p>¡Explora nuestra tienda y encuentra tus juegos favoritos!</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-gamepad"></i> Explorar Juegos
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <?php foreach ($carrito_items as $juego_id => $item): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="images/juegos/<?php echo htmlspecialchars($item['imagen'] ?: 'default.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['titulo']); ?>">
                                </div>
                                
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['titulo']); ?></h3>
                                    <div class="item-price">
                                        $<?php echo number_format($item['precio'], 2); ?>
                                    </div>
                                </div>
                                
                                <div class="item-controls">
                                    <form method="POST" class="quantity-form">
                                        <input type="hidden" name="juego_id" value="<?php echo $juego_id; ?>">
                                        <div class="quantity-controls">
                                            <button type="button" class="quantity-btn minus" onclick="decreaseQuantity(<?php echo $juego_id; ?>)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                   name="nueva_cantidad" 
                                                   value="<?php echo $item['cantidad']; ?>" 
                                                   min="1" 
                                                   max="10"
                                                   class="quantity-input"
                                                   id="quantity_<?php echo $juego_id; ?>"
                                                   onchange="updateQuantity(<?php echo $juego_id; ?>)">
                                            <button type="button" class="quantity-btn plus" onclick="increaseQuantity(<?php echo $juego_id; ?>)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <button type="submit" name="update_quantity" class="btn-update-hidden" style="display: none;">Actualizar</button>
                                    </form>
                                    
                                    <div class="item-subtotal">
                                        $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                    </div>
                                    
                                    <form method="POST" class="remove-form">
                                        <input type="hidden" name="juego_id" value="<?php echo $juego_id; ?>">
                                        <button type="submit" name="remove_from_cart" class="btn-remove">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-sidebar">
                        <div class="cart-summary-card">
                            <h3>Resumen del pedido</h3>
                            
                            <div class="summary-row">
                                <span>Productos (<?php echo $total_items_carrito; ?>)</span>
                                <span>$<?php echo number_format($total_carrito, 2); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Descuentos</span>
                                <span>-$0.00</span>
                            </div>
                            
                            <div class="summary-row total">
                                <span><strong>Total</strong></span>
                                <span><strong>$<?php echo number_format($total_carrito, 2); ?></strong></span>
                            </div>
                            
                            <div class="cart-actions">
                                <?php if (isLoggedIn()): ?>
                                    <a href="buy_game.php" class="btn btn-primary btn-checkout">
                                        <i class="fas fa-credit-card"></i> Proceder al Pago
                                    </a>
                                <?php else: ?>
                                    <a href="auth/login.php" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión para Comprar
                                    </a>
                                <?php endif; ?>
                                
                                <form method="POST" style="margin-top: 10px;">
                                    <button type="submit" name="clear_cart" class="btn btn-secondary">
                                        <i class="fas fa-trash-alt"></i> Vaciar Carrito
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="continue-shopping">
                            <a href="index.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Seguir Comprando
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function increaseQuantity(juegoId) {
            const input = document.getElementById('quantity_' + juegoId);
            const currentValue = parseInt(input.value);
            if (currentValue < 10) {
                input.value = currentValue + 1;
                updateQuantity(juegoId);
            }
        }

        function decreaseQuantity(juegoId) {
            const input = document.getElementById('quantity_' + juegoId);
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateQuantity(juegoId);
            }
        }

        function updateQuantity(juegoId) {
            const form = document.querySelector(`input[value="${juegoId}"]`).closest('form');
            const submitBtn = form.querySelector('.btn-update-hidden');
            submitBtn.click();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const juegoId = this.id.replace('quantity_', '');
                    updateQuantity(juegoId);
                });
            });
        });
    </script>
</body>
</html>