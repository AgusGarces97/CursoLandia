<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: misCursos.php");
    exit();
}

$id_curso = (int)$_GET['id'];
$id_creador = (int)$_SESSION['id_usuario'];
$mensaje = '';

$sql_check = "SELECT * FROM cursos WHERE id_curso = $id_curso AND id_creador = $id_creador";
$res_check = mysqli_query($conexion, $sql_check);

if (mysqli_num_rows($res_check) == 0) {
    header("Location: misCursos.php");
    exit();
}

$curso = mysqli_fetch_assoc($res_check);

// =========================================================
// GESTIÓN DE SOLICITUDES DE ALUMNOS (ACEPTAR / RECHAZAR)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_alumno'])) {
    $id_inscripcion = (int)$_POST['id_inscripcion'];
    $accion_alumno = $_POST['accion_alumno'];

    // Verificamos que la inscripción corresponda a este curso
    $sql_check_ins = "SELECT i.*, u.nombre, u.apellido FROM inscripciones i INNER JOIN usuarios u ON i.id_alumno = u.id_usuario WHERE i.id_inscripcion = $id_inscripcion AND i.id_curso = $id_curso";
    $res_check_ins = mysqli_query($conexion, $sql_check_ins);

    if ($row_ins = mysqli_fetch_assoc($res_check_ins)) {
        $id_alumno_target = (int)$row_ins['id_alumno'];
        $nombre_alumno = $row_ins['nombre'] . ' ' . $row_ins['apellido'];
        $titulo_curso_esc = mysqli_real_escape_string($conexion, $curso['titulo']);

        if ($accion_alumno === 'aceptar') {
            mysqli_query($conexion, "UPDATE inscripciones SET estado = 'aceptada' WHERE id_inscripcion = $id_inscripcion");

            // Notificación al alumno
            $titulo_notif = mysqli_real_escape_string($conexion, "¡Inscripción Aceptada!");
            $msj_notif = mysqli_real_escape_string($conexion, "Tu solicitud de inscripción al curso '" . $curso['titulo'] . "' ha sido aceptada por el docente.");
            mysqli_query($conexion, "
                INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                VALUES ($id_alumno_target, '$titulo_notif', '$msj_notif', 'plan_pro', 0, NOW())
            ");

            header("Location: editarCurso.php?id=$id_curso&alumno=aceptado");
            exit();

        } elseif ($accion_alumno === 'rechazar') {
            mysqli_query($conexion, "DELETE FROM inscripciones WHERE id_inscripcion = $id_inscripcion");

            // Notificación al alumno
            $titulo_notif = mysqli_real_escape_string($conexion, "Solicitud de Inscripción Rechazada");
            $msj_notif = mysqli_real_escape_string($conexion, "Tu solicitud para sumarte al curso '" . $curso['titulo'] . "' fue desestimada por el docente.");
            mysqli_query($conexion, "
                INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                VALUES ($id_alumno_target, '$titulo_notif', '$msj_notif', 'foro_eliminada', 0, NOW())
            ");

            header("Location: editarCurso.php?id=$id_curso&alumno=rechazado");
            exit();
        }
    }
}

// Valores iniciales para el formulario
$titulo_val = $curso['titulo'];
$descripcion_val = $curso['descripcion'];
$es_publico_val = (int)$curso['es_publico'];
$tipo_costo_val = (string)(int)$curso['es_pago'];
$costo_monto_val = ((float)$curso['costo'] > 0) ? number_format((float)$curso['costo'], 2, '.', '') : '';
$cupo_val = $curso['cupo'] ?? '';
$fechaInicio_val = $curso['fecha_inicio'];
$fechaFin_val = $curso['fecha_fin'];
$activo_val = ($curso['activo'] == 1 || (!empty($curso['programado']) && $curso['programado'] == 1)) ? 1 : 0;

// =========================================================
// ACTUALIZAR DATOS DEL CURSO Y ETIQUETAS
// =========================================================
if (isset($_POST['botonEditarCurso'])) {
    
    $titulo_raw = trim($_POST['titulo'] ?? '');
    $descripcion_raw = trim($_POST['descripcion'] ?? '');
    $es_publico_val = (int)($_POST['privacidad'] ?? 1);
    $tipo_costo_val = $_POST['tipo_costo'] ?? '0';
    $es_pago = ((int)$tipo_costo_val === 1) ? 1 : 0;
    $costo_monto_val = trim($_POST['costo_monto'] ?? '');
    $costo_normalizado = str_replace(',', '.', $costo_monto_val);
    $cupo_val = trim($_POST['cupo'] ?? '');
    $fechaInicio_val = trim($_POST['fechaInicio'] ?? '');
    $fechaFin_val = trim($_POST['fechaFin'] ?? '');
    $activo_val = isset($_POST['estadoCurso']) ? 1 : 0;

    $titulo_val = $titulo_raw;
    $descripcion_val = $descripcion_raw;

    $hoy = date('Y-m-d');
    if ($activo_val === 1 && $fechaInicio_val > $hoy) {
        $activo_bd = 0;
        $programado_bd = 1;
    } elseif ($activo_val === 1) {
        $activo_bd = 1;
        $programado_bd = 0;
    } else {
        $activo_bd = 0;
        $programado_bd = 0;
    }

    $puede_guardar = true;

    if ($titulo_raw === '' || $descripcion_raw === '' || $fechaInicio_val === '' || $fechaFin_val === '') {
        $puede_guardar = false;
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>Todos los campos obligatorios deben estar completos.
                    </div>";
    } elseif ($es_pago === 1 && ($costo_normalizado === '' || !is_numeric($costo_normalizado) || (float)$costo_normalizado <= 0)) {
        $puede_guardar = false;
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>Debes ingresar un precio válido mayor a $0 para un curso de pago.
                    </div>";
    } elseif ($fechaFin_val < $fechaInicio_val) {
        $puede_guardar = false;
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>La fecha de fin no puede ser anterior a la fecha de inicio.
                    </div>";
    }

    if ($puede_guardar && $_SESSION['plan'] === 'gratis' && $activo_val == 1) {
        $sql_cant = "SELECT COUNT(*) as total_activos FROM cursos WHERE id_creador = $id_creador AND (activo = 1 OR programado = 1) AND id_curso != $id_curso";
        $res_cant = mysqli_query($conexion, $sql_cant);
        $fila_cant = mysqli_fetch_assoc($res_cant);

        if ($fila_cant['total_activos'] >= 3) {
            $puede_guardar = false;
            $mensaje = "<div class='alert alert-warning text-center fw-medium mb-4' role='alert'>
                            <i class='bi bi-exclamation-triangle-fill me-2'></i>
                            Límite alcanzado: Ya tienes 3 cursos activos o programados en tu Plan Gratuito. 
                            Guárdalo como borrador desmarcando 'Publicar como Activo' o solicita el Plan Pro.
                        </div>";
        }
    }

    // Procesamiento opcional de imagen de portada (reemplazar o quitar)
    $imagen_actual = $curso['imagen'] ?? null;
    $nueva_imagen = $imagen_actual;
    $imagen_reemplazada = false;

    if ($puede_guardar) {
        if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
            $nueva_imagen = null;
        }

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                $puede_guardar = false;
                $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                                <i class='bi bi-exclamation-triangle-fill me-2'></i>Error al subir la imagen de portada (verifica que no supere el límite del servidor).
                            </div>";
            } else {
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $permitidas)) {
                    $puede_guardar = false;
                    $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                                    <i class='bi bi-exclamation-triangle-fill me-2'></i>Formato de imagen no válido. Solo se permiten JPG, PNG o WEBP.
                                </div>";
                } elseif ($_FILES['imagen']['size'] > 2097152) { // 2 MB
                    $puede_guardar = false;
                    $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                                    <i class='bi bi-exclamation-triangle-fill me-2'></i>La imagen de portada no puede superar los 2 MB.
                                </div>";
                } else {
                    $dir_cursos = '../uploads/cursos/';
                    if (!file_exists($dir_cursos)) {
                        mkdir($dir_cursos, 0777, true);
                    }
                    $nombre_subido = 'curso_' . $id_creador . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir_cursos . $nombre_subido)) {
                        $nueva_imagen = $nombre_subido;
                        $imagen_reemplazada = true;
                    } else {
                        $puede_guardar = false;
                        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                                        <i class='bi bi-exclamation-triangle-fill me-2'></i>No se pudo guardar la imagen de portada en el servidor.
                                    </div>";
                    }
                }
            }
        }
    }

    if ($puede_guardar) {
        $titulo = mysqli_real_escape_string($conexion, $titulo_raw);
        $descripcion = mysqli_real_escape_string($conexion, $descripcion_raw);
        $costo_final = ($es_pago === 1) ? number_format((float)$costo_normalizado, 2, '.', '') : '0.00';
        $cupo = ($cupo_val !== '') ? (int)$cupo_val : "NULL";
        $fechaInicio = mysqli_real_escape_string($conexion, $fechaInicio_val);
        $fechaFin = mysqli_real_escape_string($conexion, $fechaFin_val);
        $imagen_sql = $nueva_imagen ? "'" . mysqli_real_escape_string($conexion, $nueva_imagen) . "'" : "NULL";

        $sql_update = "
            UPDATE cursos SET 
                titulo = '$titulo',
                descripcion = '$descripcion',
                es_publico = $es_publico_val,
                es_pago = $es_pago,
                costo = $costo_final,
                imagen = $imagen_sql,
                cupo = $cupo,
                fecha_inicio = '$fechaInicio',
                fecha_fin = '$fechaFin',
                activo = $activo_bd,
                programado = $programado_bd
            WHERE id_curso = $id_curso AND id_creador = $id_creador
        ";

        if (mysqli_query($conexion, $sql_update)) {
            
            // Si había una imagen anterior y fue reemplazada o quitada, borramos el archivo viejo
            if (!empty($imagen_actual) && $imagen_actual !== $nueva_imagen && file_exists('../uploads/cursos/' . $imagen_actual)) {
                unlink('../uploads/cursos/' . $imagen_actual);
            }

            // ACTUALIZACIÓN DE ETIQUETAS DEL CURSO
            mysqli_query($conexion, "DELETE FROM curso_etiquetas WHERE id_curso = $id_curso");
            if (isset($_POST['etiquetas']) && is_array($_POST['etiquetas'])) {
                foreach ($_POST['etiquetas'] as $nombreEtiqueta) {
                    $nombreLimpio = mysqli_real_escape_string($conexion, trim(str_replace('#', '', $nombreEtiqueta)));
                    if (!empty($nombreLimpio)) {
                        $res_tag = mysqli_query($conexion, "SELECT id_etiqueta FROM etiquetas WHERE LOWER(nombre) = LOWER('$nombreLimpio')");
                        if (mysqli_num_rows($res_tag) > 0) {
                            $id_etiqueta = mysqli_fetch_assoc($res_tag)['id_etiqueta'];
                        } else {
                            mysqli_query($conexion, "INSERT INTO etiquetas (nombre) VALUES ('$nombreLimpio')");
                            $id_etiqueta = mysqli_insert_id($conexion);
                        }
                        mysqli_query($conexion, "INSERT IGNORE INTO curso_etiquetas (id_curso, id_etiqueta) VALUES ($id_curso, $id_etiqueta)");
                    }
                }
            }

            $mensaje = "<div class='alert alert-success text-center fw-medium mb-4' role='alert'>
                            <i class='bi bi-check-circle-fill me-2'></i>¡Curso actualizado exitosamente!
                        </div>";
            
            $res_check = mysqli_query($conexion, $sql_check);
            $curso = mysqli_fetch_assoc($res_check);
            $titulo_val = $curso['titulo'];
            $descripcion_val = $curso['descripcion'];
            $es_publico_val = (int)$curso['es_publico'];
            $tipo_costo_val = (string)(int)$curso['es_pago'];
            $costo_monto_val = ((float)$curso['costo'] > 0) ? number_format((float)$curso['costo'], 2, '.', '') : '';
            $cupo_val = $curso['cupo'] ?? '';
            $fechaInicio_val = $curso['fecha_inicio'];
            $fechaFin_val = $curso['fecha_fin'];
            $activo_val = ($curso['activo'] == 1 || (!empty($curso['programado']) && $curso['programado'] == 1)) ? 1 : 0;
        } else {
            // Si falló el UPDATE y se había subido una imagen nueva, la limpiamos
            if ($imagen_reemplazada && $nueva_imagen && file_exists('../uploads/cursos/' . $nueva_imagen)) {
                unlink('../uploads/cursos/' . $nueva_imagen);
            }
            $mensaje = "<div class='alert alert-danger text-center mb-4' role='alert'>Error: " . mysqli_error($conexion) . "</div>";
        }
    }
}

