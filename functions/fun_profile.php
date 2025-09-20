<?php
function loadUserProfileImage($conn, $user_id) {
    if (!$user_id) return '/nexusplay/images/users/default-avatar.png';
    
    try {
        $stmt = $conn->prepare("SELECT imagen_perfil FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $imagen_bd = $user_data['imagen_perfil'];
            
            if (!empty($imagen_bd) && $imagen_bd !== 'default-avatar.png') {
                $ruta_imagen = '/nexusplay/images/users/' . $imagen_bd;
                $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $ruta_imagen;
                
                if (file_exists($ruta_fisica)) {
                    return $ruta_imagen;
                } else {
                    return '/nexusplay/images/users/default-avatar.png';
                }
            } else {
                return '/nexusplay/images/users//default-avatar.png';
            }
        } else {
            return '/nexusplay/images/users/default-avatar.png';
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Error al cargar imagen de perfil: " . $e->getMessage());
        return ' /nexusplay/images/users/default-avatar.png';
    }
}
?>