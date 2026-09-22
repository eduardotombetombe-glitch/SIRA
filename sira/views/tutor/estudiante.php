<?php
$titulo = 'Progreso de ' . $estudiante['nombre'];
$activo = 'estudiantes';
require RUTA_BASE . '/views/layouts/header.php';
?>
<p class="miga"><a href="<?php echo url('tutor/estudiantes'); ?>">Mis estudiantes</a></p>

<header class="titular">
    <h1><?php echo e($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></h1>
    <p><?php echo e($estudiante['grado'] ? $estudiante['grado'] : 'Sin grado asignado'); ?> · @<?php echo e($estudiante['usuario']); ?></p>
</header>

<section class="panel-estudiante">
    <div class="panel-datos">
        <p class="etiqueta-suave">Grado actual</p>
        <h2><?php echo e($estudiante['grado'] ? $estudiante['grado'] : 'Sin grado'); ?></h2>
        <p class="texto-apoyo"><?php echo e($estudiante['grado_descripcion']); ?></p>
    </div>
    <div class="panel-cifras">
        <div class="anillo" style="--avance: <?php echo (int) $resumen['porcentaje']; ?>%;">
            <span><?php echo (int) $resumen['porcentaje']; ?>%</span>
        </div>
        <ul class="cifras">
            <li><strong><?php echo (int) $resumen['completadas']; ?></strong> lecciones completadas</li>
            <li><strong><?php echo (int) $resumen['pendientes']; ?></strong> lecciones pendientes</li>
            <li><strong><?php echo (int) $total; ?></strong> en toda la plataforma</li>
        </ul>
    </div>
</section>

<h2 class="titulo-seccion">Avance por unidad</h2>
<?php if (count($cursos) === 0): ?>
    <p class="vacio">El grado de este estudiante todavía no tiene unidades.</p>
<?php else: ?>
<div class="rejilla">
    <?php foreach ($cursos as $curso): ?>
    <article class="tarjeta-curso">
        <p class="sigla"><?php echo e($curso['icono']); ?></p>
        <h3><?php echo e($curso['nombre']); ?></h3>
        <div class="barra"><span style="width: <?php echo (int) $curso['porcentaje']; ?>%;"></span></div>
        <p class="detalle-barra"><?php echo (int) $curso['completadas']; ?> de <?php echo (int) $curso['total']; ?> lecciones</p>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<section class="dos-columnas">
    <div class="columna">
        <h2 class="titulo-seccion">Lecciones completadas</h2>
        <?php if (count($completadas) === 0): ?>
            <p class="vacio">Todavía no ha completado ninguna lección.</p>
        <?php else: ?>
        <ul class="lista-simple lista-hechas">
            <?php foreach ($completadas as $fila): ?>
                <li><span class="linea-simple">
                    <strong><?php echo e($fila['titulo']); ?></strong>
                    <small><?php echo e($fila['curso'] . ' · ' . fechaCorta($fila['fecha_completada'])); ?></small>
                </span></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <div class="columna">
        <h2 class="titulo-seccion">Lecciones pendientes</h2>
        <?php if (count($pendientes) === 0): ?>
            <p class="vacio">No quedan lecciones pendientes en este grado.</p>
        <?php else: ?>
        <ul class="lista-simple">
            <?php foreach ($pendientes as $fila): ?>
                <li><span class="linea-simple">
                    <strong><?php echo e($fila['titulo']); ?></strong>
                    <small><?php echo e($fila['curso']); ?></small>
                </span></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</section>

<h2 class="titulo-seccion">Avance por grado</h2>
<div class="lista-grados">
    <?php foreach ($grados as $grado): ?>
    <article class="fila-grado <?php echo $grado['id_grado'] == $estudiante['id_grado'] ? 'fila-actual' : ''; ?>">
        <div class="fila-grado-texto">
            <h3><?php echo e($grado['nombre']); ?></h3>
            <p class="texto-apoyo"><?php echo e($grado['descripcion']); ?></p>
        </div>
        <div class="fila-grado-barra">
            <div class="barra"><span style="width: <?php echo (int) $grado['porcentaje']; ?>%;"></span></div>
            <p class="detalle-barra"><?php echo (int) $grado['completadas']; ?> de <?php echo (int) $grado['total']; ?> lecciones · <?php echo (int) $grado['porcentaje']; ?>%</p>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
