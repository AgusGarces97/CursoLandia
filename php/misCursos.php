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
        SELECT c.*, u.nombre as profe_nombre, u.apellido as profe_apellido 
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
                    WHERE id_creador = '$idUsuario' AND activo=1
                    ";

                    $resultado = mysqli_query($conexion, $sqlCursosActivos);
                    $contador = mysqli_fetch_array($resultado);

                    echo '
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4 rounded-3" style="background-color: rgba(108, 92, 231, 0.1); color: var(--SECONDARY);">
                        <i class="bi bi-info-circle-fill fs-5 me-3"></i>
                        <div>
                            <strong>Plan Gratuito:</strong> Tienes <strong> '. $contador['total_Cursos'] . ' de 3</strong> cursos activos permitidos. <a href="solicitarPro.php" class="alert-link" style="color: var(--PRIMARY);">Sube a Plan Pro</a> para crear cursos ilimitados.
                        </div>
                    </div>
                    ';
                }
                ?>

                <!-- Grilla de Cursos Dictados -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php
                    // Consulta con subconsulta para calcular total de alumnos por curso de forma real
                    $sqlMostrar = "
                    SELECT c.*, 
                           (SELECT COUNT(*) FROM inscripciones i WHERE i.id_curso = c.id_curso) as total_alumnos
                    FROM cursos c
                    WHERE c.id_creador = '$idUsuario' 
                    ORDER BY c.fecha_creacion DESC
                    ";

                    $resultado = mysqli_query($conexion, $sqlMostrar);

                    if(mysqli_num_rows($resultado) > 0){
                        while($curso = mysqli_fetch_array($resultado)){
                            
                            $activo = ($curso['activo'] == 1) ? "Activo" : "Inactivo";
                            $privacidad = ($curso['es_publico'] == 1) ? "Público" : "Privado";
                            $cupoTexto = $curso['cupo'] ? $curso['cupo'] : 'Sin límite';

                            if($curso['activo'] == 1){
                                echo '<div class="col">
                                    <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                                        <div class="card-img-top rounded-top-4" style="height: 120px; background: linear-gradient(45deg, var(--PRIMARY), var(--TERCIARY));"></div>
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-success">' . $activo .'</span>
                                                <span class="badge bg-light text-dark border">' . $privacidad .'</span>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-1">'. htmlspecialchars($curso['titulo']) .'</h5>
                                            <p class="text-muted small mb-3"><i class="bi bi-people-fill me-1"></i>'. $curso['total_alumnos'] .' / '. $cupoTexto .' Alumnos</p>
                                            <a href="editarCurso.php?id='. $curso['id_curso'] .'" class="btn btn-outline-secondary w-100 fw-medium">Administrar Curso</a>
                                        </div>
                                    </div>
                                </div>';
                            }else{
                                echo '<div class="col">
                                    <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                                        <div class="card-img-top rounded-top-4" style="height: 120px; background: linear-gradient(45deg, #6c757d, #adb5bd);"></div>
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-secondary">' . $activo . '</span>
                                                <span class="badge bg-light text-dark border"> ' . $privacidad . '</span>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-1">' . htmlspecialchars($curso['titulo']) . '</h5>
                                            <p class="text-muted small mb-3"><i class="bi bi-people-fill me-1"></i>'. $curso['total_alumnos'] .' / '. $cupoTexto .' Alumnos</p>
                                            <a href="editarCurso.php?id='. $curso['id_curso'] .'" class="btn btn-outline-secondary w-100 fw-medium">Editar Borrador</a>
                                        </div>
                                    </div>
                                </div>';
                            }
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
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm rounded-4 curso-card-hover">
                                    <div class="card-img-top rounded-top-4" style="height: 120px; background: linear-gradient(45deg, #0dcaf0, #0d6efd);"></div>
                                    <div class="card-body p-4">
                                        <div class="mb-2">
                                            <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Inscripto</span>
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