// Alumnos inscriptos y solicitudes
$sql_alumnos = "
    SELECT i.*, u.nombre, u.apellido, u.email, u.dni
    FROM inscripciones i
    INNER JOIN usuarios u ON i.id_alumno = u.id_usuario
    WHERE i.id_curso = $id_curso
    ORDER BY i.estado DESC, i.fecha_inscripcion DESC
";
$resultado_alumnos = mysqli_query($conexion, $sql_alumnos);
$total_alumnos = mysqli_num_rows($resultado_alumnos);

// Conteo específico de confirmados para el cupo
$sql_cant_aceptados = "SELECT COUNT(*) as total FROM inscripciones WHERE id_curso = $id_curso AND estado = 'aceptada'";
$res_cant_aceptados = mysqli_query($conexion, $sql_cant_aceptados);
$total_aceptados = mysqli_fetch_assoc($res_cant_aceptados)['total'];

// Etiquetas asociadas a este curso
$sql_tags_curso = "
    SELECT e.nombre 
    FROM curso_etiquetas ce 
    INNER JOIN etiquetas e ON ce.id_etiqueta = e.id_etiqueta 
    WHERE ce.id_curso = $id_curso 
    ORDER BY e.nombre ASC
";
$res_tags_curso = mysqli_query($conexion, $sql_tags_curso);

