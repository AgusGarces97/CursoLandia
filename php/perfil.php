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

    // 1. Consultamos todos los datos del usuario activo
    $sql_usuario = "SELECT * FROM usuarios WHERE id_usuario = $idUsuario";
    $resultado = mysqli_query($conexion, $sql_usuario);
    $usuario = mysqli_fetch_assoc($resultado);

    // 2. Consultamos las etiquetas de interés del usuario actual
    $sql_intereses_usuario = "
        SELECT e.id_etiqueta, e.nombre 
        FROM usuario_intereses ui 
        INNER JOIN etiquetas e ON ui.id_etiqueta = e.id_etiqueta 
        WHERE ui.id_usuario = $idUsuario 
        ORDER BY e.nombre ASC
    ";
    $resultado_intereses = mysqli_query($conexion, $sql_intereses_usuario);

    // 3. Consultamos todas las etiquetas del sistema para las sugerencias del buscador
    $sql_todas_etiquetas = "SELECT * FROM etiquetas ORDER BY nombre ASC";
    $resultado_todas_etiquetas = mysqli_query($conexion, $sql_todas_etiquetas);

    $titulo_pagina = 'Perfil - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 
?>

    <main class="container my-5">
        
        <!-- BANNER DE PERFIL -->
        <section class="perfil-banner rounded-4 shadow-sm mb-5 p-4 p-md-5 d-flex align-items-center">
            <div class="d-flex align-items-center gap-4 w-100">
                <div class="position-relative">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario['nombre'] . '+' . $usuario['apellido']); ?>&background=ffffff&color=6c5ce7&size=150" alt="Foto de Perfil" class="rounded-circle border border-4 border-white shadow-sm perfil-img">
                </div>
                
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
            
            <!-- BLOQUE DE ALERTAS (Perfil, Solicitudes Pro y Contraseña) -->
            <?php if(isset($_GET['editado']) && $_GET['editado'] == 'exito'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><strong>¡Perfil actualizado!</strong> Tus datos e intereses se guardaron correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif(isset($_GET['editado']) && $_GET['editado'] == 'error'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Ocurrió un error.</strong> No se pudieron guardar los cambios.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif(isset($_GET['pass']) && $_GET['pass'] == 'exito'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-shield-check me-2"></i><strong>¡Contraseña actualizada!</strong> Tu nueva clave fue guardada correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif(isset($_GET['pass']) && $_GET['pass'] == 'actual_incorrecta'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Error:</strong> La contraseña actual ingresada es incorrecta.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif(isset($_GET['pass']) && $_GET['pass'] == 'no_coinciden'): ?>
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><strong>Atención:</strong> La nueva contraseña y su confirmación no coinciden.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif(isset($_GET['solicitud']) && $_GET['solicitud'] == 'enviada'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><strong>¡Solicitud enviada!</strong> Tu pedido para pasar al Plan Pro fue registrado y está en revisión.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif(isset($_GET['solicitud']) && $_GET['solicitud'] == 'pendiente'): ?>
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-clock-history me-2"></i><strong>Solicitud en curso.</strong> Ya tienes una solicitud para Plan Pro pendiente de aprobación.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="editarPerfil.php" method="POST">
                
                <!-- Cabecera de la sección -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 border-bottom pb-3">
                    <h3 class="fw-bold mb-0 text-dark">Datos Personales</h3>
                    <div class="d-flex gap-2">
                        <!-- Botón que abre el Modal de Bootstrap -->
                        <button type="button" class="btn btn-outline-secondary fw-medium" data-bs-toggle="modal" data-bs-target="#modalCambiarPassword">
                            <i class="bi bi-key-fill me-1"></i>Cambiar Contraseña
                        </button>
                        <button type="submit" class="btn btn-purple fw-medium" name="botonEditarPerfil">Guardar Cambios</button>
                    </div>
                </div>

                <!-- Formulario en 3 columnas -->
                <div class="row g-4">
                    
                    <!-- COLUMNA 1: Datos Personales -->
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
                                <input type="text" class="form-control custom-input bg-light text-muted" id="dni" value="<?php echo htmlspecialchars($usuario['dni']); ?>" readonly>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label fw-medium text-dark">Email</label>
                                <input type="email" class="form-control custom-input bg-light text-muted" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 2: Antecedentes -->
                    <div class="col-lg-4">
                        <label for="antecedentes" class="form-label fw-medium text-dark">Antecedentes</label>
                        <textarea class="form-control custom-input" id="antecedentes" name="antecedentes" rows="8" placeholder="Escribe tu trayectoria, conocimientos previos o experiencia..."><?php echo htmlspecialchars($usuario['antecedentes'] ?? ''); ?></textarea>
                    </div>

                    <!-- COLUMNA 3: Intereses Dinámicos -->
                    <div class="col-lg-4">
                        <label for="buscadorEtiquetas" class="form-label fw-medium text-dark">Intereses</label>
                        
                        <div class="position-relative mb-3">
                            <input type="text" class="form-control custom-input text-center" id="buscadorEtiquetas" list="listaSugerencias" placeholder="Buscador de Etiquetas (Presiona Enter para agregar)" autocomplete="off">
                            <datalist id="listaSugerencias">
                                <?php while($etiqueta = mysqli_fetch_assoc($resultado_todas_etiquetas)): ?>
                                    <option value="<?php echo htmlspecialchars($etiqueta['nombre']); ?>"></option>
                                <?php endwhile; ?>
                            </datalist>
                        </div>
                        
                        <div id="contenedorEtiquetas" class="border rounded-3 p-3 bg-light d-flex flex-wrap align-content-start gap-2" style="min-height: 140px;">
                            <?php while($int = mysqli_fetch_assoc($resultado_intereses)): ?>
                                <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1 item-etiqueta">
                                    #<?php echo htmlspecialchars($int['nombre']); ?>
                                    <input type="hidden" name="etiquetas[]" value="<?php echo htmlspecialchars($int['nombre']); ?>">
                                    <button type="button" class="btn-close btn-remover-etiqueta" style="font-size: 0.5rem;" aria-label="Remover"></button>
                                </span>
                            <?php endwhile; ?>
                        </div>
                        <div class="form-text mt-2" style="font-size: 0.75rem;">
                            Escribe una etiqueta y presiona Enter o selecciónala del menú para añadirla.
                        </div>
                    </div>

                </div>
            </form>

        </section>
    </main>

    <!-- MODAL BOOTSTRAP PARA CAMBIAR CONTRASEÑA -->
    <div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-labelledby="modalCambiarPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-dark" id="modalCambiarPasswordLabel">
                        <i class="bi bi-shield-lock color-logo me-2"></i>Cambiar Contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="editarPerfil.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="password_actual" class="form-label fw-medium text-dark">Contraseña Actual <span class="text-danger">*</span></label>
                            <input type="password" class="form-control custom-input" id="password_actual" name="password_actual" placeholder="Tu clave actual..." required>
                        </div>

                        <div class="mb-3">
                            <label for="password_nueva" class="form-label fw-medium text-dark">Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control custom-input" id="password_nueva" name="password_nueva" placeholder="Mínimo 6 caracteres..." required>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmar" class="form-label fw-medium text-dark">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control custom-input" id="password_confirmar" name="password_confirmar" placeholder="Repite la nueva contraseña..." required>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-top p-3">
                        <button type="button" class="btn btn-light border fw-medium" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-purple fw-bold px-4" name="botonCambiarPassword">Actualizar Clave</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPT DE INTERACCIÓN DE ETIQUETAS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputEtiqueta = document.getElementById('buscadorEtiquetas');
            const contenedor = document.getElementById('contenedorEtiquetas');

            function agregarEtiqueta(nombre) {
                let textoLimpio = nombre.trim().replace(/^#+/, '');
                if (textoLimpio === '') return;

                const etiquetasExistentes = Array.from(contenedor.querySelectorAll('input[name="etiquetas[]"]'))
                    .map(inp => inp.value.toLowerCase());

                if (etiquetasExistentes.includes(textoLimpio.toLowerCase())) {
                    inputEtiqueta.value = '';
                    return;
                }

                const badge = document.createElement('span');
                badge.className = 'badge bg-white text-secondary border p-2 d-flex align-items-center gap-1 item-etiqueta';
                badge.innerHTML = `
                    #${textoLimpio}
                    <input type="hidden" name="etiquetas[]" value="${textoLimpio}">
                    <button type="button" class="btn-close btn-remover-etiqueta" style="font-size: 0.5rem;" aria-label="Remover"></button>
                `;

                contenedor.appendChild(badge);
                inputEtiqueta.value = '';
            }

            inputEtiqueta.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    agregarEtiqueta(this.value);
                }
            });

            inputEtiqueta.addEventListener('change', function() {
                agregarEtiqueta(this.value);
            });

            contenedor.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remover-etiqueta')) {
                    e.target.closest('.item-etiqueta').remove();
                }
            });
        });
    </script>

<?php require_once 'footer.php'; ?>