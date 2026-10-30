<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$alumno_id = $_GET['id'] ?? '';
$estudiante = null;
$calificaciones = [];

if (!empty($alumno_id)) {
    try {
        // Obtener datos del estudiante
        $stmt_est = $conn->prepare("SELECT * FROM alumno WHERE id = :id");
        $stmt_est->execute([':id' => $alumno_id]);
        $estudiante = $stmt_est->fetch(PDO::FETCH_ASSOC);

        // Obtener sus calificaciones con las asignaturas
        $stmt_notas = $conn->prepare("
            SELECT asig.nombre_asignatura, c.nota, c.lapso 
            FROM calificacion c
            JOIN asignatura asig ON c.asignatura_id = asig.id
            WHERE c.alumno_id = :id
            ORDER BY c.lapso ASC, asig.nombre_asignatura ASC
        ");
        $stmt_notas->execute([':id' => $alumno_id]);
        $calificaciones = $stmt_notas->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error = "Error al generar la boleta: " . $e->getMessage();
    }
}

// Obtener lista para el selector si no se ha elegido ninguno
$stmt_todos = $conn->query("SELECT id, nombres, apellidos, cedula_escolar, grado, seccion FROM alumno ORDER BY apellidos ASC");
$lista_alumnos = $stmt_todos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta Informativa - U.E.P. Arq. San Rafael</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 900px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; }
        .nav-links { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { color: #007bff; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        h2, h3 { color: #2c3e50; margin-bottom: 15px; }
        .select-box { display: flex; gap: 10px; margin-bottom: 25px; }
        .select-box select { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.95rem; }
        .btn-view { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .btn-view:hover { background-color: #0056b3; }
        .student-info { background: #f8fafc; border-left: 5px solid #007bff; padding: 15px; border-radius: 5px; margin-bottom: 25px; }
        .student-info p { margin-bottom: 5px; font-size: 0.95rem; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 0.9rem; }
        th { background-color: #007bff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .empty-msg { text-align: center; padding: 20px; color: #64748b; }
        .btn-print { margin-top: 20px; padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; display: inline-block; text-decoration: none; }
        .btn-print:hover { background-color: #218838; }
        @media print {
            .nav-links, .select-box, .btn-print, .btn-view { display: none; }
            body { background: white; }
            .container { box-shadow: none; margin: 0; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-links">
        <a href="dashboard.php">&larr; Volver al Panel de Control</a>
        <a href="gestionar_notas.php">Ir a Gestión de Notas</a>
    </div>

    <h2>Boleta de Calificaciones (Educación Primaria)</h2>

    <!-- Selector de estudiante -->
    <form method="GET" action="" class="select-box">
        <select name="id" required>
            <option value="">Seleccione un estudiante para ver su boleta...</option>
            <?php foreach ($lista_alumnos as $al): ?>
                <option value="<?= $al['id'] ?>" <?= ($alumno_id == $al['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($al['apellidos'] . ", " . $al['nombres'] . " (" . ($al['grado'] ?? 'S/G') . " " . ($al['seccion'] ?? '') . ")") ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-view">Consultar</button>
    </form>

    <?php if ($estudiante): ?>
        <div class="student-info">
            <h3>Datos del Estudiante</h3>
            <p><strong>Apellidos y Nombres:</strong> <?= htmlspecialchars($estudiante['apellidos'] . ", " . $estudiante['nombres']) ?></p>
            <p><strong>Cédula Escolar:</strong> <?= htmlspecialchars($estudiante['cedula_escolar']) ?></p>
            <p><strong>Grado y Sección:</strong> <?= htmlspecialchars(($estudiante['grado'] ?? 'No asignado') . " - '" . ($estudiante['seccion'] ?? '-') . "'") ?></p>
        </div>

        <h3>Rendimiento Académico por Asignatura</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Asignatura</th>
                    <th>Lapso Académico</th>
                    <th>Literal Obtenido</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($calificaciones)): ?>
                    <?php $i = 1; foreach ($calificaciones as $cal): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($cal['nombre_asignatura']) ?></td>
                            <td><?= htmlspecialchars($cal['lapso']) ?></td>
                            <td><strong style="color: #007bff; font-size: 1.1rem;"><?= htmlspecialchars($cal['nota']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-msg">Este estudiante no posee calificaciones registradas todavía.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <button onclick="window.print();" class="btn-print">Imprimir / Guardar Boleta</button>
    <?php endif; ?>
</div>

</body>
</html>