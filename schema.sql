-- ==========================================
-- 1. MÓDULO DE SEGURIDAD Y CONTROL DE USUARIOS
-- ==========================================

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT
);

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    rol_id INT REFERENCES roles(id),
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE permisos (
    id SERIAL PRIMARY KEY,
    nombre_permiso VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT
);

CREATE TABLE bitacora_auditoria (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios(id),
    accion VARCHAR(100) NOT NULL,
    detalles TEXT,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ==========================================
-- 2. MÓDULO DE GESTIÓN INSTITUCIONAL
-- ==========================================

CREATE TABLE periodos_escolares (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE grados (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE secciones (
    id SERIAL PRIMARY KEY,
    grado_id INT REFERENCES grados(id),
    nombre VARCHAR(10) NOT NULL
);

CREATE TABLE turnos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);


-- ==========================================
-- 3. MÓDULO DE ACTORES Y PERFILES
-- ==========================================

CREATE TABLE personas (
    id SERIAL PRIMARY KEY,
    cedula VARCHAR(20) UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    genero VARCHAR(10),
    telefono VARCHAR(20),
    direccion TEXT
);

CREATE TABLE estudiantes (
    id SERIAL PRIMARY KEY,
    persona_id INT REFERENCES personas(id) UNIQUE,
    codigo_escolar VARCHAR(50) UNIQUE
);

CREATE TABLE representantes (
    id SERIAL PRIMARY KEY,
    persona_id INT REFERENCES personas(id) UNIQUE,
    parentesco VARCHAR(50)
);

CREATE TABLE estudiante_representante (
    id SERIAL PRIMARY KEY,
    estudiante_id INT REFERENCES estudiantes(id),
    representante_id INT REFERENCES representantes(id),
    es_principal BOOLEAN DEFAULT TRUE
);

CREATE TABLE docentes (
    id SERIAL PRIMARY KEY,
    persona_id INT REFERENCES personas(id) UNIQUE,
    especialidad VARCHAR(100)
);

CREATE TABLE personal_administrativo (
    id SERIAL PRIMARY KEY,
    persona_id INT REFERENCES personas(id) UNIQUE,
    cargo VARCHAR(100)
);

CREATE TABLE personal_obrero (
    id SERIAL PRIMARY KEY,
    persona_id INT REFERENCES personas(id) UNIQUE,
    funcion VARCHAR(100)
);


-- ==========================================
-- 4. MÓDULO DE EXPEDIENTE INTEGRAL Y SOCIO-SANITARIO
-- ==========================================

CREATE TABLE fichas_medicas (
    id SERIAL PRIMARY KEY,
    estudiante_id INT REFERENCES estudiantes(id) UNIQUE,
    tipo_sangre VARCHAR(5),
    alergias TEXT,
    condiciones_especiales TEXT
);

CREATE TABLE antecedentes_vacunacion (
    id SERIAL PRIMARY KEY,
    estudiante_id INT REFERENCES estudiantes(id),
    nombre_vacuna VARCHAR(100),
    dosis_aplicada VARCHAR(50),
    fecha_aplicacion DATE
);

CREATE TABLE datos_socioeconomicos (
    id SERIAL PRIMARY KEY,
    estudiante_id INT REFERENCES estudiantes(id) UNIQUE,
    tipo_vivienda VARCHAR(50),
    ingreso_familiar_estimado NUMERIC(10,2),
    beneficiario_programa_alimentacion BOOLEAN DEFAULT TRUE
);

CREATE TABLE control_antropometrico (
    id SERIAL PRIMARY KEY,
    estudiante_id INT REFERENCES estudiantes(id),
    peso_kg NUMERIC(5,2),
    talla_cm NUMERIC(5,2),
    fecha_medicion DATE
);

CREATE TABLE observaciones_formativas (
    id SERIAL PRIMARY KEY,
    estudiante_id INT REFERENCES estudiantes(id),
    docente_id INT REFERENCES docentes(id),
    observacion TEXT,
    fecha DATE
);


-- ==========================================
-- 5. MÓDULO ACADÉMICO Y MATRICULACIÓN
-- ==========================================

CREATE TABLE materias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);

CREATE TABLE planes_estudio (
    id SERIAL PRIMARY KEY,
    grado_id INT REFERENCES grados(id),
    materia_id INT REFERENCES materias(id)
);

CREATE TABLE docente_materia_seccion (
    id SERIAL PRIMARY KEY,
    docente_id INT REFERENCES docentes(id),
    materia_id INT REFERENCES materias(id),
    seccion_id INT REFERENCES secciones(id),
    periodo_id INT REFERENCES periodos_escolares(id)
);

CREATE TABLE inscripciones (
    id SERIAL PRIMARY KEY,
    estudiante_id INT REFERENCES estudiantes(id),
    seccion_id INT REFERENCES secciones(id),
    periodo_id INT REFERENCES periodos_escolares(id),
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ==========================================
-- 6. MÓDULO ADMINISTRATIVO Y DE PAGOS
-- ==========================================

CREATE TABLE conceptos_pago (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    monto_base NUMERIC(10,2)
);

CREATE TABLE cuotas_cobro (
    id SERIAL PRIMARY KEY,
    concepto_id INT REFERENCES conceptos_pago(id),
    periodo_id INT REFERENCES periodos_escolares(id),
    mes_correspondiente VARCHAR(20),
    monto NUMERIC(10,2)
);

CREATE TABLE pagos (
    id SERIAL PRIMARY KEY,
    estudiante_id INT REFERENCES estudiantes(id),
    cuota_id INT REFERENCES cuotas_cobro(id),
    referencia_bancaria VARCHAR(100),
    monto_pagado NUMERIC(10,2),
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE solicitudes_documentos (
    id SERIAL PRIMARY KEY,
    estudiante_id INT REFERENCES estudiantes(id),
    tipo_documento VARCHAR(100),
    estado VARCHAR(50) DEFAULT 'Pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ==========================================
-- 7. MÓDULO DE INFRAESTRUCTURA
-- ==========================================

CREATE TABLE aulas (
    id SERIAL PRIMARY KEY,
    codigo_aula VARCHAR(20) UNIQUE,
    capacidad INT,
    ubicacion VARCHAR(100)
);

CREATE TABLE inventario_recursos (
    id SERIAL PRIMARY KEY,
    aula_id INT REFERENCES aulas(id),
    nombre_recurso VARCHAR(100),
    cantidad INT,
    estado VARCHAR(50)
);