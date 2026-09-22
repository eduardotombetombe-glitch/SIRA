<?php
$titulo = 'Mis lecciones';
$activo = 'lecciones';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Mis lecciones</h1>
    <p>Lo que sigue en <?php echo e($estudiante['grado']); ?> y lo que ya terminaste.</p>
</header>

<section class="dos-columnas">
    <div class="columna">
        <h2 class="titulo-seccion">Siguientes lecciones</h2>
        <?php if (count($pendientes) === 0): ?>
            <p class="vacio">Completaste todas las lecciones de este grado. Cambia de grado desde tu perfil para seguir.</p>
        <?php else: ?>
        <ul class="lista-simple">
            <?php foreach ($pendientes as $leccion): ?>
            <li>
                <a href="<?php echo url('estudiante/leccion', array('id' => $leccion['id_leccion'])); ?>">
                    <strong><?php echo e($leccion['titulo']); ?></strong>
                    <small><?php echo e($leccion['curso']); ?></small>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <div class="columna">
        <h2 class="titulo-seccion">Terminadas hace poco</h2>
        <?php if (count($recientes) === 0): ?>
            <p class="vacio">Todavía no has completado ninguna lección.</p>
        <?php else: ?>
        <ul class="lista-simple lista-hechas">
            <?php foreach ($recientes as $leccion): ?>
            <li>
                <a href="<?php echo url('estudiante/leccion', array('id' => $leccion['id_leccion'])); ?>">
                    <strong><?php echo e($leccion['titulo']); ?></strong>
                    <small><?php echo e($leccion['curso'] . ' · ' . date('d/m/Y', strtotime($leccion['fecha_completada']))); ?></small>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</section>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
