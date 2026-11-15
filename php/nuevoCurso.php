<?php 
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_creador = (int)$_SESSION['id_usuario'];
$mensaje = '';

// Sincronizamos el plan real desde la BD (y degradamos cursos excedentes si venció el Plan Pro)
$tipo_usuario = verificarPlanUsuario($conexion, $id_creador);

// Verificamos cuántos cursos activos o programados tiene actualmente el usuario
$sql_cantCursos_prev = "SELECT COUNT(*) as total_activos FROM cursos WHERE id_creador = $id_creador AND (activo = 1 OR programado = 1)";
$res_cant_prev = mysqli_query($conexion, $sql_cantCursos_prev);
$cant_activos_actuales = (int)mysqli_fetch_assoc($res_cant_prev)['total_activos'];
$limite_gratis_alcanzado = ($tipo_usuario === 'gratis' && $cant_activos_actuales >= 3);

// Valores por defecto del formulario (se conservan al hacer POST)
$titulo_val = trim($_POST['titulo'] ?? '');
$descripcion_val = trim($_POST['descripcion'] ?? '');
$privacidad_val = $_POST['privacidad'] ?? '0';
$tipo_costo_val = $_POST['tipo_costo'] ?? '0';
$costo_monto_val = trim($_POST['costo_monto'] ?? '');
$cupo_val = trim($_POST['cupo'] ?? '');
$fechaInicio_val = trim($_POST['fechaInicio'] ?? '');
$fechaFin_val = trim($_POST['fechaFin'] ?? '');
$etiquetas_val = (isset($_POST['etiquetas']) && is_array($_POST['etiquetas'])) ? $_POST['etiquetas'] : [];
$estadoCurso_checked = isset($_POST['botonCrearCurso']) ? isset($_POST['estadoCurso']) : !$limite_gratis_alcanzado;

