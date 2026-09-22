<?php
$titulo = 'Progreso de los estudiantes';
$activo = 'progreso';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Progreso de los estudiantes</h1>
    <p>Avance de cada estudiante dentro del grado que tiene asignado.</p>
</header>

<?php if (count($avances) === 0): ?>
    <p class="vacio">Todavía no hay estudiantes registrados.</p>
<?php else: ?>
<table class="tabla">
    <thead><tr><th>Estudiante</th><th>Usuario</th><th>Grado</th><th>Avance</th><th>Completadas</th><th>Pendientes</th></tr></thead>
    <tbody>
    <?php foreach ($avances as $fila): ?>
        <tr>
            <td><?php echo e($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
            <td>@<?php echo e($fila['usuario']); ?></td>
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
<?php endif; ?>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
