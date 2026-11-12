<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['id_usuario'])) {
        header("Location: ../index.php");
        exit();
    }

    require_once 'conexion.php';

    $id_usuario = (int)$_SESSION['id_usuario'];

    // ACCIÓN: Marcar todas las notificaciones del usuario como leídas
    if (isset($_GET['accion']) && $_GET['accion'] === 'marcar_todas') {
        mysqli_query($conexion, "UPDATE notificaciones SET leida = 1 WHERE id_usuario = $id_usuario");
        header("Location: notificaciones.php");
        exit();
    }

    // ACCIÓN: Marcar una notificación individual como leída al hacer clic
    if (isset($_GET['marcar']) && is_numeric($_GET['marcar'])) {
        $id_notif = (int)$_GET['marcar'];
        mysqli_query($conexion, "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = $id_notif AND id_usuario = $id_usuario");
        header("Location: notificaciones.php");
        exit();
    }

    // Consulta de notificaciones del usuario activo
    $sql_notificaciones = "
        SELECT * FROM notificaciones 
        WHERE id_usuario = $id_usuario 
        ORDER BY fecha_creacion DESC
    ";
    $resultado_notificaciones = mysqli_query($conexion, $sql_notificaciones);
    $total_notificaciones = mysqli_num_rows($resultado_notificaciones);

    // Función auxiliar para formatear la antigüedad
    function haceCuanto($fecha) {
        $timestamp = strtotime($fecha);
        $diferencia = time() - $timestamp;
        
        if ($diferencia < 60) {
            return 'Hace un momento';
        } elseif ($diferencia < 3600) {
            $mins = floor($diferencia / 60);
            return "Hace $mins min" . ($mins > 1 ? 's' : '');
        } elseif ($diferencia < 86400) {
            $horas = floor($diferencia / 3600);
            return "Hace $horas hora" . ($horas > 1 ? 's' : '');
        } elseif ($diferencia < 172800) {
            return 'Ayer';
        } else {
            return date("d/m/Y", $timestamp);
        }
    }

    $titulo_pagina = 'Notificaciones - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 
?>

    <main class="container my-5">
        
        <!-- Encabezado de Notificaciones -->
        <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
            <div>
                <h2 class="fw-bold text-dark mb-1">Notificaciones</h2>
                <p class="text-muted mb-0">Mantente al día con la actividad de tus cursos y cuenta.</p>
            </div>
            <?php if($total_notificaciones > 0): ?>
                <a href="notificaciones.php?accion=marcar_todas" class="btn btn-sm btn-outline-secondary fw-medium">
                    <i class="bi bi-check2-all me-1"></i>Marcar todas leídas
                </a>
            <?php endif; ?>
        </div>

        <!-- Contenedor de la Lista -->
        <?php if($total_notificaciones > 0): ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="list-group list-group-flush">
                    
                    <?php while($notif = mysqli_fetch_assoc($resultado_notificaciones)): ?>
                        <?php 
                            $es_leida = ($notif['leida'] == 1);
                            $tipo = $notif['tipo'];

                            // Configuración de ícono y clases visuales por tipo
                            $icono = 'bi-bell-fill';
                            $clase_bg = 'bg-secondary bg-opacity-10 text-secondary';

                            if ($tipo === 'foro_pregunta') {
                                $icono = 'bi-chat-left-text-fill';
                                $clase_bg = 'bg-primary bg-opacity-10 text-primary';
                            } elseif ($tipo === 'foro_respuesta') {
                                $icono = 'bi-reply-fill';
                                $clase_bg = 'bg-info bg-opacity-10 text-info';
                            } elseif ($tipo === 'foro_destacada') {
                                $icono = 'bi-star-fill';
                                $clase_bg = 'bg-warning bg-opacity-25 text-warning';
                            } elseif ($tipo === 'foro_eliminada') {
                                $icono = 'bi-trash-fill';
                                $clase_bg = 'bg-danger bg-opacity-10 text-danger';
                            } elseif ($tipo === 'plan_pro' || $tipo === 'sistema') {
                                $icono = 'bi-patch-check-fill';
                                $clase_bg = 'bg-success bg-opacity-10 text-success';
                            }
                        ?>

                        <a href="notificaciones.php?marcar=<?php echo $notif['id_notificacion']; ?>" class="list-group-item list-group-item-action p-4 <?php echo !$es_leida ? 'notif-unread border-start-primary' : ''; ?>">
                            <div class="d-flex gap-3">
                                <div class="notif-icon-box <?php echo $clase_bg; ?>">
                                    <i class="bi <?php echo $icono; ?> fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($notif['titulo']); ?></h6>
                                        <small class="<?php echo !$es_leida ? 'text-primary fw-medium' : 'text-muted'; ?> text-nowrap ms-2">
                                            <?php echo haceCuanto($notif['fecha_creacion']); ?>
                                        </small>
                                    </div>
                                    <p class="text-secondary mb-0 small"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                                </div>

                                <?php if(!$es_leida): ?>
                                    <!-- Punto indicador de "no leído" -->
                                    <div class="align-self-center ms-2">
                                        <span class="p-2 bg-primary border border-light rounded-circle d-inline-block"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endwhile; ?>

                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                <i class="bi bi-bell-slash display-4 mb-3 opacity-50"></i>
                <h5 class="fw-bold text-dark">Sin notificaciones</h5>
                <p class="mb-0">No tienes ninguna notificación registrada en tu cuenta por el momento.</p>
            </div>
        <?php endif; ?>
    </main>

<?php require_once 'footer.php'; ?>