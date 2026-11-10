<?php 
    if(session_status() == PHP_SESSION_NONE){
        session_start();
    }

    // Si no está logueado, lo mandamos al inicio
    if(!isset($_SESSION['id_usuario'])){
        header("Location: ../index.php");
        exit();
    }

    require_once 'conexion.php';

    $idUsuario = $_SESSION['id_usuario'];

    // Consultamos todos los datos del usuario activo
    $sql_usuario = "SELECT * FROM usuarios WHERE id_usuario = $idUsuario";
    $resultado = mysqli_query($conexion, $sql_usuario);
    $usuario = mysqli_fetch_assoc($resultado);

    $titulo_pagina = 'Perfil - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 
?>

    <main class="container my-5">
        
        <!-- BANNER DE PERFIL -->
        <section class="perfil-banner rounded-4 shadow-sm mb-5 p-4 p-md-5 d-flex align-items-center">
            <div class="d-flex align-items-center gap-4 w-100">
                <!-- Foto de Perfil Dinámica (Usa la inicial del nombre real) -->
                <div class="position-relative">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario['nombre'] . '+' . $usuario['apellido']); ?>&background=ffffff&color=6c5ce7&size=150" alt="Foto de Perfil" class="rounded-circle border border-4 border-white shadow-sm perfil-img">
                </div>
                
                <!-- Información del Usuario -->
                <div class="text-white">
                    <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h2>
                    <p class="mb-0 fs-5 opacity-75">Rol: <?php echo ucfirst($usuario['rol']); ?></p>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <?php if($usuario['plan'] == 'pro'): ?>
                            <span class="badge bg-warning text-dark px-3 py-2 border-0">
                                <i class="bi bi-star-fill me-1"></i>Plan Pro
                            </span>
                            <?php if(!empty($usuario['fecha_vencimiento'])): ?>
                                <small class="fw-medium text-white ms-2">
                                    Vto: <?php echo date("d/m/Y", strtotime($usuario['fecha_vencimiento'])); ?>
                                </small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-white text-primary px-3 py-2 border-0">Plan Gratuito</span>
                            
                            <!-- Enlace al formulario de solicitud con comprobante -->
                            <a href="solicitarPro.php" class="btn btn-warning btn-sm fw-bold border-0 shadow-sm px-3 py-2 ms-2">
                                <i class="bi bi-rocket-takeoff-fill me-1"></i>Solicitar Plan Pro
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN DE DATOS Y FORMULARIO -->
        <section class="bg-white rounded-4 shadow-sm p-4 p-lg-5 border">
            
            <!-- BLOQUE DE ALERTAS (Edición de Perfil y Solicitudes Pro) -->
            <?php if(isset($_GET['editado']) && $_GET['editado'] == 'exito'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><strong>¡Perfil actualizado!</strong> Tus datos se guardaron correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif(isset($_GET['editado']) && $_GET['editado'] == 'error'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Ocurrió un error.</strong> No se pudieron guardar los cambios.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif(isset($_GET['solicitud']) && $_GET['solicitud'] == 'enviada'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><strong>¡Solicitud enviada!</strong> Tu pedido para pasar al Plan Pro fue registrado y está en revisión por el administrador.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif(isset($_GET['solicitud']) && $_GET['solicitud'] == 'pendiente'): ?>
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-clock-history me-2"></i><strong>Solicitud en curso.</strong> Ya tienes una solicitud para Plan Pro pendiente de aprobación.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="editarPerfil.php" method="POST">
                
                <!-- Cabecera de la sección (Título y Botones) -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 border-bottom pb-3">
                    <h3 class="fw-bold mb-0 text-dark">Datos Personales</h3>
                    <div class="d-flex gap-2">
                        <!-- Type="button" para que no envíe el formulario principal -->
                        <button type="button" class="btn btn-outline-secondary fw-medium">Cambiar Contraseña</button>
                        <!-- Type="submit" para guardar los cambios del perfil -->
                        <button type="submit" class="btn btn-purple fw-medium" name="botonEditarPerfil">Guardar Cambios</button>
                    </div>
                </div>

                <!-- Formulario dividido en 3 columnas -->
                <div class="row g-4">
                    
                    <!-- COLUMNA 1: Datos Personales (Inputs) -->
                    <div class="col-lg-4">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="nombre" class="form-label fw-medium text-dark">Nombre</label>
                                <input type="text" class="form-control custom-input" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                            <div class="col-sm-6">
                                <label for="apellido" class="form-label fw-medium text-dark">Apellido</label>
                                <input type="text" class="form-control custom-input" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                            </div>
                            <div class="col-sm-4">
                                <label for="edad" class="form-label fw-medium text-dark">Edad</label>
                                <input type="number" class="form-control custom-input" id="edad" name="edad" value="<?php echo htmlspecialchars($usuario['edad']); ?>" required>
                            </div>
                            <div class="col-sm-8">
                                <label for="dni" class="form-label fw-medium text-dark">DNI</label>
                                <!-- readonly por RN02 -->
                                <input type="text" class="form-control custom-input bg-light text-muted" id="dni" value="<?php echo htmlspecialchars($usuario['dni']); ?>" readonly>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label fw-medium text-dark">Email</label>
                                <!-- readonly por RN02 -->
                                <input type="email" class="form-control custom-input bg-light text-muted" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 2: Antecedentes -->
                    <div class="col-lg-4">
                        <label for="antecedentes" class="form-label fw-medium text-dark">Antecedentes</label>
                        <textarea class="form-control custom-input" id="antecedentes" name="antecedentes" rows="8" placeholder="Escribe tu trayectoria, conocimientos previos o experiencia..."><?php echo htmlspecialchars($usuario['antecedentes'] ?? ''); ?></textarea>
                    </div>

                    <!-- COLUMNA 3: Intereses (Buscador y Etiquetas Elegidas) -->
                    <div class="col-lg-4">
                        <label for="buscadorEtiquetas" class="form-label fw-medium text-dark">Intereses</label>
                        
                        <!-- Buscador de Etiquetas -->
                        <div class="position-relative mb-3">
                            <input type="text" class="form-control custom-input text-center" id="buscadorEtiquetas" placeholder="Buscador de Etiquetas">
                        </div>
                        
                        <!-- Caja de Etiquetas Elegidas (Estáticas por ahora) -->
                        <div class="border rounded-3 p-3 bg-light d-flex flex-wrap align-content-start gap-2" style="min-height: 140px;">
                            <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1">
                                #Programación <button type="button" class="btn-close" style="font-size: 0.5rem;"></button>
                            </span>
                            <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1">
                                #PHP <button type="button" class="btn-close" style="font-size: 0.5rem;"></button>
                            </span>
                        </div>
                    </div>

                </div>
            </form>

        </section>
    </main>

<?php require_once 'footer.php'; ?>