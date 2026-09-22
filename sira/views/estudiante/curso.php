<?php
$titulo = $curso['nombre'];
$activo = 'cursos';
require RUTA_BASE . '/views/layouts/header.php';
?>
<p class="miga"><a href="<?php echo url('estudiante/cursos'); ?>">Mis cursos</a> · <?php echo e($curso['grado']); ?></p>

<header class="titular">
    <h1><?php echo e($curso['nombre']); ?></h1>
    <p><?php echo e($curso['descripcion']); ?></p>
</header>

<section class="resumen-curso">
    <div class="barra barra-ancha"><span style="width: <?php echo (int) $resumen['porcentaje']; ?>%;"></span></div>
    <p class="detalle-barra">
        <?php echo (int) $resumen['completadas']; ?> de <?php echo (int) $resumen['total']; ?> lecciones ·
        <?php echo (int) $resumen['porcentaje']; ?>% · <?php echo (int) $resumen['pendientes']; ?> pendientes
    </p>
</section>

<ol class="lista-lecciones">
    <?php foreach ($lecciones as $leccion): ?>
    <li class="<?php echo $leccion['completada'] ? 'hecha' : ''; ?>">
        <a href="<?php echo url('estudiante/leccion', array('id' => $leccion['id_leccion'])); ?>">
            <span class="numero"><?php echo (int) $leccion['orden']; ?></span>
            <span class="texto">
                <strong><?php echo e($leccion['titulo']); ?></strong>
                <small><?php echo e($leccion['descripcion']); ?></small>
            </span>
            <span class="estado"><?php echo $leccion['completada'] ? 'Completada' : 'Pendiente'; ?></span>
        </a>
    </li>
    <?php endforeach; ?>
</ol>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
