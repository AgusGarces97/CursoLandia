<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Validamos que venga un ID por la URL
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: ../index.php");
        exit();
    }

    require_once 'conexion.php';

    $id_curso = (int)$_GET['id'];
    $id_usuario_actual = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;

    // 1. Traemos los datos del curso y de su creador
    $sql_curso = "
        SELECT c.*, u.nombre, u.apellido 
        FROM cursos c
        INNER JOIN usuarios u ON c.id_creador = u.id_usuario
        WHERE c.id_curso = $id_curso
    ";
    $resultado_curso = mysqli_query($conexion, $sql_curso);

    // Si el curso no existe, lo mandamos al inicio
    if (mysqli_num_rows($resultado_curso) == 0) {
        header("Location: ../index.php");
        exit();
    }
    $curso = mysqli_fetch_assoc($resultado_curso);

    // 2. Variables de control de acceso
    $es_creador = ($id_usuario_actual == $curso['id_creador']);
    $esta_inscripto = false;

    // 3. Consultamos cuántos alumnos hay inscriptos para el cupo
    $sql_cupo = "SELECT COUNT(id_inscripcion) as total_alumnos FROM inscripciones WHERE id_curso = $id_curso";
    $res_cupo = mysqli_query($conexion, $sql_cupo);
    $total_alumnos = mysqli_fetch_assoc($res_cupo)['total_alumnos'];

    // 4. Verificamos si el usuario actual ya está inscripto (si no es el creador)
    if ($id_usuario_actual && !$es_creador) {
        $sql_check_inscripcion = "SELECT * FROM inscripciones WHERE id_curso = $id_curso AND id_alumno = $id_usuario_actual";
        $res_check = mysqli_query($conexion, $sql_check_inscripcion);
        if (mysqli_num_rows($res_check) > 0) {
            $esta_inscripto = true;
        }
    }

    $titulo_pagina = $curso['titulo'] . ' - Cursolandia';
    $ruta = '../';
    require_once 'header.php' ;
