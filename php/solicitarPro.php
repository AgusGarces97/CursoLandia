<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

require_once 'conexion.php';

$idUsuario = $_SESSION['id_usuario'];

// Si ya es Pro, no necesita solicitarlo
if ($_SESSION['plan'] === 'pro') {
    header("Location: perfil.php");
    exit();
}

// Verificamos si ya tiene una solicitud pendiente
$sql_check = "SELECT * FROM solicitudes_pro WHERE id_usuario = $idUsuario AND estado = 'pendiente'";
$res_check = mysqli_query($conexion, $sql_check);
if (mysqli_num_rows($res_check) > 0) {
    header("Location: perfil.php?solicitud=pendiente");
    exit();
}

$titulo_pagina = 'Solicitar Plan Pro - Cursolandia';
$ruta = '../'; 
require_once 'header.php'; 
?>

<main class="container my-5" style="min-height: 70vh;">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <h4 class="fw-bold text-dark mb-0">
                        <i class="bi bi-rocket-takeoff-fill color-logo me-2"></i>Solicitud de Plan PRO
                    </h4>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <p class="text-secondary mb-4">
                        Para activar tu subscripción sin límites, adjunta el comprobante de transferencia/pago junto a una breve aclaración para la revisión del administrador.
                    </p>

                    <form action="procesarSolicitud.php" method="POST" enctype="multipart/form-data">
                        
                        <!-- Mensaje -->
                        <div class="mb-4">
                            <label for="mensaje" class="form-label fw-medium text-dark">Mensaje / Observaciones</label>
                            <textarea class="form-control custom-input" id="mensaje" name="mensaje" rows="4" placeholder="Ej: Realicé la transferencia desde la cuenta de Mercado Pago a nombre de..." required></textarea>
                        </div>

                        <!-- Adjunto Comprobante -->
                        <div class="mb-4">
                            <label for="comprobante" class="form-label fw-medium text-dark">Comprobante de Pago (PDF, JPG, PNG)</label>
                            <input type="file" class="form-control custom-input" id="comprobante" name="comprobante" accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="form-text">Tamaño máximo permitido: 5 MB.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                            <a href="perfil.php" class="btn btn-outline-secondary fw-medium px-4">Cancelar</a>
                            <button type="submit" name="botonEnviarSolicitud" class="btn btn-purple fw-bold px-4">
                                <i class="bi bi-send-fill me-2"></i>Enviar Solicitud
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>