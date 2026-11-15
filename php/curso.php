<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    date_default_timezone_set('America/Argentina/Buenos_Aires');
    
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: ../index.php");
        exit();
    }

    require_once 'conexion.php';

    $id_curso = (int)$_GET['id'];
    $id_usuario_actual = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null;
    $nombre_usuario_actual = isset($_SESSION['nombre']) ? $_SESSION['nombre'] . ' ' . $_SESSION['apellido'] : 'Un usuario';
    $plan_usuario_actual = $id_usuario_actual ? verificarPlanUsuario($conexion, $id_usuario_actual) : 'gratis';

    $sql_curso = "
        SELECT c.*, u.nombre, u.apellido 
        FROM cursos c
        INNER JOIN usuarios u ON c.id_creador = u.id_usuario
        WHERE c.id_curso = $id_curso
    ";
    $resultado_curso = mysqli_query($conexion, $sql_curso);

    if (mysqli_num_rows($resultado_curso) == 0) {
        header("Location: ../index.php");
        exit();
    }
    $curso = mysqli_fetch_assoc($resultado_curso);

    $es_creador = ($id_usuario_actual == $curso['id_creador']);
    $es_programado = ($curso['activo'] == 0 && !empty($curso['programado']) && $curso['programado'] == 1);
    $esta_inscripto = false;
    $estado_inscripcion = null;
    $costo_curso = (float)($curso['costo'] ?? 0);
    $costo_formateado = '$' . number_format($costo_curso, 2, ',', '.');

    // Validación de fecha de inicio del curso vs fecha actual
    $hoy_fecha = date('Y-m-d');
    $curso_iniciado = ($hoy_fecha >= $curso['fecha_inicio']);

    // Solo se cuentan los alumnos con inscripción aceptada para el cupo
    $sql_cupo = "SELECT COUNT(id_inscripcion) as total_alumnos FROM inscripciones WHERE id_curso = $id_curso AND estado = 'aceptada'";
    $res_cupo = mysqli_query($conexion, $sql_cupo);
    $total_alumnos = mysqli_fetch_assoc($res_cupo)['total_alumnos'];

    // Consultamos el estado exacto del alumno actual
    if ($id_usuario_actual && !$es_creador) {
        $sql_check_inscripcion = "SELECT estado FROM inscripciones WHERE id_curso = $id_curso AND id_alumno = $id_usuario_actual";
        $res_check = mysqli_query($conexion, $sql_check_inscripcion);
        if (mysqli_num_rows($res_check) > 0) {
            $row_check = mysqli_fetch_assoc($res_check);
            $estado_inscripcion = $row_check['estado'];
            if ($estado_inscripcion === 'aceptada') {
                $esta_inscripto = true;
            }
        }
    }

    // Permisos del foro condicionados a que el curso ya haya comenzado (fecha actual >= fecha_inicio)
    $puede_crear_pregunta = (!$es_creador && $esta_inscripto && (int)$curso['activo'] === 1 && $curso_iniciado);
    $puede_participar_foro = ($es_creador || ($esta_inscripto && (int)$curso['activo'] === 1));

    // =========================================================
    // LÓGICA DE GESTIÓN DE COMUNICADOS (CONDICIONADA A FECHA DE INICIO)
    // =========================================================
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_comunicado']) && $_POST['accion_comunicado'] === 'crear' && $es_creador && $curso_iniciado) {
        $titulo_com = mysqli_real_escape_string($conexion, trim($_POST['titulo_comunicado']));
        $cuerpo_com = mysqli_real_escape_string($conexion, trim($_POST['cuerpo_comunicado']));

        if (!empty($titulo_com) && !empty($cuerpo_com)) {
            $sql_ins_com = "INSERT INTO comunicados_curso (id_curso, titulo, cuerpo, fecha_creacion) VALUES ($id_curso, '$titulo_com', '$cuerpo_com', NOW())";
            if (mysqli_query($conexion, $sql_ins_com)) {
                
                // Notificar a todos los alumnos inscriptos aceptados
                $titulo_notif = mysqli_real_escape_string($conexion, "Nuevo comunicado en " . $curso['titulo']);
                $msj_notif = mysqli_real_escape_string($conexion, "El docente publicó un nuevo comunicado: '$titulo_com'");

                $sql_alumnos_com = "SELECT id_alumno FROM inscripciones WHERE id_curso = $id_curso AND estado = 'aceptada'";
                $res_alumnos_com = mysqli_query($conexion, $sql_alumnos_com);
                while ($al = mysqli_fetch_assoc($res_alumnos_com)) {
                    $id_dest = (int)$al['id_alumno'];
                    mysqli_query($conexion, "
                        INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                        VALUES ($id_dest, '$titulo_notif', '$msj_notif', 'comunicado', 0, NOW())
                    ");
                }

                header("Location: curso.php?id=$id_curso&com=creado_ok#comunicados");
                exit();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_comunicado']) && $_POST['accion_comunicado'] === 'eliminar' && $es_creador) {
        $id_comunicado = (int)$_POST['id_comunicado'];
        mysqli_query($conexion, "DELETE FROM comunicados_curso WHERE id_comunicado = $id_comunicado AND id_curso = $id_curso");
        header("Location: curso.php?id=$id_curso&com=eliminado_ok#comunicados");
        exit();
    }

    // =========================================================
    // LÓGICA DE GESTIÓN DE MATERIALES SEGÚN PLAN DEL CREADOR
    // =========================================================
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_material']) && $_POST['accion_material'] === 'subir' && $es_creador) {
        if (!isset($_FILES['archivo_material']) || $_FILES['archivo_material']['error'] !== UPLOAD_ERR_OK) {
            $error_code = $_FILES['archivo_material']['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($error_code === UPLOAD_ERR_INI_SIZE || $error_code === UPLOAD_ERR_FORM_SIZE) {
                header("Location: curso.php?id=$id_curso&mat=exceso_tamano");
            } else {
                header("Location: curso.php?id=$id_curso&mat=error_subida");
            }
            exit();
        }

        $tamano_nuevo = (int)$_FILES['archivo_material']['size'];
        if ($tamano_nuevo <= 0) {
            header("Location: curso.php?id=$id_curso&mat=error_subida");
            exit();
        }

        if ($plan_usuario_actual === 'gratis') {
            $limite_individual = 5242880;  
            $limite_peso_total = 10485760; 

            if ($tamano_nuevo > $limite_individual) {
                header("Location: curso.php?id=$id_curso&mat=exceso_tamano");
                exit();
            }

            $sql_stats_mat = "SELECT COUNT(*) as total_archivos, COALESCE(SUM(tamano_bytes), 0) as peso_total FROM materiales WHERE id_curso = $id_curso";
            $res_stats_mat = mysqli_query($conexion, $sql_stats_mat);
            $stats_mat = mysqli_fetch_assoc($res_stats_mat);

            $cantidad_actual = (int)$stats_mat['total_archivos'];
            $peso_actual = (int)$stats_mat['peso_total'];

            if ($cantidad_actual >= 5) {
                header("Location: curso.php?id=$id_curso&mat=limite_archivos");
                exit();
            }

            if (($peso_actual + $tamano_nuevo) > $limite_peso_total) {
                header("Location: curso.php?id=$id_curso&mat=exceso_total");
                exit();
            }
        }

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
                        VALUES ($id_curso, '$nombre_original', '$ruta_relativa', $tamano_nuevo)";
            mysqli_query($conexion, $sql_ins);
            header("Location: curso.php?id=$id_curso&mat=exito");
            exit();
        } else {
            header("Location: curso.php?id=$id_curso&mat=error_subida");
            exit();
        }
    }

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

    // =========================================================
    // LÓGICA DEL FORO DE DUDAS Y DISPARO DE NOTIFICACIONES
    // =========================================================

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_foro']) && $_POST['accion_foro'] === 'crear_pregunta' && $puede_crear_pregunta) {
        $titulo_p = mysqli_real_escape_string($conexion, trim($_POST['titulo_pregunta']));
        $cuerpo_p = mysqli_real_escape_string($conexion, trim($_POST['cuerpo_pregunta']));
        
        if (!empty($titulo_p) && !empty($cuerpo_p)) {
            $sql_ins_p = "INSERT INTO foro_preguntas (id_curso, id_autor, titulo, cuerpo, estado) VALUES ($id_curso, $id_usuario_actual, '$titulo_p', '$cuerpo_p', 'abierta')";
            
            if (mysqli_query($conexion, $sql_ins_p)) {
                $titulo_notif = mysqli_real_escape_string($conexion, "Nueva pregunta en " . $curso['titulo']);
                $msj_notif = mysqli_real_escape_string($conexion, "$nombre_usuario_actual ha iniciado una nueva duda en el foro: '$titulo_p'");

                $sql_destinatarios = "
                    SELECT id_alumno AS id_destinatario FROM inscripciones WHERE id_curso = $id_curso AND id_alumno != $id_usuario_actual AND estado = 'aceptada'
                    UNION
                    SELECT id_creador AS id_destinatario FROM cursos WHERE id_curso = $id_curso AND id_creador != $id_usuario_actual
                ";
                $res_destinatarios = mysqli_query($conexion, $sql_destinatarios);

                while ($dest = mysqli_fetch_assoc($res_destinatarios)) {
                    $id_dest = (int)$dest['id_destinatario'];
                    mysqli_query($conexion, "
                        INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                        VALUES ($id_dest, '$titulo_notif', '$msj_notif', 'foro_pregunta', 0, NOW())
                    ");
                }

                header("Location: curso.php?id=$id_curso&foro=pregunta_ok#foro");
                exit();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_foro']) && $_POST['accion_foro'] === 'crear_respuesta' && $puede_participar_foro) {
        $id_pregunta = (int)$_POST['id_pregunta'];
        $cuerpo_r = mysqli_real_escape_string($conexion, trim($_POST['cuerpo_respuesta']));
        
        if (!empty($cuerpo_r) && $id_pregunta > 0) {
            $sql_info_p = "SELECT titulo, estado FROM foro_preguntas WHERE id_pregunta = $id_pregunta AND id_curso = $id_curso";
            $res_info_p = mysqli_query($conexion, $sql_info_p);
            $info_p = mysqli_fetch_assoc($res_info_p);

            if (!$info_p || $info_p['estado'] === 'cerrada') {
                header("Location: curso.php?id=$id_curso&foro=tema_cerrado#foro");
                exit();
            }

            $sql_ultima_r = "SELECT id_autor FROM foro_respuestas WHERE id_pregunta = $id_pregunta ORDER BY fecha_creacion DESC, id_respuesta DESC LIMIT 1";
            $res_ultima_r = mysqli_query($conexion, $sql_ultima_r);
            if ($row_ultima_r = mysqli_fetch_assoc($res_ultima_r)) {
                if ((int)$row_ultima_r['id_autor'] === $id_usuario_actual) {
                    header("Location: curso.php?id=$id_curso&foro=doble_respuesta#foro");
                    exit();
                }
            }

            $sql_ins_r = "INSERT INTO foro_respuestas (id_pregunta, id_autor, cuerpo, es_aceptada) VALUES ($id_pregunta, $id_usuario_actual, '$cuerpo_r', 0)";
            
            if (mysqli_query($conexion, $sql_ins_r)) {
                $titulo_preg = $info_p['titulo'];

                $titulo_notif = mysqli_real_escape_string($conexion, "Nueva respuesta en tu hilo");
                $msj_notif = mysqli_real_escape_string($conexion, "$nombre_usuario_actual respondió a la consulta sobre '$titulo_preg'");

                $sql_participantes = "
                    SELECT id_autor AS id_destinatario FROM foro_preguntas WHERE id_pregunta = $id_pregunta AND id_autor != $id_usuario_actual
                    UNION
                    SELECT id_autor AS id_destinatario FROM foro_respuestas WHERE id_pregunta = $id_pregunta AND id_autor != $id_usuario_actual
                ";
                $res_participantes = mysqli_query($conexion, $sql_participantes);

                while ($part = mysqli_fetch_assoc($res_participantes)) {
                    $id_dest = (int)$part['id_destinatario'];
                    mysqli_query($conexion, "
                        INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                        VALUES ($id_dest, '$titulo_notif', '$msj_notif', 'foro_respuesta', 0, NOW())
                    ");
                }

                header("Location: curso.php?id=$id_curso&foro=respuesta_ok#foro");
                exit();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_foro']) && $_POST['accion_foro'] === 'editar_respuesta' && $puede_participar_foro) {
        $id_respuesta = (int)$_POST['id_respuesta'];
        $id_pregunta = (int)$_POST['id_pregunta'];
        $cuerpo_editado = mysqli_real_escape_string($conexion, trim($_POST['cuerpo_respuesta']));

        if (!empty($cuerpo_editado) && $id_respuesta > 0 && $id_pregunta > 0) {
            $sql_chk_p = "SELECT estado FROM foro_preguntas WHERE id_pregunta = $id_pregunta AND id_curso = $id_curso";
            $res_chk_p = mysqli_query($conexion, $sql_chk_p);
            $row_chk_p = mysqli_fetch_assoc($res_chk_p);

            if ($row_chk_p && $row_chk_p['estado'] !== 'cerrada') {
                $sql_ultima_r = "SELECT id_respuesta, id_autor FROM foro_respuestas WHERE id_pregunta = $id_pregunta ORDER BY fecha_creacion DESC, id_respuesta DESC LIMIT 1";
                $res_ultima_r = mysqli_query($conexion, $sql_ultima_r);
                $row_ultima_r = mysqli_fetch_assoc($res_ultima_r);

                if ($row_ultima_r && (int)$row_ultima_r['id_respuesta'] === $id_respuesta && (int)$row_ultima_r['id_autor'] === $id_usuario_actual) {
                    mysqli_query($conexion, "UPDATE foro_respuestas SET cuerpo = '$cuerpo_editado' WHERE id_respuesta = $id_respuesta");
                    header("Location: curso.php?id=$id_curso&foro=editada_ok#foro");
                    exit();
                }
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_foro']) && $_POST['accion_foro'] === 'destacar_pregunta' && $es_creador) {
        $id_pregunta = (int)$_POST['id_pregunta'];
        
        $sql_chk_p = "SELECT id_autor, titulo, es_destacada FROM foro_preguntas WHERE id_pregunta = $id_pregunta AND id_curso = $id_curso";
        $res_chk_p = mysqli_query($conexion, $sql_chk_p);
        
        if ($row_p = mysqli_fetch_assoc($res_chk_p)) {
            $nuevo_estado_destacada = ($row_p['es_destacada'] == 1) ? 0 : 1;
            mysqli_query($conexion, "UPDATE foro_preguntas SET es_destacada = $nuevo_estado_destacada WHERE id_pregunta = $id_pregunta");

            $id_autor_preg = (int)$row_p['id_autor'];
            if ($nuevo_estado_destacada == 1 && $id_autor_preg != $id_usuario_actual) {
                $titulo_notif = mysqli_real_escape_string($conexion, "¡Tu pregunta fue destacada!");
                $msj_notif = mysqli_real_escape_string($conexion, "El docente destacó tu pregunta '" . $row_p['titulo'] . "' en el curso '" . $curso['titulo'] . "'");
                mysqli_query($conexion, "
                    INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                    VALUES ($id_autor_preg, '$titulo_notif', '$msj_notif', 'foro_destacada', 0, NOW())
                ");
            }

            header("Location: curso.php?id=$id_curso&foro=pregunta_destacada_ok#foro");
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_foro']) && $_POST['accion_foro'] === 'marcar_aceptada' && $puede_participar_foro) {
        $id_respuesta = (int)$_POST['id_respuesta'];
        $id_pregunta = (int)$_POST['id_pregunta'];
        
        $sql_chk_p = "SELECT id_autor, titulo, estado FROM foro_preguntas WHERE id_pregunta = $id_pregunta AND id_curso = $id_curso";
        $res_chk_p = mysqli_query($conexion, $sql_chk_p);
        
        if ($row_p = mysqli_fetch_assoc($res_chk_p)) {
            if (($es_creador || $row_p['id_autor'] == $id_usuario_actual) && $row_p['estado'] !== 'cerrada') {
                mysqli_query($conexion, "UPDATE foro_respuestas SET es_aceptada = 0 WHERE id_pregunta = $id_pregunta");
                mysqli_query($conexion, "UPDATE foro_respuestas SET es_aceptada = 1 WHERE id_respuesta = $id_respuesta AND id_pregunta = $id_pregunta");
                mysqli_query($conexion, "UPDATE foro_preguntas SET estado = 'cerrada' WHERE id_pregunta = $id_pregunta");

                $sql_resp_autor = "SELECT id_autor FROM foro_respuestas WHERE id_respuesta = $id_respuesta";
                $res_resp_autor = mysqli_query($conexion, $sql_resp_autor);
                $autor_resp = mysqli_fetch_assoc($res_resp_autor);
                $id_autor_respuesta = $autor_resp ? (int)$autor_resp['id_autor'] : 0;

                $titulo_preg = $row_p['titulo'];

                if ($id_autor_respuesta > 0 && $id_autor_respuesta != $id_usuario_actual) {
                    $titulo_dest = mysqli_real_escape_string($conexion, "¡Tu respuesta fue destacada!");
                    $msj_dest = mysqli_real_escape_string($conexion, "$nombre_usuario_actual ha marcado tu respuesta como aceptada en '$titulo_preg', cerrando el tema.");
                    mysqli_query($conexion, "
                        INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                        VALUES ($id_autor_respuesta, '$titulo_dest', '$msj_dest', 'foro_destacada', 0, NOW())
                    ");
                }

                header("Location: curso.php?id=$id_curso&foro=destacada_ok#foro");
                exit();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_foro']) && $_POST['accion_foro'] === 'eliminar_pregunta' && $puede_participar_foro) {
        $id_pregunta = (int)$_POST['id_pregunta'];
        $sql_chk_p = "SELECT id_autor, titulo FROM foro_preguntas WHERE id_pregunta = $id_pregunta AND id_curso = $id_curso";
        $res_chk_p = mysqli_query($conexion, $sql_chk_p);
        
        if ($row_p = mysqli_fetch_assoc($res_chk_p)) {
            $id_autor_pregunta = (int)$row_p['id_autor'];
            $titulo_preg = $row_p['titulo'];

            if ($es_creador || $id_autor_pregunta == $id_usuario_actual) {
                
                if ($id_autor_pregunta != $id_usuario_actual) {
                    $titulo_del = mysqli_real_escape_string($conexion, "Pregunta eliminada del foro");
                    $msj_del = mysqli_real_escape_string($conexion, "El docente eliminó tu pregunta '$titulo_preg' en el curso '" . $curso['titulo'] . "'");
                    mysqli_query($conexion, "
                        INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                        VALUES ($id_autor_pregunta, '$titulo_del', '$msj_del', 'foro_eliminada', 0, NOW())
                    ");
                }

                mysqli_query($conexion, "DELETE FROM foro_respuestas WHERE id_pregunta = $id_pregunta");
                mysqli_query($conexion, "DELETE FROM foro_preguntas WHERE id_pregunta = $id_pregunta");
                header("Location: curso.php?id=$id_curso&foro=eliminada_ok#foro");
                exit();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_foro']) && $_POST['accion_foro'] === 'eliminar_respuesta' && $puede_participar_foro) {
        $id_respuesta = (int)$_POST['id_respuesta'];
        $sql_chk_r = "
            SELECT fr.id_autor, fp.estado 
            FROM foro_respuestas fr
            INNER JOIN foro_preguntas fp ON fr.id_pregunta = fp.id_pregunta
            WHERE fr.id_respuesta = $id_respuesta AND fp.id_curso = $id_curso
        ";
        $res_chk_r = mysqli_query($conexion, $sql_chk_r);
        if ($row_r = mysqli_fetch_assoc($res_chk_r)) {
            if ($row_r['estado'] !== 'cerrada' && ($es_creador || $row_r['id_autor'] == $id_usuario_actual)) {
                mysqli_query($conexion, "DELETE FROM foro_respuestas WHERE id_respuesta = $id_respuesta");
                header("Location: curso.php?id=$id_curso&foro=respuesta_del_ok#foro");
                exit();
            }
        }
    }

    // Consultas para renderizado
    $sql_materiales = "SELECT * FROM materiales WHERE id_curso = $id_curso ORDER BY fecha_subida DESC";
    $resultado_materiales = mysqli_query($conexion, $sql_materiales);
    $total_materiales = mysqli_num_rows($resultado_materiales);

    $sql_peso_mat = "SELECT COALESCE(SUM(tamano_bytes), 0) as peso_total FROM materiales WHERE id_curso = $id_curso";
    $peso_total_materiales = (int)mysqli_fetch_assoc(mysqli_query($conexion, $sql_peso_mat))['peso_total'];
    $limite_materiales_gratis_alcanzado = ($plan_usuario_actual === 'gratis' && ($total_materiales >= 5 || $peso_total_materiales >= 10485760));
    $excede_limite_gratis_previo = ($plan_usuario_actual === 'gratis' && ($total_materiales > 5 || $peso_total_materiales > 10485760));

    // Consultar comunicados del curso
    $sql_comunicados = "SELECT * FROM comunicados_curso WHERE id_curso = $id_curso ORDER BY fecha_creacion DESC";
    $resultado_comunicados = mysqli_query($conexion, $sql_comunicados);
    $total_comunicados = mysqli_num_rows($resultado_comunicados);

    $sql_preguntas = "
        SELECT fp.*, u.nombre, u.apellido 
        FROM foro_preguntas fp
        INNER JOIN usuarios u ON fp.id_autor = u.id_usuario
        WHERE fp.id_curso = $id_curso
        ORDER BY fp.es_destacada DESC, fp.fecha_creacion DESC
    ";
    $resultado_preguntas = mysqli_query($conexion, $sql_preguntas);
    $total_preguntas = mysqli_num_rows($resultado_preguntas);

    $sql_tags_curso = "
        SELECT e.nombre 
        FROM curso_etiquetas ce 
        INNER JOIN etiquetas e ON ce.id_etiqueta = e.id_etiqueta 
        WHERE ce.id_curso = $id_curso 
        ORDER BY e.nombre ASC
    ";
    $res_tags_curso = mysqli_query($conexion, $sql_tags_curso);

    function formatearBytes($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    $tiene_imagen = (!empty($curso['imagen']) && file_exists('../uploads/cursos/' . $curso['imagen']));
    $clase_gradiente_banner = $tiene_imagen ? '' : 'curso-gradiente-' . (($curso['id_curso'] % 5) + 1);
    $bg_banner = $tiene_imagen
        ? 'style="background: linear-gradient(rgba(30, 30, 40, 0.6), rgba(30, 30, 40, 0.85)), url(\'../uploads/cursos/' . htmlspecialchars($curso['imagen']) . '\'); background-size: cover; background-position: center;"'
        : '';

    $titulo_pagina = $curso['titulo'] . ' - Cursolandia';
    $ruta = '../';
    require_once 'header.php' ;
?>

    <main class="container my-5">
        
        <?php if(isset($_GET['mat'])): ?>
            <?php if($_GET['mat'] == 'exito'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>Material subido correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['mat'] == 'exceso_tamano'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>El archivo supera el límite permitido de 5 MB por archivo individual.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['mat'] == 'limite_archivos'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Límite alcanzado: Las cuentas gratuitas solo pueden subir un máximo de 5 archivos por curso.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['mat'] == 'exceso_total'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Límite de almacenamiento alcanzado: El peso total combinado de los archivos en el Plan Gratuito no puede superar los 10 MB.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['mat'] == 'error_subida'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>No se pudo subir el archivo. Verifica que sea válido e inténtalo nuevamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['mat'] == 'eliminado'): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>Material eliminado.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(isset($_GET['com'])): ?>
            <?php if($_GET['com'] == 'creado_ok'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>Comunicado publicado y notificado a los alumnos con éxito.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['com'] == 'eliminado_ok'): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>Comunicado eliminado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(isset($_GET['foro'])): ?>
            <?php if($_GET['foro'] == 'doble_respuesta'): ?>
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>No puedes responder deux veces seguidas en la misma pregunta. Edita tu última respuesta o espera a que otro usuario participe.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['foro'] == 'tema_cerrado'): ?>
                <div class="alert alert-secondary alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-lock-fill me-2"></i>Este tema ya fue marcado como resuelto y cerrado; no admite nuevas respuestas ni ediciones.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['foro'] == 'editada_ok'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>Tu última respuesta fue actualizada correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['foro'] == 'pregunta_destacada_ok'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-star-fill me-2"></i>Se ha actualizado el estado de destacado de la pregunta correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Banner del Curso -->
        <div class="curso-header-banner <?php echo $clase_gradiente_banner; ?> mb-5 rounded-4 shadow-sm p-4 p-md-5" <?php echo $bg_banner; ?>>
            <div class="d-flex flex-column justify-content-center h-100">
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <?php if($curso['activo'] == 1): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php elseif($es_programado): ?>
                        <span class="badge bg-primary"><i class="bi bi-calendar-check me-1"></i>Programado (inicia el <?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Borrador</span>
                    <?php endif; ?>

                    <?php if($curso['es_pago'] == 1): ?>
                        <?php if(!$esta_inscripto): ?>
                            <span class="badge bg-warning text-dark fw-semibold">
                                <i class="bi bi-tag-fill me-1"></i><?php echo $costo_formateado; ?> ARS
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">De Pago</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge bg-secondary">Gratuito</span>
                    <?php endif; ?>

                    <?php if($curso['es_publico'] == 1): ?>
                        <span class="badge bg-info text-dark">Público</span>
                    <?php else: ?>
                        <span class="badge bg-dark text-white">Privado</span>
                    <?php endif; ?>
                </div>
                
                <h1 class="fw-bold text-white mb-2"><?php echo htmlspecialchars($curso['titulo']); ?></h1>
                <p class="text-white mb-0 fs-5" style="opacity: 0.9;">
                    Creado por: <span class="fw-bold text-white"><?php echo htmlspecialchars($curso['nombre'] . ' ' . $curso['apellido']); ?></span>
                </p>
            </div>
        </div>

        <div class="row g-5">
            
            <div class="col-lg-8">
                
                <section class="mb-5">
                    <h4 class="fw-bold mb-3">Acerca de este curso</h4>
                    <p class="text-secondary" style="line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                    
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <?php if(mysqli_num_rows($res_tags_curso) > 0): ?>
                            <?php while($tag = mysqli_fetch_assoc($res_tags_curso)): ?>
                                <span class="badge bg-light text-secondary border">#<?php echo htmlspecialchars($tag['nombre']); ?></span>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <span class="badge bg-light text-muted border">Sin etiquetas</span>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- SECCIÓN: COMUNICADOS DEL DOCENTE -->
                <section id="comunicados" class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0"><i class="bi bi-megaphone-fill text-primary me-2"></i>Comunicados del Docente (<?php echo $total_comunicados; ?>)</h4>
                        <?php if($es_creador && $curso_iniciado): ?>
                            <button class="btn fw-medium text-white shadow-sm btn-sm" style="background-color: var(--PRIMARY);" data-bs-toggle="modal" data-bs-target="#modalNuevoComunicado">
                                <i class="bi bi-plus-lg me-1"></i>Nuevo Comunicado
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if($es_creador && !$curso_iniciado): ?>
                        <div class="alert alert-info border-0 shadow-sm mb-4 rounded-3">
                            <i class="bi bi-calendar-event-fill me-2"></i>Podrás publicar comunicados a partir de la fecha de inicio del curso (<?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?>).
                        </div>
                    <?php endif; ?>

                    <?php if($total_comunicados > 0): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php while($com = mysqli_fetch_assoc($resultado_comunicados)): ?>
                                <div class="card border-0 shadow-sm rounded-4 border-start border-4 border-primary bg-white">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($com['titulo']); ?></h5>
                                            <div class="d-flex align-items-center gap-2">
                                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?php echo date("d/m/Y H:i", strtotime($com['fecha_creacion'])); ?></small>
                                                <?php if($es_creador): ?>
                                                    <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este comunicado?');">
                                                        <input type="hidden" name="accion_comunicado" value="eliminar">
                                                        <input type="hidden" name="id_comunicado" value="<?php echo $com['id_comunicado']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="Eliminar comunicado">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <p class="text-secondary mb-0" style="white-space: pre-wrap; line-height: 1.6;"><?php echo htmlspecialchars($com['cuerpo']); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm bg-light rounded-4">
                            <div class="card-body p-4 text-center text-muted">
                                <i class="bi bi-bell-slash display-6 mb-2 d-block opacity-50"></i>
                                Aún no hay comunicados publicados por el docente en este curso.
                            </div>
                        </div>
                    <?php endif; ?>
                </section>

                <section id="foro">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Foro de Dudas (<?php echo $total_preguntas; ?>)</h4>
                        <?php if($puede_crear_pregunta): ?>
                            <button class="btn fw-medium text-white shadow-sm" style="background-color: var(--PRIMARY);" data-bs-toggle="modal" data-bs-target="#modalNuevaPregunta">
                                <i class="bi bi-chat-left-text me-2"></i>Iniciar Pregunta
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if($esta_inscripto && !$curso_iniciado): ?>
                        <div class="alert alert-info border-0 shadow-sm mb-4 rounded-3">
                            <i class="bi bi-calendar-event-fill me-2"></i>El foro de dudas estará disponible para iniciar preguntas a partir de la fecha de inicio del curso (<?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?>).
                        </div>
                    <?php elseif($esta_inscripto && (int)$curso['activo'] === 0): ?>
                        <div class="alert alert-secondary border-0 shadow-sm mb-4 rounded-3">
                            <i class="bi bi-pause-circle-fill me-2"></i>Este curso se encuentra actualmente pausado o en borrador. Conservas acceso al material ya publicado, pero la participación en el foro está temporalmente deshabilitada.
                        </div>
                    <?php endif; ?>

                    <?php if($total_preguntas > 0): ?>
                        <?php while($pregunta = mysqli_fetch_assoc($resultado_preguntas)): ?>
                            <?php 
                                $id_preg = (int)$pregunta['id_pregunta'];
                                $tema_cerrado = ($pregunta['estado'] === 'cerrada');
                                $es_destacada = ((int)$pregunta['es_destacada'] === 1);

                                $sql_ultima = "SELECT id_respuesta, id_autor FROM foro_respuestas WHERE id_pregunta = $id_preg ORDER BY fecha_creacion DESC, id_respuesta DESC LIMIT 1";
                                $res_ultima = mysqli_query($conexion, $sql_ultima);
                                $ultima_resp = mysqli_fetch_assoc($res_ultima);
                                $id_ultima_respuesta = $ultima_resp ? (int)$ultima_resp['id_respuesta'] : 0;
                                $id_ultimo_autor = $ultima_resp ? (int)$ultima_resp['id_autor'] : 0;
                                $es_doble_respuesta = ($id_usuario_actual && $id_ultimo_autor === $id_usuario_actual);

                                $sql_respuestas = "
                                    SELECT fr.*, u.nombre, u.apellido 
                                    FROM foro_respuestas fr
                                    INNER JOIN usuarios u ON fr.id_autor = u.id_usuario
                                    WHERE fr.id_pregunta = $id_preg
                                    ORDER BY fr.es_aceptada DESC, fr.fecha_creacion ASC
                                ";
                                $res_respuestas = mysqli_query($conexion, $sql_respuestas);
                            ?>

                            <div class="card border-0 shadow-sm mb-4 rounded-4 <?php echo $es_destacada ? 'border border-warning' : ''; ?>" style="<?php echo $es_destacada ? 'border-left: 5px solid #ffc107 !important;' : ''; ?>">
                                <div class="card-body p-4">
                                    
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <h5 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($pregunta['titulo']); ?></h5>
                                                <?php if($es_destacada): ?>
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Pregunta Destacada</span>
                                                <?php endif; ?>
                                                <?php if($tema_cerrado): ?>
                                                    <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>Tema Cerrado</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">
                                                Por <span class="fw-medium text-dark"><?php echo htmlspecialchars($pregunta['nombre'] . ' ' . $pregunta['apellido']); ?></span> 
                                                • <?php echo date("d/m/Y H:i", strtotime($pregunta['fecha_creacion'])); ?>
                                            </small>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <?php if($es_creador): ?>
                                                <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="accion_foro" value="destacar_pregunta">
                                                    <input type="hidden" name="id_pregunta" value="<?php echo $id_preg; ?>">
                                                    <button type="submit" class="btn btn-sm <?php echo $es_destacada ? 'btn-warning text-dark' : 'btn-outline-warning text-muted'; ?>" title="<?php echo $es_destacada ? 'Quitar destacado' : 'Destacar pregunta'; ?>">
                                                        <i class="bi bi-star-fill"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if($puede_participar_foro && ($es_creador || $pregunta['id_autor'] == $id_usuario_actual)): ?>
                                                <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta pregunta y todas sus respuestas?');">
                                                    <input type="hidden" name="accion_foro" value="eliminar_pregunta">
                                                    <input type="hidden" name="id_pregunta" value="<?php echo $id_preg; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Eliminar pregunta">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <p class="text-secondary mb-3" style="white-space: pre-wrap;"><?php echo htmlspecialchars($pregunta['cuerpo']); ?></p>

                                    <?php if($puede_participar_foro && !$tema_cerrado): ?>
                                        <?php if($es_doble_respuesta): ?>
                                            <div class="alert alert-light border small text-muted py-2 px-3 mb-3">
                                                <i class="bi bi-info-circle me-1"></i>Tu respuesta es la más reciente en este hilo. Puedes <strong>editarla</strong> abajo o esperar a que otro usuario responda antes de publicar una nueva.
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-3">
                                                <button class="btn btn-sm btn-outline-secondary border-0 px-0" type="button" data-bs-toggle="collapse" data-bs-target="#responderCollapse<?php echo $id_preg; ?>">
                                                    <i class="bi bi-reply-fill me-1"></i>Responder
                                                </button>
                                            </div>

                                            <div class="collapse mb-3" id="responderCollapse<?php echo $id_preg; ?>">
                                                <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST" class="bg-light p-3 rounded-3 border">
                                                    <input type="hidden" name="accion_foro" value="crear_respuesta">
                                                    <input type="hidden" name="id_pregunta" value="<?php echo $id_preg; ?>">
                                                    <textarea name="cuerpo_respuesta" class="form-control custom-input mb-2" rows="2" placeholder="Escribe tu respuesta..." required></textarea>
                                                    <button type="submit" class="btn btn-purple btn-sm fw-medium">Publicar Respuesta</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if(mysqli_num_rows($res_respuestas) > 0): ?>
                                        <div class="mt-3 pt-3 border-top">
                                            <?php while($resp = mysqli_fetch_assoc($res_respuestas)): ?>
                                                <?php 
                                                    $id_resp_actual = (int)$resp['id_respuesta'];
                                                    $puede_editar_esta_resp = ($puede_participar_foro && !$tema_cerrado && $id_resp_actual === $id_ultima_respuesta && (int)$resp['id_autor'] === $id_usuario_actual);
                                                ?>
                                                <div class="p-3 mb-2 rounded-3 <?php echo ($resp['es_aceptada'] == 1) ? 'bg-light' : 'bg-body-tertiary'; ?>" style="<?php echo ($resp['es_aceptada'] == 1) ? 'border-left: 4px solid var(--PRIMARY);' : ''; ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <span class="fw-bold text-dark me-2"><?php echo htmlspecialchars($resp['nombre'] . ' ' . $resp['apellido']); ?></span>
                                                            <?php if($resp['es_aceptada'] == 1): ?>
                                                                <span class="badge text-white" style="background-color: var(--PRIMARY);"><i class="bi bi-star-fill me-1"></i>Respuesta Aceptada</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <small class="text-muted"><?php echo date("d/m/Y H:i", strtotime($resp['fecha_creacion'])); ?></small>
                                                    </div>

                                                    <p class="text-secondary mb-2" style="white-space: pre-wrap;"><?php echo htmlspecialchars($resp['cuerpo']); ?></p>

                                                    <?php if($puede_editar_esta_resp): ?>
                                                        <div class="collapse mb-2" id="editarRespCollapse<?php echo $id_resp_actual; ?>">
                                                            <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST" class="bg-white p-3 rounded-3 border">
                                                                <input type="hidden" name="accion_foro" value="editar_respuesta">
                                                                <input type="hidden" name="id_respuesta" value="<?php echo $id_resp_actual; ?>">
                                                                <input type="hidden" name="id_pregunta" value="<?php echo $id_preg; ?>">
                                                                <textarea name="cuerpo_respuesta" class="form-control custom-input mb-2" rows="2" required><?php echo htmlspecialchars($resp['cuerpo']); ?></textarea>
                                                                <div class="d-flex gap-2">
                                                                    <button type="submit" class="btn btn-purple btn-sm fw-medium">Guardar Edición</button>
                                                                    <button type="button" class="btn btn-light btn-sm border" data-bs-toggle="collapse" data-bs-target="#editarRespCollapse<?php echo $id_resp_actual; ?>">Cancelar</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="d-flex gap-3 align-items-center">
                                                        <?php if($puede_participar_foro && !$tema_cerrado && ($es_creador || $pregunta['id_autor'] == $id_usuario_actual) && $resp['es_aceptada'] == 0): ?>
                                                            <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar esta respuesta como aceptada y cerrar el tema?');">
                                                                <input type="hidden" name="accion_foro" value="marcar_aceptada">
                                                                <input type="hidden" name="id_respuesta" value="<?php echo $id_resp_actual; ?>">
                                                                <input type="hidden" name="id_pregunta" value="<?php echo $id_preg; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-success border-0 px-0">
                                                                    <i class="bi bi-check-circle-fill me-1"></i>Destacar Respuesta
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>

                                                        <?php if($puede_editar_esta_resp): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 px-0" data-bs-toggle="collapse" data-bs-target="#editarRespCollapse<?php echo $id_resp_actual; ?>">
                                                                <i class="bi bi-pencil-square me-1"></i>Editar Respuesta
                                                            </button>
                                                        <?php endif; ?>

                                                        <?php if($puede_participar_foro && !$tema_cerrado && ($es_creador || $resp['id_autor'] == $id_usuario_actual)): ?>
                                                            <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST" class="d-inline ms-auto" onsubmit="return confirm('¿Eliminar respuesta?');">
                                                                <input type="hidden" name="accion_foro" value="eliminar_respuesta">
                                                                <input type="hidden" name="id_respuesta" value="<?php echo $id_resp_actual; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                                    <i class="bi bi-trash-fill"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4 text-center text-muted">
                                <i class="bi bi-chat-dots display-4 mb-2 d-block opacity-50"></i>
                                Aún no hay preguntas formuladas en este curso.<?php echo $puede_crear_pregunta ? ' ¡Haz la primera!' : ''; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </section>
            </div>

            <!-- TARJETA LATERAL DE INFORMACIÓN E INSCRIPCIÓN -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        <?php if($es_creador): ?>
                            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-video3 me-2"></i>Eres el docente</h5>
                        <?php elseif($estado_inscripcion === 'aceptada'): ?>
                            <h5 class="fw-bold text-success mb-3"><i class="bi bi-check-circle me-2"></i>Inscripto</h5>
                        <?php elseif($estado_inscripcion === 'pendiente'): ?>
                            <h5 class="fw-bold text-warning mb-3"><i class="bi bi-clock-history me-2"></i>Inscripción Pendiente</h5>
                        <?php else: ?>
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle me-2"></i>Información</h5>
                        <?php endif; ?>

                        <?php if(!$esta_inscripto): ?>
                            <div class="p-3 mb-3 rounded-3 bg-light border">
                                <span class="text-muted small d-block mb-1">Valor del curso</span>
                                <?php if($curso['es_pago'] == 1): ?>
                                    <span class="fs-3 fw-bold text-dark"><?php echo $costo_formateado; ?></span>
                                    <span class="text-muted small">ARS</span>
                                <?php else: ?>
                                    <span class="fs-4 fw-bold text-success">Gratuito</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <ul class="list-unstyled text-start text-secondary mb-4">
                            <li class="mb-2"><i class="bi bi-calendar-event me-2"></i><strong>Inicio:</strong> <?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?></li>
                            <li class="mb-2"><i class="bi bi-calendar-x me-2"></i><strong>Fin:</strong> <?php echo date("d/m/Y", strtotime($curso['fecha_fin'])); ?></li>
                            <li><i class="bi bi-people me-2"></i><strong>Cupo:</strong> <?php echo $total_alumnos; ?> / <?php echo $curso['cupo'] ? $curso['cupo'] : 'Sin límite'; ?> alumnos</li>
                        </ul>
                        
                        <?php if(!$id_usuario_actual): ?>
                            <a href="login.php" class="btn btn-purple w-100 fw-medium">Inicia sesión para inscribirte</a>
                        <?php elseif($es_creador): ?>
                            <a href="editarCurso.php?id=<?php echo $id_curso; ?>" class="btn btn-outline-primary w-100 fw-medium">Editar Datos del Curso</a>
                        <?php elseif($estado_inscripcion === 'aceptada'): ?>
                            <form action="procesarInscripcion.php" method="POST">
                                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                                <input type="hidden" name="accion" value="abandonar">
                                <button type="submit" class="btn btn-outline-danger w-100 fw-medium">Abandonar Curso</button>
                            </form>
                        <?php elseif($estado_inscripcion === 'pendiente'): ?>
                            <div class="alert alert-warning border-0 p-2 text-center small mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>El docente debe validar tu vacante para cursos de pago.
                            </div>
                            <form action="procesarInscripcion.php" method="POST">
                                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                                <input type="hidden" name="accion" value="abandonar">
                                <button type="submit" class="btn btn-outline-secondary w-100 fw-medium">Cancelar Solicitud</button>
                            </form>
                        <?php elseif($curso['activo'] == 0): ?>
                            <div class="alert alert-secondary border-0 p-2 text-center small mb-0">
                                <i class="bi bi-clock-history me-1"></i>
                                <?php echo $es_programado ? 'Este curso aún no inició. Las inscripciones abrirán el ' . date("d/m/Y", strtotime($curso['fecha_inicio'])) . '.' : 'Este curso no se encuentra disponible para inscripciones.'; ?>
                            </div>
                        <?php else: ?>
                            <form action="procesarInscripcion.php" method="POST">
                                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                                <input type="hidden" name="accion" value="inscribir">
                                <?php $hay_cupo = ($curso['cupo'] === null || $total_alumnos < $curso['cupo']); ?>
                                <button type="submit" class="btn btn-purple w-100 fw-medium" <?php echo !$hay_cupo ? 'disabled' : ''; ?>>
                                    <?php echo $hay_cupo ? ($curso['es_pago'] == 1 ? 'Solicitar Inscripción (' . $costo_formateado . ')' : 'Inscribirse al Curso') : 'Cupo Lleno'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

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
                    <div class="card-footer bg-white border-top p-3 text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary w-100 fw-medium" data-bs-toggle="modal" data-bs-target="#modalSubirMaterial" style="color: var(--PRIMARY); border-color: var(--PRIMARY);" <?php echo $limite_materiales_gratis_alcanzado ? 'disabled' : ''; ?>>
                            <i class="bi bi-cloud-arrow-up me-2"></i>Subir Material
                        </button>
                        <?php if($plan_usuario_actual === 'pro'): ?>
                            <div class="form-text mt-2 text-success" style="font-size: 0.75rem;">
                                <i class="bi bi-star-fill me-1"></i>Plan Pro: Subida de archivos y almacenamiento ilimitados.
                            </div>
                        <?php else: ?>
                            <div class="form-text mt-2" style="font-size: 0.75rem;">
                                Plan Gratuito: <?php echo $total_materiales; ?>/5 archivos • <?php echo formatearBytes($peso_total_materiales); ?> / 10 MB total (máx. 5 MB/archivo).
                            </div>
                            <?php if($excede_limite_gratis_previo): ?>
                                <div class="form-text text-warning fw-medium mt-1" style="font-size: 0.73rem;">
                                    Conservas los archivos subidos durante tu Plan Pro, pero debes liberar espacio o renovar Pro para subir nuevos.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <!-- MODAL NUEVO COMUNICADO -->
    <?php if($es_creador && $curso_iniciado): ?>
    <div class="modal fade" id="modalNuevoComunicado" tabindex="-1" aria-labelledby="modalNuevoComunicadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-dark" id="modalNuevoComunicadoLabel">
                        <i class="bi bi-megaphone color-logo me-2"></i>Publicar Comunicado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST">
                    <input type="hidden" name="accion_comunicado" value="crear">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="titulo_comunicado" class="form-label fw-medium text-dark">Título del Comunicado</label>
                            <input type="text" class="form-control custom-input" id="titulo_comunicado" name="titulo_comunicado" placeholder="Ej: Suspensión de clase / Aviso importante" required>
                        </div>
                        <div class="mb-3">
                            <label for="cuerpo_comunicado" class="form-label fw-medium text-dark">Mensaje</label>
                            <textarea class="form-control custom-input" id="cuerpo_comunicado" name="cuerpo_comunicado" rows="4" placeholder="Escribe el mensaje o aviso para los alumnos..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-3">
                        <button type="button" class="btn btn-light border fw-medium" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-purple fw-bold px-4 text-white">Publicar y Notificar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($puede_crear_pregunta): ?>
    <div class="modal fade" id="modalNuevaPregunta" tabindex="-1" aria-labelledby="modalNuevaPreguntaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-dark" id="modalNuevaPreguntaLabel">
                        <i class="bi bi-chat-left-text color-logo me-2"></i>Nueva Pregunta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="curso.php?id=<?php echo $id_curso; ?>" method="POST">
                    <input type="hidden" name="accion_foro" value="crear_pregunta">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="titulo_pregunta" class="form-label fw-medium text-dark">Título o Asunto</label>
                            <input type="text" class="form-control custom-input" id="titulo_pregunta" name="titulo_pregunta" placeholder="Ej: Error al conectar la base de datos" required>
                        </div>
                        <div class="mb-3">
                            <label for="cuerpo_pregunta" class="form-label fw-medium text-dark">Detalle de tu consulta</label>
                            <textarea class="form-control custom-input" id="cuerpo_pregunta" name="cuerpo_pregunta" rows="4" placeholder="Explica tu duda lo más detalladamente posible..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-3">
                        <button type="button" class="btn btn-light border fw-medium" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-purple fw-bold px-4">Publicar Pregunta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                            <?php if($plan_usuario_actual === 'pro'): ?>
                                <div class="form-text text-success">Plan Pro: Sin límite de cantidad ni de peso por curso.</div>
                            <?php else: ?>
                                <div class="form-text">Plan Gratuito: Máximo 5 MB por archivo individual, hasta 5 archivos y 10 MB en total por curso.</div>
                            <?php endif; ?>
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