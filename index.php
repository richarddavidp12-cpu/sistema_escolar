<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensaje = "";
$tipo_alerta = "";

// Procesar el registro al enviar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula_escolar'] ?? '');
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $grado = $_POST['grado'] ?? '';
    $seccion = $_POST['seccion'] ?? '';

    // Validacion 1: Campos obligatorios
    if (empty($cedula) || empty($nombres) || empty($apellidos) || empty($grado) || empty($seccion)) {
        $mensaje = "Por favor, complete todos los campos obligatorios del formulario.";
        $tipo_alerta = "danger";
    } else {
        try {
            // Validacion 2: Control de cédula escolar duplicada
            $stmt_check = $conn->prepare("SELECT id FROM alumno WHERE cedula_escolar = :cedula");
            $stmt_check->execute([':cedula' => $cedula]);

            if ($stmt_check->rowCount() > 0) {
                $mensaje = "Error: Ya existe un estudiante registrado con la cédula escolar " . htmlspecialchars($cedula) . ".";
                $tipo_alerta = "danger";
            } else {
                // Inserción segura con sentencia preparada
                $stmt = $conn->prepare("INSERT INTO alumno (cedula_escolar, nombres, apellidos, grado, seccion) VALUES (:cedula, :nombres, :apellidos, :grado, :seccion)");
                $stmt->execute([
                    ':cedula' => $cedula,
                    ':nombres' => $nombres,
                    ':apellidos' => $apellidos,
                    ':grado' => $grado,
                    ':seccion' => $seccion
                ]);

                $mensaje = "¡Estudiante registrado exitosamente en la institución!";
                $tipo_alerta = "success";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al procesar el registro en la base de datos: " . $e->getMessage();
            $tipo_alerta = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Estudiante - U.E.P. Arq. San Rafael</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 650px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; }
        .nav-links { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { color: #007bff; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        h2 { color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .form-group { margin-bottom: 18px; display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 6px; font-weight: bold; color: #475569; font-size: 0.9rem; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; }
        .form-group input:focus, .form-group select:focus { border-color: #007bff; outline: none; }
        .btn-submit { padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 6px; font-weight: bold; font-size: 1rem; cursor: pointer; width: 100%; margin-top: 10px; transition: background 0.3s; }
        .btn-submit:hover { background-color: #0056b3; }
        .alert { padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; font-size: 0.9rem; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-links">
        <a href="dashboard.php">&larr; Volver al Panel de Control</a>
        <a href="listado_estudiantes.php">Ver Listado General &rarr;</a>
    </div>

    <h2>Registro de Estudiante (Educación Primaria)</h2>

    <!-- Mensajes de alerta dinámicos -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_alerta ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="cedula_escolar">Cédula Escolar / Identificación:</label>
            <input type="text" id="cedula_escolar" name="cedula_escolar" placeholder="Ej: 10255445" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="nombres">Nombres del Estudiante:</label>
            <input type="text" id="nombres" name="nombres" placeholder="Ej: Juan Carlos" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="apellidos">Apellidos del Estudiante:</label>
            <input type="text" id="apellidos" name="apellidos" placeholder="Ej: Pérez Gómez" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="grado">Grado Escolar:</label>
            <select id="grado" name="grado" required>
                <option value="">Seleccione el grado</option>
                <option value="1er Grado">1er Grado</option>
                <option value="2do Grado">2do Grado</option>
                <option value="3er Grado">3er Grado</option>
                <option value="4to Grado">4to Grado</option>
                <option value="5to Grado">5to Grado</option>
                <option value="6to Grado">6to Grado</option>
            </select>
        </div>

        <div class="form-group">
            <label for="seccion">Sección:</label>
            <select id="seccion" name="seccion" required>
                <option value="">Seleccione la sección</option>
                <option value="A">Sección A</option>
                <option value="B">Sección B</option>
                <option value="C">Sección C</option>
                <option value="D">Sección D</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">Guardar Registro de Estudiante</button>
    </form>
</div>

</body>
</html>