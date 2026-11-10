<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

// 1. Verificación de sesión iniciada
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// 2. Validación de ID recibido por URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: misCursos.php");
    exit();
}

$id_curso = (int)$_GET['id'];
$id_creador = $_SESSION['id_usuario'];
$mensaje = '';

// 3. Verificamos que el curso exista y pertenezca al usuario logueado
$sql_check = "SELECT * FROM cursos WHERE id_curso = $id_curso AND id_creador = $id_creador";
$res_check = mysqli_query($conexion, $sql_check);

if (mysqli_num_rows($res_check) == 0) {
    header("Location: misCursos.php");
    exit();
}

$curso = mysqli_fetch_assoc($res_check);

// 4. Procesar actualización de datos
if (isset($_POST['botonEditarCurso'])) {
    
    $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $es_publico = (int)$_POST['privacidad'];
    $es_pago = (int)$_POST['costo'];
    $cupo = !empty($_POST['cupo']) ? (int)$_POST['cupo'] : "NULL";
    $fechaInicio = mysqli_real_escape_string($conexion, $_POST['fechaInicio']);
    $fechaFin = mysqli_real_escape_string($conexion, $_POST['fechaFin']);
    $activo = isset($_POST['estadoCurso']) ? 1 : 0;

    $puede_guardar = true;

    // Validación RN04: si intenta activar/mantener activo un curso en Plan Gratuito
    if ($_SESSION['plan'] === 'gratis' && $activo == 1) {
        // Contamos los demás cursos activos del usuario (excluyendo el actual)
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
            $mensaje = "<div class='alert alert-success text-center fw-medium mb-4' role='alert'>
                            <i class='bi bi-check-circle-fill me-2'></i>¡Curso actualizado exitosamente!
                        </div>";
            
            // Recargamos los datos actualizados del curso para reflejarlos en los inputs
            $res_check = mysqli_query($conexion, $sql_check);
            $curso = mysqli_fetch_assoc($res_check);
        } else {
            $mensaje = "<div class='alert alert-danger text-center mb-4' role='alert'>Error: " . mysqli_error($conexion) . "</div>";
        }
    }
}

$titulo_pagina = 'Editar Curso - Cursolandia';
$ruta = '../'; 
require_once 'header.php'; 
?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <?php echo $mensaje; ?>

                <!-- Tarjeta del Formulario -->
                <div class="card shadow-sm border-0 rounded-3">
                    
                    <div class="card-header bg-white border-bottom py-4 px-4 px-lg-5 d-flex justify-content-between align-items-center">
                        <h3 class="fw-bold mb-0 text-dark">Editar Curso</h3>
                        <a href="curso.php?id=<?php echo $id_curso; ?>" class="btn btn-outline-primary btn-sm fw-medium">
                            <i class="bi bi-eye me-1"></i>Ver Vista Pública
                        </a>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        <form action="editarCurso.php?id=<?php echo $id_curso; ?>" method="POST">
                            
                            <div class="row g-4">
                                
                                <!-- COLUMNA IZQUIERDA: Textos Principales -->
                                <div class="col-md-7">
                                    <!-- Título -->
                                    <div class="mb-4">
                                        <label for="titulo" class="form-label fw-medium text-dark">Título del Curso <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control custom-input" id="titulo" name="titulo" maxlength="30" value="<?php echo htmlspecialchars($curso['titulo']); ?>" required>
                                        <div class="form-text">Máximo 30 caracteres.</div>
                                    </div>
                                    
                                    <!-- Descripción -->
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label fw-medium text-dark">Descripción <span class="text-danger">*</span></label>
                                        <textarea class="form-control custom-input" id="descripcion" name="descripcion" rows="8" maxlength="1000" required><?php echo htmlspecialchars($curso['descripcion']); ?></textarea>
                                        <div class="form-text">Máximo 1000 caracteres.</div>
                                    </div>
                                </div>

                                <!-- COLUMNA DERECHA: Configuraciones y Fechas -->
                                <div class="col-md-5">
                                    
                                    <!-- Privacidad y Costo -->
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

                                    <!-- Cupo (Opcional) -->
                                    <div class="mb-4">
                                        <label for="cupo" class="form-label fw-medium text-dark">Cupo máximo (Opcional)</label>
                                        <input type="number" class="form-control custom-input" id="cupo" name="cupo" min="1" value="<?php echo htmlspecialchars($curso['cupo'] ?? ''); ?>" placeholder="Ej: 30 (Dejar vacío si es sin límite)">
                                    </div>

                                    <!-- Fechas -->
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

                                    <!-- Estado del Curso (Switch Toggle) -->
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

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-end gap-3">
                                <a href="misCursos.php" class="btn btn-light fw-medium px-4 text-muted border">Cancelar</a>
                                <button type="submit" class="btn btn-purple fw-medium px-4" name="botonEditarCurso">Guardar Cambios</button>
                            </div>
                            
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </main>

<?php require_once 'footer.php'; ?>