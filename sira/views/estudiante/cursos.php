<?php
$titulo = 'Mis cursos';
$activo = 'cursos';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Mis cursos</h1>
    <p>Todo el recorrido, del Grado 0 al Grado 5. Puedes revisar cualquier unidad cuando quieras.</p>
</header>

<?php foreach ($porGrado as $bloque): ?>
    <?php $grado = $bloque['grado']; $resumen = $bloque['resumen']; ?>
    <section class="bloque-grado <?php echo $grado['id_grado'] == $estudiante['id_grado'] ? 'bloque-actual' : ''; ?>">
        <div class="encabezado-grado">
            <div>
                <h2><?php echo e($grado['nombre']); ?>
                    <?php if ($grado['id_grado'] == $estudiante['id_grado']): ?>
                        <span class="insignia">Tu grado</span>
                    <?php endif; ?>
                </h2>
                <p class="texto-apoyo"><?php echo e($grado['descripcion']); ?></p>
            </div>
            <p class="porcentaje-grado"><?php echo (int) $resumen['porcentaje']; ?>%</p>
        </div>

        <?php if (count($bloque['cursos']) === 0): ?>
            <p class="vacio">Este grado no tiene unidades todavía.</p>
        <?php else: ?>
        <div class="rejilla">
            <?php foreach ($bloque['cursos'] as $curso): ?>
            <article class="tarjeta-curso">
                <p class="sigla"><?php echo e($curso['icono']); ?></p>
                <h3><?php echo e($curso['nombre']); ?></h3>
                <p class="texto-apoyo"><?php echo e($curso['descripcion']); ?></p>
                <div class="barra"><span style="width: <?php echo (int) $curso['porcentaje']; ?>%;"></span></div>
                <p class="detalle-barra"><?php echo (int) $curso['completadas']; ?> de <?php echo (int) $curso['total']; ?> lecciones</p>
                <a class="enlace-curso" href="<?php echo url('estudiante/curso', array('id' => $curso['id_curso'])); ?>">Ver unidad</a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
<?php endforeach; ?>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
