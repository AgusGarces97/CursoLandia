<?php 

    $titulo_pagina = 'Recomendados - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 

?>

    <main class="container my-5">
        
        <!-- Encabezado de la página -->
        <div class="mb-5 border-bottom pb-4">
            <h2 class="fw-bold text-dark mb-2">Recomendados para ti</h2>
            <p class="text-muted mb-3">Basado en el pareo de tus intereses con las etiquetas de los cursos activos.</p>
            
            <!-- Intereses del usuario que generaron las recomendaciones -->
            <div class="d-flex flex-wrap gap-2">
                <span class="text-secondary fw-medium me-2 align-self-center">Tus intereses detectados:</span>
                <span class="badge bg-light text-secondary border px-2 py-1"><i class="bi bi-tag-fill me-1"></i>#Programación</span>
                <span class="badge bg-light text-secondary border px-2 py-1"><i class="bi bi-tag-fill me-1"></i>#PHP</span>
                <span class="badge bg-light text-secondary border px-2 py-1"><i class="bi bi-tag-fill me-1"></i>#BasesDeDatos</span>
            </div>
        </div>

        <!-- Grilla de Cursos Recomendados -->
        <div class="row g-4">
            
            <!-- ========================================== -->
            <!-- CURSO 1: DESTACADO (Creador con Plan Pro) -->
            <!-- ========================================== -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm curso-card-hover curso-pro-destacado position-relative">
                    
                    <!-- Etiqueta Visual de Destacado -->
                    <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill px-3 py-2 text-white shadow-sm" style="background-color: var(--PRIMARY); z-index: 2;">
                        <i class="bi bi-star-fill text-warning me-1"></i> Curso Destacado
                    </div>

                    <!-- Imagen del Curso -->
                    <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top curso-img" alt="Portada Desarrollo Web">

                    <div class="card-body pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-success">Activo</span>
                            <span class="badge bg-secondary">Gratuito</span>
                        </div>
                        <h5 class="card-title fw-bold text-dark mt-3">Desarrollo Web Full Stack con PHP</h5>
                        <p class="text-muted small mb-3">Por: <span class="fw-bold" style="color: var(--PRIMARY);">ProfesorGimenez</span> <i class="bi bi-patch-check-fill text-primary" title="Usuario Pro"></i></p>
                        
                        <p class="text-secondary small line-clamp-3">Aprende a crear aplicaciones web 2.0 dinámicas conectadas a bases de datos relacionales desde cero, aplicando buenas prácticas de ingeniería de software.</p>
                        
                        <!-- Etiquetas coincidentes -->
                        <div class="d-flex flex-wrap gap-1 mt-auto pt-2">
                            <span class="badge bg-light text-dark border border-primary">#PHP</span>
                            <span class="badge bg-light text-dark border border-primary">#BasesDeDatos</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0">
                        <button class="btn btn-purple w-100 fw-medium shadow-sm">Ver Detalles</button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- CURSO 2: DESTACADO (Creador con Plan Pro) -->
            <!-- ========================================== -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm curso-card-hover curso-pro-destacado position-relative">
                    
                    <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill px-3 py-2 text-white shadow-sm" style="background-color: var(--PRIMARY); z-index: 2;">
                        <i class="bi bi-star-fill text-warning me-1"></i> Curso Destacado
                    </div>

                    <!-- Imagen del Curso -->
                    <img src="https://images.unsplash.com/photo-1555066931-4365d14bab8c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top curso-img" alt="Portada Algoritmos">

                    <div class="card-body pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-success">Activo</span>
                            <span class="badge bg-dark"><i class="bi bi-currency-dollar"></i> Pago</span>
                        </div>
                        <h5 class="card-title fw-bold text-dark mt-3">Lógica de Programación y Algoritmos</h5>
                        <p class="text-muted small mb-3">Por: <span class="fw-bold" style="color: var(--PRIMARY);">CodeMaster99</span> <i class="bi bi-patch-check-fill text-primary" title="Usuario Pro"></i></p>
                        
                        <p class="text-secondary small line-clamp-3">Domina las estructuras de control, bucles y lógica fundamental para cualquier lenguaje de programación moderno.</p>
                        
                        <div class="d-flex flex-wrap gap-1 mt-auto pt-2">
                            <span class="badge bg-light text-dark border border-primary">#Programación</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0">
                        <button class="btn btn-purple w-100 fw-medium shadow-sm">Ver Detalles</button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- CURSO 3: NORMAL (Creador con Plan Gratuito) -->
            <!-- ========================================== -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm curso-card-hover mt-3 mt-lg-0">
                    
                    <!-- Imagen del Curso (Sin etiqueta de destacado) -->
                    <img src="https://images.unsplash.com/photo-1544383835-bda2bc66a55d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top curso-img" alt="Portada Bases de Datos">

                    <div class="card-body pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-success">Activo</span>
                            <span class="badge bg-secondary">Gratuito</span>
                        </div>
                        <h5 class="card-title fw-bold text-dark mt-3">Diseño de Bases de Datos Relacionales</h5>
                        <p class="text-muted small mb-3">Por: MariaLopez99</p>
                        
                        <p class="text-secondary small line-clamp-3">Aprende a normalizar hasta la 3FN, crear modelos de entidad-relación y usar claves foráneas para mantener la integridad de los datos.</p>
                        
                        <div class="d-flex flex-wrap gap-1 mt-auto pt-2">
                            <span class="badge bg-light text-secondary border">#BasesDeDatos</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0">
                        <button class="btn btn-outline-primary w-100 fw-medium" style="border-color: var(--PRIMARY); color: var(--PRIMARY);">Ver Detalles</button>
                    </div>
                </div>
            </div>

        </div>
    </main>


<?php require_once 'footer.php'; ?>