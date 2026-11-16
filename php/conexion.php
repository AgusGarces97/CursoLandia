<?php

// PARA SETEAR LA ZONA HORARIA, por temas de fechas de inicio de curso y demás
date_default_timezone_set('America/Argentina/Buenos_Aires');

$config = require __DIR__ . '/config.php';

$conexion = mysqli_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['dbname'],
    $config['port']
);


// Verificar si hubo algún error
if (!$conexion) {
    die("Error crítico: No se pudo conectar a la base de datos. " . mysqli_connect_error());
}

// Configurar el conjunto de caracteres a UTF-8 para evitar problemas con acentos y eñes
mysqli_set_charset($conexion, "utf8mb4");

/**
 * Sincroniza el plan del usuario, ajusta los cursos activos/programados excedentes
 * a borrador si su Plan Pro venció y le envía una notificación.
 */
function verificarPlanUsuario($conexion, $id_usuario) {
    $id_usuario = (int)$id_usuario;
    
    if ($id_usuario <= 0) {
        return 'gratis';
    }

    $hoy_bd = date('Y-m-d');
    $sql_u = "SELECT plan, vencimiento_pro FROM usuarios WHERE id_usuario = $id_usuario";
    $res_u = mysqli_query($conexion, $sql_u);

    if (!$res_u || mysqli_num_rows($res_u) === 0) {
        return 'gratis';
    }

    $datos_u = mysqli_fetch_assoc($res_u);
    $plan_actual = $datos_u['plan'];


    // Una vez obtenida la consulta de la bd, si fija si el plan obtenido es pro, si hay una fecha de vencimiento y si la fecha de vencimiento ya pasó
    if ($plan_actual === 'pro' && !empty($datos_u['vencimiento_pro']) && $datos_u['vencimiento_pro'] < $hoy_bd) {
        // Cambiamos el plan a gratuito
        mysqli_query($conexion, "UPDATE usuarios SET plan = 'gratis', vencimiento_pro = NULL WHERE id_usuario = $id_usuario");
        $plan_actual = 'gratis';

        // Buscamos todos sus cursos activos o programados (priorizamos los que terminan antes / más antiguos)
        $sql_cursos_user = "
            SELECT id_curso, titulo 
            FROM cursos 
            WHERE id_creador = $id_usuario AND (activo = 1 OR programado = 1)
            ORDER BY fecha_fin ASC, id_curso ASC
        ";
        $res_cursos_user = mysqli_query($conexion, $sql_cursos_user);

        // Se fija si el usuario supera la cantidad de cursos inscriptos o creados del plan gratis
        if ($res_cursos_user && mysqli_num_rows($res_cursos_user) > 3) {
            $contador = 0;
            $cursos_borrador = [];

            while ($c_row = mysqli_fetch_assoc($res_cursos_user)) {
                $contador++;
                if ($contador > 3) {
                    $id_c_bajar = (int)$c_row['id_curso'];
                    mysqli_query($conexion, "UPDATE cursos SET activo = 0, programado = 0 WHERE id_curso = $id_c_bajar");
                    $cursos_borrador[] = "'" . $c_row['titulo'] . "'";
                }
            }

        // Toma los cursos guardados en el arreglo borrador y los guarda en una cadena para luego enviarlos en una notificación
            if (!empty($cursos_borrador)) {
                $lista_titulos = implode(', ', $cursos_borrador);
                $titulo_notif = mysqli_real_escape_string($conexion, "Tu Plan Pro ha vencido");
                $msj_notif = mysqli_real_escape_string(
                    $conexion,
                    "Tu suscripción Pro finalizó y tu cuenta pasó al Plan Gratuito (máximo 3 cursos activos). Los siguientes cursos excedentes se pasaron a borrador sin perder su contenido: $lista_titulos."
                );
                mysqli_query($conexion, "
                    INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                    VALUES ($id_usuario, '$titulo_notif', '$msj_notif', 'plan_pro', 0, NOW())
                ");
            }
        }
    }

    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['id_usuario']) && (int)$_SESSION['id_usuario'] === $id_usuario) {
        $_SESSION['plan'] = $plan_actual;
    }

    return $plan_actual;
}

// =========================================================
// SINCRONIZACIÓN DE PLAN DEL USUARIO CON SESIÓN ACTIVA
// =========================================================
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['id_usuario'])) {
    verificarPlanUsuario($conexion, (int)$_SESSION['id_usuario']);
}

// =========================================================
// ACTIVACIÓN AUTOMÁTICA DE CURSOS PROGRAMADOS AL LLEGAR SU FECHA DE INICIO
// =========================================================
$hoy_bd = date('Y-m-d');
//SQL para los cursos programados y que hayamos llegado a su fecha de inicio
$sql_prog = "
    SELECT id_curso, id_creador
    FROM cursos
    WHERE activo = 0 AND programado = 1 AND fecha_inicio <= '$hoy_bd'
    ORDER BY fecha_inicio ASC, id_curso ASC
";
$res_prog = mysqli_query($conexion, $sql_prog);

if ($res_prog && mysqli_num_rows($res_prog) > 0) {
    while ($curso_prog = mysqli_fetch_assoc($res_prog)) {
        $id_c_prog = (int)$curso_prog['id_curso'];
        $id_cr_prog = (int)$curso_prog['id_creador'];

        // Ejecuta la degradación y limpieza de excedentes si su Plan Pro venció
        $plan_cr = verificarPlanUsuario($conexion, $id_cr_prog);

        if ($plan_cr === 'pro') {
            mysqli_query($conexion, "UPDATE cursos SET activo = 1, programado = 0 WHERE id_curso = $id_c_prog");
        } else {
            // Si es plan gratuito, verificamos que no supere el límite de 3 activos
            $res_act = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM cursos WHERE id_creador = $id_cr_prog AND activo = 1");
            $total_act = (int)mysqli_fetch_assoc($res_act)['total'];
            if ($total_act < 3) {
                mysqli_query($conexion, "UPDATE cursos SET activo = 1, programado = 0 WHERE id_curso = $id_c_prog");
            } else {
                // Si por algún motivo quedó programado por encima del cupo, pasa a borrador
                mysqli_query($conexion, "UPDATE cursos SET activo = 0, programado = 0 WHERE id_curso = $id_c_prog");
            }
        }
    }
}

?>