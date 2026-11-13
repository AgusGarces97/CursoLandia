<?php

date_default_timezone_set('America/Argentina/Buenos_Aires');

$dbHost = 'localhost';
$dbUsuario = 'usuario_admin';
$dbPassword = 'admin';
$db = 'cursolandia_db';

$conexion = mysqli_connect($dbHost, $dbUsuario, $dbPassword, $db);

// Verificar si hubo algún error
if (!$conexion) {
    die("Error crítico: No se pudo conectar a la base de datos. " . mysqli_connect_error());
}

// Configurar el conjunto de caracteres a UTF-8 para evitar problemas con acentos y eñes
mysqli_set_charset($conexion, "utf8mb4");

// =========================================================
// ACTIVACIÓN AUTOMÁTICA DE CURSOS PROGRAMADOS AL LLEGAR SU FECHA DE INICIO
// =========================================================
$hoy_bd = date('Y-m-d');
$sql_prog = "
    SELECT c.id_curso, c.id_creador, u.plan, u.vencimiento_pro
    FROM cursos c
    INNER JOIN usuarios u ON c.id_creador = u.id_usuario
    WHERE c.activo = 0 AND c.programado = 1 AND c.fecha_inicio <= '$hoy_bd'
    ORDER BY c.fecha_inicio ASC, c.id_curso ASC
";
$res_prog = mysqli_query($conexion, $sql_prog);

if ($res_prog && mysqli_num_rows($res_prog) > 0) {
    while ($curso_prog = mysqli_fetch_assoc($res_prog)) {
        $id_c_prog = (int)$curso_prog['id_curso'];
        $id_cr_prog = (int)$curso_prog['id_creador'];
        $plan_cr = $curso_prog['plan'];

        // Verificamos si su plan Pro venció
        if ($plan_cr === 'pro' && !empty($curso_prog['vencimiento_pro']) && $curso_prog['vencimiento_pro'] < $hoy_bd) {
            mysqli_query($conexion, "UPDATE usuarios SET plan = 'gratis', vencimiento_pro = NULL WHERE id_usuario = $id_cr_prog");
            $plan_cr = 'gratis';
        }

        if ($plan_cr === 'pro') {
            mysqli_query($conexion, "UPDATE cursos SET activo = 1, programado = 0 WHERE id_curso = $id_c_prog");
        } else {
            // Si es plan gratuito, verificamos que no supere el límite de 3 activos
            $res_act = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM cursos WHERE id_creador = $id_cr_prog AND activo = 1");
            $total_act = (int)mysqli_fetch_assoc($res_act)['total'];
            if ($total_act < 3) {
                mysqli_query($conexion, "UPDATE cursos SET activo = 1, programado = 0 WHERE id_curso = $id_c_prog");
            }
        }
    }
}

?>