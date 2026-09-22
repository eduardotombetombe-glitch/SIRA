<?php
$titulo = 'Tablero';
$activo = 'inicio';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Tablero de administración</h1>
    <p>Estado general de SIRA-LSC.</p>
</header>

<div class="rejilla-cifras">
    <article class="tarjeta-cifra">
        <p class="numero-grande"><?php echo (int) $resumen['estudiantes']; ?></p>
        <p class="etiqueta-cifra">Estudiantes</p>
    </article>
    <article class="tarjeta-cifra">
        <p class="numero-grande"><?php echo (int) $resumen['tutores']; ?></p>
        <p class="etiqueta-cifra">Tutores</p>
    </article>
    <article class="tarjeta-cifra">
        <p class="numero-grande"><?php echo (int) $resumen['administradores']; ?></p>
        <p class="etiqueta-cifra">Administradores</p>
    </article>
    <article class="tarjeta-cifra">
        <p class="numero-grande"><?php echo (int) $resumen['grados']; ?></p>
        <p class="etiqueta-cifra">Grados</p>
    </article>
    <article class="tarjeta-cifra">
        <p class="numero-grande"><?php echo (int) $resumen['cursos']; ?></p>
        <p class="etiqueta-cifra">Unidades</p>
    </article>
    <article class="tarjeta-cifra">
        <p class="numero-grande"><?php echo (int) $resumen['lecciones']; ?></p>
        <p class="etiqueta-cifra">Lecciones</p>
    </article>
    <article class="tarjeta-cifra">
        <p class="numero-grande"><?php echo (int) $resumen['completadas']; ?></p>
        <p class="etiqueta-cifra">Lecciones completadas</p>
    </article>
</div>

<h2 class="titulo-seccion">Estudiantes con más avance</h2>
<?php if (count($avances) === 0): ?>
    <p class="vacio">Todavía no hay estudiantes registrados.</p>
<?php else: ?>
<table class="tabla">
    <thead><tr><th>Estudiante</th><th>Grado</th><th>Avance</th><th>Completadas</th><th>Pendientes</th></tr></thead>
    <tbody>
    <?php foreach ($avances as $fila): ?>
        <tr>
            <td><?php echo e($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
            <td><?php echo e($fila['grado'] ? $fila['grado'] : 'Sin grado'); ?></td>
            <td>
                <div class="barra barra-tabla"><span style="width: <?php echo (int) $fila['porcentaje']; ?>%;"></span></div>
                <small><?php echo (int) $fila['porcentaje']; ?>%</small>
            </td>
            <td><?php echo (int) $fila['completadas']; ?></td>
            <td><?php echo (int) $fila['pendientes']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p class="texto-apoyo"><a href="<?php echo url('admin/progreso'); ?>">Ver el progreso de todos los estudiantes</a></p>
<?php endif; ?>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
