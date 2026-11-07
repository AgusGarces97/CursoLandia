<?php 

    $titulo_pagina = 'Inicio - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 

?>

    <main class="container my-5">
        
        <!-- Encabezado de Notificaciones -->
        <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
            <div>
                <h2 class="fw-bold text-dark mb-1">Notificaciones</h2>
                <p class="text-muted mb-0">Mantente al día con la actividad de tus cursos y cuenta.</p>
            </div>
            <button class="btn btn-sm btn-outline-secondary fw-medium">
                <i class="bi bi-check2-all me-1"></i>Marcar leídas
            </button>
        </div>

        <!-- Contenedor de la Lista -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="list-group list-group-flush">
                
                <!-- Notificación 1: Nueva Pregunta (RN12) - NO LEÍDA -->
                <a href="#" class="list-group-item list-group-item-action p-4 notif-unread border-start-primary">
                    <div class="d-flex gap-3">
                        <div class="notif-icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-chat-left-text-fill fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold text-dark mb-0">Nueva pregunta en Desarrollo Web Full Stack</h6>
                                <small class="text-primary fw-medium text-nowrap ms-2">Hace 5 min</small>
                            </div>
                            <p class="text-secondary mb-0 small"><strong>CarlosGomez</strong> ha iniciado una nueva duda en el foro de tu curso.</p>
                        </div>
                        <!-- Punto indicador de "no leído" -->
                        <div class="align-self-center ms-2">
                            <span class="p-2 bg-primary border border-light rounded-circle d-inline-block"></span>
                        </div>
                    </div>
                </a>

                <!-- Notificación 2: Respuesta Aceptada (RN17) - NO LEÍDA -->
                <a href="#" class="list-group-item list-group-item-action p-4 notif-unread border-start-primary">
                    <div class="d-flex gap-3">
                        <div class="notif-icon-box bg-warning bg-opacity-25 text-warning">
                            <i class="bi bi-star-fill fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold text-dark mb-0">¡Tu respuesta fue destacada!</h6>
                                <small class="text-primary fw-medium text-nowrap ms-2">Hace 2 horas</small>
                            </div>
                            <p class="text-secondary mb-0 small">El docente <strong>CodeMaster99</strong> ha marcado tu respuesta como aceptada en <em>Lógica de Programación</em>.</p>
                        </div>
                        <div class="align-self-center ms-2">
                            <span class="p-2 bg-primary border border-light rounded-circle d-inline-block"></span>
                        </div>
                    </div>
                </a>

                <!-- Notificación 3: Plan Pro Activado (Sistema) - LEÍDA -->
                <a href="#" class="list-group-item list-group-item-action p-4">
                    <div class="d-flex gap-3">
                        <div class="notif-icon-box bg-success bg-opacity-10 text-success">
                            <i class="bi bi-patch-check-fill fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold text-dark mb-0">Plan Pro Activado</h6>
                                <small class="text-muted text-nowrap ms-2">Ayer</small>
                            </div>
                            <p class="text-secondary mb-0 small">Un administrador ha validado tu comprobante. ¡Ya cuentas con los beneficios para crear cursos ilimitados!</p>
                        </div>
                    </div>
                </a>

                <!-- Notificación 4: Respuesta en el foro (RN14) - LEÍDA -->
                <a href="#" class="list-group-item list-group-item-action p-4">
                    <div class="d-flex gap-3">
                        <div class="notif-icon-box bg-info bg-opacity-10 text-info">
                            <i class="bi bi-reply-fill fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold text-dark mb-0">Nueva respuesta en tu hilo</h6>
                                <small class="text-muted text-nowrap ms-2">Ayer</small>
                            </div>
                            <p class="text-secondary mb-0 small"><strong>MariaLopez99</strong> respondió a tu pregunta sobre <em>Normalización de Bases de Datos</em>.</p>
                        </div>
                    </div>
                </a>

                <!-- Notificación 5: Pregunta Eliminada (RN13) - LEÍDA -->
                <a href="#" class="list-group-item list-group-item-action p-4">
                    <div class="d-flex gap-3">
                        <div class="notif-icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-trash-fill fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold text-dark mb-0">Pregunta eliminada del foro</h6>
                                <small class="text-muted text-nowrap ms-2">Hace 2 días</small>
                            </div>
                            <p class="text-secondary mb-0 small">El docente eliminó tu pregunta en el foro de <em>Diseño de Bases de Datos Relacionales</em>.</p>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </main>

<?php require_once 'footer.php'; ?>