<?php 


if(session_status() == PHP_SESSION_NONE){
    session_start();
}

require_once 'conexion.php';
// Si alguien intenta entrar a nuevoCurso.php sin loguearse, lo mando al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['botonCrearCurso'])){
    
    $id_creador = $_SESSION['id_usuario'];
    $tipo_usuario = $_SESSION['plan'];
    $estado_Curso = 1;

    $puede_crear = true;
    
    if(isset($_POST['estadoCurso'])){
        $estado_Curso = 1;
    }else{
        $estado_Curso = 0;
    }
    
    if($tipo_usuario === "gratis" && $estado_Curso === 1){
        $sql_cantCursos = "
        SELECT COUNT(*) as total_activos FROM cursos
        WHERE id_creador='$id_creador' AND activo=1
        ";
        $resultadoCantCursos = mysqli_query($conexion, $sql_cantCursos);
        $filaResultadoCant = mysqli_fetch_array($resultadoCantCursos);

        if($filaResultadoCant['total_activos'] >= 3){
            $puede_crear = false;
            $mensaje = "<div class='alert alert-warning text-center fw-medium' role='alert'>
                            <i class='bi bi-exclamation-triangle-fill me-2'></i>
                            Límite alcanzado: Ya tienes 3 cursos activos en tu Plan Gratuito. 
                            Puedes guardarlo como borrador desmarcando 'Publicar como Activo' o solicitar el Plan Pro.
                        </div>";
        }
    }
    

    if($puede_crear){
        extract($_POST);

        if($privacidad == "0"){
            $es_publico = 0; // Es Publico
        }else{
            $es_publico = 1; // Es Privado
        }

        if($costo == "1"){
            $es_pago = 1; // Es Pago
        }else{
            $es_pago = 0; // Es Gratuito
        }

        $sql_insert = "
        INSERT INTO cursos (id_creador, titulo, descripcion, es_publico, es_pago, cupo, fecha_inicio, fecha_fin, activo)
        VALUES ($id_creador, '$titulo', '$descripcion', $es_publico, $es_pago, $cupo, '$fechaInicio', $fechaFin, $estadoCurso)
        ";

        if (mysqli_query($conexion, $sql_insert)) {
            $mensaje = "<div class='alert alert-success text-center fw-medium' role='alert'>
                            <i class='bi bi-check-circle-fill me-2'></i>¡Curso creado exitosamente!
                        </div>";
        } else {
            $mensaje = "<div class='alert alert-danger text-center' role='alert'>Error: " . mysqli_error($conexion) . "</div>";
        }
    }

}

    $titulo_pagina = 'Nuevo Curso - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 

?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Tarjeta del Formulario -->
                <div class="card shadow-sm border-0 rounded-3">
                    
                    <div class="card-header bg-white border-bottom py-4 px-4 px-lg-5">
                        <h3 class="fw-bold mb-0 text-dark">Crear Nuevo Curso</h3>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        <form action="nuevoCurso.php" method="POST">
                            
                            <div class="row g-4">
                                
                                <!-- COLUMNA IZQUIERDA: Textos Principales -->
                                <div class="col-md-7">
                                    <!-- Título (Máx 30 caracteres según diccionario) -->
                                    <div class="mb-4">
                                        <label for="titulo" class="form-label fw-medium text-dark">Título del Curso <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control custom-input" id="titulo" name="titulo" maxlength="30" placeholder="Ej: Desarrollo Web con PHP" required>
                                        <div class="form-text">Máximo 30 caracteres.</div>
                                    </div>
                                    
                                    <!-- Descripción (Máx 1000 caracteres) -->
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label fw-medium text-dark">Descripción <span class="text-danger">*</span></label>
                                        <textarea class="form-control custom-input" id="descripcion" name="descripcion" rows="8" maxlength="1000" placeholder="Escribe de qué trata el curso, qué aprenderán los alumnos..." required></textarea>
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
                                                <option value="0">Público</option>
                                                <option value="1">Privado</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label for="costo" class="form-label fw-medium text-dark">Costo</label>
                                            <select class="form-select custom-input" id="costo" name="costo">
                                                <option value="0">Gratuito</option>
                                                <option value="1">Pago</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Cupo (Opcional) -->
                                    <div class="mb-4">
                                        <label for="cupo" class="form-label fw-medium text-dark">Cupo máximo (Opcional)</label>
                                        <input type="number" class="form-control custom-input" id="cupo" name="cupo" min="1" placeholder="Ej: 30 (Dejar vacío si es sin límite)">
                                    </div>

                                    <!-- Fechas -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <label for="fechaInicio" class="form-label fw-medium text-dark">Inicio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaInicio" name="fechaInicio" required>
                                        </div>
                                        <div class="col-6">
                                            <label for="fechaFin" class="form-label fw-medium text-dark">Fin <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control custom-input" id="fechaFin" name="fechaFin" required>
                                        </div>
                                    </div>

                                    <!-- Estado del Curso (Switch Toggle) -->
                                    <div class="mb-3 p-3 bg-light rounded border">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="estadoCurso" name="estadoCurso" value="1" checked>
                                            <label class="form-check-label fw-medium text-dark" for="estadoCurso">Publicar como Activo</label>
                                        </div>
                                        <div class="form-text small mb-0">Desmárcalo si solo quieres guardar un borrador.</div>
                                    </div>
                                    
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <!-- Botones de Acción (Confirmar y Navegar_a) -->
                            <div class="d-flex justify-content-end gap-3">
                                <a href="index.html" class="btn btn-light fw-medium px-4 text-muted border">Cancelar</a>
                                <button type="submit" class="btn btn-purple fw-medium px-4" name="botonCrearCurso">Confirmar Creación</button>
                            </div>
                            
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </main>


<?php require_once 'footer.php'; ?>