?>

    <main class="container my-5">
        
        <!-- Cabecera del Curso con Imagen de Fondo -->
        <div class="curso-header-banner mb-5 rounded-4 shadow-sm p-4 p-md-5">
            <div class="d-flex flex-column justify-content-center h-100">
                
                <!-- Badges Dinámicos -->
                <div class="d-flex gap-2 mb-3">
                    <?php if($curso['activo'] == 1): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Borrador</span>
                    <?php endif; ?>

                    <?php if($curso['es_pago'] == 1): ?>
                        <span class="badge bg-warning text-dark">De Pago</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Gratuito</span>
                    <?php endif; ?>

                    <?php if($curso['es_publico'] == 1): ?>
                        <span class="badge bg-info text-dark">Público</span>
                    <?php else: ?>
                        <span class="badge bg-dark text-white">Privado</span>
                    <?php endif; ?>
                </div>
                
                <!-- Título -->
                <h1 class="fw-bold text-white mb-2"><?php echo $curso['titulo']; ?></h1>
                
                <!-- Creador -->
                <p class="text-white mb-0 fs-5" style="opacity: 0.9;">
                    Creado por: <span class="fw-bold text-white"><?php echo $curso['nombre'] . ' ' . $curso['apellido']; ?></span>
                </p>
                
            </div>
        </div>

        <div class="row g-5">
            
            <!-- COLUMNA IZQUIERDA: Descripción y Foro -->
            <div class="col-lg-8">
                
                <!-- Sección Descripción -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3">Acerca de este curso</h4>
                    <p class="text-secondary" style="line-height: 1.6; white-space: pre-wrap;"><?php echo $curso['descripcion']; ?></p>
                    
                    <div class="d-flex gap-2 mt-3">
                        <span class="badge bg-light text-secondary border">#Programación</span>
                        <span class="badge bg-light text-secondary border">#Cursolandia</span>
                    </div>
                </section>

                <!-- Sección Foro de Preguntas (Estático por ahora) -->
                <section>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Foro de Dudas</h4>
                        <?php if($esta_inscripto || $es_creador): ?>
                            <button class="btn fw-medium text-white shadow-sm" style="background-color: var(--PRIMARY);">
                                <i class="bi bi-chat-left-text me-2"></i>Iniciar Pregunta
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Hilo de Pregunta de ejemplo -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold mb-0">Ejemplo de pregunta del foro</h6>
                                <small class="text-muted">Hace 2 horas</small>
                            </div>
                            <p class="text-secondary mb-3">Las preguntas reales se cargarán aquí cuando programemos la tabla del foro.</p>
                            
                            <?php if($esta_inscripto || $es_creador): ?>
                                <div class="d-flex gap-3">
                                    <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-reply-fill me-1"></i>Responder</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </section>
            </div>

            <!-- COLUMNA DERECHA: Panel de Información y Materiales -->
            <div class="col-lg-4">
                
                <!-- Tarjeta de Estado / Inscripción -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        
                        <?php if($es_creador): ?>
                            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-video3 me-2"></i>Eres el docente</h5>
                        <?php elseif($esta_inscripto): ?>
                            <h5 class="fw-bold text-success mb-3"><i class="bi bi-check-circle me-2"></i>Inscripto</h5>
                        <?php else: ?>
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle me-2"></i>Información</h5>
                        <?php endif; ?>

                        <ul class="list-unstyled text-start text-secondary mb-4">
                            <li class="mb-2"><i class="bi bi-calendar-event me-2"></i><strong>Inicio:</strong> <?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?></li>
                            <li class="mb-2"><i class="bi bi-calendar-x me-2"></i><strong>Fin:</strong> <?php echo date("d/m/Y", strtotime($curso['fecha_fin'])); ?></li>
                            <li>
                                <i class="bi bi-people me-2"></i><strong>Cupo:</strong> 
                                <?php echo $total_alumnos; ?> / <?php echo $curso['cupo'] ? $curso['cupo'] : 'Sin límite'; ?> alumnos
                            </li>
                        </ul>
                        
                        <!-- Lógica de Botones Dinámicos -->
                        <?php if(!$id_usuario_actual): ?>
                            <!-- Si no está logueado, lo mandamos al login -->
                            <a href="login.php" class="btn btn-purple w-100 fw-medium">Inicia sesión para inscribirte</a>
                        <?php elseif($es_creador): ?>
                            <button class="btn btn-outline-primary w-100 fw-medium">Administrar Curso</button>
                        <?php elseif($esta_inscripto): ?>
                            <form action="procesar_inscripcion.php" method="POST">
                                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                                <input type="hidden" name="accion" value="abandonar">
                                <button type="submit" class="btn btn-outline-danger w-100 fw-medium">Abandonar Curso</button>
                            </form>
                        <?php else: ?>
                            <form action="procesar_inscripcion.php" method="POST">
                                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                                <input type="hidden" name="accion" value="inscribir">
                                <?php 
                                    // Verificamos si hay cupo disponible para habilitar el botón
                                    $hay_cupo = ($curso['cupo'] === null || $total_alumnos < $curso['cupo']);
                                ?>
                                <button type="submit" class="btn btn-purple w-100 fw-medium" <?php echo !$hay_cupo ? 'disabled' : ''; ?>>
                                    <?php echo $hay_cupo ? 'Inscribirse al Curso' : 'Cupo Lleno'; ?>
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Tarjeta de Materiales Adjuntos -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">Material Didáctico</h6>
                        <span class="badge bg-light text-secondary border">0 Archivos</span>
                    </div>
                    
                    <div class="card-body p-4 text-center text-muted">
                        Aún no hay materiales disponibles.
                    </div>
                    
                    <?php if($es_creador): ?>
                    <!-- Botón de subida visible SOLO para el docente -->
                    <div class="card-footer bg-white border-top p-3 text-center">
                        <button class="btn btn-sm btn-outline-primary w-100 fw-medium" style="color: var(--PRIMARY); border-color: var(--PRIMARY);">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Subir Material
                        </button>
                        <div class="form-text mt-2" style="font-size: 0.75rem;">Límite: 5MB por archivo. Total: 10MB.</div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

<?php require_once 'footer.php'; ?>