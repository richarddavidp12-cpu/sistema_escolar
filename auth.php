<?php
session_start();
require_once 'conexion.php';

// Función para verificar si el usuario tiene permiso para realizar una acción en una vista
function verificarPermiso($usuario_id, $ruta_vista, $nombre_permiso) {
    global $conn;

    $sql = "SELECT vp.permitido 
            FROM vistas_permiso vp
            JOIN vistas v ON vp.vista_id = v.id
            JOIN permisos p ON vp.permiso_id = p.id
            JOIN r_modulo_rol rmr ON vp.r_modulo_rol_id = rmr.id
            JOIN r_usuario_rol rur ON rmr.rol_id = rur.rol_id
            WHERE rur.usuario_id = :usuario_id 
              AND v.ruta_acceso = :ruta_vista 
              AND p.nombre_permiso = :nombre_permiso
              AND rmr.habilitado = TRUE
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':ruta_vista' => $ruta_vista,
        ':nombre_permiso' => $nombre_permiso
    ]);

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    return ($resultado && $resultado['permitido']);
}
?>