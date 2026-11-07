<?php 

    $titulo_pagina = 'Inicio - Cursolandia';
    $ruta = ''; // Como index.php está en la raíz, no necesitamos subir de nivel
    require_once 'php/header.php' 

?>

   <main class="container my-5">
    
        <!-- Banner de la Página -->
        <!-- Se agregó text-center para asegurar que el h2 quede centrado en pantallas pequeñas -->
        <section class="banner-placeholder d-flex align-items-center justify-content-center mb-5 shadow-sm text-center px-3">
            <h2 class="text-white fw-bold">Banner Página</h2>
        </section>

        <!-- Sección: Destacados -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-3 px-2">
                <h4 class="fw-bold mb-0 text-dark">Destacados</h4>
                <a href="#" class="text-decoration-none fw-medium" style="color: var(--PRIMARY);">Ver Todos...</a>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <!-- Flecha Izquierda: d-none d-md-block oculta la flecha en celulares -->
                <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-left-destacados"><i class="bi bi-caret-left-fill"></i></button>                
                
                <div class="carrusel-cursos d-flex overflow-auto flex-nowrap gap-4 w-100 py-2 px-1" id="carrusel-destacados">                    
                    <div class="curso-card flex-shrink-0 d-flex flex-column justify-content-center align-items-center position-relative shadow-sm">
                        <span class="badge position-absolute top-0 start-0 m-2" style="background-color: var(--PRIMARY);">Pro</span>
                        <span class="text-muted fw-medium">Curso Destacado 1</span>
                    </div>
                    <div class="curso-card flex-shrink-0 d-flex justify-content-center align-items-center shadow-sm">
                        <span class="text-muted fw-medium">Curso Destacado 2</span>
                    </div>
                    <div class="curso-card flex-shrink-0 d-flex justify-content-center align-items-center shadow-sm">
                        <span class="text-muted fw-medium">Curso Destacado 3</span>
                    </div>
                    <div class="curso-card flex-shrink-0 d-flex justify-content-center align-items-center shadow-sm">
                        <span class="text-muted fw-medium">Curso Destacado 4</span>
                    </div>
                    <div class="curso-card flex-shrink-0 d-flex justify-content-center align-items-center shadow-sm">
                        <span class="text-muted fw-medium">Curso Destacado 5</span>
                    </div>
                </div>
                
                <!-- Flecha Derecha: Oculta en celulares -->
                <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-right-destacados"><i class="bi bi-caret-right-fill"></i></button>
            </div>
        </section>

        <!-- Sección: Recomendados -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-3 px-2">
                <h4 class="fw-bold mb-0 text-dark">Recomendados</h4>
                <a href="#" class="text-decoration-none fw-medium" style="color: var(--PRIMARY);">Ver Todos...</a>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <!-- Flecha Izquierda: Oculta en celulares -->
                <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-left-recomendados"><i class="bi bi-caret-left-fill"></i></button>
                
                <div class="carrusel-cursos d-flex overflow-auto flex-nowrap gap-4 w-100 py-2 px-1" id="carrusel-recomendados">
                    <div class="curso-card flex-shrink-0 d-flex justify-content-center align-items-center shadow-sm">
                        <span class="text-muted fw-medium">Curso Recomendado 1</span>
                    </div>
                    <div class="curso-card flex-shrink-0 d-flex justify-content-center align-items-center shadow-sm">
                        <span class="text-muted fw-medium">Curso Recomendado 2</span>
                    </div>
                    <div class="curso-card flex-shrink-0 d-flex justify-content-center align-items-center shadow-sm">
                        <span class="text-muted fw-medium">Curso Recomendado 3</span>
                    </div>
                    <div class="curso-card flex-shrink-0 d-flex justify-content-center align-items-center shadow-sm">
                        <span class="text-muted fw-medium">Curso Recomendado 4</span>
                    </div>
                    <div class="curso-card flex-shrink-0 d-flex justify-content-center align-items-center shadow-sm">
                        <span class="text-muted fw-medium">Curso Recomendado 5</span>
                    </div>
                </div>
                
                <!-- Flecha Derecha: Oculta en celulares -->
                <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-right-recomendados"><i class="bi bi-caret-right-fill"></i></button>
            </div>
        </section>

    </main>

<?php require_once 'php/footer.php'; ?>