<?php
include 'controladores/cont_agg_card.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Tarjeta / Recarga - NexusPlay</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/agg_card.css">
    <link rel="stylesheet" href="assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="card-container">
            <div class="card-header">
                <h1><i class="fas fa-credit-card"></i> Agregar / Recargar</h1>
                <p>Guarda tu tarjeta de forma segura y recarga tu saldo</p>
            </div>

            <!-- Contenedor de mensajes PHP -->
            <div id="js-message-container">
                <?php if(!empty($mensaje)): ?>
                    <div class="message <?php echo $mensaje_tipo === 'success' ? 'message-success' : 'message-error'; ?>">
                        <i class="fas <?php echo $mensaje_tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-content">
                <div class="card-preview">
                    <div class="credit-card" id="creditCard">
                        <div class="card-front">
                            <div class="card-logo">
                                <i class="fas fa-credit-card"></i>
                                <span>NexusPlay</span>
                            </div>
                            <div class="card-number" id="cardNumber">**** **** **** ****</div>
                            <div class="card-details">
                                <div class="card-holder">
                                    <span class="label">NOMBRE DEL TITULAR</span>
                                    <span class="value" id="cardHolder">TU NOMBRE</span>
                                </div>
                                <div class="card-expiry">
                                    <span class="label">VÁLIDA HASTA</span>
                                    <span class="value" id="cardExpiry">MM/YY</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORMULARIO CORREGIDO -->
                <form method="POST" class="card-form">
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Información de la Recarga</h3>

                        <div class="form-group">
                            <label for="monto_recarga">Monto a recargar</label>
                            <select id="monto_recarga" name="monto_recarga" required>
                                <option value="">-- Selecciona un monto --</option>
                                <option value="5">5 USD</option>
                                <option value="10">10 USD</option>
                                <option value="20">20 USD</option>
                                <option value="50">50 USD</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="metodo_pago">Método de pago</label>
                            <select id="metodo_pago" name="metodo_pago" required>
                                <option value="">-- Escoge un método --</option>
                                <option value="tarjeta_guardada">Tarjeta guardada</option>
                                <option value="nueva_tarjeta">Usar nueva tarjeta</option>
                            </select>
                        </div>

                        <div id="nueva_tarjeta_section" style="display:none;">
                            <h3><i class="fas fa-credit-card"></i> Información de la Nueva Tarjeta</h3>
                            <div class="form-group">
                                <label for="numero_tarjeta">Número de Tarjeta</label>
                                <input type="text" id="numero_tarjeta" name="numero_tarjeta" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_expiracion">Fecha de Expiración</label>
                                    <input type="text" id="fecha_expiracion" name="fecha_expiracion" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nombre_titular">Nombre del Titular</label>
                                <input type="text" id="nombre_titular" name="nombre_titular" placeholder="Como aparece en la tarjeta">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="agregar_tarjeta" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Recargar / Agregar Tarjeta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const metodo_pago = document.getElementById('metodo_pago');
        const nuevaTarjetaSection = document.getElementById('nueva_tarjeta_section');

        // Mostrar sección de nueva tarjeta según selección
        metodo_pago.addEventListener('change', () => {
            nuevaTarjetaSection.style.display = metodo_pago.value === 'nueva_tarjeta' ? 'block' : 'none';
        });
    });
    </script>
</body>
</html>
