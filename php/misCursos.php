<?php 
    if(session_status() == PHP_SESSION_NONE){
        session_start();
    }

    require_once 'conexion.php';

    if(!isset($_SESSION['id_usuario'])){
        header("Location: ../index.php");
        exit();
    }

    $idUsuario = $_SESSION['id_usuario'];

    // OBTENEMOS LAS INSCRIPCIONES PARA LA PESTAÑA 2
    $sql_mis_inscripciones = "
        SELECT c.*, i.estado as estado_inscripcion, u.nombre as profe_nombre, u.apellido as profe_apellido 
        FROM inscripciones i
        INNER JOIN cursos c ON i.id_curso = c.id_curso
        INNER JOIN usuarios u ON c.id_creador = u.id_usuario
        WHERE i.id_alumno = $idUsuario
        ORDER BY i.id_inscripcion DESC
    ";
    $resultado_inscripciones = mysqli_query($conexion, $sql_mis_inscripciones);
    $cantidad_inscripciones = mysqli_num_rows($resultado_inscripciones);

    $titulo_pagina = 'Mis Cursos - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 
?>

    <main class="container my-5" style="min-height: 70vh;">
        
        <!-- Cabecera de la sección -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-3 border-bottom gap-3">
            <h2 class="fw-bold text-dark mb-0">Gestión de Cursos</h2>
            <a href="nuevoCurso.php" class="btn btn-purple fw-medium shadow-sm">
                <i class="bi bi-plus-lg me-2"></i>Crear Nuevo Curso
            </a>
        </div>

        <!-- Alerta si se supera el límite de inscripciones gratuitas -->
        <?php if(isset($_GET['limite']) && $_GET['limite'] == 'alcanzado'): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>¡Límite alcanzado!</strong> No puedes inscribirte a más de 3 cursos simultáneamente en el Plan Gratuito.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

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
                <?php 
                if($_SESSION['plan'] === "gratis"){

                    $sqlCursosActivos = "
                    SELECT COUNT(id_creador) as total_Cursos FROM cursos
                    INNER JOIN usuarios ON id_creador = id_usuario
                    WHERE id_creador = '$idUsuario' AND (activo = 1 OR programado = 1)
                    ";

                    $resultado = mysqli_query($conexion, $sqlCursosActivos);
                    $contador = mysqli_fetch_array($resultado);

                    echo '
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4 rounded-3" style="background-color: rgba(108, 92, 231, 0.1); color: var(--SECONDARY);">
                        <i class="bi bi-info-circle-fill fs-5 me-3"></i>
                        <div>
                            <strong>Plan Gratuito:</strong> Tienes <strong> '. $contador['total_Cursos'] . ' de 3</strong> cursos activos/programados permitidos. <a href="solicitarPro.php" class="alert-link" style="color: var(--PRIMARY);">Sube a Plan Pro</a> para crear cursos ilimitados.
                        </div>
                    </div>
                    ';
                }
                ?>

                <!-- Grilla de Cursos Dictados -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php
                    $sqlMostrar = "
                    SELECT c.*, 
                           (SELECT COUNT(*) FROM inscripciones i WHERE i.id_curso = c.id_curso AND i.estado = 'aceptada') as total_alumnos
                    FROM cursos c
                    WHERE c.id_creador = '$idUsuario' 
                    ORDER BY c.fecha_creacion DESC
                    ";

                    $resultado = mysqli_query($conexion, $sqlMostrar);

                    if(mysqli_num_rows($resultado) > 0){
                        while($curso = mysqli_fetch_array($resultado)){
                            
                            $es_prog = ($curso['activo'] == 0 && !empty($curso['programado']) && $curso['programado'] == 1);
                            $privacidad = ($curso['es_publico'] == 1) ? "Público" : "Privado";
                            $cupoTexto = $curso['cupo'] ? $curso['cupo'] : 'Sin límite';
                            $tiene_img = (!empty($curso['imagen']) && file_exists('../uploads/cursos/' . $curso['imagen']));

                            if ($tiene_img) {
                                $portada_html = '<div class="card-img-top rounded-top-4 overflow-hidden" style="height: 120px;">
                                                    <img src="../uploads/cursos/' . htmlspecialchars($curso['imagen']) . '" alt="' . htmlspecialchars($curso['titulo']) . '" class="w-100 h-100" style="object-fit: cover;">
                                                 </div>';
                            } elseif ($curso['activo'] == 1 || $es_prog) {
                                $clase_grad = 'curso-gradiente-' . (($curso['id_curso'] % 5) + 1);
                                $portada_html = '<div class="card-img-top rounded-top-4 ' . $clase_grad . '" style="height: 120px;"></div>';
                            } else {
                                $portada_html = '<div class="card-img-top rounded-top-4" style="height: 120px; background: linear-gradient(45deg, #6c757d, #adb5bd);"></div>';
                            }

                            if ($curso['activo'] == 1) {
                                $estado_texto = 'Activo';
                                $badge_estado = 'bg-success';
                                $texto_boton = 'Administrar Curso';
                            } elseif ($es_prog) {
                                $estado_texto = 'Inicia ' . date("d/m/Y", strtotime($curso['fecha_inicio']));
                                $badge_estado = 'bg-info text-dark';
                                $texto_boton = 'Administrar Curso';
                            } else {
                                $estado_texto = 'Borrador';
                                $badge_estado = 'bg-secondary';
                                $texto_boton = 'Editar Borrador';
                            }

                            echo '<div class="col">
                                <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                                    ' . $portada_html . '
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge ' . $badge_estado . '">' . $estado_texto .'</span>
                                            <span class="badge bg-light text-dark border">' . $privacidad .'</span>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">'. htmlspecialchars($curso['titulo']) .'</h5>
                                        <p class="text-muted small mb-3"><i class="bi bi-people-fill me-1"></i>'. $curso['total_alumnos'] .' / '. $cupoTexto .' Alumnos</p>
                                        <a href="editarCurso.php?id='. $curso['id_curso'] .'" class="btn btn-outline-secondary w-100 fw-medium">' . $texto_boton . '</a>
                                    </div>
                                </div>
                            </div>';
                        }
                    }else{
                        echo '<div class="col-12 text-center p-5 bg-light rounded-4 border border-dashed w-100">
                                    <i class="bi bi-journal-x display-1 text-muted mb-3"></i>
                                    <h5 class="fw-bold text-dark">Aún no has creado cursos</h5>
                                </div>';
                    }
                    ?>
                </div>
            </div>

            <!-- PESTAÑA 2: MIS INSCRIPCIONES -->
            <div class="tab-pane fade" id="inscriptos" role="tabpanel" tabindex="0">
                
                <!-- Aviso de Límite (RN05) dinámico -->
                <?php if($_SESSION['plan'] === "gratis"): ?>
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 rounded-3">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                        <div>
                            <strong>Plan Gratuito:</strong> Tienes <strong><?php echo $cantidad_inscripciones; ?> de 3</strong> inscripciones activas simultáneas. <a href="solicitarPro.php" class="alert-link" style="color: var(--PRIMARY);">Sube a Plan Pro</a> para inscribirte a cursos ilimitados. 
                            <?php if($cantidad_inscripciones >= 3) echo "Has alcanzado tu límite."; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Grilla de Cursos Inscriptos -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    
                    <?php if ($cantidad_inscripciones > 0): ?>
                        
                        <?php while ($inscripcion = mysqli_fetch_assoc($resultado_inscripciones)): ?>
                            <?php 
                                $tiene_img_ins = (!empty($inscripcion['imagen']) && file_exists('../uploads/cursos/' . $inscripcion['imagen']));
                                $clase_grad_ins = $tiene_img_ins ? '' : 'curso-gradiente-' . (($inscripcion['id_curso'] % 5) + 1);
                                $es_prog_ins = ($inscripcion['activo'] == 0 && !empty($inscripcion['programado']) && $inscripcion['programado'] == 1);
                            ?>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                                    <div class="card-img-top rounded-top-4 overflow-hidden <?php echo $clase_grad_ins; ?>" style="height: 120px;">
                                        <?php if ($tiene_img_ins): ?>
                                            <img src="../uploads/cursos/<?php echo htmlspecialchars($inscripcion['imagen']); ?>" alt="<?php echo htmlspecialchars($inscripcion['titulo']); ?>" class="w-100 h-100" style="object-fit: cover;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <?php if ($inscripcion['estado_inscripcion'] === 'aceptada'): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Inscripto</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pendiente</span>
                                            <?php endif; ?>

                                            <?php if ($es_prog_ins): ?>
                                                <span class="badge bg-info text-dark"><i class="bi bi-calendar-event me-1"></i>Inicia <?php echo date("d/m/Y", strtotime($inscripcion['fecha_inicio'])); ?></span>
                                            <?php endif; ?>

                                            <?php if ($inscripcion['es_pago'] == 1): ?>
                                                <span class="badge bg-warning text-dark">Pago</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Gratuito</span>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($inscripcion['titulo']); ?></h5>
                                        <p class="text-muted small mb-3">Docente: <span class="fw-medium"><?php echo htmlspecialchars($inscripcion['profe_nombre'] . ' ' . $inscripcion['profe_apellido']); ?></span></p>
                                        <a href="curso.php?id=<?php echo $inscripcion['id_curso']; ?>" class="btn btn-purple w-100 fw-medium">Entrar al Curso</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <div class="col-12 text-center p-5 bg-light rounded-4 border border-dashed w-100">
                            <i class="bi bi-journal-plus display-1 text-muted mb-3"></i>
                            <h5 class="fw-bold text-dark">Aún no estás inscripto en ningún curso</h5>
                            <p class="text-muted mb-4">Explora el catálogo y comienza a aprender hoy mismo.</p>
                            <a href="../index.php" class="btn btn-outline-primary px-4 py-2 fw-medium">Ir al Catálogo</a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </main>

<?php require_once 'footer.php'; ?>