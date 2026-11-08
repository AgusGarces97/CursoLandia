-- Creamos la base de datos y la seleccionamos
CREATE DATABASE IF NOT EXISTS cursolandia_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cursolandia_db;

-- =======================================================
-- 1. TABLA USUARIOS
-- =======================================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    dni VARCHAR(15) UNIQUE,
    edad INT,
    antecedentes TEXT,
    imagen_perfil VARCHAR(255) DEFAULT 'default_avatar.png',
    rol ENUM('admin', 'regular') DEFAULT 'regular' NOT NULL,
    plan ENUM('gratis', 'pro') DEFAULT 'gratis' NOT NULL,
    vencimiento_pro DATE NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =======================================================
-- 2. TABLA SOLICITUDES PLAN PRO
-- =======================================================
CREATE TABLE solicitudes_pro (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    archivo_comprobante VARCHAR(255) NOT NULL,
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente' NOT NULL,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =======================================================
-- 3. TABLA ETIQUETAS (Para Cursos y Perfiles)
-- =======================================================
CREATE TABLE etiquetas (
    id_etiqueta INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- =======================================================
-- 4. TABLA INTERESES USUARIO (Relación N:M)
-- =======================================================
CREATE TABLE usuario_intereses (
    id_usuario INT NOT NULL,
    id_etiqueta INT NOT NULL,
    PRIMARY KEY (id_usuario, id_etiqueta),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_etiqueta) REFERENCES etiquetas(id_etiqueta) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =======================================================
-- 5. TABLA CURSOS
-- =======================================================
CREATE TABLE cursos (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    id_creador INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    es_publico BOOLEAN DEFAULT TRUE NOT NULL,
    es_pago BOOLEAN DEFAULT FALSE NOT NULL,
    cupo INT NULL, -- NULL significa sin límite
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    activo BOOLEAN DEFAULT TRUE NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_creador) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =======================================================
-- 6. TABLA ETIQUETAS DEL CURSO (Relación N:M)
-- =======================================================
CREATE TABLE curso_etiquetas (
    id_curso INT NOT NULL,
    id_etiqueta INT NOT NULL,
    PRIMARY KEY (id_curso, id_etiqueta),
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE,
    FOREIGN KEY (id_etiqueta) REFERENCES etiquetas(id_etiqueta) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =======================================================
-- 7. TABLA INSCRIPCIONES
-- =======================================================
CREATE TABLE inscripciones (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_alumno INT NOT NULL,
    estado ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'aceptada' NOT NULL,
    fecha_inscripcion DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (id_curso, id_alumno), -- Evita que un alumno se inscriba 2 veces al mismo curso
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE,
    FOREIGN KEY (id_alumno) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =======================================================
-- 8. TABLA MATERIALES
-- =======================================================
CREATE TABLE materiales (
    id_material INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    nombre_archivo VARCHAR(150) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    tamano_bytes INT NOT NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =======================================================
-- 9. TABLA FORO (PREGUNTAS)
-- =======================================================
CREATE TABLE foro_preguntas (
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_autor INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    cuerpo TEXT NOT NULL,
    estado ENUM('abierta', 'cerrada') DEFAULT 'abierta' NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE,
    FOREIGN KEY (id_autor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =======================================================
-- 10. TABLA FORO (RESPUESTAS)
-- =======================================================
CREATE TABLE foro_respuestas (
    id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_pregunta INT NOT NULL,
    id_autor INT NOT NULL,
    cuerpo TEXT NOT NULL,
    es_aceptada BOOLEAN DEFAULT FALSE NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pregunta) REFERENCES foro_preguntas(id_pregunta) ON DELETE CASCADE,
    FOREIGN KEY (id_autor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =======================================================
-- 11. TABLA NOTIFICACIONES
-- =======================================================
CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo VARCHAR(50) NULL, -- ej: 'foro', 'plan_pro', 'inscripcion'
    leida BOOLEAN DEFAULT FALSE NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;


-- 1. Crear el usuario de la base de datos (si no existe)
CREATE USER IF NOT EXISTS 'usuario_admin'@'localhost' IDENTIFIED BY 'admin';

-- 2. Otorgarle todos los permisos SOLO sobre la base de datos del proyecto
GRANT ALL PRIVILEGES ON cursolandia_db.* TO 'usuario_admin'@'localhost';

-- 3. Recargar los privilegios para que MySQL los aplique inmediatamente
FLUSH PRIVILEGES;

-- 1. INSERTAR USUARIOS DE PRUEBA
INSERT INTO usuarios (id_usuario, nombre, apellido, email, password, dni, edad, antecedentes, rol, plan) VALUES 
(100, 'Agustín', 'Garcés', 'agustin@ejemplo.com', '123456', '44111222', 23, 'Desarrollador Web Full Stack y profesor particular.', 'regular', 'pro'),
(200, 'María', 'Gómez', 'maria@ejemplo.com', '123456', '45222333', 21, 'Estudiante de diseño gráfico interesada en UX/UI.', 'regular', 'gratis'),
(300, 'Carlos', 'Admin', 'admin@cursolandia.com', 'admin123', '30444555', 35, 'Administrador general de la plataforma.', 'admin', 'pro');

-- 2. INSERTAR CURSOS VINCULADOS AL USUARIO 1 (Agustín)
INSERT INTO cursos (id_creador, titulo, descripcion, es_publico, es_pago, cupo, fecha_inicio, fecha_fin, activo) VALUES 
(1, 'Desarrollo Web Full Stack con PHP', 'Aprende a crear aplicaciones web dinámicas desde cero utilizando HTML, CSS, PHP y MySQL. Ideal para principiantes que buscan su primer empleo IT. Incluye proyectos reales y buenas prácticas de código.', 1, 0, NULL, '2026-11-20', '2026-12-20', 1),
(1, 'Masterclass de Seguridad Web', 'Descubre las vulnerabilidades más comunes en aplicaciones web (XSS, Inyección SQL, CSRF) y aprende a proteger tus sistemas de bases de datos como un profesional del lado del servidor.', 1, 1, 50, '2026-11-25', '2026-12-05', 1),
(1, 'Diseño de Interfaces UX/UI', 'Domina los principios de diseño centrado en el usuario. Crea interfaces hermosas y funcionales que destaquen en tu portafolio usando las herramientas más demandadas del mercado.', 1, 1, 30, '2026-12-01', '2027-01-15', 1),
(1, 'Lógica de Programación Básica', 'El primer paso para cualquier programador. Desarrolla tu pensamiento lógico resolviendo algoritmos y diagramas de flujo sin tocar código complejo. Sienta las bases para cualquier lenguaje.', 1, 0, 100, '2026-11-10', '2026-12-10', 1),
(1, 'Proyecto Secreto (Borrador)', 'Este es un curso que aún estoy preparando. Como tiene activo = 0, el sistema NO debería mostrarlo en el index principal, pero sí en la pestaña de Mis Cursos.', 1, 0, NULL, '2027-01-01', '2027-02-01', 0),
(1, 'Tutoría Exclusiva de Física', 'Clases privadas y avanzadas de física. Como tiene es_publico = 0, tampoco debería aparecer en el catálogo público del inicio.', 0, 1, 5, '2026-11-15', '2026-12-15', 1);
