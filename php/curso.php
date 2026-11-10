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

    // =========================================================
    // LÓGICA DE SUBIDA Y ELIMINACIÓN DE MATERIALES (SÓLO DOCENTE)
    // =========================================================

    // A. Subir Material
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_material']) && $_POST['accion_material'] === 'subir' && $es_creador) {
        if (isset($_FILES['archivo_material']) && $_FILES['archivo_material']['error'] === UPLOAD_ERR_OK) {
            $tamano = $_FILES['archivo_material']['size'];
            
            // Límite de 5 MB
            if ($tamano <= 5242880) {
                $nombre_original = mysqli_real_escape_string($conexion, $_FILES['archivo_material']['name']);
                $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                
                $dir = '../uploads/materiales/';
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                
                $nombre_guardado = 'mat_' . $id_curso . '_' . time() . '.' . $extension;
                $ruta_relativa = 'uploads/materiales/' . $nombre_guardado;
                $ruta_destino = '../' . $ruta_relativa;
                
                if (move_uploaded_file($_FILES['archivo_material']['tmp_name'], $ruta_destino)) {
                    $sql_ins = "INSERT INTO materiales (id_curso, nombre_archivo, ruta_archivo, tamano_bytes) 
                                VALUES ($id_curso, '$nombre_original', '$ruta_relativa', $tamano)";
                    mysqli_query($conexion, $sql_ins);
                    header("Location: curso.php?id=$id_curso&mat=exito");
                    exit();
                }
            } else {
                header("Location: curso.php?id=$id_curso&mat=exceso_tamano");
                exit();
            }
        }
    }

    // B. Eliminar Material
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_material']) && $_POST['accion_material'] === 'eliminar' && $es_creador) {
        $id_material = (int)$_POST['id_material'];
        $sql_mat_del = "SELECT ruta_archivo FROM materiales WHERE id_material = $id_material AND id_curso = $id_curso";
        $res_mat_del = mysqli_query($conexion, $sql_mat_del);
        
        if ($row_del = mysqli_fetch_assoc($res_mat_del)) {
            $file_path = '../' . $row_del['ruta_archivo'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            mysqli_query($conexion, "DELETE FROM materiales WHERE id_material = $id_material");
        }
        header("Location: curso.php?id=$id_curso&mat=eliminado");
        exit();
    }

    // 5. Consultar los materiales adjuntos reales de la tabla
    $sql_materiales = "SELECT * FROM materiales WHERE id_curso = $id_curso ORDER BY fecha_subida DESC";
    $resultado_materiales = mysqli_query($conexion, $sql_materiales);
    $total_materiales = mysqli_num_rows($resultado_materiales);

    // Función auxiliar para formatear los bytes
    function formatearBytes($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    $titulo_pagina = $curso['titulo'] . ' - Cursolandia';
    $ruta = '../';
    require_once 'header.php' ;
?>

    <main class="container my-5">
        
        <!-- Alertas de estado de subida/eliminación -->
        <?php if(isset($_GET['mat'])): ?>
            <?php if($_GET['mat'] == 'exito'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>Material subido correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['mat'] == 'exceso_tamano'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>El archivo supera el límite permitido (5 MB).
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['mat'] == 'eliminado'): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>Material eliminado.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

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
                <h1 class="fw-bold text-white mb-2"><?php echo htmlspecialchars($curso['titulo']); ?></h1>
                
                <!-- Creador -->
                <p class="text-white mb-0 fs-5" style="opacity: 0.9;">
                    Creado por: <span class="fw-bold text-white"><?php echo htmlspecialchars($curso['nombre'] . ' ' . $curso['apellido']); ?></span>
                </p>
                
            </div>
        </div>

        <div class="row g-5">
            
            <!-- COLUMNA IZQUIERDA: Descripción y Foro -->
            <div class="col-lg-8">
                
                <!-- Sección Descripción -->
                <section class="mb-5">
                    <h4 class="fw-bold mb-3">Acerca de este curso</h4>
                    <p class="text-secondary" style="line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                    
                    <div class="d-flex gap-2 mt-3">
                        <span class="badge bg-light text-secondary border">#Programación</span>
                        <span class="badge bg-light text-secondary border">#Cursolandia</span>
                    </div>
                </section>

                <!-- Sección Foro de Preguntas -->
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
                            <a href="editarCurso.php?id=<?php echo $id_curso; ?>" class="btn btn-outline-primary w-100 fw-medium">Editar Datos del Curso</a>
                        <?php elseif($esta_inscripto): ?>
                            <form action="procesarInscripcion.php" method="POST">
                                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                                <input type="hidden" name="accion" value="abandonar">
                                <button type="submit" class="btn btn-outline-danger w-100 fw-medium">Abandonar Curso</button>
                            </form>
                        <?php else: ?>
                            <form action="procesarInscripcion.php" method="POST">
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
                        <span class="badge bg-light text-secondary border"><?php echo $total_materiales; ?> Archivos</span>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if($total_materiales > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while($material = mysqli_fetch_assoc($resultado_materiales)): ?>
                                    <?php 
                                        $ext = strtolower(pathinfo($material['nombre_archivo'], PATHINFO_EXTENSION));
                                        $icono = 'bi-file-earmark-text-fill text-primary';
                                        if($ext === 'pdf') $icono = 'bi-file-earmark-pdf-fill text-danger';
                                        elseif(in_array($ext, ['zip', 'rar', '7z'])) $icono = 'bi-file-earmark-zip-fill text-warning';
                                        elseif(in_array($ext, ['jpg', 'png', 'jpeg'])) $icono = 'bi-file-earmark-image-fill text-success';
                                    ?>
                                    
                                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                        <div class="d-flex align-items-center gap-3 text-truncate">
                                            <i class="bi <?php echo $icono; ?> fs-4"></i>
                                            <div class="text-truncate">
                                                <!-- Solo inscriptos o el creador pueden descargar -->
                                                <?php if($esta_inscripto || $es_creador): ?>
                                                    <a href="../<?php echo $material['ruta_archivo']; ?>" download class="mb-0 fw-medium text-dark text-decoration-none d-block text-truncate" style="font-size: 0.9rem;">
                                                        <?php echo htmlspecialchars($material['nombre_archivo']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <p class="mb-0 fw-medium text-secondary text-truncate" style="font-size: 0.9rem;" title="Inscríbete para descargar">
                                                        <?php echo htmlspecialchars($material['nombre_archivo']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <small class="text-muted"><?php echo formatearBytes($material['tamano_bytes']); ?></small>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <?php if($esta_inscripto || $es_creador): ?>
                                                <a href="../<?php echo $material['ruta_archivo']; ?>" download class="btn btn-sm btn-light text-muted border-0">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            <?php endif; ?>

                                            <!-- Botón de eliminar exclusivo para el creador -->
                                            <?php if($es_creador): ?>
                                                <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST" onsubmit="return confirm('¿Eliminar este material?');">
                                                    <input type="hidden" name="accion_material" value="eliminar">
                                                    <input type="hidden" name="id_material" value="<?php echo $material['id_material']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                Aún no hay materiales disponibles.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($es_creador): ?>
                    <!-- Botón para abrir el Modal de Subida -->
                    <div class="card-footer bg-white border-top p-3 text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary w-100 fw-medium" data-bs-toggle="modal" data-bs-target="#modalSubirMaterial" style="color: var(--PRIMARY); border-color: var(--PRIMARY);">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Subir Material
                        </button>
                        <div class="form-text mt-2" style="font-size: 0.75rem;">Límite: 5MB por archivo.</div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <!-- MODAL PARA SUBIR MATERIAL -->
    <?php if($es_creador): ?>
    <div class="modal fade" id="modalSubirMaterial" tabindex="-1" aria-labelledby="modalSubirMaterialLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-dark" id="modalSubirMaterialLabel">
                        <i class="bi bi-cloud-arrow-up color-logo me-2"></i>Subir Archivo Adjunto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion_material" value="subir">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="archivo_material" class="form-label fw-medium text-dark">Seleccionar Archivo (PDF, ZIP, PNG, JPG)</label>
                            <input type="file" class="form-control custom-input" id="archivo_material" name="archivo_material" required>
                            <div class="form-text">Tamaño máximo permitido: 5 MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-3">
                        <button type="button" class="btn btn-light border fw-medium" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-purple fw-bold px-4">Subir Archivo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php require_once 'footer.php'; ?>