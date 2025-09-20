<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evento - Sorteo de Códigos de Juegos</title>
    <link rel="stylesheet" href="css/evento.css">
    <link rel="stylesheet" href="assests/fontawesome/css/all.min.css">
</head>
<body>
    <div class="evento-container">
        <div class="evento-content">
            <div class="evento-header">
                <div class="evento-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h1>¡Gran Sorteo NexusPlay!</h1>
                <h2>Participa para Ganar 5 Códigos de Juegos Premium</h2>
            </div>

            <div class="evento-description">
                <div class="premio-info">
                    <div class="premio-item">
                        <i class="fas fa-gamepad"></i>
                        <span>5 Códigos de Juegos AAA</span>
                    </div>
                    <div class="premio-item">
                        <i class="fas fa-gift"></i>
                        <span>Valor hasta $300</span>
                    </div>
                    <div class="premio-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Sorteo cada mes</span>
                    </div>
                </div>

                <div class="evento-rules">
                    <h3>¿Cómo participar?</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> Ingresa tu correo electrónico</li>
                        <li><i class="fas fa-check"></i> Haz clic en "Participar"</li>
                        <li><i class="fas fa-check"></i> ¡Espera los resultados!</li>
                    </ul>
                </div>
            </div>

            <div id="mensaje-exito" class="mensaje-exito" style="display:none;">
                <i class="fas fa-check-circle"></i>
                ¡Gracias por participar! Tu registro ha sido exitoso.<br>
                Recibirás los resultados del sorteo por correo electrónico.
            </div>

            <div class="evento-form">
                <form id="participacionForm">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Correo Electrónico
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="tu@email.com" 
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn-participar">
                        <i class="fas fa-ticket-alt"></i>
                        ¡Participar en el Sorteo!
                    </button>
                </form>
            </div>

            <div class="evento-footer">
                <p class="disclaimer">
                    <i class="fas fa-info-circle"></i>
                    Solo una participación por correo electrónico. El sorteo se realiza mensualmente.
                </p>
                
                <div class="premio-showcase">
                    <h3>Premios incluidos:</h3>
                    <div class="juegos-grid">
                        <div class="juego-premio">
                            <i class="fas fa-star"></i>
                            <span>Juegos AAA</span>
                        </div>
                        <div class="juego-premio">
                            <i class="fas fa-fire"></i>
                            <span>Nuevos Lanzamientos</span>
                        </div>
                        <div class="juego-premio">
                            <i class="fas fa-heart"></i>
                            <span>Favoritos de la Comunidad</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('participacionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const mensaje = document.getElementById('mensaje-exito');

        if (email) {
            mensaje.style.display = 'block';
            
            document.getElementById('email').value = '';
        }
    });
</script>
</body>
</html>