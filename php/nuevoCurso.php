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

// Sincronizamos el plan real desde la BD antes de validar o renderizar
$sql_plan = "SELECT plan, vencimiento_pro FROM usuarios WHERE id_usuario = $id_creador";
$res_plan = mysqli_query($conexion, $sql_plan);
if ($res_plan && mysqli_num_rows($res_plan) > 0) {
    $datos_plan = mysqli_fetch_assoc($res_plan);
    $tipo_usuario = $datos_plan['plan'];

    if ($tipo_usuario === 'pro' && !empty($datos_plan['vencimiento_pro']) && $datos_plan['vencimiento_pro'] < date('Y-m-d')) {
        mysqli_query($conexion, "UPDATE usuarios SET plan = 'gratis', vencimiento_pro = NULL WHERE id_usuario = $id_creador");
        $tipo_usuario = 'gratis';
    }
    $_SESSION['plan'] = $tipo_usuario;
} else {
    $tipo_usuario = $_SESSION['plan'] ?? 'gratis';
}

// Verificamos cuántos cursos activos tiene actualmente el usuario
$sql_cantCursos_prev = "SELECT COUNT(*) as total_activos FROM cursos WHERE id_creador = $id_creador AND activo = 1";
$res_cant_prev = mysqli_query($conexion, $sql_cantCursos_prev);
$cant_activos_actuales = (int)mysqli_fetch_assoc($res_cant_prev)['total_activos'];
$limite_gratis_alcanzado = ($tipo_usuario === 'gratis' && $cant_activos_actuales >= 3);

// Valores por defecto del formulario (se conservan al hacer POST)
$titulo_val = trim($_POST['titulo'] ?? '');
$descripcion_val = trim($_POST['descripcion'] ?? '');
$privacidad_val = $_POST['privacidad'] ?? '0';
$costo_val = $_POST['costo'] ?? '0';
$cupo_val = trim($_POST['cupo'] ?? '');
$fechaInicio_val = trim($_POST['fechaInicio'] ?? '');
$fechaFin_val = trim($_POST['fechaFin'] ?? '');
$etiquetas_val = (isset($_POST['etiquetas']) && is_array($_POST['etiquetas'])) ? $_POST['etiquetas'] : [];
$estadoCurso_checked = isset($_POST['botonCrearCurso']) ? isset($_POST['estadoCurso']) : !$limite_gratis_alcanzado;

if (isset($_POST['botonCrearCurso'])) {
    
    $estadoCurso = isset($_POST['estadoCurso']) ? 1 : 0;

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
    } elseif ($cupo_val !== '' && (int)$cupo_val < 1) {
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>El cupo máximo debe ser al menos 1 o dejarse vacío.
                    </div>";
    // 2. Validación de coherencia entre fechas
    } elseif (!$fechasValidas || $fechaFin_val < $fechaInicio_val) {
        $mensaje = "<div class='alert alert-danger text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>La fecha de fin no puede ser anterior a la fecha de inicio.
                    </div>";
    // 3. Validación de límite de cursos activos según el plan
    } elseif ($tipo_usuario === 'gratis' && $estadoCurso === 1 && $cant_activos_actuales >= 3) {
        $mensaje = "<div class='alert alert-warning text-center fw-medium mb-4' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>
                        Límite alcanzado: Ya tienes 3 cursos activos en tu Plan Gratuito. 
                        Puedes guardarlo como borrador desmarcando 'Publicar como Activo' o solicitar el Plan Pro.
                    </div>";
    } else {
        $titulo = mysqli_real_escape_string($conexion, $titulo_val);
        $descripcion = mysqli_real_escape_string($conexion, $descripcion_val);
        $es_publico = ((int)$privacidad_val === 0) ? 1 : 0;
        $es_pago = ((int)$costo_val === 1) ? 1 : 0;
        $cupo = ($cupo_val !== '') ? (int)$cupo_val : "NULL";
        $fechaInicio = mysqli_real_escape_string($conexion, $fechaInicio_val);
        $fechaFin = mysqli_real_escape_string($conexion, $fechaFin_val);

        $sql_insert = "
            INSERT INTO cursos (id_creador, titulo, descripcion, es_publico, es_pago, cupo, fecha_inicio, fecha_fin, activo)
            VALUES ($id_creador, '$titulo', '$descripcion', $es_publico, $es_pago, $cupo, '$fechaInicio', '$fechaFin', $estadoCurso)
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
            $mensaje = "<div class='alert alert-danger text-center mb-4' role='alert'>Error: " . mysqli_error($conexion) . "</div>";
        }
    }
}

// Consultamos el catálogo de etiquetas para las sugerencias
$sql_todas_etiquetas = "SELECT * FROM etiquetas ORDER BY nombre ASC";
$res_todas_etiquetas = mysqli_query($conexion, $sql_todas_etiquetas);

