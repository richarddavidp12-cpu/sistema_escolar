<?php
// Incluir archivos de autenticación y conexión
include_once 'auth.php';
include_once 'conexion.php';

// Consultar la lista de representantes y contar cuántos alumnos tiene cada uno
try {
    $stmt = $conn->query("SELECT r.*, COUNT(a.id) as total_alumnos 
                          FROM representante r 
                          LEFT JOIN alumno a ON r.id = a.id_representante 
                          GROUP BY r.id 
                          ORDER BY r.id DESC");
    $representantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $representantes = [];
    $error = "Error al cargar los datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Representantes - U.E.P. Arq. San Rafael</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="dashboard.php" class="btn btn-secondary">&larr; Volver al Panel de Control</a>
            <h2 class="text-primary m-0">Listado de Representantes</h2>
            <a href="alumno.php" class="btn btn-primary">+ Registrar Nuevo</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Cédula</th>
                                <th>Nombres y Apellidos</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th class="text-center">Alumnos Asociados</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($representantes) > 0): ?>
                                <?php foreach ($representantes as $index => $rep): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($rep['cedula']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($rep['nombres'] . ' ' . $rep['apellidos']); ?></td>
                                        <td><?php echo htmlspecialchars($rep['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($rep['direccion']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark"><?php echo $rep['total_alumnos']; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay representantes registrados todavía.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>