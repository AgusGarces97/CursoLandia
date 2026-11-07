<?php

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }   

    require_once 'conexion.php';

    $mensaje = '';

    if(isset($_POST['botonCrearCuenta'])){

        // Capturamos y limpiamos los datos para evitar inyección SQL
        $nombreUsuario = mysqli_real_escape_string($conexion, $_POST['nombreUsuario']);
        $apellidoUsuario = mysqli_real_escape_string($conexion, $_POST['apellidoUsuario']);
        $emailUsuario = mysqli_real_escape_string($conexion, $_POST['emailUsuario']);
        $passwordUsuario = $_POST['passwordUsuario'];

        $dniUsuario = mysqli_real_escape_string($conexion, $_POST['dniUsuario']);
        $edadUsuario = (int)$_POST['edadUsuario'];
        $antecedentesUsuario = mysqli_real_escape_string($conexion, $_POST['antecedentesUsuario']);

        // VERIFICAR SI EL EMAIL YA EXISTE EN LA BASE DE DATOS
        $sql_verificar = "
        SELECT id_usuario from usuarios
        WHERE email='$emailUsuario' OR dni='$dniUsuario'
        ";

        $resultadoCheck = mysqli_query($conexion, $sql_verificar);

        if(mysqli_num_rows($resultadoCheck) > 0){
            $mensaje = "<div class='alert alert-warning text-center' role='alert'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>El correo o dni ya está registrado.
                    </div>";
        }else{
            $sql_insert = "
            INSERT INTO usuarios (nombre, apellido, email, password, dni, edad, antecedentes)
            VALUES ('$nombreUsuario', '$apellidoUsuario', '$emailUsuario', '$passwordUsuario', '$dniUsuario', '$edadUsuario', '$antecedentesUsuario')
            ";

            $resultadoInsert = mysqli_query($conexion, $sql_insert);
            if($resultadoInsert){

                // INICIA SESION AUTOMATICAMENTE

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

        <!-- Mostramos la alerta (si hay alguna) -->
        <?php echo $mensaje; ?>

        <form action="register.php" method="POST">
            
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="nombreUsuario" class="form-label fw-medium text-dark">Nombre</label>
                    <input type="text" class="form-control custom-input" id="nombre" name="nombreUsuario" required placeholder="Ej: Juan">
                </div>
                <div class="col-sm-6">
                    <label for="apellidoUsuario" class="form-label fw-medium text-dark">Apellido</label>
                    <input type="text" class="form-control custom-input" id="apellido" name="apellidoUsuario" required placeholder="Ej: Pérez">
                </div>
            </div>

            <div class="mb-3">
                <label for="emailUsuario" class="form-label fw-medium text-dark">Correo Electrónico</label>
                <input type="email" class="form-control custom-input" id="email" name="emailUsuario" required placeholder="ejemplo@correo.com">
            </div>
            
            <div class="mb-4">
                <label for="passwordUsuario" class="form-label fw-medium text-dark">Contraseña</label>
                <input type="password" class="form-control custom-input" id="password" name="passwordUsuario" required placeholder="Crea una contraseña segura" minlength="6">
            </div>

            <!-- DNI y EDAD -->
            <div class="row g-3 mb-3">
                <div class="col-sm-8">
                    <label for="dniUsuario" class="form-label fw-medium text-dark">DNI <span class="text-danger">*</span></label>
                    <input type="text" class="form-control custom-input" id="dni" name="dniUsuario" required placeholder="Sin puntos ni espacios">
                </div>
                <div class="col-sm-4">
                    <label for="edadUsuario" class="form-label fw-medium text-dark">Edad <span class="text-danger">*</span></label>
                    <input type="number" class="form-control custom-input" id="edad" name="edadUsuario" required min="10" max="99">
                </div>
            </div>

            <div class="mb-3">
                <label for="antecedentesUsuario" class="form-label fw-medium text-dark">Antecedentes</label>
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