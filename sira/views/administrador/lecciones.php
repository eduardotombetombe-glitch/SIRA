<?php
$titulo = 'Lecciones';
$activo = 'lecciones';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Lecciones</h1>
    <p>Contenidos educativos de Lengua de Señas Colombiana.</p>
</header>

<section class="barra-acciones">
    <form class="filtros" method="get" action="<?php echo BASE_URL; ?>index.php">
        <input type="hidden" name="ruta" value="admin/lecciones">
        <label class="campo-en-linea">
            <span>Grado</span>
            <select name="id_grado">
                <option value="0">Todos</option>
                <?php foreach ($grados as $grado): ?>
                    <option value="<?php echo (int) $grado['id_grado']; ?>" <?php echo $idGrado === (int) $grado['id_grado'] ? 'selected' : ''; ?>>
                        <?php echo e($grado['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="campo-en-linea">
            <span>Unidad</span>
            <select name="id_curso">
                <option value="0">Todas</option>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?php echo (int) $curso['id_curso']; ?>" <?php echo $idCurso === (int) $curso['id_curso'] ? 'selected' : ''; ?>>
                        <?php echo e($curso['grado'] . ' · ' . $curso['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="boton-mini" type="submit">Filtrar</button>
    </form>

    <a class="boton boton-corto" href="<?php echo url('admin/leccion'); ?>">Crear lección</a>
</section>

<?php if (count($lecciones) === 0): ?>
    <p class="vacio">No hay lecciones con ese filtro.</p>
<?php else: ?>
<table class="tabla">
    <thead><tr><th>#</th><th>Lección</th><th>Unidad</th><th>Grado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($lecciones as $fila): ?>
        <tr>
            <td><?php echo (int) $fila['orden']; ?></td>
            <td>
                <strong><?php echo e($fila['titulo']); ?></strong><br>
                <small class="texto-apoyo"><?php echo e($fila['descripcion']); ?></small>
            </td>
            <td><?php echo e($fila['curso']); ?></td>
            <td><?php echo e($fila['grado']); ?></td>
            <td class="acciones">
                <a class="boton-mini" href="<?php echo url('admin/leccion', array('id' => $fila['id_leccion'])); ?>">Editar</a>
                <form method="post" action="<?php echo url('admin/leccion/eliminar'); ?>">
                    <input type="hidden" name="id_leccion" value="<?php echo (int) $fila['id_leccion']; ?>">
                    <button class="boton-mini boton-peligro" type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
