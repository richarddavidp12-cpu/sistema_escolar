<?php
// Incluir archivos de autenticación y conexión
include_once 'auth.php';
include_once 'conexion.php';

// Consultar datos reales y dinámicos desde la base de datos
try {
    // 1. Total de estudiantes registrados en la base de datos
    $stmtTotal = $conn->query("SELECT COUNT(*) FROM alumno");
    $totalEstudiantes = $stmtTotal->fetchColumn();
    
    // 2. Total de representantes registrados en la base de datos
    $stmtRep = $conn->query("SELECT COUNT(*) FROM representante");
    $totalRepresentantes = $stmtRep->fetchColumn();

    // 3. Total de secciones oficiales institucionales (Ejemplo: 1ro a 6to Grado, Secciones A a D = 24)
    // Puedes modificar este número según la planificación oficial de la institución
    $totalSecciones = 24; 

} catch (PDOException $e) {
    $totalEstudiantes = 0;
    $totalRepresentantes = 0;
    $totalSecciones = 24;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - U.E.P. Arq. San Rafael</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        /* Sidebar moderno */
        #sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #212529 0%, #343a40 100%);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        #sidebar .nav-link {
            color: #adb5bd;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        #sidebar .nav-link:hover, #sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }
        /* Tarjetas de estadísticas con efecto hover */
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        /* Tarjetas de módulos */
        .module-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #fff;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0,0,0,.1)!important;
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Menú Lateral Moderno -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block p-3 collapse d-md-block">
                <div class="position-sticky pt-2">
                    <div class="text-center mb-4">
                        <i class="bi bi-mortarboard-fill text-primary fs-1"></i>
                        <h5 class="text-light mt-2 fw-bold">U.E.P. Arq. San Rafael</h5>
                        <small class="text-muted">Sistema Escolar ZEUS</small>
                    </div>
                    <hr class="text-secondary">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link active">
                                <i class="bi bi-house-door-fill me-2"></i> Panel Principal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="alumno.php" class="nav-link">
                                <i class="bi bi-person-plus-fill me-2"></i> Registrar Alumno
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="listado_estudiantes.php" class="nav-link">
                                <i class="bi bi-list-ul me-2"></i> Listado General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="listado_representantes.php" class="nav-link">
                                <i class="bi bi-people-fill me-2"></i> Listado Representantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="gestionar_notas.php" class="nav-link">
                                <i class="bi bi-journal-check me-2"></i> Gestión de Notas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ver_boleta.php" class="nav-link">
                                <i class="bi bi-file-earmark-text me-2"></i> Boleta Individual
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="resumen_seccion.php" class="nav-link">
                                <i class="bi bi-bar-chart-fill me-2"></i> Resumen por Sección
                            </a>
                        </li>
                    </ul>
                    <hr class="text-secondary mt-5">
                    <div class="px-2">
                        <a href="logout.php" class="btn btn-outline-danger w-100 btn-sm">
                            <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Contenido Principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Cabecera Superior -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
                    <div>
                        <h1 class="h3 fw-bold text-dark">Panel de Control Institucional</h1>
                        <p class="text-muted m-0">Bienvenido al núcleo de gestión escolar y expedientes.</p>
                    </div>
                    <div class="badge bg-primary p-2 px-3 fs-6 rounded-pill">
                        <i class="bi bi-person-circle me-1"></i> Administrador
                    </div>
                </div>

                <!-- Tarjetas de Estadísticas Principales -->
                <div class="row mb-5">
                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card stat-card shadow-sm border-start border-primary border-4 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">Estudiantes Activos</div>
                                        <div class="h3 mb-0 fw-bold text-dark"><?php echo $totalEstudiantes; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fs-1 text-primary opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card stat-card shadow-sm border-start border-success border-4 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs fw-bold text-success text-uppercase mb-1">Representantes Registrados</div>
                                        <div class="h3 mb-0 fw-bold text-dark"><?php echo $totalRepresentantes; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-person-badge fs-1 text-success opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card stat-card shadow-sm border-start border-info border-4 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs fw-bold text-info text-uppercase mb-1">Secciones Activas</div>
                                        <div class="h3 mb-0 fw-bold text-dark"><?php echo $totalSecciones; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-grid-3x3-gap fs-1 text-info opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulos del Sistema -->
                <h4 class="mb-4 fw-bold text-secondary"><i class="bi bi-grid-fill me-2"></i> Módulos de Operación</h4>
                
                <!-- Fila 1 de Módulos -->
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card module-card shadow-sm h-100 p-3">
                            <div class="card-body">
                                <div class="text-primary mb-3 fs-2"><i class="bi bi-person-plus-fill"></i></div>
                                <h5 class="card-title fw-bold text-dark">Registrar Estudiante</h5>
                                <p class="card-text text-muted small">Agrega nuevos expedientes escolares vinculando al representante de forma segura.</p>
                                <a href="alumno.php" class="btn btn-primary w-100 mt-3 fw-semibold">Ir al Formulario</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card module-card shadow-sm h-100 p-3">
                            <div class="card-body">
                                <div class="text-success mb-3 fs-2"><i class="bi bi-list-columns-reverse"></i></div>
                                <h5 class="card-title fw-bold text-dark">Listado General</h5>
                                <p class="card-text text-muted small">Visualiza, audita y consulta la tabla completa de alumnos matriculados.</p>
                                <a href="listado_estudiantes.php" class="btn btn-success w-100 mt-3 fw-semibold text-white">Ver Estudiantes</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card module-card shadow-sm h-100 p-3">
                            <div class="card-body">
                                <div class="text-info mb-3 fs-2"><i class="bi bi-people-fill"></i></div>
                                <h5 class="card-title fw-bold text-dark">Listado Representantes</h5>
                                <p class="card-text text-muted small">Consulta teléfonos, direcciones y los alumnos asociados a cada representante.</p>
                                <a href="listado_representantes.php" class="btn btn-info w-100 mt-3 fw-semibold text-white">Ver Representantes</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fila 2 de Módulos -->
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card module-card shadow-sm h-100 p-3">
                            <div class="card-body">
                                <div class="text-warning mb-3 fs-2"><i class="bi bi-journal-check"></i></div>
                                <h5 class="card-title fw-bold text-dark">Gestión de Notas</h5>
                                <p class="card-text text-muted small">Asigna calificaciones conceptuales y consulta el historial por lapso académico.</p>
                                <a href="gestionar_notas.php" class="btn btn-warning w-100 mt-3 fw-semibold text-dark">Gestionar Notas</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card module-card shadow-sm h-100 p-3">
                            <div class="card-body">
                                <div class="text-danger mb-3 fs-2"><i class="bi bi-file-earmark-text"></i></div>
                                <h5 class="card-title fw-bold text-dark">Boleta Individual</h5>
                                <p class="card-text text-muted small">Genera y consulta la boleta de calificaciones detallada por estudiante.</p>
                                <a href="ver_boleta.php" class="btn btn-danger w-100 mt-3 fw-semibold">Ver Boleta</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card module-card shadow-sm h-100 p-3">
                            <div class="card-body">
                                <div class="text-secondary mb-3 fs-2"><i class="bi bi-bar-chart-fill"></i></div>
                                <h5 class="card-title fw-bold text-dark">Resumen por Sección</h5>
                                <p class="card-text text-muted small">Revisa el consolidado estadístico y demográfico por grados y secciones.</p>
                                <a href="resumen_seccion.php" class="btn btn-secondary w-100 mt-3 fw-semibold">Ver Resumen</a>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>