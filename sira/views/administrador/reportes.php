<?php
$titulo = 'Reportes';
$activo = 'reportes';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Reporte del sistema</h1>
    <p>Información generada directamente desde la base de datos. Fecha: <?php echo e($fecha); ?></p>
</header>

<div class="barra-acciones">
    <a class="boton" href="<?php echo url('admin/reporte/exportar'); ?>">Exportar progreso a CSV</a>
</div>

<div class="rejilla-cifras">
    <article class="tarjeta-cifra"><p class="numero-grande"><?php echo (int) $resumen['estudiantes']; ?></p><p class="etiqueta-cifra">Estudiantes</p></article>
    <article class="tarjeta-cifra"><p class="numero-grande"><?php echo (int) $resumen['tutores']; ?></p><p class="etiqueta-cifra">Tutores</p></article>
    <article class="tarjeta-cifra"><p class="numero-grande"><?php echo (int) $resumen['cursos']; ?></p><p class="etiqueta-cifra">Unidades</p></article>
    <article class="tarjeta-cifra"><p class="numero-grande"><?php echo (int) $resumen['lecciones']; ?></p><p class="etiqueta-cifra">Lecciones</p></article>
    <article class="tarjeta-cifra"><p class="numero-grande"><?php echo (int) $resumen['completadas']; ?></p><p class="etiqueta-cifra">Completadas</p></article>
</div>

<section class="tabla-caja">
    <h2 class="titulo-seccion">Reporte de progreso por estudiante</h2>
    <?php if (count($avances) === 0): ?>
        <p class="vacio">No hay estudiantes para mostrar.</p>
    <?php else: ?>
    <table class="tabla">
        <thead>
            <tr><th>ID</th><th>Estudiante</th><th>Usuario</th><th>Grado</th><th>Total</th><th>Completadas</th><th>Pendientes</th><th>Avance</th></tr>
        </thead>
        <tbody>
        <?php foreach ($avances as $fila): ?>
            <tr>
                <td><?php echo (int) $fila['id_usuario']; ?></td>
                <td><?php echo e($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
                <td>@<?php echo e($fila['usuario']); ?></td>
                <td><?php echo e($fila['grado'] ? $fila['grado'] : 'Sin grado'); ?></td>
                <td><?php echo (int) $fila['total']; ?></td>
                <td><?php echo (int) $fila['completadas']; ?></td>
                <td><?php echo (int) $fila['pendientes']; ?></td>
                <td><?php echo (int) $fila['porcentaje']; ?>%</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
