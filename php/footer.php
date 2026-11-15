<footer class="custom-footer pt-5 pb-4 mt-auto">
        <div class="container px-lg-5">
            <div class="row text-center text-md-start">
                
                <div class="col-md-6 col-lg-6 mb-4">
                    <h5 class="fw-bold mb-3 text-white">Soporte e Información</h5>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#modalAcerca">Acerca de Cursolandia</a></li>
                        <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#modalPlanPro">Beneficios del Plan Pro</a></li>
                        <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#modalFaq">Preguntas Frecuentes (FAQ)</a></li>
                        <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#modalContacto">Contacto / Ayuda</a></li>
                    </ul>
                </div>

                <div class="col-md-6 col-lg-6 mb-4">
                    <h5 class="fw-bold mb-3 text-white">Legales</h5>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#modalTerminos">Términos y Condiciones</a></li>
                        <li><a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#modalPrivacidad">Política de Privacidad</a></li>
                    </ul>
                </div>
                
            </div>

            <hr class="footer-divider my-4">

            <div class="row align-items-center text-center text-md-start">
                <div class="col-md-7 col-lg-8 mb-3 mb-md-0">
                    <p class="mb-1 text-white opacity-75">
                        &copy; 2026 Cursolandia. Todos los derechos reservados.
                    </p>
                    <p class="mb-0 small text-white opacity-50">
                        Proyecto desarrollado por Agustín Garcés para la materia Ingeniería de Software y Programación III (UNSL).
                    </p>
                </div>
                
                <div class="col-md-5 col-lg-4 text-center text-md-end">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-2 fw-bold text-white opacity-75">
                        <i class="bi bi-mortarboard-fill fs-4"></i>
                        <span class="fs-5">Cursolandia</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- ========================================================= -->
    <!-- MODALES DE INFORMACIÓN DEL FOOTER                         -->
    <!-- ========================================================= -->

    <!-- 1. Modal Acerca de Cursolandia -->
    <div class="modal fade" id="modalAcerca" tabindex="-1" aria-labelledby="modalAcercaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4 text-dark">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold" id="modalAcercaLabel"><i class="bi bi-mortarboard-fill color-logo me-2"></i>Acerca de Cursolandia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="line-height: 1.6;">
                    <p><strong>Cursolandia</strong> es una plataforma integral de gestión y dictado de cursos en línea diseñada para conectar a docentes y estudiantes en un entorno dinámico, seguro y colaborativo.</p>
                    <p>Este sistema forma parte de un proyecto académico desarrollado por <strong>Agustín Garcés</strong> en el marco de la asignatura <em>Ingeniería de Software y Programación III</em> de la Universidad Nacional de San Luis (UNSL).</p>
                    <p class="mb-0 text-muted small">Nuestra misión es potenciar la educación digital ofreciendo herramientas de control de cupos, foros interactivos con moderación docente, subida de materiales didácticos y gestión de planes de suscripción.</p>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-purple fw-bold px-4 text-white" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Modal Beneficios del Plan Pro -->
    <div class="modal fade" id="modalPlanPro" tabindex="-1" aria-labelledby="modalPlanProLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4 text-dark">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-dark" id="modalPlanProLabel"><i class="bi bi-star-fill text-warning me-2"></i>Beneficios del Plan Pro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="line-height: 1.6;">
                    <p>Lleva tu experiencia docente y de aprendizaje al siguiente nivel con el <strong>Plan Pro de Cursolandia</strong>:</p>
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-3">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5 mt-1"></i>
                            <div><strong>Cursos activos ilimitados:</strong> Publica y mantén abiertos todos los cursos que desees sin restricciones de cupo activo del plan gratuito.</div>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5 mt-1"></i>
                            <div><strong>Almacenamiento extendido de materiales:</strong> Sube archivos sin límite de peso total por curso (el plan gratuito limita a 5 archivos y 10 MB totales).</div>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5 mt-1"></i>
                            <div><strong>Distintivo Pro en el catálogo:</strong> Tus cursos y perfil resaltarán con la insignia oficial Pro frente a la comunidad.</div>
                        </li>
                    </ul>
                    <p class="mb-0 text-muted small">Puedes solicitar tu actualización a Plan Pro desde la sección de tu perfil adjuntando tu comprobante de validación.</p>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-purple fw-bold px-4 text-white" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Modal Preguntas Frecuentes (FAQ) -->
    <div class="modal fade" id="modalFaq" tabindex="-1" aria-labelledby="modalFaqLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4 text-dark">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold" id="modalFaqLabel"><i class="bi bi-question-circle-fill color-logo me-2"></i>Preguntas Frecuentes (FAQ)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="line-height: 1.6;">
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark">¿Cómo me inscribo a un curso?</h6>
                        <p class="text-secondary small mb-0">Explora el catálogo o cursos recomendados, haz clic en "Ver detalles" y presiona el botón de inscripción. Si el curso es de pago, tu estado quedará pendiente hasta que el docente lo apruebe.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark">¿Puedo crear mis propios cursos?</h6>
                        <p class="text-secondary small mb-0">¡Sí! Cualquier usuario registrado puede crear cursos desde la opción "Nuevo Curso" en la barra de navegación superior.</p>
                    </div>
                    <div class="mb-0">
                        <h6 class="fw-bold text-dark">¿Cómo funciona el foro de dudas?</h6>
                        <p class="text-secondary small mb-0">Una vez que el curso inicia, los alumnos inscriptos pueden abrir hilos de consulta. Los docentes y compañeros pueden responder, y tanto el autor como el docente pueden destacar respuestas válidas cerrando el hilo.</p>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-purple fw-bold px-4 text-white" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Modal Contacto / Ayuda -->
    <div class="modal fade" id="modalContacto" tabindex="-1" aria-labelledby="modalContactoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4 text-dark">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold" id="modalContactoLabel"><i class="bi bi-headset color-logo me-2"></i>Contacto y Soporte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="line-height: 1.6;">
                    <p>¿Necesitas asistencia técnica o tienes consultas sobre el funcionamiento de la plataforma?</p>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-3">
                        <li><i class="bi bi-envelope-fill text-primary me-2"></i><strong>Soporte Académico:</strong> soporte@cursolandia.com</li>
                        <li><i class="bi bi-geo-alt-fill text-danger me-2"></i><strong>Institución:</strong> Universidad Nacional de San Luis (UNSL), San Luis, Argentina.</li>
                        <li><i class="bi bi-clock-fill text-warning me-2"></i><strong>Horario de atención:</strong> Lunes a Viernes de 09:00 a 18:00 hs.</li>
                    </ul>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-purple fw-bold px-4 text-white" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Modal Términos y Condiciones -->
    <div class="modal fade" id="modalTerminos" tabindex="-1" aria-labelledby="modalTerminosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4 text-dark">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold" id="modalTerminosLabel"><i class="bi bi-file-text-fill color-logo me-2"></i>Términos y Condiciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 350px; overflow-y: auto; line-height: 1.6;">
                    <p>Bienvenido a Cursolandia. Al registrarte y utilizar nuestros servicios, aceptas cumplir con los siguientes términos y condiciones de uso:</p>
                    <p><strong>1. Uso de la plataforma:</strong> Cursolandia es un entorno educativo de uso académico e interurbano. Queda prohibido el intercambio de contenido malicioso, fraudulento o que vulnere los derechos de propiedad intelectual.</p>
                    <p><strong>2. Cuentas y Seguridad:</strong> El usuario es responsable de mantener la confidencialidad de su contraseña y de la actividad generada en su cuenta.</p>
                    <p class="mb-0"><strong>3. Modificaciones:</strong> Los desarrolladores se reservan el derecho de modificar la interfaz, políticas y reglas de negocio del sitio con fines de mejora continua.</p>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-purple fw-bold px-4 text-white" data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Modal Política de Privacidad -->
    <div class="modal fade" id="modalPrivacidad" tabindex="-1" aria-labelledby="modalPrivacidadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4 text-dark">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold" id="modalPrivacidadLabel"><i class="bi bi-shield-lock-fill color-logo me-2"></i>Política de Privacidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 350px; overflow-y: auto; line-height: 1.6;">
                    <p>En Cursolandia valoramos y protegemos la privacidad de los datos personales de nuestros usuarios:</p>
                    <p><strong>Recopilación de datos:</strong> Almacenamos únicamente la información esencial proporcionada en el registro (nombre, apellido, correo electrónico, DNI y antecedentes) para fines estrictamente vinculados a la gestión de cursos e inscripciones.</p>
                    <p><strong>Seguridad:</strong> Las credenciales de acceso se almacenan de forma segura utilizando algoritmos de cifrado de contraseñas mediante hashing (`password_hash`).</p>
                    <p class="mb-0"><strong>Confidencialidad:</strong> Los datos nunca son compartidos con terceros ajenos a la plataforma académica.</p>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-purple fw-bold px-4 text-white" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <!-- Usamos la variable $ruta para no romper la ruta del JS -->
    <script src="<?php echo $ruta; ?>js/comp.js"></script>
</body>
</html>