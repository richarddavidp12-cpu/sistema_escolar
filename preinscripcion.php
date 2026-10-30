<?php
$host = 'localhost';
$dbname = 'nombre_de_tu_base_de_datos'; // Reemplaza con el nombre real
$username = 'postgres';                 // Tu usuario de PostgreSQL
$password = 'tu_contraseña';            // Tu contraseña de PostgreSQL

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    // Configurar PDO para que lance excepciones en caso de errores
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>