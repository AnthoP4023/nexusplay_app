<?php
include 'controladores/cont_agg_card.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Tarjeta - NexusPlay</title>
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
                <h1><i class="fas fa-credit-card"></i> Agregar Nueva Tarjeta</h1>
                <p>Guarda tu tarjeta de forma segura para futuras compras</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="message message-<?php echo $mensaje_tipo; ?>">
                    <i class="fas fa-<?php echo $mensaje_tipo === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

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

                <form method="POST" class="card-form" novalidate>
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Información de la Tarjeta</h3>
                        
                        <div class="form-group">
                            <label for="numero_tarjeta">Número de Tarjeta</label>
                            <input type="text" id="numero_tarjeta" name="numero_tarjeta" 
                                   placeholder="1234 5678 9012 3456" maxlength="19" required>
                            <div class="input-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_expiracion">Fecha de Expiración</label>
                                <input type="text" id="fecha_expiracion" name="fecha_expiracion" 
                                       placeholder="MM/YY" maxlength="5" required>
                                <div class="input-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" 
                                       placeholder="123" maxlength="4" required>
                                <div class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nombre_titular">Nombre del Titular</label>
                            <input type="text" id="nombre_titular" name="nombre_titular" 
                                   placeholder="Como aparece en la tarjeta" required>
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="alias_tarjeta">Alias para la Tarjeta (Opcional)</label>
                            <input type="text" id="alias_tarjeta" name="alias_tarjeta" 
                                   placeholder="Mi Tarjeta Personal" maxlength="50">
                            <div class="input-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                        </div>
                    </div>

                    <div class="security-info">
                        <div class="security-badge">
                            <i class="fas fa-shield-alt"></i>
                            <div class="security-text">
                                <h4>Información Segura</h4>
                                <p>Tus datos están protegidos con cifrado de nivel bancario SSL de 256 bits</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="agregar_tarjeta" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar Tarjeta
                        </button>
                        <a href="profile/user/mis_tarjetas.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </form>

                <div class="card-benefits">
                    <h3><i class="fas fa-star"></i> Beneficios de Guardar tu Tarjeta</h3>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Compras más rápidas y sencillas</li>
                        <li><i class="fas fa-check-circle"></i> No necesitas ingresar datos cada vez</li>
                        <li><i class="fas fa-check-circle"></i> Información cifrada y segura</li>
                        <li><i class="fas fa-check-circle"></i> Puedes eliminar la tarjeta cuando quieras</li>
                        <li><i class="fas fa-check-circle"></i> Múltiples tarjetas guardadas</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const numeroTarjetaInput = document.getElementById('numero_tarjeta');
            const fechaExpiracionInput = document.getElementById('fecha_expiracion');
            const cvvInput = document.getElementById('cvv');
            const nombreTitularInput = document.getElementById('nombre_titular');

            const cardNumberDisplay = document.getElementById('cardNumber');
            const cardHolderDisplay = document.getElementById('cardHolder');
            const cardExpiryDisplay = document.getElementById('cardExpiry');

            // Formateo y máscara de tarjeta
            numeroTarjetaInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/g, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || '';
                e.target.value = formattedValue.substr(0, 19);
                cardNumberDisplay.textContent = value.length > 0 
                    ? value.replace(/\d(?=\d{4})/g, '*').match(/.{1,4}/g)?.join(' ') 
                    : '**** **** **** ****';
            });

            // Formato de fecha
            fechaExpiracionInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) value = value.substring(0,2)+'/'+value.substring(2,4);
                e.target.value = value;
                cardExpiryDisplay.textContent = value || 'MM/YY';
            });

            // CVV solo números
            cvvInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });

            // Nombre del titular en mayúsculas
            nombreTitularInput.addEventListener('input', function(e) {
                cardHolderDisplay.textContent = e.target.value.toUpperCase() || 'TU NOMBRE';
            });

            // Validación del formulario al enviar
            const form = document.querySelector('.card-form');
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const numeroTarjeta = numeroTarjetaInput.value.replace(/\s/g, '');
                const fechaExp = fechaExpiracionInput.value;
                const cvv = cvvInput.value;
                const nombreTitular = nombreTitularInput.value.trim();

                // Número de tarjeta
                if (!numeroTarjeta) {
                    showError(numeroTarjetaInput, 'Falta ingresar el número de tarjeta');
                    isValid = false;
                } else if (!/^\d+$/.test(numeroTarjeta)) {
                    showError(numeroTarjetaInput, 'El número de tarjeta solo puede contener números');
                    isValid = false;
                } else if (numeroTarjeta.length < 13 || numeroTarjeta.length > 19) {
                    showError(numeroTarjetaInput, 'Número de tarjeta inválido');
                    isValid = false;
                } else hideError(numeroTarjetaInput);

                // Fecha de expiración
                if (!fechaExp) {
                    showError(fechaExpiracionInput, 'Falta ingresar la fecha de expiración');
                    isValid = false;
                } else if (!/^\d{2}\/\d{2}$/.test(fechaExp)) {
                    showError(fechaExpiracionInput, 'Formato de fecha inválido (MM/YY)');
                    isValid = false;
                } else {
                    const [mes, año] = fechaExp.split('/').map(Number);
                    const fechaActual = new Date();
                    const fechaTarjeta = new Date(2000+año, mes-1);
                    fechaTarjeta.setMonth(fechaTarjeta.getMonth()+1, 0);
                    if (mes<1||mes>12) {
                        showError(fechaExpiracionInput, 'Mes inválido');
                        isValid = false;
                    } else if (fechaTarjeta <= fechaActual) {
                        showError(fechaExpiracionInput, 'Tarjeta vencida');
                        isValid = false;
                    } else hideError(fechaExpiracionInput);
                }

                // CVV
                if (!cvv) {
                    showError(cvvInput, 'Falta ingresar el CVV');
                    isValid = false;
                } else if (!/^\d{3,4}$/.test(cvv)) {
                    showError(cvvInput, 'CVV inválido');
                    isValid = false;
                } else hideError(cvvInput);

                // Nombre titular
                if (!nombreTitular) {
                    showError(nombreTitularInput, 'Falta ingresar el nombre del titular');
                    isValid = false;
                } else if (nombreTitular.length < 3) {
                    showError(nombreTitularInput, 'Nombre del titular muy corto');
                    isValid = false;
                } else hideError(nombreTitularInput);

                if (!isValid) e.preventDefault();
            });

            function showError(input, message) {
                hideError(input);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = message;
                input.parentNode.appendChild(errorDiv);
                input.classList.add('error');
            }

            function hideError(input) {
                const existingError = input.parentNode.querySelector('.error-message');
                if (existingError) existingError.remove();
                input.classList.remove('error');
            }
        });
    </script>
</body>
</html>
