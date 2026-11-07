<?php 

    $titulo_pagina = 'Solicitudes - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 

?>

    <main class="container my-5">
        
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Solicitudes Plan Pro</h2>
                <p class="text-muted mb-0">Gestión de activaciones de cuentas premium.</p>
            </div>
            <span class="badge bg-warning text-dark fs-6 shadow-sm"><i class="bi bi-inbox-fill me-2"></i>3 Pendientes</span>
        </div>

        <!-- Lista de Solicitudes (Tabla responsiva en una Tarjeta) -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th scope="col" class="ps-4 py-3 fw-medium">Usuario (Solicitante)</th>
                            <th scope="col" class="py-3 fw-medium">Fecha de Solicitud</th>
                            <th scope="col" class="py-3 fw-medium">Estado</th>
                            <th scope="col" class="pe-4 py-3 text-end fw-medium">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <!-- Solicitud 1 -->
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-person-circle fs-3 text-secondary"></i>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">CarlosGomez</h6>
                                        <small class="text-muted">carlos@ejemplo.com</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 text-secondary">26 Oct 2026, 10:30 hs</td>
                            <td class="py-3">
                                <span class="badge bg-warning text-dark">Sin Revisar</span>
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <!-- Botón que abre el Modal de Detalles -->
                                <button class="btn btn-sm btn-outline-primary fw-medium" data-bs-toggle="modal" data-bs-target="#detalleSolicitudModal">
                                    Ver Detalle
                                </button>
                            </td>
                        </tr>

                        <!-- Solicitud 2 -->
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-person-circle fs-3 text-secondary"></i>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">MariaLopez99</h6>
                                        <small class="text-muted">maria.l@ejemplo.com</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 text-secondary">25 Oct 2026, 18:15 hs</td>
                            <td class="py-3">
                                <span class="badge bg-warning text-dark">Sin Revisar</span>
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <button class="btn btn-sm btn-outline-primary fw-medium" data-bs-toggle="modal" data-bs-target="#detalleSolicitudModal">
                                    Ver Detalle
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- MODAL "VER DETALLE" (Se abre al presionar el botón)     -->
        <!-- ======================================================= -->
        <div class="modal fade" id="detalleSolicitudModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    
                    <div class="modal-header border-bottom px-4 py-3">
                        <h5 class="modal-title fw-bold text-dark" id="detalleModalLabel">Detalle de Solicitud: <span class="text-primary">CarlosGomez</span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body px-4 py-4">
                        <div class="row g-4">
                            
                            <!-- Lado Izquierdo: Mensaje y Comprobante -->
                            <div class="col-md-7 border-end">
                                
                                <!-- Mensaje de la solicitud -->
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-chat-left-text me-2"></i>Mensaje del Usuario</h6>
                                    <div class="bg-light p-3 rounded border text-secondary" style="font-size: 0.95rem;">
                                        "Hola administrador, adjunto el comprobante de transferencia bancaria por la suscripción anual al Plan Pro. Quedo a la espera de la activación, muchas gracias."
                                    </div>
                                </div>

                                <!-- Archivo Adjunto (Comprobante) -->
                                <div>
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-paperclip me-2"></i>Comprobante Adjunto</h6>
                                    <a href="#" class="list-group-item list-group-item-action border rounded p-3 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                            <div>
                                                <p class="mb-0 fw-medium text-dark">transferencia_carlos.pdf</p>
                                                <small class="text-muted">1.2 MB</small>
                                            </div>
                                        </div>
                                        <span class="btn btn-sm btn-light border">Descargar</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Lado Derecho: Acción del Administrador (Vencimiento) -->
                            <div class="col-md-5">
                                <div class="alert alert-info border-0 bg-opacity-10 mb-4" role="alert" style="background-color: rgba(108, 92, 231, 0.1); color: var(--PRIMARY);">
                                    <i class="bi bi-info-circle-fill me-2"></i> Revisa el comprobante antes de activar el plan.
                                </div>
                                
                                <form action="activarPlan.php" method="POST">
                                    <!-- Fecha de Vencimiento requerida para activar -->
                                    <div class="mb-3">
                                        <label for="fechaVencimiento" class="form-label fw-bold text-dark">Establecer Vencimiento <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control custom-input" id="fechaVencimiento" name="fechaVencimiento" required>
                                        <div class="form-text small">Fecha en la que expirará el Plan Pro del usuario.</div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                    <!-- Botones de Acción: Rechazar o Activar -->
                    <div class="modal-footer border-top bg-light px-4 py-3 justify-content-between">
                        <button type="button" class="btn btn-outline-danger fw-medium px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Rechazar
                        </button>
                        <button type="submit" class="btn btn-purple fw-medium px-4">
                            <i class="bi bi-check-circle me-1"></i> Activar Pro
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>

    </main>

<?php require_once 'footer.php'; ?>