if (isset($_POST['botonCrearCurso'])) {
    
    $estadoCurso = isset($_POST['estadoCurso']) ? 1 : 0;
    $es_pago_post = ((int)$tipo_costo_val === 1) ? 1 : 0;
    $costo_normalizado = str_replace(',', '.', $costo_monto_val);

    $fechaIniObj = DateTime::createFromFormat('Y-m-d', $fechaInicio_val);
    $fechaFinObj = DateTime::createFromFormat('Y-m-d', $fechaFin_val);
    $fechasValidas = ($fechaIniObj && $fechaIniObj->format('Y-m-d') === $fechaInicio_val) &&
                     ($fechaFinObj && $fechaFinObj->format('Y-m-d') === $fechaFin_val);

    // 1. Validación de campos obligatorios y longitudes
    if ($titulo_val === '' || $descripcion_val === '' || $fechaInicio_val === '' || $fechaFin_val === '') {
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>Todos los campos obligatorios deben estar completos.
                    </div>";
    } elseif (mb_strlen($titulo_val, 'UTF-8') > 100) {
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>El título no puede superar los 100 caracteres.
                    </div>";
    } elseif (mb_strlen($descripcion_val, 'UTF-8') > 1000) {
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>La descripción no puede superar los 1000 caracteres.
                    </div>";
    } elseif ($es_pago_post === 1 && ($costo_normalizado === '' || !is_numeric($costo_normalizado) || (float)$costo_normalizado <= 0)) {
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>Debes ingresar un precio válido mayor a $0 para un curso de pago.
                    </div>";
    } elseif ($cupo_val !== '' && (int)$cupo_val < 1) {
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>El cupo máximo debe ser al menos 1 o dejarse vacío.
                    </div>";
    // 2. Validación de coherencia entre fechas
    } elseif (!$fechasValidas || $fechaFin_val < $fechaInicio_val) {
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>La fecha de fin no puede ser anterior a la fecha de inicio.
                    </div>";
    // 3. Validación de límite de cursos activos/programados según el plan
    } elseif ($tipo_usuario === 'gratis' && $estadoCurso === 1 && $cant_activos_actuales >= 3) {
        $mensaje = "<div class='alert alert-warning text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>
                        Límite alcanzado: Ya tienes 3 cursos activos o programados en tu Plan Gratuito. 
                        Puedes guardarlo como borrador desmarcando 'Publicar como Activo' o solicitar el Plan Pro.
                    </div>";
    } else {
        $error_imagen = false;
        $imagen_nombre = null;

        // Validación y subida opcional de imagen de portada
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                                <i class='bi bi-exclamation-triangle-fill me-2'></i>Error al subir la imagen de portada (verifica que no supere el límite del servidor).
                            </div>";
                $error_imagen = true;
            } else {
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $permitidas)) {
                    $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                                    <i class='bi bi-exclamation-triangle-fill me-2'></i>Formato de imagen no válido. Solo se permiten JPG, PNG o WEBP.
                                </div>";
                    $error_imagen = true;
                } elseif ($_FILES['imagen']['size'] > 2097152) { // 2 MB
                    $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                                    <i class='bi bi-exclamation-triangle-fill me-2'></i>La imagen de portada no puede superar los 2 MB.
                                </div>";
                    $error_imagen = true;
                } else {
                    $dir_cursos = '../uploads/cursos/';
                    if (!file_exists($dir_cursos)) {
                        mkdir($dir_cursos, 0777, true);
                    }
                    $imagen_nombre = 'curso_' . $id_creador . '_' . time() . '.' . $ext;
                    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $dir_cursos . $imagen_nombre)) {
                        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                                        <i class='bi bi-exclamation-triangle-fill me-2'></i>No se pudo guardar la imagen de portada en el servidor.
                                    </div>";
                        $error_imagen = true;
                    }
                }
            }
        }

        if (!$error_imagen) {
            $titulo = mysqli_real_escape_string($conexion, $titulo_val);
            $descripcion = mysqli_real_escape_string($conexion, $descripcion_val);
            $es_publico = ((int)$privacidad_val === 0) ? 1 : 0;
            $es_pago = $es_pago_post;
            $costo_final = ($es_pago === 1) ? number_format((float)$costo_normalizado, 2, '.', '') : '0.00';
            $cupo = ($cupo_val !== '') ? (int)$cupo_val : "NULL";
            $fechaInicio = mysqli_real_escape_string($conexion, $fechaInicio_val);
            $fechaFin = mysqli_real_escape_string($conexion, $fechaFin_val);
            $imagen_sql = $imagen_nombre ? "'" . mysqli_real_escape_string($conexion, $imagen_nombre) . "'" : "NULL";

            // Determinamos si se activa ahora o queda programado para su fecha de inicio
            $hoy = date('Y-m-d');
            if ($estadoCurso === 1 && $fechaInicio_val > $hoy) {
                $activo_bd = 0;
                $programado_bd = 1;
            } elseif ($estadoCurso === 1) {
                $activo_bd = 1;
                $programado_bd = 0;
            } else {
                $activo_bd = 0;
                $programado_bd = 0;
            }

            $sql_insert = "
                INSERT INTO cursos (id_creador, titulo, descripcion, es_publico, es_pago, costo, imagen, cupo, fecha_inicio, fecha_fin, activo, programado)
                VALUES ($id_creador, '$titulo', '$descripcion', $es_publico, $es_pago, $costo_final, $imagen_sql, $cupo, '$fechaInicio', '$fechaFin', $activo_bd, $programado_bd)
            ";

            if (mysqli_query($conexion, $sql_insert)) {
                $id_curso_nuevo = mysqli_insert_id($conexion);

                // PROCESAMIENTO DE ETIQUETAS DEL CURSO
                if (!empty($etiquetas_val)) {
                    foreach ($etiquetas_val as $nombreEtiqueta) {
                        $nombreLimpio = mysqli_real_escape_string($conexion, trim(str_replace('#', '', $nombreEtiqueta)));
                        if (!empty($nombreLimpio)) {
                            $res_tag = mysqli_query($conexion, "SELECT id_etiqueta FROM etiquetas WHERE LOWER(nombre) = LOWER('$nombreLimpio')");
                            if (mysqli_num_rows($res_tag) > 0) {
                                $id_etiqueta = mysqli_fetch_assoc($res_tag)['id_etiqueta'];
                            } else {
                                mysqli_query($conexion, "INSERT INTO etiquetas (nombre) VALUES ('$nombreLimpio')");
                                $id_etiqueta = mysqli_insert_id($conexion);
                            }
                            mysqli_query($conexion, "INSERT IGNORE INTO curso_etiquetas (id_curso, id_etiqueta) VALUES ($id_curso_nuevo, $id_etiqueta)");
                        }
                    }
                }

                $mensaje = "<div class='alert alert-success text-center fw-medium mb-4' role='alert'>
                                <i class='bi bi-check-circle-fill me-2'></i>¡Curso creado exitosamente!
                            </div>";
                
                // Recalculamos para la vista
                $res_cant_prev = mysqli_query($conexion, $sql_cantCursos_prev);
                $cant_activos_actuales = (int)mysqli_fetch_assoc($res_cant_prev)['total_activos'];
                $limite_gratis_alcanzado = ($tipo_usuario === 'gratis' && $cant_activos_actuales >= 3);

                header("Location: ../index.php");
                exit();
            } else {
                if ($imagen_nombre && file_exists('../uploads/cursos/' . $imagen_nombre)) {
                    unlink('../uploads/cursos/' . $imagen_nombre);
                }
                $mensaje = "<div class='alert alert-danger text-center mb-4' role='alert'>Error: " . mysqli_error($conexion) . "</div>";
            }
        }
    }
}

