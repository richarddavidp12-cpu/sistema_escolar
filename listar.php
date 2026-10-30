<?php
require_once 'conexion.php';
require_once 'auth.php';

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$ruta_actual = '/listar.php'; // Ruta registrada en la tabla 'vistas'
$permiso_requerido = 'LEER';   // Permiso registrado en la tabla 'permisos'

// Validar con la función RBAC
if (!verificarPermisoRBAC($conn, $usuario_id, $ruta_actual, $permiso_requerido)) {
    // Si no tiene permiso, bloquear acceso (Cumple con la prueba TEST-03 y TEST-04)
    http_response_code(403);
    echo "<h1>Error 403: Acceso Denegado</h1>";
    echo "<p>No tienes los permisos necesarios para acceder a esta vista.</p>";
    exit();
}

// Si tiene acceso, continúa cargando la vista normalmente...
?>