<?php 
    $titulo_pagina = 'Inicio - Cursolandia';
    $ruta = ''; 
    require_once 'php/header.php';
    require_once 'php/conexion.php'; 

    // CAPTURA Y LIMPIEZA DE PARÁMETROS DE BÚSQUEDA Y FILTROS
    $buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, trim($_GET['buscar'])) : '';
    $tipo = isset($_GET['tipo']) ? mysqli_real_escape_string($conexion, trim($_GET['tipo'])) : '';

    $hay_filtro = (!empty($buscar) || !empty($tipo));

    if ($hay_filtro) {
        // CONSTRUCCIÓN DE CONSULTA DINÁMICA DE BÚSQUEDA
        $condiciones = ["c.activo = 1", "c.es_publico = 1"];

        if (!empty($buscar)) {
            $condiciones[] = "(c.titulo LIKE '%$buscar%' OR c.descripcion LIKE '%$buscar%' OR u.nombre LIKE '%$buscar%' OR u.apellido LIKE '%$buscar%')";
        }

        if ($tipo === 'gratis') {
            $condiciones[] = "c.es_pago = 0";
        } elseif ($tipo === 'pago') {
            $condiciones[] = "c.es_pago = 1";
        }

        $sql_donde = implode(" AND ", $condiciones);

        $sql_busqueda = "
            SELECT c.*, u.nombre, u.apellido 
            FROM cursos c
            INNER JOIN usuarios u ON c.id_creador = u.id_usuario
            WHERE $sql_donde
            ORDER BY c.fecha_creacion DESC
        ";
        $result_busqueda = mysqli_query($conexion, $sql_busqueda);
        $total_resultados = mysqli_num_rows($result_busqueda);

    } else {
        // CONSULTAS DEFAULT PARA INICIO (DESTACADOS Y RECOMENDADOS)
        $sql_destacados = "
            SELECT c.*, u.nombre, u.apellido 
            FROM cursos c
            INNER JOIN usuarios u ON c.id_creador = u.id_usuario
            WHERE c.activo = 1 AND c.es_publico = 1
            ORDER BY c.fecha_creacion DESC LIMIT 6
        ";
        $result_destacados = mysqli_query($conexion, $sql_destacados);

        $sql_recomendados = "
            SELECT c.*, u.nombre, u.apellido 
            FROM cursos c
            INNER JOIN usuarios u ON c.id_creador = u.id_usuario
            WHERE c.activo = 1 AND c.es_publico = 1
            ORDER BY RAND() LIMIT 6
        ";
        $result_recomendados = mysqli_query($conexion, $sql_recomendados);
    }
