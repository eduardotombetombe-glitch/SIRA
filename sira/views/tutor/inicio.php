<?php
$titulo = 'Panel del tutor';
$activo = 'inicio';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Hola, <?php echo e($tutor['nombre']); ?></h1>
    <p>Así van los estudiantes que acompañas en SIRA-LSC.</p>
</header>

<?php if (count($estudiantes) === 0): ?>
    <p class="vacio">Todavía no tienes estudiantes asociados.
       <a href="<?php echo url('tutor/estudiantes'); ?>">Registra o asocia uno</a> para ver su progreso.</p>
<?php else: ?>
<div class="rejilla">
    <?php foreach ($estudiantes as $estudiante): ?>
    <article class="tarjeta-curso">
        <p class="sigla"><?php echo e(inicial($estudiante['nombre'])); ?></p>
        <h3><?php echo e($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></h3>
        <p class="texto-apoyo">
            <?php echo e($estudiante['grado'] ? $estudiante['grado'] : 'Sin grado asignado'); ?> ·
            @<?php echo e($estudiante['usuario']); ?>
        </p>
        <div class="barra"><span style="width: <?php echo (int) $estudiante['resumen']['porcentaje']; ?>%;"></span></div>
        <p class="detalle-barra">
            <?php echo (int) $estudiante['resumen']['completadas']; ?> completadas ·
            <?php echo (int) $estudiante['resumen']['pendientes']; ?> pendientes ·
            <?php echo (int) $estudiante['resumen']['porcentaje']; ?>%
        </p>
        <a class="enlace-curso" href="<?php echo url('tutor/estudiante', array('id' => $estudiante['id_usuario'])); ?>">Ver progreso</a>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
