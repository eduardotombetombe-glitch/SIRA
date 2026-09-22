<?php
$titulo = 'Mi progreso';
$activo = 'progreso';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Mi progreso</h1>
    <p>Así vas en los seis grados de SIRA-LSC.</p>
</header>

<section class="panel-estudiante">
    <div class="panel-datos">
        <p class="etiqueta-suave">Grado actual</p>
        <h2><?php echo e($estudiante['grado']); ?></h2>
        <?php if ($cursoActual): ?>
            <p class="dato-curso">Unidad en curso: <strong><?php echo e($cursoActual['nombre']); ?></strong></p>
            <a class="boton boton-claro" href="<?php echo url('estudiante/curso', array('id' => $cursoActual['id_curso'])); ?>">Continuar</a>
        <?php endif; ?>
    </div>
    <div class="panel-cifras">
        <div class="anillo" style="--avance: <?php echo (int) $resumen['porcentaje']; ?>%;">
            <span><?php echo (int) $resumen['porcentaje']; ?>%</span>
        </div>
        <ul class="cifras">
            <li><strong><?php echo (int) $resumen['completadas']; ?></strong> completadas en tu grado</li>
            <li><strong><?php echo (int) $resumen['pendientes']; ?></strong> pendientes en tu grado</li>
            <li><strong><?php echo (int) $total; ?></strong> lecciones en total</li>
        </ul>
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

<h2 class="titulo-seccion">Historial reciente</h2>
<?php if (count($recientes) === 0): ?>
    <p class="vacio">Cuando completes lecciones aparecerán aquí.</p>
<?php else: ?>
<table class="tabla">
    <thead><tr><th>Lección</th><th>Unidad</th><th>Grado</th><th>Fecha</th></tr></thead>
    <tbody>
    <?php foreach ($recientes as $fila): ?>
        <tr>
            <td><?php echo e($fila['titulo']); ?></td>
            <td><?php echo e($fila['curso']); ?></td>
            <td><?php echo e($fila['grado']); ?></td>
            <td><?php echo e(date('d/m/Y', strtotime($fila['fecha_completada']))); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
