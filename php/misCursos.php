<?php 

    $titulo_pagina = 'Mis Cursos - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 

?>

    <main class="container my-5">
        
        <!-- Cabecera de la sección -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-3 border-bottom gap-3">
            <h2 class="fw-bold text-dark mb-0">Gestión de Cursos</h2>
            <a href="nuevoCurso.php" class="btn btn-purple fw-medium shadow-sm">
                <i class="bi bi-plus-lg me-2"></i>Crear Nuevo Curso
            </a>
        </div>

        <!-- Pestañas de Navegación (Tabs) -->
        <ul class="nav nav-pills custom-pills mb-4 gap-2" id="misCursosTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 fw-medium" id="dictados-tab" data-bs-toggle="tab" data-bs-target="#dictados" type="button" role="tab">
                    <i class="bi bi-person-video3 me-2"></i>Cursos que Dicto
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-medium" id="inscriptos-tab" data-bs-toggle="tab" data-bs-target="#inscriptos" type="button" role="tab">
                    <i class="bi bi-journal-bookmark-fill me-2"></i>Mis Inscripciones
                </button>
            </li>
        </ul>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content" id="misCursosTabContent">
            
            <!-- PESTAÑA 1: CURSOS QUE DICTO -->
            <div class="tab-pane fade show active" id="dictados" role="tabpanel" tabindex="0">
                
                <!-- Aviso de Límite (RN04) -->
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4 rounded-3" style="background-color: rgba(108, 92, 231, 0.1); color: var(--SECONDARY);">
                    <i class="bi bi-info-circle-fill fs-5 me-3"></i>
                    <div>
                        <strong>Plan Gratuito:</strong> Tienes <strong>2 de 3</strong> cursos activos permitidos. <a href="#" class="alert-link" style="color: var(--PRIMARY);">Sube a Plan Pro</a> para crear cursos ilimitados.
                    </div>
                </div>

                <!-- Grilla de Cursos Dictados -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    
                    <!-- Tarjeta Curso 1 (Activo) -->
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                            <!-- Imagen simulada con gradiente -->
                            <div class="card-img-top rounded-top-4" style="height: 120px; background: linear-gradient(45deg, var(--PRIMARY), var(--TERCIARY));"></div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-success">Activo</span>
                                    <span class="badge bg-light text-dark border">Público</span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Desarrollo Web Full Stack con PHP</h5>
                                <p class="text-muted small mb-3"><i class="bi bi-people-fill me-1"></i>25 / 30 Alumnos</p>
                                <a href="#" class="btn btn-outline-secondary w-100 fw-medium">Administrar Curso</a>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta Curso 2 (Borrador/Inactivo) -->
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                            <div class="card-img-top rounded-top-4" style="height: 120px; background: linear-gradient(45deg, #6c757d, #adb5bd);"></div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-secondary">Inactivo</span>
                                    <span class="badge bg-light text-dark border">Privado</span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Introducción a Bases de Datos</h5>
                                <p class="text-muted small mb-3"><i class="bi bi-people-fill me-1"></i>0 / Sin límite</p>
                                <a href="#" class="btn btn-outline-secondary w-100 fw-medium">Editar Borrador</a>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- PESTAÑA 2: MIS INSCRIPCIONES -->
            <div class="tab-pane fade" id="inscriptos" role="tabpanel" tabindex="0">
                
                <!-- Aviso de Límite (RN05) -->
                <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 rounded-3">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                    <div>
                        <strong>Plan Gratuito:</strong> Tienes <strong>3 de 3</strong> inscripciones activas simultáneas. Has alcanzado tu límite.
                    </div>
                </div>

                <!-- Grilla de Cursos Inscriptos -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    
                    <!-- Tarjeta Inscripción 1 (Confirmada - Gratuito) -->
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                            <div class="card-img-top rounded-top-4" style="height: 120px; background: linear-gradient(45deg, #0dcaf0, #0d6efd);"></div>
                            <div class="card-body p-4">
                                <div class="mb-2">
                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Inscripto</span>
                                    <span class="badge bg-secondary">Gratuito</span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Lógica de Programación Básica</h5>
                                <p class="text-muted small mb-3">Docente: <span class="fw-medium">IngMartinez</span></p>
                                <a href="#" class="btn btn-purple w-100 fw-medium">Entrar al Curso</a>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta Inscripción 2 (Pendiente RN10 - Pago) -->
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                            <div class="card-img-top rounded-top-4" style="height: 120px; background: linear-gradient(45deg, #ffc107, #fd7e14);"></div>
                            <div class="card-body p-4">
                                <div class="mb-2">
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pendiente confirmación</span>
                                    <span class="badge bg-success">Pago</span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Masterclass de Seguridad Web</h5>
                                <p class="text-muted small mb-3">Docente: <span class="fw-medium">HackerEtico99</span></p>
                                <!-- Botón deshabilitado hasta que el docente apruebe -->
                                <button class="btn btn-outline-secondary w-100 fw-medium" disabled>A la espera del docente</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta Inscripción 3 (Confirmada - Pago) -->
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                            <div class="card-img-top rounded-top-4" style="height: 120px; background: linear-gradient(45deg, #20c997, #198754);"></div>
                            <div class="card-body p-4">
                                <div class="mb-2">
                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Inscripto</span>
                                    <span class="badge bg-success">Pago</span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Diseño de Interfaces UX/UI</h5>
                                <p class="text-muted small mb-3">Docente: <span class="fw-medium">DisenaTuWeb</span></p>
                                <a href="#" class="btn btn-purple w-100 fw-medium">Entrar al Curso</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

<?php require_once 'footer.php'; ?>