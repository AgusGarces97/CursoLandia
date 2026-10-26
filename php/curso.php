<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>(Nombre del Curso)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-white custom-navbar py-3 border-bottom">
            <div class="container-fluid px-lg-5">
                
                <!-- Logo con Icono -->
                <a class="navbar-brand d-flex align-items-center gap-2 fw-bold custom-brand" href="../index.html">
                    <i class="bi bi-mortarboard-fill color-logo fs-3"></i>
                    <span>Cursolandia<span class="color-logo">.com</span></span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menú Colapsable -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    
                    <!-- 1. CAMBIO AQUÍ: Se cambió "align-items-lg-center" por "align-items-center" para centrar los links en móvil y achicar la línea inferior -->
                    <ul class="navbar-nav mx-auto align-items-center gap-3 my-2 my-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active custom-link text-nowrap" aria-current="page" href="nuevoCurso.php">Nuevo Curso</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="#">Mis Cursos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="#">Cursos Recomendados</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="#">Solicitudes Plan Pro</a>
                        </li>                 
                    </ul>

                    <!-- 2. CAMBIO AQUÍ: Se cambió "align-items-start" por "align-items-center" para centrar todo el bloque derecho -->
                    <div class="d-flex flex-column flex-lg-row align-items-center gap-3 gap-lg-4 mt-3 mt-lg-0">
                        
                        <!-- 3. CAMBIO AQUÍ: Se limitó el ancho al 80% (máx 280px) para que sea grande pero no cubra todo el ancho -->
                        <div class="search-box position-relative" style="width: 80%; max-width: 280px;">
                            <input class="form-control custom-search-input pe-5 w-100" type="search" placeholder="Buscar cursos..." aria-label="Search"/>
                            <i class="bi bi-search search-icon text-muted"></i>
                        </div>
                        
                        <!-- Agrupación de íconos -->
                        <div class="d-flex align-items-center gap-4 mt-2 mt-lg-0">
                            <!-- Notificaciones -->
                            <a class="text-decoration-none" href="#">
                                <i class="bi bi-bell-fill color-logo fs-4"></i>
                            </a>

                            <!-- Boton de Perfil -->
                            <a class="text-decoration-none" href="#"> 
                                <i class="bi bi-person-circle color-logo fs-3"></i> 
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-5">
        
        <!-- Cabecera del Curso con Imagen de Fondo -->
        <div class="curso-header-banner mb-5 rounded-4 shadow-sm p-4 p-md-5">
            <div class="d-flex flex-column justify-content-center h-100">
                
                <!-- Badges -->
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-success">Activo</span>
                    <span class="badge bg-secondary">Gratuito</span>
                    <span class="badge bg-info text-dark">Público</span>
                </div>
                
                <!-- Título -->
                <h1 class="fw-bold text-white mb-2">Desarrollo Web Full Stack con PHP</h1>
                
                <!-- Creador -->
                <p class="text-white mb-0 fs-5" style="opacity: 0.9;">
                    Creado por: <span class="fw-bold text-white">ProfesorGimenez</span>
                </p>
                
            </div>
        </div>

        <div class="row g-5">
            
            <!-- COLUMNA IZQUIERDA: Descripción y Foro -->
            <div class="col-lg-8">
                
                <!-- Sección Descripción -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3">Acerca de este curso</h4>
                    <p class="text-secondary" style="line-height: 1.6;">
                        Este curso está diseñado para aprender las bases de la programación web utilizando HTML, CSS, Bootstrap y PHP con bases de datos MySQL. A lo largo de las semanas construiremos un proyecto completo aplicando buenas prácticas de Ingeniería de Software.
                    </p>
                    <div class="d-flex gap-2 mt-3">
                        <span class="badge bg-light text-secondary border">#Programación</span>
                        <span class="badge bg-light text-secondary border">#PHP</span>
                        <span class="badge bg-light text-secondary border">#Web2.0</span>
                    </div>
                </section>

                <!-- Sección Foro de Preguntas -->
                <section>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Foro de Dudas</h4>
                        <button class="btn fw-medium text-white shadow-sm" style="background-color: var(--PRIMARY);">
                            <i class="bi bi-chat-left-text me-2"></i>Iniciar Pregunta
                        </button>
                    </div>

                    <!-- Hilo de Pregunta 1 (Con respuesta aceptada) -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <!-- Pregunta principal -->
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold mb-0">Error de conexión con phpMyAdmin</h6>
                                <small class="text-muted">Hace 2 horas</small>
                            </div>
                            <p class="text-secondary mb-3">Hola, estoy intentando conectar la base de datos pero me sale el error "Access denied for user". ¿Alguien sabe cómo solucionarlo?</p>
                            
                            <div class="d-flex gap-3 mb-3">
                                <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-reply-fill me-1"></i>Responder</button>
                                <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash-fill me-1"></i>Eliminar</button>
                            </div>

                            <!-- Respuesta Anidada (Destacada / Aceptada) -->
                            <div class="p-3 mt-2 rounded" style="background-color: rgba(108, 92, 231, 0.05); border-left: 4px solid var(--PRIMARY);">
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <span class="fw-bold text-dark me-2">ProfesorGimenez</span>
                                        <span class="badge text-white" style="background-color: var(--PRIMARY);"><i class="bi bi-star-fill me-1"></i>Respuesta Aceptada</span>
                                    </div>
                                    <small class="text-muted">Hace 1 hora</small>
                                </div>
                                <p class="text-secondary mb-2">Revisa que en tu archivo de conexión estés usando 'root' como usuario y que la contraseña esté vacía (si usas XAMPP por defecto).</p>
                                <button class="btn btn-sm btn-outline-secondary border-0 px-0"><i class="bi bi-pencil-fill me-1"></i>Editar Respuesta</button>
                            </div>
                        </div>
                    </div>

                    <!-- Hilo de Pregunta 2 -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold mb-0">¿Límite de subida de archivos?</h6>
                                <small class="text-muted">Ayer</small>
                            </div>
                            <p class="text-secondary mb-3">¿De cuánto es el límite para subir el trabajo práctico final?</p>
                            
                            <div class="d-flex gap-3">
                                <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-reply-fill me-1"></i>Responder</button>
                                <!-- Simulación de botón visible solo para el docente -->
                                <button class="btn btn-sm btn-outline-success border-0"><i class="bi bi-check-circle-fill me-1"></i>Destacar Respuesta</button>
                            </div>
                        </div>
                    </div>

                </section>
            </div>

            <!-- COLUMNA DERECHA: Panel de Información y Materiales -->
            <div class="col-lg-4">
                
                <!-- Tarjeta de Estado / Inscripción -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold text-success mb-3"><i class="bi bi-check-circle me-2"></i>Inscripto</h5>
                        <ul class="list-unstyled text-start text-secondary mb-4">
                            <li class="mb-2"><i class="bi bi-calendar-event me-2"></i><strong>Inicio:</strong> 15/03/2026</li>
                            <li class="mb-2"><i class="bi bi-calendar-x me-2"></i><strong>Fin:</strong> 30/06/2026</li>
                            <li><i class="bi bi-people me-2"></i><strong>Cupo:</strong> 25/30 alumnos</li>
                        </ul>
                        <button class="btn btn-outline-danger w-100 fw-medium">Abandonar Curso</button>
                    </div>
                </div>

                <!-- Tarjeta de Materiales Adjuntos -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">Material Didáctico</h6>
                        <span class="badge bg-light text-secondary border">2/5 Archivos</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <!-- Archivo 1 -->
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-4"></i>
                                    <div>
                                        <p class="mb-0 fw-medium text-dark" style="font-size: 0.9rem;">Guia_Conceptos.pdf</p>
                                        <small class="text-muted">2.4 MB</small>
                                    </div>
                                </div>
                                <i class="bi bi-download text-muted"></i>
                            </a>
                            <!-- Archivo 2 -->
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-file-earmark-zip-fill text-warning fs-4"></i>
                                    <div>
                                        <p class="mb-0 fw-medium text-dark" style="font-size: 0.9rem;">Ejemplos_Codigo.zip</p>
                                        <small class="text-muted">4.1 MB</small>
                                    </div>
                                </div>
                                <i class="bi bi-download text-muted"></i>
                            </a>
                        </div>
                    </div>
                    <!-- Botón de subida visible para el docente -->
                    <div class="card-footer bg-white border-top p-3 text-center">
                        <button class="btn btn-sm btn-outline-primary w-100 fw-medium" style="color: var(--PRIMARY); border-color: var(--PRIMARY);">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Subir Material
                        </button>
                        <div class="form-text mt-2" style="font-size: 0.75rem;">Límite: 5MB por archivo. Total: 10MB.</div>
                    </div>
                </div>

            </div>
        </div>
    </main>


    <footer class="custom-footer pt-5 pb-4 mt-auto">
        <div class="container px-lg-5">
            <div class="row text-center text-md-start">
                
                <div class="col-md-6 col-lg-6 mb-4">
                    <h5 class="fw-bold mb-3 text-white">Soporte e Información</h5>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li><a href="#" class="footer-link">Acerca de Cursolandia</a></li>
                        <li><a href="#" class="footer-link">Beneficios del Plan Pro</a></li>
                        <li><a href="#" class="footer-link">Preguntas Frecuentes (FAQ)</a></li>
                        <li><a href="#" class="footer-link">Contacto / Ayuda</a></li>
                    </ul>
                </div>

                <div class="col-md-6 col-lg-6 mb-4">
                    <h5 class="fw-bold mb-3 text-white">Legales</h5>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li><a href="#" class="footer-link">Términos y Condiciones</a></li>
                        <li><a href="#" class="footer-link">Política de Privacidad</a></li>
                    </ul>
                </div>
                
            </div>

            <hr class="footer-divider my-4">

            <div class="row align-items-center text-center text-md-start">
                <div class="col-md-7 col-lg-8 mb-3 mb-md-0">
                    <p class="mb-1 text-white opacity-75">
                        &copy; 2026 Cursolandia.com. Todos los derechos reservados.
                    </p>
                    <p class="mb-0 small text-white opacity-50">
                        Proyecto desarrollado por Agustín Garcés para la materia Ingeniería de Software & Programación III (UNSL).
                    </p>
                </div>
                
                <div class="col-md-5 col-lg-4 text-center text-md-end">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-2 fw-bold text-white opacity-75">
                        <i class="bi bi-mortarboard-fill fs-4"></i>
                        <span class="fs-5">Cursolandia</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
    crossorigin="anonymous">
    </script>
    <script src="../js/comp.js"></script>
</body>
</html>