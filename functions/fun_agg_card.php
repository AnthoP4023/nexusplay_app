<?php
if (!function_exists('validarLuhn')) {
    function validarLuhn($numero) {
        $numero = preg_replace('/\D/', '', $numero);
        $longitud = strlen($numero);
        $suma = 0;

        for ($i = $longitud - 1; $i >= 0; $i--) {
            $digito = intval($numero[$i]);
            if (($longitud - $i) % 2 == 0) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }
            $suma += $digito;
        }
        return ($suma % 10) == 0;
    }
}

if (!function_exists('detectarTipoTarjeta')) {
    function detectarTipoTarjeta($numero) {
        $numero = preg_replace('/\D/', '', $numero);

        if (preg_match('/^4/', $numero)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]/', $numero)) {
            return 'MasterCard';
        } elseif (preg_match('/^3[47]/', $numero)) {
            return 'American Express';
        } elseif (preg_match('/^6(?:011|5)/', $numero)) {
            return 'Discover';
        } else {
            return 'Desconocida';
        }
    }
}

if (!function_exists('cardExists')) {
    function cardExists($conn, $user_id, $ultimos4) {
        $sql = "
            SELECT COUNT(*) as count 
            FROM tarjetas 
            WHERE usuario_id = ? 
              AND RIGHT(AES_DECRYPT(numero_tarjeta, 'clave_cifrado_segura'), 4) = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $ultimos4);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data['count'] > 0;
    }
}

if (!function_exists('insertCard')) {
    function insertCard($conn, $user_id, $numero, $fecha_exp, $nombre, $alias) {
        try {
            $sql = "
                INSERT INTO tarjetas (usuario_id, numero_tarjeta, fecha_expiracion, nombre_titular, alias, fecha_registro) 
                VALUES (?, AES_ENCRYPT(?, 'clave_cifrado_segura'), ?, ?, ?, NOW())
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issss", $user_id, $numero, $fecha_exp, $nombre, $alias);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => '¡Tarjeta agregada exitosamente!'];
            } else {
                return ['success' => false, 'message' => 'Error al insertar la tarjeta'];
            }
        } catch (Exception $e) {
            error_log("Error al insertar tarjeta: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al procesar la tarjeta'];
        }
    }
}
?>
