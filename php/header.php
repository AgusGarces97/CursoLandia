<?php

// Si la sesión no esta iniciada, la iniciamos
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

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
                    <ul class="navbar-nav mx-auto align-items-center gap-3 my-2 my-lg-0">
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="<?php echo $ruta; ?>php/nuevoCurso.php">Nuevo Curso</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="<?php echo $ruta; ?>php/misCursos.php">Mis Cursos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="<?php echo $ruta; ?>php/cursosRecomendados.php">Cursos Recomendados</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-link text-nowrap" href="<?php echo $ruta; ?>php/solicitudesPro.php">Solicitudes Plan Pro</a>
                        </li>                 
                    </ul>

                   <!-- ZONA DINÁMICA: Cambia si hay sesión o no -->
                        <div class="d-flex align-items-center gap-4 mt-2 mt-lg-0">
                            
                            <?php if(isset($_SESSION['id_usuario'])): ?>
                                <!-- SI ESTÁ LOGUEADO -->
                                <a class="text-decoration-none" href="<?php echo $ruta; ?>php/notificaciones.php">
                                    <i class="bi bi-bell-fill color-logo fs-4"></i>
                                </a>
                                
                                <!-- Dropdown de Perfil -->
                                <div class="dropdown">
                                    <a href="#" class="text-decoration-none d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-person-circle color-logo fs-3"></i>
                                        <span class="text-dark fw-medium"><?php echo $_SESSION['nombre']; ?></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                                        <li><a class="dropdown-item py-2" href="<?php echo $ruta; ?>php/perfil.php"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                                        <?php if($_SESSION['plan'] == 'pro'): ?>
                                            <li><span class="dropdown-item py-2 text-warning fw-bold"><i class="bi bi-star-fill me-2"></i>Cuenta PRO</span></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item py-2 text-danger fw-medium" href="<?php echo $ruta; ?>php/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                                    </ul>
                                </div>

                            <?php else: ?>
                                <!-- SI NO ESTÁ LOGUEADO -->
                                <a href="<?php echo $ruta; ?>php/login.php" class="btn btn-outline-primary fw-medium px-4" style="color: var(--PRIMARY); border-color: var(--PRIMARY);">Ingresar</a>
                                <a href="<?php echo $ruta; ?>php/register.php" class="btn btn-purple fw-medium px-4">Registrarse</a>
                            <?php endif; ?>

                        </div>
                </div>
            </div>
        </nav>
    </header>