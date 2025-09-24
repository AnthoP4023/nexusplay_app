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

            <form method="POST" class="card-form" novalidate>

                <!-- SELECCIONAR MONTO -->
                <div class="form-group">
                    <label for="monto">Selecciona el monto a recargar</label>
                    <select id="monto" name="monto">
                        <option value="">--Selecciona--</option>
                        <option value="10">$10</option>
                        <option value="20">$20</option>
                        <option value="50">$50</option>
                        <option value="100">$100</option>
                    </select>
                </div>

                <!-- MÉTODO DE PAGO -->
                <div class="form-group">
                    <label for="metodo_pago">Escoge Método de Pago</label>
                    <select id="metodo_pago" name="metodo_pago">
                        <option value="">--Selecciona--</option>
                        <option value="tarjeta_existente">Usar tarjeta existente</option>
                        <option value="nueva_tarjeta">Usar nueva tarjeta</option>
                    </select>
                </div>

                <!-- NUEVA TARJETA -->
                <div class="form-section" id="nueva_tarjeta_section" style="display:none;">
                    <h3><i class="fas fa-info-circle"></i> Información de la Tarjeta</h3>

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

                    <div class="form-group">
                        <label for="alias_tarjeta">Alias para la Tarjeta (Opcional)</label>
                        <input type="text" id="alias_tarjeta" name="alias_tarjeta" placeholder="Mi Tarjeta Personal" maxlength="50">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="agregar_tarjeta" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Agregar Tarjeta / Recargar
                    </button>
                    <a href="profile/user/mis_tarjetas.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const monto = document.getElementById('monto');
    const metodo_pago = document.getElementById('metodo_pago');
    const nuevaTarjetaSection = document.getElementById('nueva_tarjeta_section');
    const form = document.querySelector('.card-form');

    // Mostrar/Ocultar nueva tarjeta
    metodo_pago.addEventListener('change', () => {
        nuevaTarjetaSection.style.display = metodo_pago.value === 'nueva_tarjeta' ? 'block' : 'none';
    });

    // Validación
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Mensajes dinámicos
        const showMessage = (msg) => {
            const div = document.createElement('div');
            div.className = 'message message-error';
            div.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${msg}`;
            form.prepend(div);
        };

        // Limpiar mensajes previos
        document.querySelectorAll('.message-error').forEach(m => m.remove());

        if (!monto.value) {
            showMessage('Selecciona el monto a recargar');
            isValid = false;
        }

        if (!metodo_pago.value) {
            showMessage('Escoge un método de pago o usa nueva tarjeta');
            isValid = false;
        }

        if (metodo_pago.value === 'nueva_tarjeta') {
            const numero = document.getElementById('numero_tarjeta').value.replace(/\s/g,'');
            const fecha = document.getElementById('fecha_expiracion').value;
            const cvv = document.getElementById('cvv').value;
            const titular = document.getElementById('nombre_titular').value.trim();

            if (!numero || numero.length < 13 || numero.length > 19) {
                showMessage('Número de tarjeta inválido');
                isValid = false;
            }
            if (!/^\d{2}\/\d{2}$/.test(fecha)) {
                showMessage('Fecha inválida (MM/YY)');
                isValid = false;
            }
            if (!/^\d{3,4}$/.test(cvv)) {
                showMessage('CVV inválido');
                isValid = false;
            }
            if (!titular || titular.length < 3) {
                showMessage('Nombre del titular requerido');
                isValid = false;
            }
        }

        if (!isValid) e.preventDefault();
    });
});
</script>
</body>
</html>
