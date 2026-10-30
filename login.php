<?php
require_once 'conexion.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($cedula) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT id, nombres, apellidos, password_hash, activo FROM usuario WHERE cedula = :cedula LIMIT 1");
            $stmt->execute([':cedula' => $cedula]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['activo']) {
                if (password_verify($password, $user['password_hash']) || $password === $user['password_hash']) {
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['nombre_usuario'] = $user['nombres'] . ' ' . $user['apellidos'];
                    
                    // Redirección corregida al Panel de Control principal
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            } else {
                $error = 'El usuario no existe o se encuentra inactivo.';
            }
        } catch (PDOException $e) {
            $error = 'Error en el servidor: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor, complete todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>U.E.P. Arq. San Rafael - Control de Acceso</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-header h2 {
            color: #2c3e50;
            font-size: 1.5rem;
        }
        .login-header p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
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
            background-color: #3498db;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background-color: #2980b9;
        }
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            border: 1px solid #f5c6cb;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h2>U.E.P. Arq. San Rafael</h2>
        <p>Control de Acceso</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-msg">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="cedula">Usuario (Cédula):</label>
            <input type="text" id="cedula" name="cedula" placeholder="Ingrese su cédula" required autocomplete="off">
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-submit">Iniciar Sesión</button>
    </form>
</div>

</body>
</html>