$titulo_pagina = 'Nuevo Curso - Cursolandia';
$ruta = '../'; 
require_once 'header.php'; 
?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <?php echo $mensaje; ?>

                <?php if($limite_gratis_alcanzado && !isset($_POST['botonCrearCurso'])): ?>
                    <div class="alert alert-warning text-center fw-medium mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Tienes 3 cursos activos (límite Plan Gratuito). Solo podrás guardar este nuevo curso en modo <strong>Borrador</strong>.
                    </div>
                <?php endif; ?>

                <!-- Tarjeta del Formulario -->
                <div class="card shadow-sm border-0 rounded-4">
                    
                    <div class="card-header bg-white border-bottom py-4 px-4 px-lg-5">
                        <h3 class="fw-bold mb-0 text-dark">Crear Nuevo Curso</h3>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        <form action="nuevoCurso.php" method="POST" id="formNuevoCurso">
                            
                            <div class="row g-4">
                                
                                <!-- COLUMNA IZQUIERDA: Textos Principales -->
                                <div class="col-md-7">
                                    <div class="mb-4">
                                        <label for="titulo" class="form-label fw-medium text-dark">Título del Curso <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control custom-input" id="titulo" name="titulo" maxlength="100" value="<?php echo htmlspecialchars($titulo_val, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej: Desarrollo Web con PHP" required>
                                        <div class="form-text">Máximo 100 caracteres.</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="descripcion" class="form-label fw-medium text-dark">Descripción <span class="text-danger">*</span></label>
                                        <textarea class="form-control custom-input" id="descripcion" name="descripcion" rows="6" maxlength="1000" placeholder="Escribe de qué trata el curso..." required><?php echo htmlspecialchars($descripcion_val, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        <div class="form-text">Máximo 1000 caracteres.</div>
                                    </div>

                                    <!-- Buscador y Selección de Etiquetas -->
                                    <div class="mb-3">
                                        <label for="buscadorEtiquetas" class="form-label fw-medium text-dark">Etiquetas (Opcional)</label>
                                        <input type="text" class="form-control custom-input mb-2" id="buscadorEtiquetas" list="listaSugerencias" placeholder="Añadir etiquetas (ej: #Programación, #PHP)..." autocomplete="off">
                                        <datalist id="listaSugerencias">
                                            <?php while($tag = mysqli_fetch_assoc($res_todas_etiquetas)): ?>
                                                <option value="<?php echo htmlspecialchars($tag['nombre'], ENT_QUOTES, 'UTF-8'); ?>"></option>
                                            <?php endwhile; ?>
                                        </datalist>

                                        <div id="contenedorEtiquetas" class="border rounded-3 p-3 bg-light d-flex flex-wrap gap-2" style="min-height: 80px;">
                                            <?php 
                                            $etiquetas_mostradas = [];
                                            foreach ($etiquetas_val as $tag_item): 
                                                $tag_limpia = ltrim(trim($tag_item), '#');
                                                if ($tag_limpia === '' || in_array(mb_strtolower($tag_limpia, 'UTF-8'), $etiquetas_mostradas)) continue;
                                                $etiquetas_mostradas[] = mb_strtolower($tag_limpia, 'UTF-8');
                                            ?>
                                                <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1 item-etiqueta">
                                                    #<?php echo htmlspecialchars($tag_limpia, ENT_QUOTES, 'UTF-8'); ?>
                                                    <input type="hidden" name="etiquetas[]" value="<?php echo htmlspecialchars($tag_limpia, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="button" class="btn-close btn-remover-etiqueta" style="font-size: 0.5rem;" aria-label="Remover"></button>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- COLUMNA DERECHA: Configuraciones y Fechas -->
                                <div class="col-md-5">
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="privacidad" class="form-label fw-medium text-dark">Privacidad</label>
                                            <select class="form-select custom-input" id="privacidad" name="privacidad">
                                                <option value="0" <?php echo ($privacidad_val == '0') ? 'selected' : ''; ?>>Público</option>
                                                <option value="1" <?php echo ($privacidad_val == '1') ? 'selected' : ''; ?>>Privado</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label for="costo" class="form-label fw-medium text-dark">Costo</label>
                                            <select class="form-select custom-input" id="costo" name="costo">
                                                <option value="0" <?php echo ($costo_val == '0') ? 'selected' : ''; ?>>Gratuito</option>
                                                <option value="1" <?php echo ($costo_val == '1') ? 'selected' : ''; ?>>Pago</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="cupo" class="form-label fw-medium text-dark">Cupo máximo (Opcional)</label>
                                        <input type="number" class="form-control custom-input" id="cupo" name="cupo" min="1" value="<?php echo htmlspecialchars($cupo_val, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej: 30 (Dejar vacío si es sin límite)">
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="fechaInicio" class="form-label fw-medium text-dark">Inicio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaInicio" name="fechaInicio" value="<?php echo htmlspecialchars($fechaInicio_val, ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                        <div class="col-6">
                                            <label for="fechaFin" class="form-label fw-medium text-dark">Fin <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaFin" name="fechaFin" value="<?php echo htmlspecialchars($fechaFin_val, ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                        <div class="col-12 mt-1">
                                            <div id="error-fechas" class="text-danger small" style="display: none;">La fecha de fin no puede ser anterior a la fecha de inicio.</div>
                                        </div>
                                    </div>

                                    <div class="mb-3 p-3 bg-light rounded border">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="estadoCurso" name="estadoCurso" value="1" <?php echo $limite_gratis_alcanzado ? 'disabled' : ($estadoCurso_checked ? 'checked' : ''); ?>>
                                            <label class="form-check-label fw-medium text-dark" for="estadoCurso">Publicar como Activo</label>
                                        </div>
                                        <div class="form-text small mb-0">
                                            <?php echo $limite_gratis_alcanzado ? 'Límite de activos alcanzado. Solo puedes guardarlo como borrador.' : 'Desmárcalo si solo quieres guardar un borrador.'; ?>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <div class="d-flex justify-content-end gap-3">
                                <a href="misCursos.php" class="btn btn-light fw-medium px-4 text-muted border">Cancelar</a>
                                <button type="submit" class="btn btn-purple fw-medium px-4" name="botonCrearCurso">Confirmar Creación</button>
                            </div>
                            
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </main>

    <!-- JS de interacción de Etiquetas -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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