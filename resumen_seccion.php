<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$grado_sel = $_GET['grado'] ?? '';
$seccion_sel = $_GET['seccion'] ?? '';
$lapso_sel = $_GET['lapso'] ?? '';

$estudiantes = [];
$asignaturas = [];
$calificaciones_map = [];

// Obtener listado de asignaturas para las columnas o listado general
try {
    $stmt_asig = $conn->query("SELECT id, nombre_asignatura FROM asignatura ORDER BY nombre_asignatura ASC");
    $asignaturas = $stmt_asig->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($grado_sel) && !empty($seccion_sel)) {
        // Obtener estudiantes del grado y sección seleccionados
        $stmt_est = $conn->prepare("SELECT id, cedula_escolar, nombres, apellidos FROM alumno WHERE grado = :grado AND seccion = :seccion ORDER BY apellidos ASC");
        $stmt_est->execute([':grado' => $grado_sel, ':seccion' => $seccion_sel]);
        $estudiantes = $stmt_est->fetchAll(PDO::FETCH_ASSOC);

        // Si se seleccionó lapso, traemos las calificaciones de esos estudiantes
        if (!empty($lapso_sel) && !empty($estudiantes)) {
            $est_ids = array_column($estudiantes, 'id');
            // Crear placeholders seguros para la consulta IN
            $placeholders = implode(',', array_fill(0, count($est_ids), '?'));
            
            $sql_notas = "SELECT alumno_id, asignatura_id, nota FROM calificacion WHERE lapso = ? AND alumno_id IN ($placeholders)";
            $params = array_merge([$lapso_sel], $est_ids);
            
            $stmt_notas = $conn->prepare($sql_notas);
            $stmt_notas->execute($params);
            $notas_raw = $stmt_notas->fetchAll(PDO::FETCH_ASSOC);

            // Organizar en un mapa [alumno_id][asignatura_id] = nota
            foreach ($notas_raw as $n) {
                $calificaciones_map[$n['alumno_id']][$n['asignatura_id']] = $n['nota'];
            }
        }
    }
} catch (PDOException $e) {
    $error = "Error al consultar la base de datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen por Sección - U.E.P. Arq. San Rafael</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 1100px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; }
        .nav-links { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { color: #007bff; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        h2, h3 { color: #2c3e50; margin-bottom: 15px; }
        .filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: bold; color: #555; font-size: 0.9rem; }
        .form-group select { padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.95rem; }
        .btn-filter { grid-column: span 1; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; align-self: flex-end; }
        .btn-filter:hover { background-color: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 0.85rem; }
        th { background-color: #007bff; color: white; text-align: center; }
        td:not(:nth-child(2)):not(:nth-child(3)) { text-align: center; }
        tr:hover { background-color: #f1f1f1; }
        .empty-msg { text-align: center; padding: 25px; color: #64748b; font-size: 0.95rem; }
        .btn-print { margin-top: 20px; padding: 10px 20px; background-color: #17a2b8; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; display: inline-block; text-decoration: none; }
        .btn-print:hover { background-color: #138496; }
        @media print {
            .nav-links, .filter-form, .btn-print { display: none; }
            body { background: white; }
            .container { box-shadow: none; margin: 0; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-links">
        <a href="dashboard.php">&larr; Volver al Panel de Control</a>
        <a href="ver_boleta.php">Ver Boleta Individual</a>
    </div>

    <h2>Resumen de Calificaciones por Grado y Sección</h2>

    <!-- Filtros de búsqueda -->
    <form method="GET" action="" class="filter-form">
        <div class="form-group">
            <label for="grado">Grado Escolar:</label>
            <select id="grado" name="grado" required>
                <option value="">Seleccione grado</option>
                <option value="1er Grado" <?= ($grado_sel === '1er Grado') ? 'selected' : '' ?>>1er Grado</option>
                <option value="2do Grado" <?= ($grado_sel === '2do Grado') ? 'selected' : '' ?>>2do Grado</option>
                <option value="3er Grado" <?= ($grado_sel === '3er Grado') ? 'selected' : '' ?>>3er Grado</option>
                <option value="4to Grado" <?= ($grado_sel === '4to Grado') ? 'selected' : '' ?>>4to Grado</option>
                <option value="5to Grado" <?= ($grado_sel === '5to Grado') ? 'selected' : '' ?>>5to Grado</option>
                <option value="6to Grado" <?= ($grado_sel === '6to Grado') ? 'selected' : '' ?>>6to Grado</option>
            </select>
        </div>

        <div class="form-group">
            <label for="seccion">Sección:</label>
            <select id="seccion" name="seccion" required>
                <option value="">Seleccione sección</option>
                <option value="A" <?= ($seccion_sel === 'A') ? 'selected' : '' ?>>Sección A</option>
                <option value="B" <?= ($seccion_sel === 'B') ? 'selected' : '' ?>>Sección B</option>
                <option value="C" <?= ($seccion_sel === 'C') ? 'selected' : '' ?>>Sección C</option>
                <option value="D" <?= ($seccion_sel === 'D') ? 'selected' : '' ?>>Sección D</option>
            </select>
        </div>

        <div class="form-group">
            <label for="lapso">Lapso Académico:</label>
            <select id="lapso" name="lapso" required>
                <option value="">Seleccione lapso</option>
                <option value="1er Lapso" <?= ($lapso_sel === '1er Lapso') ? 'selected' : '' ?>>1er Lapso</option>
                <option value="2do Lapso" <?= ($lapso_sel === '2do Lapso') ? 'selected' : '' ?>>2do Lapso</option>
                <option value="3er Lapso" <?= ($lapso_sel === '3er Lapso') ? 'selected' : '' ?>>3er Lapso</option>
            </select>
        </div>

        <button type="submit" class="btn-filter">Generar Resumen</button>
    </form>

    <?php if (!empty($grado_sel) && !empty($seccion_sel)): ?>
        <h3>Sección Seleccionada: <?= htmlspecialchars($grado_sel . " - '" . $seccion_sel . "'") ?> (<?= htmlspecialchars($lapso_sel ?: 'Todos los lapsos') ?>)</h3>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cédula Escolar</th>
                    <th>Apellidos y Nombres</th>
                    <?php foreach ($asignaturas as $asig): ?>
                        <th><?= htmlspecialchars($asig['nombre_asignatura']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($estudiantes)): ?>
                    <?php $i = 1; foreach ($estudiantes as $est): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($est['cedula_escolar']) ?></td>
                            <td><strong><?= htmlspecialchars($est['apellidos'] . ", " . $est['nombres']) ?></strong></td>
                            <?php foreach ($asignaturas as $asig): ?>
                                <td>
                                    <?php 
                                        $nota_literal = $calificaciones_map[$est['id']][$asig['id']] ?? '-';
                                        $color = ($nota_literal !== '-') ? '#007bff' : '#cbd5e1';
                                        echo "<strong style='color: {$color}; font-size: 1rem;'>" . htmlspecialchars($nota_literal) . "</strong>";
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= 3 + count($asignaturas) ?>" class="empty-msg">No se encontraron estudiantes registrados en este grado y sección.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($estudiantes)): ?>
            <button onclick="window.print();" class="btn-print">Imprimir / Guardar Resumen de Sección</button>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-msg">Seleccione un grado, una sección y un lapso académico superior para generar el cuadro consolidado.</div>
    <?php endif; ?>
</div>

</body>
</html>