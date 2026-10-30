<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    try {
        $stmt_del = $conn->prepare("DELETE FROM alumno WHERE id = :id");
        $stmt_del->execute([':id' => $id_eliminar]);
        header('Location: listado_estudiantes.php?msg=eliminado');
        exit;
    } catch (PDOException $e) {
        $error = "Error al eliminar: " . $e->getMessage();
    }
}

$busqueda = trim($_GET['busqueda'] ?? '');

try {
    if (!empty($busqueda)) {
        $stmt = $conn->prepare("SELECT id, cedula_escolar, nombres, apellidos, grado, seccion FROM alumno 
                                WHERE cedula_escolar ILIKE :busq 
                                   OR nombres ILIKE :busq 
                                   OR apellidos ILIKE :busq 
                                   OR grado ILIKE :busq 
                                   OR seccion ILIKE :busq 
                                ORDER BY id DESC");
        $stmt->execute([':busq' => "%$busqueda%"]);
    } else {
        $stmt = $conn->query("SELECT id, cedula_escolar, nombres, apellidos, grado, seccion FROM alumno ORDER BY id DESC");
    }
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al consultar: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Estudiantes - U.E.P. Arq. San Rafael</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 1050px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; }
        .nav-links { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { color: #007bff; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        .btn-logout { background-color: #dc3545; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.85rem; }
        h2 { color: #2c3e50; margin-bottom: 20px; }
        .search-form { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-form input { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.95rem; }
        .btn-search { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .btn-reset { padding: 10px 15px; background-color: #6c757d; color: white; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.95rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; font-size: 0.95rem; }
        tr:hover { background-color: #f1f1f1; }
        .btn-accion { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: bold; color: white; margin-right: 5px; }
        .btn-editar { background-color: #ffc107; color: #212529; }
        .btn-eliminar { background-color: #dc3545; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .empty-msg { text-align: center; padding: 20px; color: #64748b; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-links">
        <a href="dashboard.php">&larr; Volver al Panel de Control</a>
        <a href="dashboard.php?logout=true" class="btn-logout">Cerrar Sesión</a>
    </div>

    <h2>Listado y Consulta de Estudiantes</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
        <div class="alert-success">Estudiante eliminado correctamente.</div>
    <?php endif; ?>

    <form method="GET" action="" class="search-form">
        <input type="text" name="busqueda" placeholder="Buscar por cédula, nombre, grado o sección..." value="<?= htmlspecialchars($busqueda) ?>" autocomplete="off">
        <button type="submit" class="btn-search">Buscar</button>
        <?php if (!empty($busqueda)): ?>
            <a href="listado_estudiantes.php" class="btn-reset">Limpiar</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cédula Escolar</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Grado</th>
                <th>Sección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($estudiantes)): ?>
                <?php $contador = 1; ?>
                <?php foreach ($estudiantes as $est): ?>
                    <tr>
                        <td><?= $contador++ ?></td>
                        <td><?= htmlspecialchars($est['cedula_escolar']) ?></td>
                        <td><?= htmlspecialchars($est['nombres']) ?></td>
                        <td><?= htmlspecialchars($est['apellidos']) ?></td>
                        <td><strong><?= htmlspecialchars($est['grado'] ?? 'N/A') ?></strong></td>
                        <td><strong><?= htmlspecialchars($est['seccion'] ?? 'N/A') ?></strong></td>
                        <td>
                            <a href="editar_estudiante.php?id=<?= $est['id'] ?>" class="btn-accion btn-editar">Editar</a>
                            <a href="listado_estudiantes.php?eliminar=<?= $est['id'] ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Deseas eliminar este registro?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty-msg">No se encontraron estudiantes.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>