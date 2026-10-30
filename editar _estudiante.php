<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$mensaje = '';

// Obtener el ID del estudiante a editar
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: listado_estudiantes.php');
    exit;
}

// Si se envía el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula_escolar = trim($_POST['cedula_escolar'] ?? '');
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');

    if (!empty($cedula_escolar) && !empty($nombres) && !empty($apellidos)) {
        try {
            $stmt = $conn->prepare("UPDATE alumno SET cedula_escolar = :cedula, nombres = :nombres, apellidos = :apellidos WHERE id = :id");
            $stmt->execute([
                ':cedula' => $cedula_escolar,
                ':nombres' => $nombres,
                ':apellidos' => $apellidos,
                ':id' => $id
            ]);
            $mensaje = "¡Datos del estudiante actualizados con éxito!";
        } catch (PDOException $e) {
            $error = "Error al actualizar: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, complete todos los campos.";
    }
}

// Consultar los datos actuales del estudiante
try {
    $stmt = $conn->prepare("SELECT * FROM alumno WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        header('Location: listado_estudiantes.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error en el servidor: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estudiante - U.E.P. Arq. San Rafael</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f4f6f9;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #ffc107;
            border: none;
            border-radius: 5px;
            color: #212529;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: #e0a800;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-links">
        <a href="listado_estudiantes.php">&larr; Volver al Listado</a>
    </div>

    <h2>Editar Datos del Estudiante</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="cedula_escolar">Cédula Escolar / Identidad:</label>
            <input type="text" id="cedula_escolar" name="cedula_escolar" value="<?= htmlspecialchars($estudiante['cedula_escolar']) ?>" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="nombres">Nombres:</label>
            <input type="text" id="nombres" name="nombres" value="<?= htmlspecialchars($estudiante['nombres']) ?>" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($estudiante['apellidos']) ?>" required autocomplete="off">
        </div>

        <button type="submit" class="btn-submit">Guardar Cambios</button>
    </form>
</div>

</body>
</html>