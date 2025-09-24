<?php 
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
?>