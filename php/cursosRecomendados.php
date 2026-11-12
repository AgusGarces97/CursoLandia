<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

// 1. PROTECCIÓN DE ACCESO: Solo usuarios logueados tienen intereses para parear
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];

// 2. OBTENER INTERESES DEL USUARIO ACTUAL
$sql_intereses = "
    SELECT e.id_etiqueta, e.nombre 
    FROM usuario_intereses ui
    INNER JOIN etiquetas e ON ui.id_etiqueta = e.id_etiqueta
    WHERE ui.id_usuario = $id_usuario
    ORDER BY e.nombre ASC
";
$res_intereses = mysqli_query($conexion, $sql_intereses);

$intereses_usuario = [];
$ids_intereses = [];
while ($row_int = mysqli_fetch_assoc($res_intereses)) {
    $intereses_usuario[] = $row_int['nombre'];
    $ids_intereses[] = (int)$row_int['id_etiqueta'];
}

// 3. CONSULTAR CURSOS RECOMENDADOS (Pareo con etiquetas + Prioridad Plan PRO)
$cursos_recomendados = [];

if (count($ids_intereses) > 0) {
    $lista_ids = implode(',', $ids_intereses);

    $sql_recomendados = "
        SELECT 
            c.*, 
            u.nombre AS nombre_creador, 
            u.apellido AS apellido_creador, 
            u.plan AS plan_creador,
            COUNT(ce.id_etiqueta) AS coincidencias
        FROM cursos c
        INNER JOIN usuarios u ON c.id_creador = u.id_usuario
        INNER JOIN curso_etiquetas ce ON c.id_curso = ce.id_curso
        WHERE c.activo = 1 
          AND c.es_publico = 1
          AND c.id_creador != $id_usuario
          AND ce.id_etiqueta IN ($lista_ids)
        GROUP BY c.id_curso
        ORDER BY 
            CASE WHEN u.plan = 'pro' THEN 1 ELSE 2 END ASC,
            coincidencias DESC,
            c.fecha_inicio ASC
    ";
    $res_recomendados = mysqli_query($conexion, $sql_recomendados);

    while ($curso = mysqli_fetch_assoc($res_recomendados)) {
        // Consultamos todas las etiquetas de este curso para mostrarlas en la tarjeta
        $id_c = (int)$curso['id_curso'];
        $sql_tags = "
            SELECT e.nombre 
            FROM curso_etiquetas ce 
            INNER JOIN etiquetas e ON ce.id_etiqueta = e.id_etiqueta 
            WHERE ce.id_curso = $id_c 
            ORDER BY e.nombre ASC
        ";
        $res_tags = mysqli_query($conexion, $sql_tags);
        $tags_curso = [];
        while ($t = mysqli_fetch_assoc($res_tags)) {
            $tags_curso[] = $t['nombre'];
        }
        $curso['etiquetas'] = $tags_curso;
        $cursos_recomendados[] = $curso;
    }
}

