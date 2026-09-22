<?php
$titulo  = 'Grados';
$activo  = 'grados';
$errores = isset($errores) ? $errores : array();
$datos   = isset($datos) ? $datos : array();
$edita   = isset($edita) ? $edita : null;
$valor   = function ($campo, $porDefecto = '') use ($datos, $edita) {
    if (isset($datos[$campo]) && $datos[$campo] !== '') { return $datos[$campo]; }
    if ($edita && isset($edita[$campo]))                { return $edita[$campo]; }
    return $porDefecto;
};
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Grados</h1>
    <p>Los niveles del recorrido. Cada grado agrupa unidades y lecciones.</p>
</header>

<table class="tabla">
    <thead><tr><th>Grado</th><th>Descripción</th><th>Orden</th><th>Unidades</th><th>Lecciones</th><th>Estudiantes</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($grados as $fila): ?>
        <tr>
            <td><strong><?php echo e($fila['nombre']); ?></strong></td>
            <td><?php echo e($fila['descripcion']); ?></td>
            <td><?php echo (int) $fila['orden']; ?></td>
            <td><?php echo (int) $fila['cursos']; ?></td>
            <td><?php echo (int) $fila['lecciones']; ?></td>
            <td><?php echo (int) $fila['estudiantes']; ?></td>
            <td class="acciones">
                <a class="boton-mini" href="<?php echo url('admin/grados', array('id' => $fila['id_grado'])); ?>">Editar</a>
                <form method="post" action="<?php echo url('admin/grado/eliminar'); ?>">
                    <input type="hidden" name="id_grado" value="<?php echo (int) $fila['id_grado']; ?>">
                    <button class="boton-mini boton-peligro" type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<section class="tarjeta-form">
    <h2 class="titulo-seccion"><?php echo $edita ? 'Editar ' . e($edita['nombre']) : 'Crear un grado nuevo'; ?></h2>
    <?php if ($edita): ?>
        <p class="texto-apoyo">Al eliminar un grado se borran también sus unidades y lecciones.
           <a href="<?php echo url('admin/grados'); ?>">Cancelar la edición</a></p>
    <?php endif; ?>
    <form class="formulario" method="post" action="<?php echo url('admin/grado/guardar'); ?>">
        <input type="hidden" name="id_grado" value="<?php echo $edita ? (int) $edita['id_grado'] : 0; ?>">
        <div class="fila-doble">
            <p class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo e($valor('nombre')); ?>" required>
                <?php if (isset($errores['nombre'])): ?><span class="error"><?php echo e($errores['nombre']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="orden">Orden</label>
                <input type="number" id="orden" name="orden" value="<?php echo e($valor('orden', '0')); ?>" min="0">
            </p>
        </div>
        <p class="campo">
            <label for="descripcion">Descripción</label>
            <input type="text" id="descripcion" name="descripcion" value="<?php echo e($valor('descripcion')); ?>" maxlength="255" required>
            <?php if (isset($errores['descripcion'])): ?><span class="error"><?php echo e($errores['descripcion']); ?></span><?php endif; ?>
        </p>
        <button class="boton boton-corto" type="submit"><?php echo $edita ? 'Guardar cambios' : 'Crear grado'; ?></button>
    </form>
</section>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
