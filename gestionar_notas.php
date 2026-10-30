<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensaje = "";
$tipo_alerta = "";

// Cargar listas para desplegables
try {
    $stmt_alumnos = $conn->query("SELECT id, cedula_escolar, nombres, apellidos, grado, seccion FROM alumno ORDER BY apellidos ASC");
    $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_asig = $conn->query("SELECT id, nombre_asignatura FROM asignatura ORDER BY nombre_asignatura ASC");
    $asignaturas = $stmt_asig->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $alumnos = [];
    $asignaturas = [];
}

// Procesar el guardado o actualización de notas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alumno_id = $_POST['alumno_id'] ?? '';
    $asignatura_id = $_POST['asignatura_id'] ?? '';
    $lapso = $_POST['lapso'] ?? '';
    $nota = $_POST['nota'] ?? '';

    // Validacion 1: Verificar que no haya campos vacíos
    if (empty($alumno_id) || empty($asignatura_id) || empty($lapso) || empty($nota)) {
        $mensaje = "Por favor, seleccione todos los datos requeridos para registrar la nota.";
        $tipo_alerta = "danger";
    } else {
        try {
            // Validacion 2: Verificar si la nota de esa materia y lapso ya existe para el alumno
            $stmt_check = $conn->prepare("SELECT id FROM calificacion WHERE alumno_id = :alumno_id AND asignatura_id = :asignatura_id AND lapso = :lapso");
            $stmt_check->execute([
                ':alumno_id' => $alumno_id,
                ':asignatura_id' => $asignatura_id,
                ':lapso' => $lapso
            ]);

            if ($stmt_check->rowCount() > 0) {
                // Si ya existe, la actualizamos automáticamente para no crear duplicados
                $stmt_update = $conn->prepare("UPDATE calificacion SET nota = :nota WHERE alumno_id = :alumno_id AND asignatura_id = :asignatura_id AND lapso = :lapso");
                $stmt_update->execute([
                    ':nota' => $nota,
                    ':alumno_id' => $alumno_id,
                    ':asignatura_id' => $asignatura_id,
                    ':lapso' => $lapso
                ]);

                $mensaje = "Calificación actualizada exitosamente (Se modificó el registro previo).";
                $tipo_alerta = "success";
            } else {
                // Si no existe, creamos el nuevo registro
                $stmt_insert = $conn->prepare("INSERT INTO calificacion (alumno_id, asignatura_id, lapso, nota) VALUES (:alumno_id, :asignatura_id, :lapso, :nota)");
                $stmt_insert->execute([
                    ':alumno_id' => $alumno_id,
                    ':asignatura_id' => $asignatura_id,
                    ':lapso' => $lapso,
                    ':nota' => $nota
                ]);

                $mensaje = "¡Calificación registrada correctamente!";
                $tipo_alerta = "success";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al guardar la calificación: " . $e->getMessage();
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
    <title>Gestión de Notas - U.E.P. Arq. San Rafael</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 750px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; }
        .nav-links { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { color: #007bff; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        h2 { color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .form-group { margin-bottom: 18px; display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 6px; font-weight: bold; color: #475569; font-size: 0.9rem; }
        .form-group select { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; }
        .form-group select:focus { border-color: #28a745; outline: none; }
        .btn-submit { padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 6px; font-weight: bold; font-size: 1rem; cursor: pointer; width: 100%; margin-top: 10px; transition: background 0.3s; }
        .btn-submit:hover { background-color: #218838; }
        .alert { padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; font-size: 0.9rem; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-links">
        <a href="dashboard.php">&larr; Volver al Panel de Control</a>
        <a href="ver_boleta.php">Ver Boletas &rarr;</a>
    </div>

    <h2>Asignación de Calificaciones Conceptuales</h2>

    <!-- Mensajes de alerta dinámicos -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_alerta ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="alumno_id">Seleccionar Estudiante:</label>
            <select id="alumno_id" name="alumno_id" required>
                <option value="">-- Seleccione un estudiante --</option>
                <?php foreach ($alumnos as $al): ?>
                    <option value="<?= $al['id'] ?>">
                        <?= htmlspecialchars($al['apellidos'] . ", " . $al['nombres'] . " (" . $al['grado'] . " '" . $al['seccion'] . "') - Cédula: " . $al['cedula_escolar']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="asignatura_id">Asignatura / Materia:</label>
            <select id="asignatura_id" name="asignatura_id" required>
                <option value="">-- Seleccione una asignatura --</option>
                <?php foreach ($asignaturas as $asig): ?>
                    <option value="<?= $asig['id'] ?>">
                        <?= htmlspecialchars($asig['nombre_asignatura']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="lapso">Lapso Académico:</label>
            <select id="lapso" name="lapso" required>
                <option value="">-- Seleccione el lapso --</option>
                <option value="1er Lapso">1er Lapso</option>
                <option value="2do Lapso">2do Lapso</option>
                <option value="3er Lapso">3er Lapso</option>
            </select>
        </div>

        <div class="form-group">
            <label for="nota">Literal Conceptual (Evaluación Primaria):</label>
            <select id="nota" name="nota" required>
                <option value="">-- Seleccione el literal --</option>
                <option value="A">A - Consolidó totalmente las competencias</option>
                <option value="B">B - Consolidó la mayoría de las competencias</option>
                <option value="C">C - Alcanzó la mayoría de las competencias previstas</option>
                <option value="D">D - Alcanzó algunas de las competencias</option>
                <option value="E">E - No logró alcanzar las competencias</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">Guardar Calificación</button>
    </form>
</div>

</body>
</html>