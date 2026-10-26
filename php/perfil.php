<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Perfil</title>
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
        
        <!-- BANNER DE PERFIL -->
        <section class="perfil-banner rounded-4 shadow-sm mb-5 p-4 p-md-5 d-flex align-items-center">
            <div class="d-flex align-items-center gap-4 w-100">
                <!-- Foto de Perfil -->
                <div class="position-relative">
                    <img src="https://ui-avatars.com/api/?name=Agustin+Garces&background=ffffff&color=6c5ce7&size=150" alt="Foto de Perfil" class="rounded-circle border border-4 border-white shadow-sm perfil-img">
                </div>
                
                <!-- Información del Usuario -->
                <div class="text-white">
                    <h2 class="fw-bold mb-1">AgustinGarces</h2>
                    <p class="mb-0 fs-5 opacity-75">Rol: Alumno / Docente</p>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span class="badge bg-white text-primary px-3 py-2 border-0">Plan Pro</span>
                        <small class="fw-medium">Vto: 31/12/2026</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN DE DATOS Y FORMULARIO -->
        <section class="bg-white rounded-4 shadow-sm p-4 p-lg-5 border">
            
            <!-- Cabecera de la sección (Título y Botones) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 border-bottom pb-3">
                <h3 class="fw-bold mb-0 text-dark">Datos Personales</h3>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary fw-medium">Cambiar Contraseña</button>
                    <button type="button" class="btn btn-purple fw-medium">Botón Editar Perfil</button>
                </div>
            </div>

            <!-- Formulario dividido en 3 columnas como en el boceto -->
            <form action="editarPerfil.php" method="POST">
                <div class="row g-4">
                    
                    <!-- COLUMNA 1: Datos Personales (Inputs) -->
                    <div class="col-lg-4">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="nombre" class="form-label fw-medium text-dark">Nombre</label>
                                <input type="text" class="form-control custom-input" id="nombre" value="Agustín">
                            </div>
                            <div class="col-sm-6">
                                <label for="apellido" class="form-label fw-medium text-dark">Apellido</label>
                                <input type="text" class="form-control custom-input" id="apellido" value="Garcés">
                            </div>
                            <div class="col-sm-4">
                                <label for="edad" class="form-label fw-medium text-dark">Edad</label>
                                <input type="number" class="form-control custom-input" id="edad" value="22">
                            </div>
                            <div class="col-sm-8">
                                <label for="dni" class="form-label fw-medium text-dark">DNI</label>
                                <!-- readonly por RN02 -->
                                <input type="text" class="form-control custom-input bg-light text-muted" id="dni" value="45123456" readonly>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label fw-medium text-dark">Email</label>
                                <!-- readonly por RN02 -->
                                <input type="email" class="form-control custom-input bg-light text-muted" id="email" value="agustin@ejemplo.com" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 2: Antecedentes -->
                    <div class="col-lg-4">
                        <label for="antecedentes" class="form-label fw-medium text-dark">Antecedentes</label>
                        <textarea class="form-control custom-input" id="antecedentes" rows="8" placeholder="Escribe tu trayectoria, conocimientos previos o experiencia..."></textarea>
                    </div>

                    <!-- COLUMNA 3: Intereses (Buscador y Etiquetas Elegidas) -->
                    <div class="col-lg-4">
                        <label for="buscadorEtiquetas" class="form-label fw-medium text-dark">Intereses</label>
                        
                        <!-- Buscador de Etiquetas -->
                        <div class="position-relative mb-3">
                            <input type="text" class="form-control custom-input text-center" id="buscadorEtiquetas" placeholder="Buscador de Etiquetas">
                        </div>
                        
                        <!-- Caja de Etiquetas Elegidas -->
                        <div class="border rounded-3 p-3 bg-light d-flex flex-wrap align-content-start gap-2" style="min-height: 140px;">
                            <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1">
                                #Programación <button type="button" class="btn-close" style="font-size: 0.5rem;"></button>
                            </span>
                            <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1">
                                #PHP <button type="button" class="btn-close" style="font-size: 0.5rem;"></button>
                            </span>
                            <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1">
                                #BasesDeDatos <button type="button" class="btn-close" style="font-size: 0.5rem;"></button>
                            </span>
                            <!-- Placeholder visual por si está vacío -->
                            <!-- <span class="text-muted small w-100 text-center mt-4">Acá estarán las etiquetas elegidas</span> -->
                        </div>
                    </div>

                </div>
            </form>

        </section>
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