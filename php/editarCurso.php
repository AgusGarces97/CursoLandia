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
$id_creador = $_SESSION['id_usuario'];
$mensaje = '';

$sql_check = "SELECT * FROM cursos WHERE id_curso = $id_curso AND id_creador = $id_creador";
$res_check = mysqli_query($conexion, $sql_check);

if (mysqli_num_rows($res_check) == 0) {
    header("Location: misCursos.php");
    exit();
}

$curso = mysqli_fetch_assoc($res_check);

// Actualizar datos del curso y sus etiquetas
if (isset($_POST['botonEditarCurso'])) {
    
    $titulo = mysqli_real_escape_string($conexion, trim($_POST['titulo']));
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
    $es_publico = (int)$_POST['privacidad'];
    $es_pago = (int)$_POST['costo'];
    $cupo = !empty($_POST['cupo']) ? (int)$_POST['cupo'] : "NULL";
    $fechaInicio = mysqli_real_escape_string($conexion, $_POST['fechaInicio']);
    $fechaFin = mysqli_real_escape_string($conexion, $_POST['fechaFin']);
    $activo = isset($_POST['estadoCurso']) ? 1 : 0;

    $puede_guardar = true;

    if ($_SESSION['plan'] === 'gratis' && $activo == 1) {
        $sql_cant = "SELECT COUNT(*) as total_activos FROM cursos WHERE id_creador = $id_creador AND activo = 1 AND id_curso != $id_curso";
        $res_cant = mysqli_query($conexion, $sql_cant);
        $fila_cant = mysqli_fetch_assoc($res_cant);

        if ($fila_cant['total_activos'] >= 3) {
            $puede_guardar = false;
            $mensaje = "<div class='alert alert-warning text-center fw-medium mb-4' role='alert'>
                            <i class='bi bi-exclamation-triangle-fill me-2'></i>
                            Límite alcanzado: Ya tienes 3 cursos activos en tu Plan Gratuito. 
                            Guárdalo como borrador desmarcando 'Publicar como Activo' o solicita el Plan Pro.
                        </div>";
        }
    }

    if ($puede_guardar) {
        $sql_update = "
            UPDATE cursos SET 
                titulo = '$titulo',
                descripcion = '$descripcion',
                es_publico = $es_publico,
                es_pago = $es_pago,
                cupo = $cupo,
                fecha_inicio = '$fechaInicio',
                fecha_fin = '$fechaFin',
                activo = $activo
            WHERE id_curso = $id_curso AND id_creador = $id_creador
        ";

        if (mysqli_query($conexion, $sql_update)) {
            
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
        } else {
            $mensaje = "<div class='alert alert-danger text-center mb-4' role='alert'>Error: " . mysqli_error($conexion) . "</div>";
        }
    }
}

// Alumnos inscriptos
$sql_alumnos = "
    SELECT i.*, u.nombre, u.apellido, u.email, u.dni
    FROM inscripciones i
    INNER JOIN usuarios u ON i.id_alumno = u.id_usuario
    WHERE i.id_curso = $id_curso
    ORDER BY i.id_inscripcion DESC
";
$resultado_alumnos = mysqli_query($conexion, $sql_alumnos);
$total_alumnos = mysqli_num_rows($resultado_alumnos);

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

                <div class="card shadow-sm border-0 rounded-4 mb-5">
                    
                    <div class="card-header bg-white border-bottom py-4 px-4 px-lg-5 d-flex justify-content-between align-items-center">
                        <h3 class="fw-bold mb-0 text-dark">Editar Curso</h3>
                        <a href="curso.php?id=<?php echo $id_curso; ?>" class="btn btn-outline-primary btn-sm fw-medium">
                            <i class="bi bi-eye me-1"></i>Ver Vista Pública
                        </a>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        <form action="editarCurso.php?id=<?php echo $id_curso; ?>" method="POST">
                            
                            <div class="row g-4">
                                
                                <div class="col-md-7">
                                    <div class="mb-4">
                                        <label for="titulo" class="form-label fw-medium text-dark">Título del Curso <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control custom-input" id="titulo" name="titulo" maxlength="30" value="<?php echo htmlspecialchars($curso['titulo']); ?>" required>
                                        <div class="form-text">Máximo 30 caracteres.</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="descripcion" class="form-label fw-medium text-dark">Descripción <span class="text-danger">*</span></label>
                                        <textarea class="form-control custom-input" id="descripcion" name="descripcion" rows="6" maxlength="1000" required><?php echo htmlspecialchars($curso['descripcion']); ?></textarea>
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
                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="privacidad" class="form-label fw-medium text-dark">Privacidad</label>
                                            <select class="form-select custom-input" id="privacidad" name="privacidad">
                                                <option value="1" <?php echo ($curso['es_publico'] == 1) ? 'selected' : ''; ?>>Público</option>
                                                <option value="0" <?php echo ($curso['es_publico'] == 0) ? 'selected' : ''; ?>>Privado</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label for="costo" class="form-label fw-medium text-dark">Costo</label>
                                            <select class="form-select custom-input" id="costo" name="costo">
                                                <option value="0" <?php echo ($curso['es_pago'] == 0) ? 'selected' : ''; ?>>Gratuito</option>
                                                <option value="1" <?php echo ($curso['es_pago'] == 1) ? 'selected' : ''; ?>>Pago</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="cupo" class="form-label fw-medium text-dark">Cupo máximo (Opcional)</label>
                                        <input type="number" class="form-control custom-input" id="cupo" name="cupo" min="1" value="<?php echo htmlspecialchars($curso['cupo'] ?? ''); ?>" placeholder="Ej: 30 (Dejar vacío si es sin límite)">
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="fechaInicio" class="form-label fw-medium text-dark">Inicio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaInicio" name="fechaInicio" value="<?php echo $curso['fecha_inicio']; ?>" required>
                                        </div>
                                        <div class="col-6">
                                            <label for="fechaFin" class="form-label fw-medium text-dark">Fin <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaFin" name="fechaFin" value="<?php echo $curso['fecha_fin']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-3 p-3 bg-light rounded border">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="estadoCurso" name="estadoCurso" value="1" <?php echo ($curso['activo'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-medium text-dark" for="estadoCurso">Publicar como Activo</label>
                                        </div>
                                        <div class="form-text small mb-0">Desmárcalo si solo quieres guardarlo como borrador.</div>
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

                <!-- Nómina de Alumnos -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-4 px-4 px-lg-5 d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-people-fill color-logo me-2"></i>Alumnos Inscriptos
                        </h4>
                        <span class="badge bg-purple px-3 py-2 fs-6">
                            <?php echo $total_alumnos; ?> / <?php echo $curso['cupo'] ? $curso['cupo'] : 'Sin límite'; ?>
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
                                            <th class="pe-4 text-end">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $num = 1;
                                        while ($alumno = mysqli_fetch_assoc($resultado_alumnos)): 
                                        ?>
                                            <tr>
                                                <td class="ps-4 fw-medium text-muted"><?php echo $num++; ?></td>
                                                <td class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?>
                                                </td>
                                                <td class="text-secondary"><?php echo htmlspecialchars($alumno['dni']); ?></td>
                                                <td class="text-secondary"><?php echo htmlspecialchars($alumno['email']); ?></td>
                                                <td class="pe-4 text-end">
                                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Inscripto</span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-5 text-muted">
                                <i class="bi bi-journal-x display-4 mb-3 d-block opacity-50"></i>
                                Aún no hay alumnos inscriptos en este curso.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
        </div>
    </main>

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