// Catálogo completo de etiquetas
$sql_todas_etiquetas = "SELECT * FROM etiquetas ORDER BY nombre ASC";
$res_todas_etiquetas = mysqli_query($conexion, $sql_todas_etiquetas);

$titulo_pagina = 'Editar Curso - Cursolandia';
$ruta = '../'; 
require_once 'header.php'; 
?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <?php echo $mensaje; ?>

                <?php if (isset($_GET['alumno']) && $_GET['alumno'] === 'aceptado'): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>Inscripción del alumno aceptada y notificada correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif (isset($_GET['alumno']) && $_GET['alumno'] === 'rechazado'): ?>
                    <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>Solicitud rechazada y notificada al alumno.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0 rounded-4 mb-5">
                    
                    <div class="card-header bg-white border-bottom py-4 px-4 px-lg-5 d-flex justify-content-between align-items-center">
                        <h3 class="fw-bold mb-0 text-dark">Editar Curso</h3>
                        <a href="curso.php?id=<?php echo $id_curso; ?>" class="btn btn-outline-primary btn-sm fw-medium">
                            <i class="bi bi-eye me-1"></i>Ver Vista Pública
                        </a>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        <form action="editarCurso.php?id=<?php echo $id_curso; ?>" method="POST" enctype="multipart/form-data">
                            
                            <div class="row g-4">
                                
                                <div class="col-md-7">
                                    <div class="mb-4">
                                        <label for="titulo" class="form-label fw-medium text-dark">Título del Curso <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control custom-input" id="titulo" name="titulo" maxlength="100" value="<?php echo htmlspecialchars($titulo_val); ?>" required>
                                        <div class="form-text">Máximo 100 caracteres.</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="descripcion" class="form-label fw-medium text-dark">Descripción <span class="text-danger">*</span></label>
                                        <textarea class="form-control custom-input" id="descripcion" name="descripcion" rows="6" maxlength="1000" required><?php echo htmlspecialchars($descripcion_val); ?></textarea>
                                        <div class="form-text">Máximo 1000 caracteres.</div>
                                    </div>

                                    <!-- Buscador y Selección de Etiquetas -->
                                    <div class="mb-3">
                                        <label for="buscadorEtiquetas" class="form-label fw-medium text-dark">Etiquetas (Opcional)</label>
                                        <input type="text" class="form-control custom-input mb-2" id="buscadorEtiquetas" list="listaSugerencias" placeholder="Añadir etiquetas (ej: #Programación)..." autocomplete="off">
                                        <datalist id="listaSugerencias">
                                            <?php while($tag_sug = mysqli_fetch_assoc($res_todas_etiquetas)): ?>
                                                <option value="<?php echo htmlspecialchars($tag_sug['nombre']); ?>"></option>
                                            <?php endwhile; ?>
                                        </datalist>

                                        <div id="contenedorEtiquetas" class="border rounded-3 p-3 bg-light d-flex flex-wrap gap-2" style="min-height: 80px;">
                                            <?php while($tag_c = mysqli_fetch_assoc($res_tags_curso)): ?>
                                                <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1 item-etiqueta">
                                                    #<?php echo htmlspecialchars($tag_c['nombre']); ?>
                                                    <input type="hidden" name="etiquetas[]" value="<?php echo htmlspecialchars($tag_c['nombre']); ?>">
                                                    <button type="button" class="btn-close btn-remover-etiqueta" style="font-size: 0.5rem;" aria-label="Remover"></button>
                                                </span>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    
                                    <div class="mb-4">
                                        <label for="imagen" class="form-label fw-medium text-dark">Imagen de Portada</label>
                                        <?php if (!empty($curso['imagen'])): ?>
                                            <div class="mb-2">
                                                <img src="../uploads/cursos/<?php echo htmlspecialchars($curso['imagen']); ?>" alt="Portada actual" class="img-fluid rounded-3 border w-100" style="max-height: 150px; object-fit: cover;">
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="eliminar_imagen" name="eliminar_imagen" value="1">
                                                <label class="form-check-label small text-danger" for="eliminar_imagen">Quitar imagen actual (usar color por defecto)</label>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control custom-input" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                        <div class="form-text">JPG, PNG o WEBP (máx. 2 MB). Deja vacío para conservar la actual.</div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="privacidad" class="form-label fw-medium text-dark">Privacidad</label>
                                            <select class="form-select custom-input" id="privacidad" name="privacidad">
                                                <option value="1" <?php echo ($es_publico_val == 1) ? 'selected' : ''; ?>>Público</option>
                                                <option value="0" <?php echo ($es_publico_val == 0) ? 'selected' : ''; ?>>Privado</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label for="tipo_costo" class="form-label fw-medium text-dark">Modalidad</label>
                                            <select class="form-select custom-input" id="tipo_costo" name="tipo_costo">
                                                <option value="0" <?php echo ($tipo_costo_val == '0') ? 'selected' : ''; ?>>Gratuito</option>
                                                <option value="1" <?php echo ($tipo_costo_val == '1') ? 'selected' : ''; ?>>De Pago</option>
                                            </select>
                                        </div>
                                        <div class="col-12" id="bloquePrecio" style="<?php echo ($tipo_costo_val == '1') ? '' : 'display: none;'; ?>">
                                            <label for="costo_monto" class="form-label fw-medium text-dark">Precio del Curso (ARS) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light fw-bold">$</span>
                                                <input type="number" class="form-control custom-input" id="costo_monto" name="costo_monto" min="1" step="0.01" value="<?php echo htmlspecialchars($costo_monto_val); ?>" placeholder="Ej: 15000.00" <?php echo ($tipo_costo_val == '1') ? 'required' : ''; ?>>
                                            </div>
                                            <div class="form-text">Indica el valor que abonarán los estudiantes para inscribirse.</div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="cupo" class="form-label fw-medium text-dark">Cupo máximo (Opcional)</label>
                                        <input type="number" class="form-control custom-input" id="cupo" name="cupo" min="1" value="<?php echo htmlspecialchars($cupo_val); ?>" placeholder="Ej: 30 (Dejar vacío si es sin límite)">
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="fechaInicio" class="form-label fw-medium text-dark">Inicio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaInicio" name="fechaInicio" value="<?php echo htmlspecialchars($fechaInicio_val); ?>" required>
                                        </div>
                                        <div class="col-6">
                                            <label for="fechaFin" class="form-label fw-medium text-dark">Fin <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaFin" name="fechaFin" value="<?php echo htmlspecialchars($fechaFin_val); ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-3 p-3 bg-light rounded border">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="estadoCurso" name="estadoCurso" value="1" <?php echo ($activo_val == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-medium text-dark" for="estadoCurso">Publicar como Activo</label>
                                        </div>
                                        <div class="form-text small mb-0">Si la fecha de inicio es futura, se activará automáticamente ese día.</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <div class="d-flex justify-content-end gap-3">
                                <a href="misCursos.php" class="btn btn-light fw-medium px-4 text-muted border">Cancelar</a>
                                <button type="submit" class="btn btn-purple fw-medium px-4" name="botonEditarCurso">Guardar Cambios</button>
                            </div>
                            
                        </form>
                    </div>
                </div>

                <!-- Nómina de Alumnos y Gestión de Solicitudes -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-4 px-4 px-lg-5 d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-people-fill color-logo me-2"></i>Nómina de Alumnos
                        </h4>
                        <span class="badge bg-purple px-3 py-2 fs-6">
                            <?php echo $total_aceptados; ?> / <?php echo $curso['cupo'] ? $curso['cupo'] : 'Sin límite'; ?> confirmados
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <?php if ($total_alumnos > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">#</th>
                                            <th>Alumno</th>
                                            <th>DNI</th>
                                            <th>Email</th>
                                            <th>Estado</th>
                                            <th class="pe-4 text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $num = 1;
                                        while ($alumno = mysqli_fetch_assoc($resultado_alumnos)): 
                                            $estado_ins = $alumno['estado'];
                                        ?>
                                            <tr>
                                                <td class="ps-4 fw-medium text-muted"><?php echo $num++; ?></td>
                                                <td class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?>
                                                </td>
                                                <td class="text-secondary"><?php echo htmlspecialchars($alumno['dni']); ?></td>
                                                <td class="text-secondary"><?php echo htmlspecialchars($alumno['email']); ?></td>
                                                <td>
                                                    <?php if ($estado_ins === 'aceptada'): ?>
                                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Aceptado</span>
                                                    <?php elseif ($estado_ins === 'pendiente'): ?>
                                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pendiente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Rechazado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <?php if ($estado_ins === 'pendiente'): ?>
                                                        <div class="d-inline-flex gap-2">
                                                            <form action="editarCurso.php?id=<?php echo $id_curso; ?>" method="POST" class="d-inline">
                                                                <input type="hidden" name="accion_alumno" value="aceptar">
                                                                <input type="hidden" name="id_inscripcion" value="<?php echo $alumno['id_inscripcion']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-success fw-medium">
                                                                    <i class="bi bi-check-lg me-1"></i>Aceptar
                                                                </button>
                                                            </form>

                                                            <form action="editarCurso.php?id=<?php echo $id_curso; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Rechazar solicitud de este alumno?');">
                                                                <input type="hidden" name="accion_alumno" value="rechazar">
                                                                <input type="hidden" name="id_inscripcion" value="<?php echo $alumno['id_inscripcion']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger fw-medium">
                                                                    <i class="bi bi-x-lg me-1"></i>Rechazar
                                                                </button>
                                                            </form>
                                                        </div>
                                                    <?php else: ?>
                                                        <form action="editarCurso.php?id=<?php echo $id_curso; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Dar de baja a este alumno del curso?');">
                                                            <input type="hidden" name="accion_alumno" value="rechazar">
                                                            <input type="hidden" name="id_inscripcion" value="<?php echo $alumno['id_inscripcion']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary border-0" title="Dar de baja">
                                                                <i class="bi bi-person-x-fill text-danger fs-6"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-5 text-muted">
                                <i class="bi bi-journal-x display-4 mb-3 d-block opacity-50"></i>
                                Aún no hay alumnos inscriptos ni solicitudes pendientes en este curso.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectTipoCosto = document.getElementById('tipo_costo');
            const bloquePrecio = document.getElementById('bloquePrecio');
            const inputCostoMonto = document.getElementById('costo_monto');

            function actualizarVisibilidadPrecio() {
                if (selectTipoCosto.value === '1') {
                    bloquePrecio.style.display = '';
                    inputCostoMonto.required = true;
                } else {
                    bloquePrecio.style.display = 'none';
                    inputCostoMonto.required = false;
                }
            }

            selectTipoCosto.addEventListener('change', actualizarVisibilidadPrecio);
            actualizarVisibilidadPrecio();

            const inputEtiqueta = document.getElementById('buscadorEtiquetas');
            const contenedor = document.getElementById('contenedorEtiquetas');

            function agregarEtiqueta(nombre) {
                let textoLimpio = nombre.trim().replace(/^#+/, '');
                if (textoLimpio === '') return;

                const existentes = Array.from(contenedor.querySelectorAll('input[name="etiquetas[]"]'))
                    .map(inp => inp.value.toLowerCase());

                if (existentes.includes(textoLimpio.toLowerCase())) {
                    inputEtiqueta.value = '';
                    return;
                }

                const badge = document.createElement('span');
                badge.className = 'badge bg-white text-secondary border p-2 d-flex align-items-center gap-1 item-etiqueta';
                badge.innerHTML = `
                    #${textoLimpio}
                    <input type="hidden" name="etiquetas[]" value="${textoLimpio}">
                    <button type="button" class="btn-close btn-remover-etiqueta" style="font-size: 0.5rem;" aria-label="Remover"></button>
                `;

                contenedor.appendChild(badge);
                inputEtiqueta.value = '';
            }

            inputEtiqueta.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    agregarEtiqueta(this.value);
                }
            });

            inputEtiqueta.addEventListener('change', function() {
                agregarEtiqueta(this.value);
            });

            contenedor.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remover-etiqueta')) {
                    e.target.closest('.item-etiqueta').remove();
                }
            });
        });
    </script>

<?php require_once 'footer.php'; ?>