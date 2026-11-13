<?php

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }   

    require_once 'conexion.php';

    $mensaje = '';

    if(isset($_POST['botonCrearCuenta'])){

        $nombreUsuario = mysqli_real_escape_string($conexion, trim($_POST['nombreUsuario']));
        $apellidoUsuario = mysqli_real_escape_string($conexion, trim($_POST['apellidoUsuario']));
        $emailUsuario = mysqli_real_escape_string($conexion, trim($_POST['emailUsuario']));
        $passwordUsuario = $_POST['passwordUsuario'];

        // Backend fallback: Limpieza de DNI
        $dniUsuario = mysqli_real_escape_string($conexion, str_replace([' ', '.'], '', $_POST['dniUsuario']));
        $edadUsuario = (int)$_POST['edadUsuario'];
        $antecedentesUsuario = mysqli_real_escape_string($conexion, trim($_POST['antecedentesUsuario']));

        if($edadUsuario < 10){
             $mensaje = "<div class='alert alert-danger text-center' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>Debes tener al menos 10 años para registrarte.
                    </div>";
        } else {
            $sql_verificar = "
            SELECT id_usuario from usuarios
            WHERE email='$emailUsuario' OR dni='$dniUsuario'
            ";

            $resultadoCheck = mysqli_query($conexion, $sql_verificar);

            if(mysqli_num_rows($resultadoCheck) > 0){
                $mensaje = "<div class='alert alert-warning text-center' role='alert'>
                            <i class='bi bi-exclamation-triangle-fill me-2'></i>El correo o DNI ya está registrado.
                        </div>";
            }else{
                $passwordHash = password_hash($passwordUsuario, PASSWORD_DEFAULT);
                $password_esc = mysqli_real_escape_string($conexion, $passwordHash);

                $sql_insert = "
                INSERT INTO usuarios (nombre, apellido, email, password, dni, edad, antecedentes)
                VALUES ('$nombreUsuario', '$apellidoUsuario', '$emailUsuario', '$password_esc', '$dniUsuario', '$edadUsuario', '$antecedentesUsuario')
                ";

                $resultadoInsert = mysqli_query($conexion, $sql_insert);
                if($resultadoInsert){

                    $idNuevoUsuario = mysqli_insert_id($conexion);

                    $_SESSION['id_usuario'] = $idNuevoUsuario;
                    $_SESSION['nombre'] = $nombreUsuario;
                    $_SESSION['apellido'] = $apellidoUsuario;
                    $_SESSION['rol'] = 'regular';
                    $_SESSION['plan'] = 'gratis';

                    header("Location: ../index.php");
                    exit();
                }else{
                    $mensaje = "<div class='alert alert-danger text-center' role='alert'>
                                Ocurrió un error: " . mysqli_error($conexion) . "
                            </div>";
                }
            }
        }
    }

    $titulo_pagina = 'Crear Cuenta';
    $ruta = '../';
    require_once 'header.php' ;

?>

<main class="container my-5 d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card shadow-sm border-0 rounded-4 p-4 p-lg-5" style="width: 100%; max-width: 500px;">
        
        <div class="text-center mb-4">
            <i class="bi bi-mortarboard-fill color-logo fs-1"></i>
            <h3 class="fw-bold text-dark mt-2">Únete a Cursolandia</h3>
            <p class="text-muted">Crea tu cuenta gratuita para empezar a aprender o enseñar.</p>
        </div>

        <?php echo $mensaje; ?>

        <form action="register.php" method="POST" id="formRegistro">
            
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="nombre" class="form-label fw-medium text-dark">Nombre</label>
                    <input type="text" class="form-control custom-input" id="nombre" name="nombreUsuario" required placeholder="Ej: Juan">
                </div>
                <div class="col-sm-6">
                    <label for="apellido" class="form-label fw-medium text-dark">Apellido</label>
                    <input type="text" class="form-control custom-input" id="apellido" name="apellidoUsuario" required placeholder="Ej: Pérez">
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-medium text-dark">Correo Electrónico</label>
                <input type="email" class="form-control custom-input" id="email" name="emailUsuario" required placeholder="ejemplo@correo.com">
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label fw-medium text-dark">Contraseña</label>
                <input type="password" class="form-control custom-input" id="password" name="passwordUsuario" required placeholder="Crea una contraseña segura" minlength="6">
            </div>

            <!-- DNI y EDAD -->
            <div class="row g-3 mb-3">
                <div class="col-sm-8">
                    <label for="dni" class="form-label fw-medium text-dark">DNI <span class="text-danger">*</span></label>
                    <input type="text" class="form-control custom-input" id="dni" name="dniUsuario" required placeholder="Sin puntos ni espacios">
                    <div id="error-dni" class="text-danger small mt-1" style="display: none;">El DNI debe contener solo números, sin puntos ni espacios.</div>
                </div>
                <div class="col-sm-4">
                    <label for="edad" class="form-label fw-medium text-dark">Edad <span class="text-danger">*</span></label>
                    <input type="number" class="form-control custom-input" id="edad" name="edadUsuario" required min="1" max="99">
                    <div id="error-edad" class="text-danger small mt-1" style="display: none;">Debes tener al menos 10 años.</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="antecedentes" class="form-label fw-medium text-dark">Antecedentes</label>
                <textarea class="form-control custom-input" id="antecedentes" name="antecedentesUsuario" rows="3" placeholder="Cuéntanos sobre tu experiencia o conocimientos previos..."></textarea>
            </div>

            <button type="submit" class="btn btn-purple w-100 fw-bold py-2 mb-3" name="botonCrearCuenta">Crear Cuenta</button>
            
            <div class="text-center">
                <span class="text-muted small">¿Ya tienes una cuenta? <a href="login.php" class="text-decoration-none fw-medium" style="color: var(--PRIMARY);">Ingresa acá</a></span>
            </div>
            
        </form>
    </div>
</main>

<?php require_once 'footer.php'; ?>