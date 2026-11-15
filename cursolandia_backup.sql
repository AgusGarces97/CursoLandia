-- =======================================================
-- BACKUP DE BASE DE DATOS: cursolandia_db
-- Proyecto: Cursolandia.com
--
-- Nota: este archivo NO incluye CREATE DATABASE, CREATE USER ni GRANT.
-- La base se crea en cada entorno (local o hosting) y luego se importa
-- este archivo seleccionandola previamente.
--
-- Requiere MySQL 8.0.16+ o MariaDB 10.2.1+ para que los CHECK se apliquen.
-- =======================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Tabla: usuarios
-- Reglas CHECK:
--  * nombre y apellido no pueden estar vacios
--  * email con formato basico (algo@algo.algo)
--  * edad entre 0 y 120 (o NULL)
--  * un usuario 'pro' debe tener fecha de vencimiento (la define el admin al activar el plan)
-- --------------------------------------------------------
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    dni VARCHAR(15),
    edad INT,
    antecedentes TEXT,
    imagen_perfil VARCHAR(255) DEFAULT 'default_avatar.png',
    rol ENUM('admin', 'regular') DEFAULT 'regular' NOT NULL,
    plan ENUM('gratis', 'pro') DEFAULT 'gratis' NOT NULL,
    vencimiento_pro DATE NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuarios_email UNIQUE (email),
    CONSTRAINT uq_usuarios_dni UNIQUE (dni),
    CONSTRAINT chk_usuarios_nombre CHECK (CHAR_LENGTH(TRIM(nombre)) > 0),
    CONSTRAINT chk_usuarios_apellido CHECK (CHAR_LENGTH(TRIM(apellido)) > 0),
    CONSTRAINT chk_usuarios_email CHECK (email LIKE '%_@_%._%'),
    CONSTRAINT chk_usuarios_edad CHECK (edad IS NULL OR edad BETWEEN 0 AND 120),
    CONSTRAINT chk_usuarios_pro_vencimiento CHECK (plan = 'gratis' OR vencimiento_pro IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: solicitudes_pro
-- Reglas CHECK:
--  * mensaje y comprobante no pueden estar vacios
-- --------------------------------------------------------
DROP TABLE IF EXISTS solicitudes_pro;
CREATE TABLE solicitudes_pro (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    archivo_comprobante VARCHAR(255) NOT NULL,
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente' NOT NULL,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitudes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT chk_solicitudes_mensaje CHECK (CHAR_LENGTH(TRIM(mensaje)) > 0),
    CONSTRAINT chk_solicitudes_comprobante CHECK (CHAR_LENGTH(TRIM(archivo_comprobante)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: etiquetas
-- Reglas CHECK:
--  * el nombre de la etiqueta no puede estar vacio
-- --------------------------------------------------------
DROP TABLE IF EXISTS etiquetas;
CREATE TABLE etiquetas (
    id_etiqueta INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    CONSTRAINT uq_etiquetas_nombre UNIQUE (nombre),
    CONSTRAINT chk_etiquetas_nombre CHECK (CHAR_LENGTH(TRIM(nombre)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: usuario_intereses (relacion N:M usuarios - etiquetas)
-- --------------------------------------------------------
DROP TABLE IF EXISTS usuario_intereses;
CREATE TABLE usuario_intereses (
    id_usuario INT NOT NULL,
    id_etiqueta INT NOT NULL,
    PRIMARY KEY (id_usuario, id_etiqueta),
    CONSTRAINT fk_intereses_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_intereses_etiqueta FOREIGN KEY (id_etiqueta) REFERENCES etiquetas(id_etiqueta) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: cursos
-- Reglas CHECK:
--  * titulo y descripcion no pueden estar vacios
--  * la fecha de fin no puede ser anterior a la de inicio
--  * el costo no puede ser negativo
--  * el cupo, si se informa, debe ser mayor a 0
--  * coherencia pago/costo: gratis => costo 0 (o NULL); pago => costo mayor a 0
-- --------------------------------------------------------
DROP TABLE IF EXISTS cursos;
CREATE TABLE cursos (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    id_creador INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    es_publico BOOLEAN DEFAULT TRUE NOT NULL,
    es_pago BOOLEAN DEFAULT FALSE NOT NULL,
    costo DECIMAL(10,2) DEFAULT 0.00,
    imagen VARCHAR(255) NULL,
    cupo INT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    activo BOOLEAN DEFAULT TRUE NOT NULL,
    programado BOOLEAN DEFAULT FALSE NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cursos_creador FOREIGN KEY (id_creador) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    CONSTRAINT chk_cursos_titulo CHECK (CHAR_LENGTH(TRIM(titulo)) > 0),
    CONSTRAINT chk_cursos_descripcion CHECK (CHAR_LENGTH(TRIM(descripcion)) > 0),
    CONSTRAINT chk_cursos_fechas CHECK (fecha_fin >= fecha_inicio),
    CONSTRAINT chk_cursos_costo CHECK (costo >= 0),
    CONSTRAINT chk_cursos_cupo CHECK (cupo IS NULL OR cupo > 0),
    CONSTRAINT chk_cursos_pago_costo CHECK (
        (es_pago = 0 AND COALESCE(costo, 0) = 0)
        OR (es_pago = 1 AND COALESCE(costo, 0) > 0)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: curso_etiquetas (relacion N:M cursos - etiquetas)
-- --------------------------------------------------------
DROP TABLE IF EXISTS curso_etiquetas;
CREATE TABLE curso_etiquetas (
    id_curso INT NOT NULL,
    id_etiqueta INT NOT NULL,
    PRIMARY KEY (id_curso, id_etiqueta),
    CONSTRAINT fk_curso_etiquetas_curso FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE,
    CONSTRAINT fk_curso_etiquetas_etiqueta FOREIGN KEY (id_etiqueta) REFERENCES etiquetas(id_etiqueta) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: inscripciones
-- Un alumno solo puede inscribirse una vez por curso (UNIQUE).
-- Las reglas que dependen de otras tablas (no inscribirse al propio curso,
-- limite de 3 cursos activos, curso vigente, cupo) se validan en PHP.
-- --------------------------------------------------------
DROP TABLE IF EXISTS inscripciones;
CREATE TABLE inscripciones (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_alumno INT NOT NULL,
    estado ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'aceptada' NOT NULL,
    fecha_inscripcion DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_inscripciones_curso_alumno UNIQUE (id_curso, id_alumno),
    CONSTRAINT fk_inscripciones_curso FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE,
    CONSTRAINT fk_inscripciones_alumno FOREIGN KEY (id_alumno) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: materiales
-- Reglas CHECK:
--  * nombre y ruta no pueden estar vacios
--  * el tamano debe ser mayor a 0
-- Los limites del plan gratuito (5 archivos, 5 MB c/u, 10 MB en total)
-- no se fuerzan aqui porque los usuarios pro no los tienen: se validan en PHP.
-- --------------------------------------------------------
DROP TABLE IF EXISTS materiales;
CREATE TABLE materiales (
    id_material INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    nombre_archivo VARCHAR(150) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    tamano_bytes INT NOT NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_materiales_curso FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE,
    CONSTRAINT chk_materiales_nombre CHECK (CHAR_LENGTH(TRIM(nombre_archivo)) > 0),
    CONSTRAINT chk_materiales_ruta CHECK (CHAR_LENGTH(TRIM(ruta_archivo)) > 0),
    CONSTRAINT chk_materiales_tamano CHECK (tamano_bytes > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: foro_preguntas
-- Reglas CHECK:
--  * titulo y cuerpo no pueden estar vacios
--  * es_destacada solo admite 0 o 1
-- --------------------------------------------------------
DROP TABLE IF EXISTS foro_preguntas;
CREATE TABLE foro_preguntas (
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_autor INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    cuerpo TEXT NOT NULL,
    estado ENUM('abierta', 'cerrada') DEFAULT 'abierta' NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    es_destacada TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_preguntas_curso FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE,
    CONSTRAINT fk_preguntas_autor FOREIGN KEY (id_autor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT chk_preguntas_titulo CHECK (CHAR_LENGTH(TRIM(titulo)) > 0),
    CONSTRAINT chk_preguntas_cuerpo CHECK (CHAR_LENGTH(TRIM(cuerpo)) > 0),
    CONSTRAINT chk_preguntas_destacada CHECK (es_destacada IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: foro_respuestas
-- Reglas CHECK:
--  * el cuerpo no puede estar vacio
-- La regla "no responder 2 veces seguidas" se valida en PHP.
-- --------------------------------------------------------
DROP TABLE IF EXISTS foro_respuestas;
CREATE TABLE foro_respuestas (
    id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_pregunta INT NOT NULL,
    id_autor INT NOT NULL,
    cuerpo TEXT NOT NULL,
    es_aceptada BOOLEAN DEFAULT FALSE NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_respuestas_pregunta FOREIGN KEY (id_pregunta) REFERENCES foro_preguntas(id_pregunta) ON DELETE CASCADE,
    CONSTRAINT fk_respuestas_autor FOREIGN KEY (id_autor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT chk_respuestas_cuerpo CHECK (CHAR_LENGTH(TRIM(cuerpo)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: notificaciones
-- Reglas CHECK:
--  * titulo y mensaje no pueden estar vacios
-- --------------------------------------------------------
DROP TABLE IF EXISTS notificaciones;
CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo VARCHAR(50) NULL,
    leida BOOLEAN DEFAULT FALSE NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificaciones_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT chk_notificaciones_titulo CHECK (CHAR_LENGTH(TRIM(titulo)) > 0),
    CONSTRAINT chk_notificaciones_mensaje CHECK (CHAR_LENGTH(TRIM(mensaje)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- INSERCIONES (datos de prueba)
-- --------------------------------------------------------

INSERT INTO usuarios (id_usuario, nombre, apellido, email, password, dni, edad, antecedentes, imagen_perfil, rol, plan, vencimiento_pro, fecha_registro) VALUES
(1, 'Pro', 'Ejemplo', 'pro@ejemplo.test', '$2y$10$HHp7JFbYQ0wJhSJI9QFUDuMfCgLJcv9LhUgAgHGymeyKy9N5XM7q2', '40123456', 22, 'Usuario de Prueba con Plan Pro', 'default_avatar.png', 'regular', 'pro', '2027-01-01', '2026-03-01 10:00:00'),
(2, 'Gratis', 'Ejemplo', 'gratis@ejemplo.test', '$2y$10$ifVx8WoAjTzIROXaCTbUwuXoM0yi2zZAWdfCWET47P6qHAz1sIy9O', '42345678', 20, 'Usuario de Prueba con Plan Gratis', 'default_avatar.png', 'regular', 'gratis', NULL, '2026-08-15 14:30:00'),
(3, 'Admin', 'Cursolandia', 'admin@ejemplo.test', '$2y$10$Ps0c91Ys4CdlnpvUOPoDa.Auqz/gyAKfBdBYJ.KLOkkQsDm9FMz5u', '39876543', 25, 'Administrador de Cursolandia', 'default_avatar.png', 'admin', 'pro', '2027-01-01', '2026-09-10 09:15:00');

INSERT INTO etiquetas (id_etiqueta, nombre) VALUES
(1, 'Programación'),
(2, 'PHP'),
(3, 'MySQL'),
(4, 'JavaScript'),
(5, 'HTML'),
(6, 'CSS'),
(7, 'Bootstrap'),
(8, 'Diseño UX/UI'),
(9, 'Ciberseguridad'),
(10, 'Base de Datos');

INSERT INTO usuario_intereses (id_usuario, id_etiqueta) VALUES
(2, 1), (2, 2), (2, 3);

INSERT INTO cursos (id_curso, id_creador, titulo, descripcion, es_publico, es_pago, costo, imagen, cupo, fecha_inicio, fecha_fin, activo, programado, fecha_creacion) VALUES
(1, 1, 'Masterclass de Seguridad Web', 'Descubre las vulnerabilidades más comunes en aplicaciones web (XSS, Inyección SQL, CSRF) y aprende a proteger tus sistemas de bases de datos como un profesional del lado del servidor.', 1, 1, 15000.00, NULL, 50, '2026-11-14', '2026-12-05', 1, 0, '2026-10-15 12:00:00'),
(2, 2, 'Introducción a JavaScript Moderno', 'Aprende los fundamentos de JavaScript ES6+, manipulación del DOM y consumo de APIs de forma práctica.', 1, 0, 0.00, NULL, 30, '2026-11-14', '2026-12-15', 1, 0, '2026-10-18 15:00:00'),
(3, 2, 'Diseño UX/UI desde Cero', 'Conoce las bases del diseño centrado en el usuario, wireframes y prototipado profesional con Figma.', 1, 1, 12000.00, NULL, 25, '2026-12-01', '2026-12-20', 1, 0, '2026-10-20 09:30:00');

INSERT INTO curso_etiquetas (id_curso, id_etiqueta) VALUES
(1, 9), (1, 10), (1, 2),
(2, 1), (2, 4),
(3, 8);

INSERT INTO inscripciones (id_inscripcion, id_curso, id_alumno, estado, fecha_inscripcion) VALUES
(1, 1, 2, 'pendiente', '2026-11-14 10:00:00'),
(2, 2, 1, 'aceptada', '2026-11-14 11:20:00'),
(3, 1, 3, 'pendiente', '2026-11-14 16:45:00');


SET FOREIGN_KEY_CHECKS = 1;