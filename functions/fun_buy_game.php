<?php 
function getGameImagePath($imagen) {
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
?>
