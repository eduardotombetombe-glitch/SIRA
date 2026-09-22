<?php
$titulo = 'Inicio';
$activo = 'inicio';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>SIRA-LSC</h1>
    <p>Aprende Lengua de Señas Colombiana paso a paso</p>
</header>

<section class="panel-estudiante">
    <div class="panel-datos">
        <p class="etiqueta-suave">Hola, <?php echo e($estudiante['nombre']); ?></p>
        <h2><?php echo e($estudiante['grado']); ?></h2>
        <p class="texto-apoyo"><?php echo e($estudiante['grado_descripcion']); ?></p>

        <?php if ($cursoActual): ?>
            <p class="dato-curso">Vas en la unidad <strong><?php echo e($cursoActual['nombre']); ?></strong></p>
            <a class="boton boton-claro" href="<?php echo url('estudiante/curso', array('id' => $cursoActual['id_curso'])); ?>">Continuar</a>
        <?php else: ?>
            <p class="dato-curso">Este grado todavía no tiene unidades cargadas.</p>
        <?php endif; ?>
    </div>

    <div class="panel-cifras">
        <div class="anillo" style="--avance: <?php echo (int) $resumenGrado['porcentaje']; ?>%;">
            <span><?php echo (int) $resumenGrado['porcentaje']; ?>%</span>
        </div>
        <ul class="cifras">
            <li><strong><?php echo (int) $resumenGrado['completadas']; ?></strong> lecciones completadas</li>
            <li><strong><?php echo (int) $resumenGrado['pendientes']; ?></strong> lecciones pendientes</li>
            <li><strong><?php echo (int) $totalGlobal; ?></strong> en toda la plataforma</li>
        </ul>
    </div>
</section>

<h2 class="titulo-seccion">Unidades de tu grado</h2>

<?php if (count($cursos) === 0): ?>
    <p class="vacio">Aún no hay unidades creadas para este grado. Puedes agregarlas en la tabla <strong>cursos</strong>.</p>
<?php else: ?>
<div class="rejilla">
    <?php foreach ($cursos as $curso): ?>
    <article class="tarjeta-curso">
        <p class="sigla"><?php echo e($curso['icono']); ?></p>
        <p class="etiqueta-grado"><?php echo e($estudiante['grado']); ?></p>
        <h3><?php echo e($curso['nombre']); ?></h3>
        <p class="texto-apoyo"><?php echo e($curso['descripcion']); ?></p>

        <div class="barra" role="img" aria-label="Progreso <?php echo (int) $curso['porcentaje']; ?> por ciento">
            <span style="width: <?php echo (int) $curso['porcentaje']; ?>%;"></span>
        </div>
        <p class="detalle-barra">
            <?php echo (int) $curso['completadas']; ?> de <?php echo (int) $curso['total']; ?> lecciones ·
            <?php echo (int) $curso['porcentaje']; ?>%
        </p>

        <a class="enlace-curso" href="<?php echo url('estudiante/curso', array('id' => $curso['id_curso'])); ?>">
            <?php echo $curso['porcentaje'] > 0 ? 'Continuar' : 'Empezar'; ?>
        </a>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