$titulo_pagina = 'Recomendados - Cursolandia';
$ruta = '../'; 
require_once 'header.php'; 
?>

    <main class="container my-5" style="min-height: 70vh;">
        
        <!-- Encabezado de la página -->
        <div class="mb-5 border-bottom pb-4">
            <h2 class="fw-bold text-dark mb-2">Recomendados para ti</h2>
            <p class="text-muted mb-3">Basado en el pareo de tus intereses con las etiquetas de los cursos activos.</p>
            
            <!-- Intereses del usuario que generaron las recomendaciones -->
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="text-secondary fw-medium me-2">Tus intereses detectados:</span>
                <?php if (count($intereses_usuario) > 0): ?>
                    <?php foreach ($intereses_usuario as $interes): ?>
                        <span class="badge bg-light text-secondary border px-2 py-1">
                            <i class="bi bi-tag-fill me-1"></i>#<?php echo htmlspecialchars($interes); ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-muted small fst-italic">Aún no has configurado intereses en tu perfil.</span>
                    <a href="perfil.php" class="btn btn-sm btn-outline-primary ms-2 fw-medium">Configurar Intereses</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Grilla de Cursos Recomendados -->
        <?php if (count($cursos_recomendados) > 0): ?>
            <div class="row g-4">
                
                <?php foreach ($cursos_recomendados as $curso): ?>
                    <?php 
                        $es_pro = ($curso['plan_creador'] === 'pro');
                        $imagen_portada = !empty($curso['imagen']) ? '../uploads/cursos/' . htmlspecialchars($curso['imagen']) : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                    ?>

                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm curso-card-hover position-relative <?php echo $es_pro ? 'curso-pro-destacado' : 'border-0 mt-3 mt-lg-0'; ?>">
                            
                            <?php if ($es_pro): ?>
                                <!-- Etiqueta Visual de Destacado (Creador con Plan Pro) -->
                                <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill px-3 py-2 text-white shadow-sm" style="background-color: var(--PRIMARY); z-index: 2;">
                                    <i class="bi bi-star-fill text-warning me-1"></i> Curso Destacado
                                </div>
                            <?php endif; ?>

                            <!-- Imagen del Curso -->
                            <img src="<?php echo $imagen_portada; ?>" class="card-img-top curso-img" alt="Portada <?php echo htmlspecialchars($curso['titulo']); ?>">

                            <div class="card-body pt-3 d-flex flex-column">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-success">Activo</span>
                                    <?php if ($curso['es_pago'] == 1): ?>
                                        <span class="badge bg-dark"><i class="bi bi-currency-dollar"></i> Pago</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Gratuito</span>
                                    <?php endif; ?>
                                </div>

                                <h5 class="card-title fw-bold text-dark mt-3"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                                
                                <p class="text-muted small mb-3">
                                    Por: 
                                    <?php if ($es_pro): ?>
                                        <span class="fw-bold" style="color: var(--PRIMARY);"><?php echo htmlspecialchars($curso['nombre_creador'] . ' ' . $curso['apellido_creador']); ?></span> 
                                        <i class="bi bi-patch-check-fill text-primary" title="Usuario Pro"></i>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($curso['nombre_creador'] . ' ' . $curso['apellido_creador']); ?>
                                    <?php endif; ?>
                                </p>
                                
                                <p class="text-secondary small line-clamp-3"><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                                
                                <!-- Etiquetas del curso (resaltando las que coinciden con el usuario) -->
                                <div class="d-flex flex-wrap gap-1 mt-auto pt-2">
                                    <?php foreach ($curso['etiquetas'] as $tag_curso): ?>
                                        <?php $coincide = in_array($tag_curso, $intereses_usuario); ?>
                                        <span class="badge bg-light <?php echo $coincide ? 'text-dark border border-primary' : 'text-secondary border'; ?>">
                                            #<?php echo htmlspecialchars($tag_curso); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="card-footer bg-white border-top-0 pt-0">
                                <?php if ($es_pro): ?>
                                    <a href="curso.php?id=<?php echo $curso['id_curso']; ?>" class="btn btn-purple w-100 fw-medium shadow-sm">Ver Detalles</a>
                                <?php else: ?>
                                    <a href="curso.php?id=<?php echo $curso['id_curso']; ?>" class="btn btn-outline-primary w-100 fw-medium" style="border-color: var(--PRIMARY); color: var(--PRIMARY);">Ver Detalles</a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        <?php else: ?>
            <!-- Estado Vacío -->
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                <div class="card-body">
                    <i class="bi bi-compass display-4 text-muted opacity-50 mb-3 d-block"></i>
                    <h4 class="fw-bold text-dark mb-2">No encontramos coincidencias exactas por ahora</h4>
                    <p class="text-secondary mb-4">
                        <?php if (count($intereses_usuario) > 0): ?>
                            Aún no hay cursos públicos activos que coincidan con tus etiquetas seleccionadas. Prueba sumando más intereses en tu perfil o explora el catálogo general.
                        <?php else: ?>
                            Agrega tus temas favoritos en tu perfil para que nuestro sistema pueda recomendarte cursos a tu medida.
                        <?php endif; ?>
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="perfil.php" class="btn btn-outline-primary fw-medium px-4">Editar mis Intereses</a>
                        <a href="../index.php" class="btn btn-purple fw-medium px-4">Explorar Catálogo</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>

<?php require_once 'footer.php'; ?>