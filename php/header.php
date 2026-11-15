<?php

// Si la sesión no esta iniciada, la iniciamos
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

// Consultamos la cantidad de notificaciones no leídas y sincronizamos el plan del usuario activo
$cant_notif_no_leidas = 0;
$imagen_perfil_nav = 'default_avatar.png';

if (isset($_SESSION['id_usuario'])) {
    // Protección para no incluir conexion.php si ya fue incluido en el archivo principal
    if (!isset($conexion)) {
        require_once __DIR__ . '/conexion.php';
    }
    $id_user_nav = (int)$_SESSION['id_usuario'];

    // Sincronizamos plan, rol e imagen de perfil desde la BD
    $sql_user_nav = "SELECT plan, rol, vencimiento_pro, imagen_perfil FROM usuarios WHERE id_usuario = $id_user_nav";
    $res_user_nav = mysqli_query($conexion, $sql_user_nav);
    if ($res_user_nav && mysqli_num_rows($res_user_nav) > 0) {
        $datos_user_nav = mysqli_fetch_assoc($res_user_nav);
        $plan_actual = $datos_user_nav['plan'];

        // Si el Plan Pro tiene fecha de vencimiento y ya pasó, vuelve automáticamente a gratis
        if ($plan_actual === 'pro' && !empty($datos_user_nav['vencimiento_pro']) && $datos_user_nav['vencimiento_pro'] < date('Y-m-d')) {
            mysqli_query($conexion, "UPDATE usuarios SET plan = 'gratis', vencimiento_pro = NULL WHERE id_usuario = $id_user_nav");
            $plan_actual = 'gratis';
        }

        $_SESSION['plan'] = $plan_actual;
        $_SESSION['rol'] = $datos_user_nav['rol'];
        
        if (!empty($datos_user_nav['imagen_perfil'])) {
            $imagen_perfil_nav = $datos_user_nav['imagen_perfil'];
        }
    }

    $sql_nav_notif = "SELECT COUNT(*) as total FROM notificaciones WHERE id_usuario = $id_user_nav AND leida = 0";
    $res_nav_notif = mysqli_query($conexion, $sql_nav_notif);
    if ($res_nav_notif) {
        $cant_notif_no_leidas = mysqli_fetch_assoc($res_nav_notif)['total'];
    }
}

