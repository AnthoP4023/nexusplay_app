<?php
function obtenerPedidoConVerificacion($conn, $pedido_id, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT p.*, u.username, u.email, u.nombre, u.apellido
            FROM pedidos p
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.id = ? AND p.usuario_id = ?
        ");
        $stmt->bind_param("ii", $pedido_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    } catch (mysqli_sql_exception $e) {
        error_log("Error al obtener pedido: " . $e->getMessage());
        return null;
    }
}

function obtenerJuegosConCodigos($conn, $pedido_id) {
    try {
        $stmt = $conn->prepare("
            SELECT dp.*, j.titulo, j.imagen, j.desarrollador, 
                   p.nombre as plataforma_nombre, c.nombre as categoria_nombre
            FROM detalles_pedido dp
            JOIN juegos j ON dp.juego_id = j.id
            LEFT JOIN plataformas p ON j.plataforma_id = p.id
            LEFT JOIN categorias c ON j.categoria_id = c.id
            WHERE dp.pedido_id = ?
            ORDER BY j.titulo ASC
        ");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $juegos = [];
        while ($juego = $result->fetch_assoc()) {
            $juegos[] = $juego;
        }
        
        return $juegos;
    } catch (mysqli_sql_exception $e) {
        error_log("Error al obtener juegos con códigos: " . $e->getMessage());
        return [];
    }
}

function obtenerRutaImagenJuego($imagen) {
    if (empty($imagen) || $imagen === 'default.jpg') {
        return '/images/juegos/default.jpg';
    }
    
    $ruta = '/images/juegos/' . $imagen;
    $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $ruta;
    
    if (file_exists($ruta_fisica)) {
        return $ruta;
    }
    
    return '/images/juegos/default.jpg';
}

function formatearFechaPedido($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
}

function formatearNumeroPedido($pedido_id, $longitud = 6) {
    return str_pad($pedido_id, $longitud, '0', STR_PAD_LEFT);
}


function calcularEstadisticasPedido($juegos, $pedido) {
    $stats = [
        'total_juegos' => count($juegos),
        'fecha_formateada' => formatearFechaPedido($pedido['fecha_pedido']),
        'numero_pedido' => formatearNumeroPedido($pedido['id']),
        'total_formateado' => number_format($pedido['total'], 2),
        'codigos_disponibles' => 0,
        'codigos_utilizados' => 0
    ];
    
    foreach ($juegos as $juego) {
        if (!empty($juego['codigo_entregado'])) {
            $stats['codigos_disponibles']++;
        } else {
            $stats['codigos_utilizados']++;
        }
    }
    
    return $stats;
}

function validarCodigoJuego($codigo) {
    if (empty($codigo)) {
        return false;
    }
    
    if (strlen($codigo) < 8) {
        return false;
    }
    
    if (!preg_match('/^[A-Z0-9\-]+$/', $codigo)) {
        return false;
    }
    
    return true;
}

function generarContenidoDescargaCodigos($pedido, $juegos) {
    $separador = str_repeat('=', 50);
    
    $contenido = "NEXUSPLAY - CÓDIGOS DE ACTIVACIÓN\n";
    $contenido .= $separador . "\n";
    $contenido .= "Pedido #: " . formatearNumeroPedido($pedido['id']) . "\n";
    $contenido .= "Fecha: " . formatearFechaPedido($pedido['fecha_pedido']) . "\n";
    $contenido .= "Total: $" . number_format($pedido['total'], 2) . "\n";
    $contenido .= "Cliente: " . $pedido['nombre'] . ' ' . $pedido['apellido'] . "\n";
    $contenido .= "Email: " . $pedido['email'] . "\n\n";
    
    $contenido .= $separador . "\n";
    $contenido .= "JUEGOS COMPRADOS:\n";
    $contenido .= $separador . "\n\n";
    
    foreach ($juegos as $juego) {
        $contenido .= $juego['titulo'] . "\n";
        $contenido .= "Desarrollador: " . ($juego['desarrollador'] ?: 'No especificado') . "\n";
        $contenido .= "Plataforma: " . ($juego['plataforma_nombre'] ?: 'Multiplataforma') . "\n";
        $contenido .= "Precio: $" . number_format($juego['precio_unitario'], 2) . "\n";
        
        if (validarCodigoJuego($juego['codigo_entregado'])) {
            $contenido .= "Código: " . $juego['codigo_entregado'] . "\n";
        } else {
            $contenido .= "Código: [Procesando...]\n";
        }
        
        $contenido .= str_repeat('-', 30) . "\n\n";
    }
    
    $contenido .= $separador . "\n";
    $contenido .= "INSTRUCCIONES DE ACTIVACIÓN:\n";
    $contenido .= $separador . "\n";
    $contenido .= "1. Abre tu plataforma de juegos (Steam, Epic, Origin, etc.)\n";
    $contenido .= "2. Busca la opción 'Activar código' o 'Canjear código'\n";
    $contenido .= "3. Introduce el código exactamente como aparece\n";
    $contenido .= "4. Confirma la activación\n";
    $contenido .= "5. ¡Disfruta tu juego!\n\n";
    
    $contenido .= "Soporte: soporte@nexusplay.com\n";
    $contenido .= "Web: https://nexusplay.com\n\n";
    
    $contenido .= "Gracias por tu compra en NexusPlay\n";
    $contenido .= "© " . date('Y') . " NexusPlay. Todos los derechos reservados.\n";
    
    return $contenido;
}

function registrarActividadCodigos($conn, $pedido_id, $user_id, $accion = 'view') {
    try {
        
        error_log("Actividad códigos - Usuario: $user_id, Pedido: $pedido_id, Acción: $accion");
        return true;
    } catch (Exception $e) {
        error_log("Error al registrar actividad de códigos: " . $e->getMessage());
        return false;
    }
}


function tieneCodigosPendientes($juegos) {
    foreach ($juegos as $juego) {
        if (empty($juego['codigo_entregado']) || !validarCodigoJuego($juego['codigo_entregado'])) {
            return true;
        }
    }
    return false;
}

function obtenerIconoPlataforma($plataforma) {
    $plataforma = strtolower($plataforma ?: '');
    
    $iconos = [
        'steam' => 'fab fa-steam',
        'epic' => 'fas fa-gamepad',
        'origin' => 'fas fa-gamepad',
        'uplay' => 'fas fa-gamepad',
        'pc' => 'fas fa-desktop',
        'playstation' => 'fab fa-playstation',
        'xbox' => 'fab fa-xbox',
        'nintendo' => 'fas fa-gamepad'
    ];
    
    foreach ($iconos as $key => $icono) {
        if (strpos($plataforma, $key) !== false) {
            return $icono;
        }
    }
    
    return 'fas fa-gamepad';
}


function sanitizarCodigo($codigo) {
    return htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');
}


function puedeAccederCodigos($conn, $pedido_id, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM pedidos 
            WHERE id = ? AND usuario_id = ? AND estado = 'completado'
        ");
        $stmt->bind_param("ii", $pedido_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    } catch (mysqli_sql_exception $e) {
        error_log("Error al verificar acceso a códigos: " . $e->getMessage());
        return false;
    }
}
?>