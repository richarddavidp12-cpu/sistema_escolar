<?php
$host = 'localhost';$dbname = 'san_rafael';
$username = 'postgres';$password = 'ZEUS';

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Se elimina el echo para no interferir con la maquetación HTML/CSS
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>