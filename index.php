<?php 
    $titulo_pagina = 'Inicio - Cursolandia';
    $ruta = ''; 
    require_once 'php/header.php';
    require_once 'php/conexion.php'; 

    // 1. Consulta para DESTACADOS (Traemos los 5 más recientes)
    $sql_destacados = "
        SELECT c.*, u.nombre, u.apellido 
        FROM cursos c
        INNER JOIN usuarios u ON c.id_creador = u.id_usuario
        WHERE c.activo = 1 AND c.es_publico = 1
        ORDER BY c.fecha_creacion DESC LIMIT 5
    ";
    $result_destacados = mysqli_query($conexion, $sql_destacados);

    // 2. Consulta para RECOMENDADOS (Traemos 5 aleatorios para que varíe)
    $sql_recomendados = "
        SELECT c.*, u.nombre, u.apellido 
        FROM cursos c
        INNER JOIN usuarios u ON c.id_creador = u.id_usuario
        WHERE c.activo = 1 AND c.es_publico = 1
        ORDER BY RAND() LIMIT 5
    ";
    $result_recomendados = mysqli_query($conexion, $sql_recomendados);
?>

   <main class="container my-5">
    
        <!-- Banner de la Página -->
        <section class="banner-placeholder d-flex align-items-center justify-content-center mb-5 text-center px-3">
            <div class="py-4 text-white">
                <h2 class="fw-bold mb-2">Descubre tu próximo curso</h2>
                <p class="fs-5 opacity-75 mb-0">Aprende a tu propio ritmo con expertos de la comunidad</p>
            </div>
        </section>

        <!-- Sección: Destacados -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-3 px-2">
                <h4 class="fw-bold mb-0 text-dark">Destacados</h4>
                <a href="#" class="text-decoration-none fw-medium link-primary">Ver Todos...</a>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-left-destacados"><i class="bi bi-caret-left-fill"></i></button>                
                
                <div class="carrusel-cursos d-flex overflow-auto flex-nowrap gap-4 w-100 py-2 px-1" id="carrusel-destacados">                    
                    
                    <?php if (mysqli_num_rows($result_destacados) > 0): ?>
                        <?php while ($curso = mysqli_fetch_assoc($result_destacados)): ?>
                            
                            <!-- Tarjeta Dinámica -->
                            <div class="curso-card flex-shrink-0 d-flex flex-column text-start">
                                <!-- Contenedor de imagen usando tu clase curso-img -->
                                <div class="curso-img bg-light d-flex align-items-center justify-content-center rounded-top">
                                    <i class="bi bi-image text-secondary fs-1 opacity-50"></i>
                                </div>
                                
                                <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                    <div class="mb-2">
                                        <?php if ($curso['es_pago'] == 1): ?>
                                            <span class="badge bg-warning text-dark">De Pago</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Gratis</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h5 class="fw-bold text-dark text-truncate mb-1"><?php echo $curso['titulo']; ?></h5>
                                    
                                    <!-- Usamos tu clase .line-clamp-3 en lugar de text-truncate -->
                                    <p class="text-muted small line-clamp-3 mb-3">
                                        <?php echo $curso['descripcion']; ?>
                                    </p>
                                    
                                    <div class="text-muted small mb-3 mt-auto text-truncate">
                                        <i class="bi bi-person-circle me-1"></i> <?php echo $curso['nombre'] . ' ' . $curso['apellido']; ?>
                                    </div>
                                    
                                    <a href="php/curso.php?id=<?php echo $curso['id_curso']; ?>" class="btn btn-outline-primary btn-sm w-100 fw-medium">
                                        Ver detalles
                                    </a>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-muted p-4 w-100 text-center bg-light rounded border border-dashed">No hay cursos destacados disponibles.</div>
                    <?php endif; ?>

                </div>
                
                <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-right-destacados"><i class="bi bi-caret-right-fill"></i></button>
            </div>
        </section>

        <!-- Sección: Recomendados -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-3 px-2">
                <h4 class="fw-bold mb-0 text-dark">Recomendados</h4>
                <a href="#" class="text-decoration-none fw-medium link-primary">Ver Todos...</a>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-left-recomendados"><i class="bi bi-caret-left-fill"></i></button>
                
                <div class="carrusel-cursos d-flex overflow-auto flex-nowrap gap-4 w-100 py-2 px-1" id="carrusel-recomendados">
                    
                    <?php if (mysqli_num_rows($result_recomendados) > 0): ?>
                        <?php while ($curso = mysqli_fetch_assoc($result_recomendados)): ?>
                            
                            <!-- Tarjeta Dinámica -->
                            <div class="curso-card flex-shrink-0 d-flex flex-column text-start">
                                <div class="curso-img bg-light d-flex align-items-center justify-content-center rounded-top">
                                    <i class="bi bi-journal-code text-primary fs-1 opacity-50"></i>
                                </div>
                                
                                <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                    <div class="mb-2">
                                        <?php if ($curso['es_pago'] == 1): ?>
                                            <span class="badge bg-warning text-dark">De Pago</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Gratis</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h5 class="fw-bold text-dark text-truncate mb-1"><?php echo $curso['titulo']; ?></h5>
                                    
                                    <p class="text-muted small line-clamp-3 mb-3">
                                        <?php echo $curso['descripcion']; ?>
                                    </p>
                                    
                                    <div class="text-muted small mb-3 mt-auto text-truncate">
                                        <i class="bi bi-person-circle me-1"></i> <?php echo $curso['nombre'] . ' ' . $curso['apellido']; ?>
                                    </div>
                                    
                                    <a href="php/curso.php?id=<?php echo $curso['id_curso']; ?>" class="btn btn-outline-primary btn-sm w-100 fw-medium">
                                        Ver detalles
                                    </a>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-muted p-4 w-100 text-center bg-light rounded border border-dashed">No hay cursos recomendados por ahora.</div>
                    <?php endif; ?>

                </div>
                
                <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-right-recomendados"><i class="bi bi-caret-right-fill"></i></button>
            </div>
        </section>

    </main>

<?php require_once 'php/footer.php'; ?>