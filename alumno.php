<?php
// Incluir archivos de autenticación y conexión
include_once 'auth.php';
include_once 'conexion.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Datos del Alumno
    $cedula_alumno = trim($_POST['cedula'] ?? '');
    $nombres_alumno = trim($_POST['nombres'] ?? '');
    $apellidos_alumno = trim($_POST['apellidos'] ?? '');
    $id_grado = $_POST['id_grado'] ?? null;
    $id_seccion = $_POST['id_seccion'] ?? null;

    // 2. Datos del Representante
    $cedula_rep = trim($_POST['cedula_representante'] ?? '');
    $nombres_rep = trim($_POST['nombres_representante'] ?? '');
    $apellidos_rep = trim($_POST['apellidos_representante'] ?? '');
    $telefono_rep = trim($_POST['telefono'] ?? '');
    $direccion_rep = trim($_POST['direccion'] ?? '');

    if (!empty($cedula_alumno) && !empty($cedula_rep)) {
        try {
            $conn->beginTransaction();

            // A. Insertar o actualizar Representante
            $stmtRep = $conn->prepare("INSERT INTO representante (cedula, nombres, apellidos, telefono, direccion) 
                VALUES (:cedula, :nombres, :apellidos, :telefono, :direccion)
                ON CONFLICT (cedula) DO UPDATE SET 
                    nombres = EXCLUDED.nombres,
                    apellidos = EXCLUDED.apellidos,
                    telefono = EXCLUDED.telefono,
                    direccion = EXCLUDED.direccion
                RETURNING id");
            
            $stmtRep->execute([
                ':cedula' => $cedula_rep,
                ':nombres' => $nombres_rep,
                ':apellidos' => $apellidos_rep,
                ':telefono' => $telefono_rep,
                ':direccion' => $direccion_rep
            ]);
            
            $id_representante = $stmtRep->fetchColumn();

            // B. Insertar Alumno vinculado al Representante
            $stmtAlumno = $conn->prepare("INSERT INTO alumno (cedula_escolar, nombres, apellidos, grado, seccion, id_representante) 
                VALUES (:cedula, :nombres, :apellidos, :grado, :seccion, :id_rep)");
            
            $stmtAlumno->execute([
                ':cedula' => $cedula_alumno,
                ':nombres' => $nombres_alumno,
                ':apellidos' => $apellidos_alumno,
                ':grado' => $id_grado,
                ':seccion' => $id_seccion,
                ':id_rep' => $id_representante
            ]);

            $conn->commit();
            $mensaje = "¡Estudiante y Representante registrados exitosamente!";

        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Error al realizar el registro: " . $e->getMessage();
        }
    } else {
        $error = "Por favor completa todos los campos requeridos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Estudiante y Representante - U.E.P. Arq. San Rafael</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <a href="dashboard.php" class="btn btn-secondary btn-sm">&larr; Volver al Panel de Control</a>
                        <a href="listado_estudiantes.php" class="text-decoration-none">Ver Listado General &rarr;</a>
                    </div>
                    
                    <h2 class="mb-4 text-primary">Registro de Estudiante y Representante</h2>

                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-success"><?php echo $mensaje; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="alumno.php" method="POST">
                        <!-- DATOS DEL ESTUDIANTE -->
                        <h5 class="text-secondary mb-3">Datos del Estudiante</h5>
                        <div class="mb-3">
                            <label for="cedula" class="form-label">Cédula Escolar / Identificación:</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Ej: 10255445" required>
                        </div>

                        <div class="mb-3">
                            <label for="nombres" class="form-label">Nombres del Estudiante:</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Ej: Juan Carlos" required>
                        </div>

                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos del Estudiante:</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Ej: Pérez Gómez" required>
                        </div>

                        <div class="mb-3">
                            <label for="id_grado" class="form-label">Grado Escolar:</label>
                            <select class="form-select" id="id_grado" name="id_grado" required>
                                <option value="">Seleccione el grado</option>
                                <option value="1">1er Grado</option>
                                <option value="2">2do Grado</option>
                                <option value="3">3er Grado</option>
                                <option value="4">4to Grado</option>
                                <option value="5">5to Grado</option>
                                <option value="6">6to Grado</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="id_seccion" class="form-label">Sección:</label>
                            <select class="form-select" id="id_seccion" name="id_seccion" required>
                                <option value="">Seleccione la sección</option>
                                <option value="1">Sección A</option>
                                <option value="2">Sección B</option>
                                <option value="3">Sección C</option>
                            </select>
                        </div>

                        <!-- DATOS DEL REPRESENTANTE -->
                        <hr class="my-4">
                        <h5 class="mb-3 text-primary">Datos del Representante</h5>

                        <div class="mb-3">
                            <label for="cedula_representante" class="form-label">Cédula del Representante:</label>
                            <input type="text" class="form-control" id="cedula_representante" name="cedula_representante" placeholder="Ej: V-12345678" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombres_representante" class="form-label">Nombres del Representante:</label>
                                <input type="text" class="form-control" id="nombres_representante" name="nombres_representante" placeholder="Ej: María Elena" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellidos_representante" class="form-label">Apellidos del Representante:</label>
                                <input type="text" class="form-control" id="apellidos_representante" name="apellidos_representante" placeholder="Ej: Pérez Gómez" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono de Contacto:</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Ej: 0414-1234567" required>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección de Habitación:</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="3" placeholder="Ej: Sector Los Robles..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mt-3">Guardar Registro Completo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>