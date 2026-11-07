<?php 

    $titulo_pagina = 'Perfil - Cursolandia';
    $ruta = '../'; 
    require_once 'header.php'; 

?>

    <main class="container my-5">
        
        <!-- BANNER DE PERFIL -->
        <section class="perfil-banner rounded-4 shadow-sm mb-5 p-4 p-md-5 d-flex align-items-center">
            <div class="d-flex align-items-center gap-4 w-100">
                <!-- Foto de Perfil -->
                <div class="position-relative">
                    <img src="https://ui-avatars.com/api/?name=Agustin+Garces&background=ffffff&color=6c5ce7&size=150" alt="Foto de Perfil" class="rounded-circle border border-4 border-white shadow-sm perfil-img">
                </div>
                
                <!-- Información del Usuario -->
                <div class="text-white">
                    <h2 class="fw-bold mb-1">AgustinGarces</h2>
                    <p class="mb-0 fs-5 opacity-75">Rol: Alumno / Docente</p>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span class="badge bg-white text-primary px-3 py-2 border-0">Plan Pro</span>
                        <small class="fw-medium">Vto: 31/12/2026</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN DE DATOS Y FORMULARIO -->
        <section class="bg-white rounded-4 shadow-sm p-4 p-lg-5 border">
            
            <!-- Cabecera de la sección (Título y Botones) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 border-bottom pb-3">
                <h3 class="fw-bold mb-0 text-dark">Datos Personales</h3>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary fw-medium">Cambiar Contraseña</button>
                    <button type="button" class="btn btn-purple fw-medium">Botón Editar Perfil</button>
                </div>
            </div>

            <!-- Formulario dividido en 3 columnas como en el boceto -->
            <form action="editarPerfil.php" method="POST">
                <div class="row g-4">
                    
                    <!-- COLUMNA 1: Datos Personales (Inputs) -->
                    <div class="col-lg-4">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="nombre" class="form-label fw-medium text-dark">Nombre</label>
                                <input type="text" class="form-control custom-input" id="nombre" value="Agustín">
                            </div>
                            <div class="col-sm-6">
                                <label for="apellido" class="form-label fw-medium text-dark">Apellido</label>
                                <input type="text" class="form-control custom-input" id="apellido" value="Garcés">
                            </div>
                            <div class="col-sm-4">
                                <label for="edad" class="form-label fw-medium text-dark">Edad</label>
                                <input type="number" class="form-control custom-input" id="edad" value="22">
                            </div>
                            <div class="col-sm-8">
                                <label for="dni" class="form-label fw-medium text-dark">DNI</label>
                                <!-- readonly por RN02 -->
                                <input type="text" class="form-control custom-input bg-light text-muted" id="dni" value="45123456" readonly>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label fw-medium text-dark">Email</label>
                                <!-- readonly por RN02 -->
                                <input type="email" class="form-control custom-input bg-light text-muted" id="email" value="agustin@ejemplo.com" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 2: Antecedentes -->
                    <div class="col-lg-4">
                        <label for="antecedentes" class="form-label fw-medium text-dark">Antecedentes</label>
                        <textarea class="form-control custom-input" id="antecedentes" rows="8" placeholder="Escribe tu trayectoria, conocimientos previos o experiencia..."></textarea>
                    </div>

                    <!-- COLUMNA 3: Intereses (Buscador y Etiquetas Elegidas) -->
                    <div class="col-lg-4">
                        <label for="buscadorEtiquetas" class="form-label fw-medium text-dark">Intereses</label>
                        
                        <!-- Buscador de Etiquetas -->
                        <div class="position-relative mb-3">
                            <input type="text" class="form-control custom-input text-center" id="buscadorEtiquetas" placeholder="Buscador de Etiquetas">
                        </div>
                        
                        <!-- Caja de Etiquetas Elegidas -->
                        <div class="border rounded-3 p-3 bg-light d-flex flex-wrap align-content-start gap-2" style="min-height: 140px;">
                            <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1">
                                #Programación <button type="button" class="btn-close" style="font-size: 0.5rem;"></button>
                            </span>
                            <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1">
                                #PHP <button type="button" class="btn-close" style="font-size: 0.5rem;"></button>
                            </span>
                            <span class="badge bg-white text-secondary border p-2 d-flex align-items-center gap-1">
                                #BasesDeDatos <button type="button" class="btn-close" style="font-size: 0.5rem;"></button>
                            </span>
                            <!-- Placeholder visual por si está vacío -->
                            <!-- <span class="text-muted small w-100 text-center mt-4">Acá estarán las etiquetas elegidas</span> -->
                        </div>
                    </div>

                </div>
            </form>

        </section>
    </main>


<?php require_once 'footer.php'; ?>