// Consultamos todas las etiquetas de la base de datos para pasarlas al modal
$sql_todas_etiquetas = "SELECT * FROM etiquetas ORDER BY nombre ASC";
$res_todas_etiquetas = mysqli_query($conexion, $sql_todas_etiquetas);
$todas_etiquetas_arr = [];
if ($res_todas_etiquetas) {
    while($tag = mysqli_fetch_assoc($res_todas_etiquetas)) {
        $todas_etiquetas_arr[] = $tag['nombre'];
    }
}

$titulo_pagina = 'Nuevo Curso - Cursolandia';
$ruta = '../'; 
require_once 'header.php'; 
?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <?php echo $mensaje; ?>

                <?php if($limite_gratis_alcanzado && !isset($_POST['botonCrearCurso'])): ?>
                    <div class="alert alert-warning text-center fw-medium mb-4 shadow-sm border-0" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Tienes 3 cursos activos o programados (límite Plan Gratuito). Solo podrás guardar este nuevo curso en modo <strong>Borrador</strong>.
                    </div>
                <?php endif; ?>

                <!-- Tarjeta del Formulario con Header Decorativo y Colorido -->
                <div class="card shadow-sm border-0 rounded-4 bg-white overflow-hidden">
                    
                    <div class="card-header text-white p-4 px-lg-5" style="background: linear-gradient(135deg, var(--PRIMARY), var(--SECONDARY));">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white bg-opacity-25 p-3 rounded-3 text-white fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-journal-plus"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1 text-white">Crear Nuevo Curso</h3>
                                <p class="mb-0 text-white opacity-75 small">Completa la información para publicar un nuevo espacio de aprendizaje.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        <form action="nuevoCurso.php" method="POST" enctype="multipart/form-data" id="formNuevoCurso">
                            
                            <div class="row g-4">
                                
                                <!-- COLUMNA IZQUIERDA: Textos Principales -->
                                <div class="col-md-7">
                                    <div class="mb-4">
                                        <label for="titulo" class="form-label fw-medium text-dark"><i class="bi bi-fonts text-primary me-1"></i> Título del Curso <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control custom-input" id="titulo" name="titulo" maxlength="100" value="<?php echo htmlspecialchars($titulo_val, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej: Desarrollo Web con PHP" required>
                                        <div class="form-text">Máximo 100 caracteres.</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="descripcion" class="form-label fw-medium text-dark"><i class="bi bi-card-text text-primary me-1"></i> Descripción <span class="text-danger">*</span></label>
                                        <textarea class="form-control custom-input" id="descripcion" name="descripcion" rows="6" maxlength="1000" placeholder="Escribe de qué trata el curso..." required><?php echo htmlspecialchars($descripcion_val, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        <div class="form-text">Máximo 1000 caracteres.</div>
                                    </div>

                                    <!-- Selector de Etiquetas mediante Modal -->
                                    <div class="mb-3">
                                        <label class="form-label fw-medium text-dark"><i class="bi bi-tags-fill text-primary me-1"></i> Etiquetas (Opcional)</label>
                                        <div class="mb-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm w-100 py-2 fw-medium d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#modalEtiquetas">
                                                <i class="bi bi-tags"></i> Seleccionar Etiquetas de la Base de Datos
                                            </button>
                                        </div>

                                        <!-- Contenedor visual de badges y donde se inyectan los inputs hidden reales -->
                                        <div id="contenedorEtiquetas" class="border rounded-3 p-3 bg-light d-flex flex-wrap gap-2" style="min-height: 80px;">
                                            <!-- Se rellena dinámicamente con JS -->
                                        </div>
                                    </div>
                                </div>

                                <!-- COLUMNA DERECHA: Configuraciones y Fechas -->
                                <div class="col-md-5">
                                    
                                    <div class="mb-4">
                                        <label for="imagen" class="form-label fw-medium text-dark"><i class="bi bi-image-fill text-primary me-1"></i> Imagen de Portada (Opcional)</label>
                                        <input type="file" class="form-control custom-input" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                        <div class="form-text">JPG, PNG o WEBP (máx. 2 MB). Si no subes una, se asignará un color por defecto.</div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="privacidad" class="form-label fw-medium text-dark"><i class="bi bi-shield-lock text-primary me-1"></i> Privacidad</label>
                                            <select class="form-select custom-input" id="privacidad" name="privacidad">
                                                <option value="0" <?php echo ($privacidad_val == '0') ? 'selected' : ''; ?>>Público</option>
                                                <option value="1" <?php echo ($privacidad_val == '1') ? 'selected' : ''; ?>>Privado</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label for="tipo_costo" class="form-label fw-medium text-dark"><i class="bi bi-cash-stack text-primary me-1"></i> Costo</label>
                                            <select class="form-select custom-input" id="tipo_costo" name="tipo_costo">
                                                <option value="0" <?php echo ($tipo_costo_val == '0') ? 'selected' : ''; ?>>Gratuito</option>
                                                <option value="1" <?php echo ($tipo_costo_val == '1') ? 'selected' : ''; ?>>De Pago</option>
                                            </select>
                                        </div>
                                        <div class="col-12" id="bloquePrecio" style="<?php echo ($tipo_costo_val == '1') ? '' : 'display: none;'; ?>">
                                            <label for="costo_monto" class="form-label fw-medium text-dark">Precio del Curso (ARS) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light fw-bold text-primary">$</span>
                                                <input type="number" class="form-control custom-input" id="costo_monto" name="costo_monto" min="1" step="0.01" value="<?php echo htmlspecialchars($costo_monto_val, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej: 15000.00" <?php echo ($tipo_costo_val == '1') ? 'required' : ''; ?>>
                                            </div>
                                            <div class="form-text">Indica el valor que abonarán los estudiantes para inscribirse.</div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="cupo" class="form-label fw-medium text-dark"><i class="bi bi-people-fill text-primary me-1"></i> Cupo máximo (Opcional)</label>
                                        <input type="number" class="form-control custom-input" id="cupo" name="cupo" min="1" value="<?php echo htmlspecialchars($cupo_val, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej: 30 (Dejar vacío si es sin límite)">
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="fechaInicio" class="form-label fw-medium text-dark"><i class="bi bi-calendar-event text-primary me-1"></i> Inicio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaInicio" name="fechaInicio" value="<?php echo htmlspecialchars($fechaInicio_val, ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                        <div class="col-6">
                                            <label for="fechaFin" class="form-label fw-medium text-dark"><i class="bi bi-calendar-check text-primary me-1"></i> Fin <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaFin" name="fechaFin" value="<?php echo htmlspecialchars($fechaFin_val, ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                        <div class="col-12 mt-1">
                                            <div id="error-fechas" class="text-danger small" style="display: none;">La fecha de fin no puede ser anterior a la fecha de inicio.</div>
                                        </div>
                                    </div>

                                    <div class="mb-3 p-3 bg-light rounded-3 border-start border-primary border-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="estadoCurso" name="estadoCurso" value="1" <?php echo $limite_gratis_alcanzado ? 'disabled' : ($estadoCurso_checked ? 'checked' : ''); ?>>
                                            <label class="form-check-label fw-medium text-dark" for="estadoCurso">Publicar como Activo</label>
                                        </div>
                                        <div class="form-text small mb-0 mt-1">
                                            <?php echo $limite_gratis_alcanzado ? 'Límite de activos alcanzado. Solo puedes guardarlo como borrador.' : 'Si la fecha de inicio es futura, se activará automáticamente ese día.'; ?>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <div class="d-flex justify-content-end gap-3">
                                <a href="misCursos.php" class="btn btn-light fw-medium px-4 text-muted border py-2">Cancelar</a>
                                <button type="submit" class="btn btn-purple fw-medium px-4 py-2 text-white" name="botonCrearCurso">
                                    <i class="bi bi-check-lg me-1"></i> Confirmar Creación
                                </button>
                            </div>
                            
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </main>

    <!-- Modal de Selección de Etiquetas -->
    <div class="modal fade" id="modalEtiquetas" tabindex="-1" aria-labelledby="modalEtiquetasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-dark" id="modalEtiquetasLabel">
                        <i class="bi bi-tags color-logo me-2"></i>Seleccionar Etiquetas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Marca las etiquetas de la base de datos que apliquen al curso:</p>
                    <div class="row g-2" style="max-height: 280px; overflow-y: auto;">
                        <?php if (count($todas_etiquetas_arr) > 0): ?>
                            <?php foreach ($todas_etiquetas_arr as $nombre_tag): 
                                $is_checked = in_array($nombre_tag, $etiquetas_val) ? 'checked' : '';
                            ?>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input checkbox-etiqueta" type="checkbox" value="<?php echo htmlspecialchars($nombre_tag, ENT_QUOTES, 'UTF-8'); ?>" id="tag_<?php echo md5($nombre_tag); ?>" <?php echo $is_checked; ?>>
                                        <label class="form-check-label text-dark" for="tag_<?php echo md5($nombre_tag); ?>">
                                            #<?php echo htmlspecialchars($nombre_tag, ENT_QUOTES, 'UTF-8'); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center text-muted py-3">
                                No hay etiquetas registradas en la base de datos.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-purple fw-bold px-4 text-white" data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS de interacción de Precio, Modal y Sincronización Robusta de Etiquetas -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectTipoCosto = document.getElementById('tipo_costo');
            const bloquePrecio = document.getElementById('bloquePrecio');
            const inputCostoMonto = document.getElementById('costo_monto');
            const formNuevoCurso = document.getElementById('formNuevoCurso');

            function actualizarVisibilidadPrecio() {
                if (selectTipoCosto.value === '1') {
                    bloquePrecio.style.display = '';
                    inputCostoMonto.required = true;
                } else {
                    bloquePrecio.style.display = 'none';
                    inputCostoMonto.required = false;
                }
            }

            if (selectTipoCosto) {
                selectTipoCosto.addEventListener('change', actualizarVisibilidadPrecio);
                actualizarVisibilidadPrecio();
            }

            // Sincronización robusta de Etiquetas con inputs hidden inyectados en el formulario principal
            const checkboxesEtiquetas = document.querySelectorAll('.checkbox-etiqueta');
            const contenedorEtiquetas = document.getElementById('contenedorEtiquetas');

            function actualizarBadges() {
                // Removemos inputs hidden anteriores de etiquetas para recrearlos limpios
                formNuevoCurso.querySelectorAll('input.input-etiqueta-hidden').forEach(el => el.remove());
                contenedorEtiquetas.innerHTML = '';
                
                const seleccionadas = Array.from(checkboxesEtiquetas).filter(cb => cb.checked);

                if (seleccionadas.length === 0) {
                    contenedorEtiquetas.innerHTML = '<span class="text-muted small fst-italic">Ninguna etiqueta seleccionada.</span>';
                    return;
                }

                seleccionadas.forEach(cb => {
                    const tagName = cb.value;

                    // Creamos el input hidden que viaja por POST en el form principal
                    const inputHidden = document.createElement('input');
                    inputHidden.type = 'hidden';
                    inputHidden.name = 'etiquetas[]';
                    inputHidden.value = tagName;
                    inputHidden.className = 'input-etiqueta-hidden';
                    formNuevoCurso.appendChild(inputHidden);

                    // Creamos el badge visual
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-white text-secondary border p-2 d-flex align-items-center gap-1 item-etiqueta';
                    badge.innerHTML = `
                        #${tagName}
                        <button type="button" class="btn-close btn-remover-etiqueta" style="font-size: 0.5rem;" aria-label="Remover" data-value="${tagName}"></button>
                    `;
                    contenedorEtiquetas.appendChild(badge);
                });
            }

            checkboxesEtiquetas.forEach(cb => {
                cb.addEventListener('change', actualizarBadges);
            });

            contenedorEtiquetas.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remover-etiqueta')) {
                    const tagName = e.target.getAttribute('data-value');
                    const cb = Array.from(checkboxesEtiquetas).find(item => item.value === tagName);
                    if (cb) {
                        cb.checked = false;
                        actualizarBadges();
                    }
                }
            });

            // Inicializar al cargar la página
            actualizarBadges();
        });
    </script>

<?php require_once 'footer.php'; ?>