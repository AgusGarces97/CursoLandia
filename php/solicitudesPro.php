<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

// 1. RESTRICCIÓN DE ACCESO: Si no está logueado o si NO es admin, lo expulsamos.
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['plan']) || $_SESSION['plan'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$idUsuarioActual = $_SESSION['id_usuario'];
$rolUsuario = $_SESSION['plan']; // Ajustado para usar 'plan' que es donde guardas 'admin' según header.php

// Procesamiento de aprobación / rechazo por parte del Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_solicitud'])) {
    
    $idSolicitud = (int)$_POST['id_solicitud'];
    $idUsuarioSolicitante = (int)$_POST['id_usuario_solicitante'];
    $accion = $_POST['accion_solicitud'];

    if ($accion === 'aprobar') {
        $fechaVencimiento = mysqli_real_escape_string($conexion, $_POST['fecha_vencimiento']);
        
        // 1. Marcar solicitud como aprobada
        mysqli_query($conexion, "UPDATE solicitudes_pro SET estado = 'aprobada' WHERE id_solicitud = $idSolicitud");
        
        // 2. Activar Plan Pro y asignar fecha de vencimiento al usuario
        mysqli_query($conexion, "UPDATE usuarios SET plan = 'pro', fecha_vencimiento = '$fechaVencimiento' WHERE id_usuario = $idUsuarioSolicitante");
        
        // 3. Notificamos al usuario
        $titulo_notif = "Plan Pro Activado";
        $msj_notif = "Un administrador ha validado tu comprobante. ¡Ya cuentas con los beneficios para crear cursos ilimitados hasta el " . date("d/m/Y", strtotime($fechaVencimiento)) . "!";
        mysqli_query($conexion, "
            INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
            VALUES ($idUsuarioSolicitante, '$titulo_notif', '$msj_notif', 'plan_pro', 0, NOW())
        ");
        
    } elseif ($accion === 'rechazar') {
        mysqli_query($conexion, "UPDATE solicitudes_pro SET estado = 'rechazada' WHERE id_solicitud = $idSolicitud");
        
        // Notificamos al usuario del rechazo
        $titulo_notif = "Solicitud Pro Rechazada";
        $msj_notif = "Tu comprobante de pago fue revisado pero no cumple los requisitos para activar el plan. Por favor, reenvía una nueva solicitud.";
        mysqli_query($conexion, "
            INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
            VALUES ($idUsuarioSolicitante, '$titulo_notif', '$msj_notif', 'foro_eliminada', 0, NOW())
        ");
    }
    
    header("Location: solicitudesPro.php?status=actualizado");
    exit();
}

// Consultar todas las solicitudes (Ya sabemos que es admin por la validación inicial)
$sql_solicitudes = "
    SELECT s.*, u.nombre, u.apellido, u.email 
    FROM solicitudes_pro s
    INNER JOIN usuarios u ON s.id_usuario = u.id_usuario
    ORDER BY s.fecha_solicitud DESC
";

$resultado_solicitudes = mysqli_query($conexion, $sql_solicitudes);

$titulo_pagina = 'Panel Admin - Solicitudes Plan Pro';
$ruta = '../'; 
require_once 'header.php'; 
?>

<main class="container my-5" style="min-height: 70vh;">
    
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <h2 class="fw-bold text-dark mb-0">
            <i class="bi bi-star-fill text-warning me-2"></i>Solicitudes de Plan PRO
        </h2>
        <span class="badge bg-danger px-3 py-2 fs-6">Panel Administrador</span>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'actualizado'): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>La solicitud ha sido procesada con éxito y se ha notificado al usuario.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($resultado_solicitudes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Usuario</th>
                                <th>Mensaje</th>
                                <th>Comprobante</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acción y Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($solicitud = mysqli_fetch_assoc($resultado_solicitudes)): ?>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">
                                        <?php echo htmlspecialchars($solicitud['nombre'] . ' ' . $solicitud['apellido']); ?>
                                        <div class="small text-muted"><?php echo htmlspecialchars($solicitud['email']); ?></div>
                                    </td>
                                    
                                    <!-- Mensaje del usuario -->
                                    <td class="text-secondary small" style="max-width: 220px;">
                                        <?php echo !empty($solicitud['mensaje']) ? htmlspecialchars($solicitud['mensaje']) : '<em>Sin mensaje</em>'; ?>
                                    </td>

                                    <!-- Adjunto -->
                                    <td>
                                        <?php if(!empty($solicitud['comprobante'])): ?>
                                            <a href="../uploads/comprobantes/<?php echo htmlspecialchars($solicitud['comprobante']); ?>" target="_blank" class="btn btn-sm btn-outline-primary fw-medium">
                                                <i class="bi bi-file-earmark-text me-1"></i>Ver Adjunto
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin archivo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-muted small">
                                        <?php echo date("d/m/Y H:i", strtotime($solicitud['fecha_solicitud'])); ?>
                                    </td>

                                    <td>
                                        <?php if($solicitud['estado'] == 'pendiente'): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pendiente</span>
                                        <?php elseif($solicitud['estado'] == 'aprobada'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aprobada</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Rechazada</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <?php if($solicitud['estado'] == 'pendiente'): ?>
                                            
                                            <!-- Formulario para Aprobar especificando Fecha de Vencimiento -->
                                            <form action="solicitudesPro.php" method="POST" class="d-inline-flex align-items-center gap-2 justify-content-end">
                                                <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['id_solicitud']; ?>">
                                                <input type="hidden" name="id_usuario_solicitante" value="<?php echo $solicitud['id_usuario']; ?>">
                                                
                                                <!-- Fecha de vencimiento por defecto a 1 año desde hoy -->
                                                <input type="date" name="fecha_vencimiento" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required style="width: 140px;">
                                                
                                                <button type="submit" name="accion_solicitud" value="aprobar" class="btn btn-sm btn-success fw-medium">Aprobar</button>
                                                <button type="submit" name="accion_solicitud" value="rechazar" class="btn btn-sm btn-outline-danger fw-medium">Rechazar</button>
                                            </form>

                                        <?php else: ?>
                                            <span class="text-muted small">Procesada</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-inbox display-4 mb-3 d-block opacity-50"></i>
                    No hay solicitudes registradas por el momento.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>