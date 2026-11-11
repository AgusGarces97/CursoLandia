<?php
// 1. Iniciamos sesión y validamos seguridad
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';

// 2. Verificamos que los datos vengan por POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_curso']) && isset($_POST['accion'])) {
    
    $id_curso = (int)$_POST['id_curso'];
    $accion = $_POST['accion'];
    $id_usuario = $_SESSION['id_usuario'];
    $plan_usuario = $_SESSION['plan'];

    if ($accion === 'inscribir') {
        
        $puede_inscribirse = true;

        // REGLA DE NEGOCIO: Límite de 3 inscripciones activas para usuarios Gratis
        if ($plan_usuario === 'gratis') {
            $sql_limite = "SELECT COUNT(*) as total_inscripciones FROM inscripciones WHERE id_alumno = $id_usuario";
            $res_limite = mysqli_query($conexion, $sql_limite);
            $fila_limite = mysqli_fetch_assoc($res_limite);
            
            if ($fila_limite['total_inscripciones'] >= 3) {
                $puede_inscribirse = false;
                // Si falla por límite, lo mandamos a "Mis Cursos" para que vea la advertencia
                header("Location: misCursos.php?limite=alcanzado");
                exit();
            }
        }

        if ($puede_inscribirse) {
            // Verificamos que no esté inscripto ya (seguridad extra)
            $sql_check = "SELECT id_inscripcion FROM inscripciones WHERE id_curso = $id_curso AND id_alumno = $id_usuario";
            $res_check = mysqli_query($conexion, $sql_check);
            
            if (mysqli_num_rows($res_check) == 0) {
                // Insertamos la inscripción
                $sql_insert = "INSERT INTO inscripciones (id_curso, id_alumno) VALUES ($id_curso, $id_usuario)";
                mysqli_query($conexion, $sql_insert);
            }
        }

    } elseif ($accion === 'abandonar') {
        
        // Eliminamos el registro de la tabla
        $sql_delete = "DELETE FROM inscripciones WHERE id_curso = $id_curso AND id_alumno = $id_usuario";
        mysqli_query($conexion, $sql_delete);
        
    }

    // Volvemos a cargar la página del curso para que se actualicen los botones y el cupo
    header("Location: curso.php?id=$id_curso");
    exit();

} else {
    // Si alguien entra directo a procesarInscripcion.php por URL, lo expulsamos al index
    header("Location: ../index.php");
    exit();
}