// Detectamos el nombre del archivo actual para marcar la pestaña activa en el nav
$pagina_actual = basename($_SERVER['PHP_SELF']);
// Si estamos en la raíz (index.php), asignamos un identificador único
$es_index = ($pagina_actual === 'index.php');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina : 'Cursolandia'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Usamos la variable $ruta para no romper la ruta del CSS -->
    <link rel="stylesheet" href="<?php echo $ruta; ?>css/estilos.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-white custom-navbar py-3 border-bottom">
            <div class="container-fluid px-lg-5">
                
                <a class="navbar-brand d-flex align-items-center gap-2 fw-bold custom-brand" href="<?php echo $ruta; ?>index.php">
                    <i class="bi bi-mortarboard-fill color-logo fs-3"></i>
                    <span>Cursolandia<span class="color-logo">.com</span></span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Enlaces principales centrados -->
                    <ul class="navbar-nav mx-auto align-items-lg-center text-center gap-3 my-2 my-lg-0">
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap <?php echo ($pagina_actual === 'nuevoCurso.php') ? 'active' : ''; ?>" href="<?php echo $ruta; ?>php/nuevoCurso.php">Nuevo Curso</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap <?php echo (in_array($pagina_actual, ['misCursos.php', 'editarCurso.php', 'curso.php'])) ? 'active' : ''; ?>" href="<?php echo $ruta; ?>php/misCursos.php">Mis Cursos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap <?php echo ($pagina_actual === 'cursosRecomendados.php') ? 'active' : ''; ?>" href="<?php echo $ruta; ?>php/cursosRecomendados.php">Cursos Recomendados</a>
                        </li>
                        
                        <!-- ZONA RESTRINGIDA: Solo visible para Administradores -->
                        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap <?php echo ($pagina_actual === 'solicitudesPro.php') ? 'active' : ''; ?>" href="<?php echo $ruta; ?>php/solicitudesPro.php">Solicitudes Plan Pro</a>
                        </li>                 
                        <?php endif; ?>
                    </ul>

                   <!-- ZONA DINÁMICA: Envuelto dentro del collapse para mantener alineación en móviles y escritorio -->
                    <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center gap-3 mt-3 mt-lg-0">
                        
                        <?php if(isset($_SESSION['id_usuario'])): ?>
                            <!-- SI ESTÁ LOGUEADO: Campana con enlace directo y contador Badge -->
                            <a class="text-decoration-none position-relative" href="<?php echo $ruta; ?>php/notificaciones.php" title="Notificaciones">
                                <i class="bi bi-bell-fill color-logo fs-4"></i>
                                <?php if($cant_notif_no_leidas > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        <?php echo $cant_notif_no_leidas > 99 ? '99+' : $cant_notif_no_leidas; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Dropdown de Perfil -->
                            <div class="dropdown text-center">
                                <a href="#" class="text-decoration-none d-flex align-items-center justify-content-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php if (!empty($imagen_perfil_nav) && $imagen_perfil_nav !== 'default_avatar.png' && file_exists(__DIR__ . '/../uploads/perfiles/' . $imagen_perfil_nav)): ?>
                                        <img src="<?php echo $ruta; ?>uploads/perfiles/<?php echo htmlspecialchars($imagen_perfil_nav); ?>" alt="Avatar" class="rounded-circle border" style="width: 35px; height: 35px; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="bi bi-person-circle color-logo fs-3"></i>
                                    <?php endif; ?>
                                    <span class="text-dark fw-medium"><?php echo $_SESSION['nombre']; ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                                    <li><a class="dropdown-item py-2" href="<?php echo $ruta; ?>php/perfil.php"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                                    <?php if($_SESSION['plan'] == 'pro'): ?>
                                        <li><span class="dropdown-item py-2 text-warning fw-bold"><i class="bi bi-star-fill me-2"></i>Cuenta PRO</span></li>
                                    <?php endif; ?>
                                    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
                                        <li><span class="dropdown-item py-2 text-danger fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Admin</span></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item py-2 text-danger fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#modalCerrarSesion"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                                </ul>
                            </div>

                        <?php else: ?>
                            <!-- SI NO ESTÁ LOGUEADO: Botones con ancho ajustado (sin w-100) -->
                            <a href="<?php echo $ruta; ?>php/login.php" class="btn btn-outline-primary fw-medium px-4" style="color: var(--PRIMARY); border-color: var(--PRIMARY);">Ingresar</a>
                            <a href="<?php echo $ruta; ?>php/register.php" class="btn btn-purple fw-medium px-4 text-white">Registrarse</a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- MODAL DE CONFIRMACIÓN PARA CERRAR SESIÓN -->
    <div class="modal fade" id="modalCerrarSesion" tabindex="-1" aria-labelledby="modalCerrarSesionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4 text-dark">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold" id="modalCerrarSesionLabel">
                        <i class="bi bi-box-arrow-right text-danger me-2"></i>Cerrar Sesión
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-exclamation-circle text-warning display-4 mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark mb-2">¿Estás seguro que deseas salir?</h5>
                    <p class="text-muted small mb-0">Tendrás que volver a ingresar tus credenciales para acceder a tu cuenta en Cursolandia.</p>
                </div>
                <div class="modal-footer border-top p-3 d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border fw-medium px-4" data-bs-dismiss="modal">Cancelar</button>
                    <a href="<?php echo $ruta; ?>php/logout.php" class="btn btn-danger fw-bold px-4">Sí, cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>