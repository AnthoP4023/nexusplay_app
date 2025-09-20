<?php
include 'controladores/cont_buy_game.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - NexusPlay</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/buy_game.css">
    <link rel="stylesheet" href="assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="checkout-container">
            <div class="checkout-header">
                <h1><i class="fas fa-credit-card"></i> Finalizar Compra</h1>
                <p>Revisa tu pedido y selecciona tu método de pago</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="message message-<?php echo $mensaje_tipo; ?>">
                    <i class="fas fa-<?php echo $mensaje_tipo === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <div class="checkout-grid">
                <div class="order-summary">
                    <h2><i class="fas fa-shopping-bag"></i> Resumen del Pedido</h2>
                    
                    <div class="order-items">
                        <?php foreach ($carrito_items as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="<?php echo getGameImagePath($item['imagen']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['titulo']); ?>">
                                </div>
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['titulo']); ?></h4>
                                    <p class="item-quantity">Cantidad: <?php echo $item['cantidad']; ?></p>
                                    <p class="item-price">$<?php echo number_format($item['precio'], 2); ?> c/u</p>
                                </div>
                                <div class="item-total">
                                    <strong>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($total_carrito, 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Impuestos:</span>
                            <span>$0.00</span>
                        </div>
                        <div class="total-row total-final">
                            <span><strong>Total:</strong></span>
                            <span><strong>$<?php echo number_format($total_carrito, 2); ?></strong></span>
                        </div>
                    </div>
                </div>

                <div class="payment-section">
                    <h2><i class="fas fa-credit-card"></i> Método de Pago</h2>
                    
                    <form method="POST" class="payment-form">
                        <div class="payment-option">
                            <input type="radio" id="cartera" name="metodo_pago" value="cartera" 
                                   <?php echo $saldo_cartera >= $total_carrito ? '' : 'disabled'; ?>>
                            <label for="cartera" class="payment-label">
                                <div class="payment-header">
                                    <i class="fas fa-wallet"></i>
                                    <span>Cartera NexusPlay</span>
                                    <span class="balance">Saldo: $<?php echo number_format($saldo_cartera, 2); ?></span>
                                </div>
                                <?php if ($saldo_cartera < $total_carrito): ?>
                                    <p class="insufficient-funds">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Saldo insuficiente. Necesitas $<?php echo number_format($total_carrito - $saldo_cartera, 2); ?> más.
                                    </p>
                                <?php endif; ?>
                            </label>
                        </div>

                        <?php if (!empty($tarjetas)): ?>
                            <div class="payment-option">
                                <input type="radio" id="tarjeta" name="metodo_pago" value="tarjeta">
                                <label for="tarjeta" class="payment-label">
                                    <div class="payment-header">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Tarjeta Guardada</span>
                                    </div>
                                </label>
                                
                                <div class="payment-details" id="tarjeta-details" style="display: none;">
                                    <div class="saved-cards">
                                        <?php foreach ($tarjetas as $tarjeta): ?>
                                            <div class="saved-card">
                                                <input type="radio" id="card_<?php echo $tarjeta['id']; ?>" 
                                                       name="tarjeta_seleccionada" value="<?php echo $tarjeta['id']; ?>">
                                                <label for="card_<?php echo $tarjeta['id']; ?>" class="card-option">
                                                    <i class="fas fa-credit-card"></i>
                                                    <span><?php echo htmlspecialchars($tarjeta['alias']); ?></span>
                                                    <span>**** **** **** <?php echo $tarjeta['ultimos_4']; ?></span>
                                                    <span><?php echo $tarjeta['fecha_expiracion']; ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="payment-option">
                            <input type="radio" id="nueva_tarjeta" name="metodo_pago" value="nueva_tarjeta">
                            <label for="nueva_tarjeta" class="payment-label">
                                <div class="payment-header">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Nueva Tarjeta</span>
                                </div>
                            </label>
                            
                            <div class="payment-details" id="nueva_tarjeta-details" style="display: none;">
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="numero_tarjeta">Número de Tarjeta</label>
                                        <input type="text" id="numero_tarjeta" name="numero_tarjeta" 
                                               placeholder="1234 5678 9012 3456" maxlength="19">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="fecha_expiracion">Fecha de Expiración</label>
                                        <input type="text" id="fecha_expiracion" name="fecha_expiracion" 
                                               placeholder="MM/YY" maxlength="5">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cvv">CVV</label>
                                        <input type="text" id="cvv" name="cvv" 
                                               placeholder="123" maxlength="4">
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label for="nombre_titular">Nombre del Titular</label>
                                        <input type="text" id="nombre_titular" name="nombre_titular" 
                                               placeholder="Juan Pérez">
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label for="alias_tarjeta">Nombre para la tarjeta (opcional)</label>
                                        <input type="text" id="alias_tarjeta" name="alias_tarjeta" 
                                               placeholder="Mi Tarjeta Principal">
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="guardar_tarjeta">
                                            <span class="checkmark"></span>
                                            Guardar esta tarjeta para futuras compras
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="cart.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Carrito
                            </a>
                            <button type="submit" name="realizar_compra" class="btn btn-primary btn-purchase">
                                <i class="fas fa-shopping-cart"></i>
                                Completar Compra - $<?php echo number_format($total_carrito, 2); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
<script>
    document.querySelectorAll('input[name="metodo_pago"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.payment-details').forEach(detail => {
                detail.style.display = 'none';
            });
            
            const detailsId = this.value + '-details';
            const details = document.getElementById(detailsId);
            if (details) {
                details.style.display = 'block';
            }
        });
    });

    document.getElementById('numero_tarjeta')?.addEventListener('input', function() {
        let value = this.value.replace(/\s/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        this.value = formattedValue;
    });

    document.getElementById('fecha_expiracion')?.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        this.value = value;
    });

    document.getElementById('cvv')?.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });

</script>
</body>
</html>