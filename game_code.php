<?php
include 'controladores/cont_game_code.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Códigos de Juegos - NexusPlay</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/game_code.css">
    <link rel="stylesheet" href="assests/fontawesome/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="codes-container">
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>¡Compra Completada!</h1>
                <p>Tu pedido ha sido procesado exitosamente. Aquí tienes los códigos de activación de tus juegos.</p>
            </div>

            <div class="order-info">
                <div class="order-details">
                    <div class="detail-item">
                        <i class="fas fa-receipt"></i>
                        <div>
                            <span class="label">Pedido #</span>
                            <span class="value"><?php echo $numero_pedido; ?></span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <span class="label">Fecha</span>
                            <span class="value"><?php echo $fecha_formateada; ?></span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-dollar-sign"></i>
                        <div>
                            <span class="label">Total</span>
                            <span class="value">$<?php echo $total_formateado; ?></span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-gamepad"></i>
                        <div>
                            <span class="label">Juegos</span>
                            <span class="value"><?php echo $total_juegos; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="codes-section">
                <h2><i class="fas fa-key"></i> Tus Códigos de Activación</h2>
                <p class="codes-info">
                    <i class="fas fa-info-circle"></i>
                    Guarda estos códigos en un lugar seguro. Los necesitarás para activar tus juegos.
                </p>

                <div class="games-codes">
                    <?php foreach ($juegos_comprados as $juego): ?>
                        <div class="game-code-card">
                            <div class="game-image">
                                <img src="<?php echo obtenerRutaImagenJuego($juego['imagen']); ?>" 
                                     alt="<?php echo htmlspecialchars($juego['titulo']); ?>">
                            </div>
                            
                            <div class="game-info">
                                <h3><?php echo htmlspecialchars($juego['titulo']); ?></h3>
                                <div class="game-meta">
                                    <span class="developer">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($juego['desarrollador'] ?: 'No especificado'); ?>
                                    </span>
                                    <span class="platform">
                                        <i class="<?php echo obtenerIconoPlataforma($juego['plataforma_nombre']); ?>"></i>
                                        <?php echo htmlspecialchars($juego['plataforma_nombre'] ?: 'Multiplataforma'); ?>
                                    </span>
                                    <span class="price">
                                        <i class="fas fa-tag"></i>
                                        $<?php echo number_format($juego['precio_unitario'], 2); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="code-section">
                                <div class="code-label">
                                    <i class="fas fa-key"></i>
                                    Código de Activación
                                </div>
                                <div class="code-container">
                                    <input type="text" class="game-code" 
                                           value="<?php echo sanitizarCodigo($juego['codigo_entregado']); ?>" 
                                           readonly id="code_<?php echo $juego['id']; ?>">
                                    <button class="copy-btn" onclick="copyCode('code_<?php echo $juego['id']; ?>', this)">
                                        <i class="fas fa-copy"></i>
                                        Copiar
                                    </button>
                                </div>
                                <div class="code-status">
                                    <i class="fas fa-<?php echo validarCodigoJuego($juego['codigo_entregado']) ? 'shield-alt' : 'exclamation-triangle'; ?>"></i>
                                    <?php echo validarCodigoJuego($juego['codigo_entregado']) ? 'Código único y válido' : 'Código en procesamiento'; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="instructions-section">
                <h2><i class="fas fa-question-circle"></i> Cómo Usar tus Códigos</h2>
                <div class="instructions-grid">
                    <div class="instruction-card">
                        <div class="step-number">1</div>
                        <h4>Copia el Código</h4>
                        <p>Haz clic en "Copiar" junto al código del juego que quieres activar.</p>
                    </div>
                    
                    <div class="instruction-card">
                        <div class="step-number">2</div>
                        <h4>Abre tu Plataforma</h4>
                        <p>Ve a Steam, Epic Games, Origin, o la plataforma correspondiente al juego.</p>
                    </div>
                    
                    <div class="instruction-card">
                        <div class="step-number">3</div>
                        <h4>Activa el Código</h4>
                        <p>Busca la opción "Activar código" o "Canjear código" y pega tu código.</p>
                    </div>
                    
                    <div class="instruction-card">
                        <div class="step-number">4</div>
                        <h4>¡Disfruta!</h4>
                        <p>Una vez activado, podrás descargar e instalar tu juego inmediatamente.</p>
                    </div>
                </div>
            </div>

            <div class="support-section">
                <div class="support-card">
                    <i class="fas fa-headset"></i>
                    <div>
                        <h3>¿Necesitas Ayuda?</h3>
                        <p>Si tienes problemas para activar algún código, nuestro equipo de soporte está aquí para ayudarte.</p>
                        <div class="support-actions">
                            <a href="mailto:soporte@nexusplay.com" class="btn btn-support">
                                <i class="fas fa-envelope"></i>
                                Contactar Soporte
                            </a>
                            <a href="profile/user/mis_pedidos.php" class="btn btn-secondary">
                                <i class="fas fa-history"></i>
                                Ver Mis Pedidos
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="final-actions">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-gamepad"></i>
                    Explorar Más Juegos
                </a>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i>
                    Imprimir Códigos
                </button>
                <button onclick="downloadCodes()" class="btn btn-secondary">
                    <i class="fas fa-download"></i>
                    Descargar Códigos
                </button>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function copyCode(inputId, button) {
            const input = document.getElementById(inputId);
            const originalText = button.innerHTML;
            
            input.select();
            input.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                button.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('copied');
                }, 2000);
            } catch (err) {
                console.error('Error al copiar: ', err);
                button.innerHTML = '<i class="fas fa-times"></i> Error';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
            }
        }

        function downloadCodes() {
            const content = `<?php echo addslashes(generarContenidoDescargaCodigos($pedido, $juegos_comprados)); ?>`;
            
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            
            a.href = url;
            a.download = `NexusPlay_Pedido_<?php echo $numero_pedido; ?>_Códigos.txt`;
            a.click();
            
            window.URL.revokeObjectURL(url);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.game-code-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animate-in');
                }, index * 150);
            });
        });

        function copyAllCodes() {
            const codes = [];
            const gameCards = document.querySelectorAll('.game-code-card');
            
            gameCards.forEach(card => {
                const gameTitle = card.querySelector('h3').textContent;
                const gameCode = card.querySelector('.game-code').value;
                codes.push(`${gameTitle}: ${gameCode}`);
            });

            const allCodes = codes.join('\n');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(allCodes).then(() => {
                    alert('¡Todos los códigos copiados al portapapeles!');
                });
            } else {
                const textArea = document.createElement('textarea');
                textArea.value = allCodes;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('¡Todos los códigos copiados al portapapeles!');
            }
        }
    </script>
</body>
</html>