<?php
include 'controladores/cont_recharge.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recargar Cartera - NexusPlay</title>
<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/recharge.css">
<link rel="stylesheet" href="assests/fontawesome/css/all.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="main-content">
<div class="recharge-container">
    <div class="recharge-header">
        <h1><i class="fas fa-wallet"></i> Recargar Cartera</h1>
        <p>Agrega fondos a tu cartera digital para comprar juegos</p>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="message message-<?php echo $mensaje_tipo; ?>">
            <i class="fas fa-<?php echo $mensaje_tipo === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <div class="recharge-content">
        <div class="balance-info">
            <h3>Saldo Actual</h3>
            <div class="current-balance">$<?php echo number_format($saldo_cartera, 2); ?></div>
        </div>

        <form method="POST" class="recharge-form">
            <h3>Selecciona el monto a recargar</h3>
            
            <div class="amount-options">
                <input type="radio" id="amount_10" name="monto_recarga" value="10.00">
                <label for="amount_10" class="amount-card"><span class="amount">$10.00</span></label>

                <input type="radio" id="amount_25" name="monto_recarga" value="25.00">
                <label for="amount_25" class="amount-card"><span class="amount">$25.00</span></label>

                <input type="radio" id="amount_50" name="monto_recarga" value="50.00">
                <label for="amount_50" class="amount-card"><span class="amount">$50.00</span></label>

                <input type="radio" id="amount_100" name="monto_recarga" value="100.00">
                <label for="amount_100" class="amount-card"><span class="amount">$100.00</span></label>

                <input type="radio" id="amount_custom" name="monto_recarga" value="custom">
                <label for="amount_custom" class="amount-card custom-amount"><span class="amount">Personalizado</span></label>
            </div>

            <div class="custom-amount-input" id="customAmountDiv" style="display: none;">
                <label for="custom_amount">Monto personalizado:</label>
                <input type="number" id="custom_amount" name="custom_amount" min="1" max="1000" step="0.01" placeholder="Ingresa el monto">
            </div>

            <h3>Método de Pago</h3>

            <div class="payment-methods">
                <?php if (!empty($tarjetas)): ?>
                    <div class="saved-cards">
                        <h4><i class="fas fa-credit-card"></i> Mis Tarjetas</h4>
                        <?php foreach ($tarjetas as $tarjeta): ?>
                            <div class="payment-option">
                                <input type="radio" id="card_<?php echo $tarjeta['id']; ?>" 
                                       name="metodo_pago" value="tarjeta_<?php echo $tarjeta['id']; ?>">
                                <label for="card_<?php echo $tarjeta['id']; ?>" class="card-option">
                                    <div class="card-info">
                                        <i class="fas fa-credit-card"></i>
                                        <span>**** **** **** <?php echo $tarjeta['ultimos_4']; ?></span>
                                        <span class="card-alias"><?php echo htmlspecialchars($tarjeta['alias'] ?: 'Tarjeta'); ?></span>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="new-card-section">
                    <div class="payment-option">
                        <input type="radio" id="new_card" name="metodo_pago" value="nueva_tarjeta">
                        <label for="new_card" class="payment-label"><i class="fas fa-plus-circle"></i> Usar Nueva Tarjeta</label>
                    </div>

                    <div class="new-card-form" id="newCardForm" style="display: none;">
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
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="guardar_tarjeta" value="1">
                                <span class="checkmark"></span>
                                Guardar esta tarjeta para futuras compras
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="recharge-summary">
                <div class="summary-row"><span>Saldo Actual:</span><span>$<?php echo number_format($saldo_cartera, 2); ?></span></div>
                <div class="summary-row"><span>Monto a Recargar:</span><span id="selected-amount">$0.00</span></div>
                <div class="summary-row total"><span>Nuevo Saldo:</span><span id="new-balance">$<?php echo number_format($saldo_cartera, 2); ?></span></div>
            </div>

            <button type="submit" name="realizar_recarga" class="btn btn-primary btn-recharge">
                <i class="fas fa-credit-card"></i> Recargar Cartera
            </button>
        </form>
    </div>
</div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customAmountRadio = document.getElementById('amount_custom');
    const customAmountDiv = document.getElementById('customAmountDiv');
    const customAmountInput = document.getElementById('custom_amount');
    const newCardRadio = document.getElementById('new_card');
    const newCardForm = document.getElementById('newCardForm');

    // Monto personalizado
    customAmountRadio.addEventListener('change', function() {
        customAmountDiv.style.display = 'block';
        customAmountInput.required = true;
    });
    document.querySelectorAll('input[name="monto_recarga"]:not(#amount_custom)').forEach(radio => {
        radio.addEventListener('change', function() {
            customAmountDiv.style.display = 'none';
            customAmountInput.required = false;
            customAmountInput.value = '';
        });
    });

    // Nueva tarjeta
    if (newCardRadio) {
        newCardRadio.addEventListener('change', function() {
            newCardForm.style.display = 'block';
            ['numero_tarjeta','fecha_expiracion','cvv','nombre_titular'].forEach(id => {
                document.getElementById(id).required = true;
            });
        });
    }
    document.querySelectorAll('input[name="metodo_pago"]:not(#new_card)').forEach(radio => {
        radio.addEventListener('change', function() {
            newCardForm.style.display = 'none';
            ['numero_tarjeta','fecha_expiracion','cvv','nombre_titular'].forEach(id => {
                document.getElementById(id).required = false;
            });
        });
    });

    // Resumen de recarga
    function updateSummary() {
        const selectedRadio = document.querySelector('input[name="monto_recarga"]:checked');
        let amount = 0;
        if (selectedRadio) {
            if (selectedRadio.value === 'custom') {
                amount = parseFloat(customAmountInput.value) || 0;
            } else {
                amount = parseFloat(selectedRadio.value) || 0;
            }
        }
        const currentBalance = <?php echo $saldo_cartera; ?>;
        document.getElementById('selected-amount').textContent = '$' + amount.toFixed(2);
        document.getElementById('new-balance').textContent = '$' + (currentBalance + amount).toFixed(2);
    }

    document.querySelectorAll('input[name="monto_recarga"]').forEach(radio => {
        radio.addEventListener('change', updateSummary);
    });
    customAmountInput.addEventListener('input', updateSummary);
});
</script>
</body>
</html>
