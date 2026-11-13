<?php

if(session_status() == PHP_SESSION_NONE){
    session_start();
}

require_once 'conexion.php';
$mensaje = "";

if(isset($_POST['botonLogin'])){
     
    $emailUsuario = mysqli_real_escape_string($conexion, $_POST['emailUsuario']);
    $passwordUsuario = $_POST['passwordUsuario'];

    $sql_verificar = "
    SELECT * FROM usuarios
    WHERE email='$emailUsuario'
    ";

    $resultado = mysqli_query($conexion, $sql_verificar);

    if(mysqli_num_rows($resultado) > 0){
        $usuario = mysqli_fetch_array($resultado);

        if($passwordUsuario === $usuario['password']){
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['apellido'] = $usuario['apellido'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['plan'] = $usuario['plan'];

        header('Location: ../index.php');
        exit();
        }else{
            $mensaje = "La Contraseña es incorrecta";
        }
    }else{
        $mensaje = "No existe una cuenta con ese email";
    }


}

$titulo_pagina = 'Iniciar Sesión - Cursolandia';
$ruta = '../';
require_once 'header.php';

?>

<main class="container my-5 d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card shadow-sm border-0 rounded-4 p-4 p-lg-5" style="width: 100%; max-width: 450px;">
        
        <div class="text-center mb-4">
            <i class="bi bi-mortarboard-fill color-logo fs-1"></i>
            <h3 class="fw-bold text-dark mt-2">¡Hola de nuevo!</h3>
            <p class="text-muted">Ingresa a tu cuenta de Cursolandia</p>
        </div>

        <!-- Alerta de error -->
        <?php if ($mensaje != ''): ?>
            <div class="alert alert-danger p-2 text-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="emailUsuario" class="form-label fw-medium text-dark">Correo Electrónico</label>
                <input type="text" class="form-control custom-input" id="emailUsuario" name="emailUsuario" required placeholder="ejemplo@correo.com">
            </div>
            
            <div class="mb-4">
                <label for="passwordUsuario" class="form-label fw-medium text-dark">Contraseña</label>
                <input type="password" class="form-control custom-input" id="passwordUsuario" name="passwordUsuario" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-purple w-100 fw-bold py-2 mb-3" name="botonLogin">Iniciar Sesión</button>
            
            <div class="text-center">
                <!-- Enlace hacia tu archivo register.php -->
                <span class="text-muted small">¿No tienes cuenta? <a href="register.php" class="text-decoration-none fw-medium" style="color: var(--PRIMARY);">Regístrate acá</a></span>
            </div>
        </form>
        
    </div>
</main>

<?php require_once 'footer.php'; ?>