?>

   <main class="container my-5">
    
        <!-- Banner de la Página con Buscador Integrado -->
        <section class="banner-placeholder d-flex align-items-center justify-content-center mb-5 text-center px-3 py-5 rounded-4 shadow-sm">
            <div class="py-3 text-white w-100" style="max-width: 750px;">
                <h2 class="fw-bold mb-2 display-6">Descubre tu próximo curso</h2>
                <p class="fs-5 opacity-75 mb-4">Aprende a tu propio ritmo con expertos de la comunidad</p>
                
                <!-- Formulario de Búsqueda y Filtros -->
                <form action="index.php" method="GET" class="row g-2 justify-content-center bg-white p-2 rounded-4 shadow-sm text-start">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="buscar" class="form-control border-0 shadow-none" placeholder="Buscar por título, tema o docente..." value="<?php echo htmlspecialchars($buscar); ?>">
                        </div>
                    </div>
                    <div class="col-md-3 border-start-md">
                        <select name="tipo" class="form-select border-0 shadow-none text-secondary">
                            <option value="" <?php echo ($tipo == '') ? 'selected' : ''; ?>>Todos los precios</option>
                            <option value="gratis" <?php echo ($tipo == 'gratis') ? 'selected' : ''; ?>>Gratuitos</option>
                            <option value="pago" <?php echo ($tipo == 'pago') ? 'selected' : ''; ?>>De Pago</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-purple w-100 fw-bold py-2 rounded-3">
                            Filtrar Cursos
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($hay_filtro): ?>
            
            <!-- ========================================== -->
            <!-- VISTA DE RESULTADOS DE BÚSQUEDA -->
            <!-- ========================================== -->
            <section class="mb-5">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom gap-3">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Resultados de Búsqueda</h4>
                        <p class="text-muted small mb-0">Se encontraron <strong><?php echo $total_resultados; ?></strong> cursos disponibles.</p>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm fw-medium">
                        <i class="bi bi-x-circle me-1"></i>Limpiar Filtros
                    </a>
                </div>

                <?php if ($total_resultados > 0): ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php while ($curso = mysqli_fetch_assoc($result_busqueda)): ?>
                            <div class="col">
                                <div class="curso-card flex-shrink-0 card border-0 shadow-sm rounded-4 text-start h-100">
                                    <div class="curso-img bg-light d-flex align-items-center justify-content-center rounded-top border-bottom">
                                        <i class="bi bi-journal-code text-primary fs-1 opacity-50"></i>
                                    </div>
                                    
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="mb-2">
                                            <?php if ($curso['es_pago'] == 1): ?>
                                                <span class="badge bg-warning text-dark">De Pago</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Gratis</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <h5 class="fw-bold text-dark text-truncate mb-1"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                                        <p class="text-muted small line-clamp-3 mb-3"><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                                        
                                        <div class="text-muted small mb-3 mt-auto text-truncate">
                                            <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($curso['nombre'] . ' ' . $curso['apellido']); ?>
                                        </div>
                                        
                                        <a href="php/curso.php?id=<?php echo $curso['id_curso']; ?>" class="btn btn-outline-primary btn-sm w-100 fw-medium">
                                            Ver detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5 bg-light rounded-4 border border-dashed">
                        <i class="bi bi-search-heart display-1 text-muted mb-3 d-block"></i>
                        <h5 class="fw-bold text-dark mb-2">No encontramos resultados</h5>
                        <p class="text-muted mb-4">Intenta cambiando los términos de búsqueda o quitando los filtros aplicados.</p>
                        <a href="index.php" class="btn btn-purple fw-medium px-4">Ver Catálogo Completo</a>
                    </div>
                <?php endif; ?>
            </section>

        <?php else: ?>

            <!-- ========================================== -->
            <!-- VISTA DEFAULT (DESTACADOS Y RECOMENDADOS) -->
            <!-- ========================================== -->

            <!-- Sección: Destacados -->
            <section class="mb-5">
                <div class="d-flex justify-content-between align-items-end mb-3 px-2">
                    <h4 class="fw-bold mb-0 text-dark">Destacados</h4>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-left-destacados"><i class="bi bi-caret-left-fill"></i></button>                
                    
                    <div class="carrusel-cursos d-flex overflow-auto flex-nowrap gap-4 w-100 py-2 px-1" id="carrusel-destacados">                    
                        
                        <?php if (mysqli_num_rows($result_destacados) > 0): ?>
                            <?php while ($curso = mysqli_fetch_assoc($result_destacados)): ?>
                                
                                <div class="curso-card flex-shrink-0 d-flex flex-column text-start">
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
                                        
                                        <h5 class="fw-bold text-dark text-truncate mb-1"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                                        <p class="text-muted small line-clamp-3 mb-3"><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                                        
                                        <div class="text-muted small mb-3 mt-auto text-truncate">
                                            <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($curso['nombre'] . ' ' . $curso['apellido']); ?>
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
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <button class="btn p-0 border-0 arrow-btn d-none d-md-block" id="btn-left-recomendados"><i class="bi bi-caret-left-fill"></i></button>
                    
                    <div class="carrusel-cursos d-flex overflow-auto flex-nowrap gap-4 w-100 py-2 px-1" id="carrusel-recomendados">
                        
                        <?php if (mysqli_num_rows($result_recomendados) > 0): ?>
                            <?php while ($curso = mysqli_fetch_assoc($result_recomendados)): ?>
                                
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
                                        
                                        <h5 class="fw-bold text-dark text-truncate mb-1"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                                        <p class="text-muted small line-clamp-3 mb-3"><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                                        
                                        <div class="text-muted small mb-3 mt-auto text-truncate">
                                            <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($curso['nombre'] . ' ' . $curso['apellido']); ?>
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

        <?php endif; ?>

    </main>

<?php require_once 'php/footer.php'; ?>