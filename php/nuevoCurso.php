<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Nuevo Curso</title>
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
                    
                    
                    <ul class="navbar-nav mx-auto align-items-center gap-3 my-2 my-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active custom-link text-nowrap" aria-current="page" href="#">Nuevo Curso</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="../html/misCursos.html">Mis Cursos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="../html/cursosRecomendados.html">Cursos Recomendados</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="../html/solicitudesPro.html">Solicitudes Plan Pro</a>
                        </li>                 
                    </ul>

                    
                    <div class="d-flex flex-column flex-lg-row align-items-center gap-3 gap-lg-4 mt-3 mt-lg-0">
                        
                        
                        <div class="search-box position-relative" style="width: 80%; max-width: 280px;">
                            <input class="form-control custom-search-input pe-5 w-100" type="search" placeholder="Buscar cursos..." aria-label="Search"/>
                            <i class="bi bi-search search-icon text-muted"></i>
                        </div>
                        
                        <!-- Agrupación de íconos -->
                        <div class="d-flex align-items-center gap-4 mt-2 mt-lg-0">
                            <!-- Notificaciones -->
                            <a class="text-decoration-none" href="../html/notificaciones.html">
                                <i class="bi bi-bell-fill color-logo fs-4"></i>
                            </a>

                            <!-- Boton de Perfil -->
                            <a class="text-decoration-none" href="../php/perfil.php"> 
                                <i class="bi bi-person-circle color-logo fs-3"></i> 
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Tarjeta del Formulario -->
                <div class="card shadow-sm border-0 rounded-3">
                    
                    <div class="card-header bg-white border-bottom py-4 px-4 px-lg-5">
                        <h3 class="fw-bold mb-0 text-dark">Crear Nuevo Curso</h3>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        <form action="nuevoCurso.php" method="POST">
                            
                            <div class="row g-4">
                                
                                <!-- COLUMNA IZQUIERDA: Textos Principales -->
                                <div class="col-md-7">
                                    <!-- Título (Máx 30 caracteres según diccionario) -->
                                    <div class="mb-4">
                                        <label for="titulo" class="form-label fw-medium text-dark">Título del Curso <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control custom-input" id="titulo" name="titulo" maxlength="30" placeholder="Ej: Desarrollo Web con PHP" required>
                                        <div class="form-text">Máximo 30 caracteres.</div>
                                    </div>
                                    
                                    <!-- Descripción (Máx 1000 caracteres) -->
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label fw-medium text-dark">Descripción <span class="text-danger">*</span></label>
                                        <textarea class="form-control custom-input" id="descripcion" name="descripcion" rows="8" maxlength="1000" placeholder="Escribe de qué trata el curso, qué aprenderán los alumnos..." required></textarea>
                                        <div class="form-text">Máximo 1000 caracteres.</div>
                                    </div>
                                </div>

                                <!-- COLUMNA DERECHA: Configuraciones y Fechas -->
                                <div class="col-md-5">
                                    
                                    <!-- Privacidad y Costo -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="privacidad" class="form-label fw-medium text-dark">Privacidad</label>
                                            <select class="form-select custom-input" id="privacidad" name="privacidad">
                                                <option value="0">Público</option>
                                                <option value="1">Privado</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label for="costo" class="form-label fw-medium text-dark">Costo</label>
                                            <select class="form-select custom-input" id="costo" name="costo">
                                                <option value="0">Gratuito</option>
                                                <option value="1">Pago</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Cupo (Opcional) -->
                                    <div class="mb-4">
                                        <label for="cupo" class="form-label fw-medium text-dark">Cupo máximo (Opcional)</label>
                                        <input type="number" class="form-control custom-input" id="cupo" name="cupo" min="1" placeholder="Ej: 30 (Dejar vacío si es sin límite)">
                                    </div>

                                    <!-- Fechas -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="fechaInicio" class="form-label fw-medium text-dark">Inicio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaInicio" name="fechaInicio" required>
                                        </div>
                                        <div class="col-6">
                                            <label for="fechaFin" class="form-label fw-medium text-dark">Fin <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaFin" name="fechaFin" required>
                                        </div>
                                    </div>

                                    <!-- Estado del Curso (Switch Toggle) -->
                                    <div class="mb-3 p-3 bg-light rounded border">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="estadoCurso" name="estadoCurso" value="1" checked>
                                            <label class="form-check-label fw-medium text-dark" for="estadoCurso">Publicar como Activo</label>
                                        </div>
                                        <div class="form-text small mb-0">Desmárcalo si solo quieres guardar un borrador.</div>
                                    </div>
                                    
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <!-- Botones de Acción (Confirmar y Navegar_a) -->
                            <div class="d-flex justify-content-end gap-3">
                                <a href="index.html" class="btn btn-light fw-medium px-4 text-muted border">Cancelar</a>
                                <button type="submit" class="btn btn-purple fw-medium px-4">Confirmar Creación</button>
                            </div>
                            
                        </form>
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