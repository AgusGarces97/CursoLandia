<?php 

    $titulo_pagina = 'Curso - Cursolandia';
    $ruta = '../';
    require_once 'header.php' ;

?>

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


<?php require_once 'footer.php'; ?>