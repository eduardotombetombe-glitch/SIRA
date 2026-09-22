<?php
$titulo = $leccion['titulo'];
$activo = 'cursos';
require RUTA_BASE . '/views/layouts/header.php';
?>
<p class="miga">
    <a href="<?php echo url('estudiante/cursos'); ?>">Mis cursos</a> ·
    <a href="<?php echo url('estudiante/curso', array('id' => $leccion['id_curso'])); ?>"><?php echo e($leccion['curso']); ?></a> ·
    <?php echo e($leccion['grado']); ?>
</p>

<article class="leccion">
    <header class="titular">
        <h1><?php echo e($leccion['titulo']); ?></h1>
        <p><?php echo e($leccion['descripcion']); ?></p>
    </header>

    <div class="contenido-leccion">
        <p><?php echo nl2br(e($leccion['contenido'])); ?></p>
    </div>

    <?php if (!empty($leccion['actividad'])): ?>
    <section class="actividad">
        <h2>Actividad</h2>
        <p><?php echo nl2br(e($leccion['actividad'])); ?></p>
    </section>
    <?php endif; ?>

    <div class="pie-leccion">
        <?php if ($completada): ?>
            <p class="marca-hecha">Ya completaste esta lección</p>
            <?php if ($siguiente): ?>
                <a class="boton" href="<?php echo url('estudiante/leccion', array('id' => $siguiente['id_leccion'])); ?>">Siguiente lección</a>
            <?php else: ?>
                <a class="boton" href="<?php echo url('estudiante/curso', array('id' => $leccion['id_curso'])); ?>">Volver a la unidad</a>
            <?php endif; ?>
        <?php else: ?>
            <form method="post" action="<?php echo url('estudiante/completar'); ?>">
                <input type="hidden" name="id_leccion" value="<?php echo (int) $leccion['id_leccion']; ?>">
                <button class="boton" type="submit">Marcar como completada</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="barra barra-ancha"><span style="width: <?php echo (int) $resumen['porcentaje']; ?>%;"></span></div>
    <p class="detalle-barra">Unidad al <?php echo (int) $resumen['porcentaje']; ?>% · <?php echo (int) $resumen['pendientes']; ?> lecciones pendientes</p>
</article>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
