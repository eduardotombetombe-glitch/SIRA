<?php
$edita   = isset($leccion) && $leccion;
$titulo  = $edita ? 'Editar lección' : 'Crear lección';
$activo  = 'lecciones';
$errores = isset($errores) ? $errores : array();
$datos   = isset($datos) ? $datos : array();
$valor   = function ($campo, $porDefecto = '') use ($datos, $leccion) {
    if (isset($datos[$campo]) && $datos[$campo] !== '') { return $datos[$campo]; }
    if ($leccion && isset($leccion[$campo]))            { return $leccion[$campo]; }
    return $porDefecto;
};
require RUTA_BASE . '/views/layouts/header.php';
?>
<p class="miga"><a href="<?php echo url('admin/lecciones'); ?>">Lecciones</a></p>

<header class="titular">
    <h1><?php echo e($titulo); ?></h1>
    <p>El contenido es lo que lee el estudiante; la actividad es lo que practica después.</p>
</header>

<section class="tarjeta-form">
    <form class="formulario" method="post" action="<?php echo url('admin/leccion/guardar'); ?>">
        <input type="hidden" name="id_leccion" value="<?php echo $edita ? (int) $leccion['id_leccion'] : 0; ?>">

        <div class="fila-doble">
            <p class="campo">
                <label for="id_curso">Unidad</label>
                <select id="id_curso" name="id_curso" required>
                    <option value="">Selecciona la unidad</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo (int) $curso['id_curso']; ?>"
                            <?php echo ((string) $valor('id_curso') === (string) $curso['id_curso']) ? 'selected' : ''; ?>>
                            <?php echo e($curso['grado'] . ' · ' . $curso['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['id_curso'])): ?><span class="error"><?php echo e($errores['id_curso']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="orden">Orden dentro de la unidad</label>
                <input type="number" id="orden" name="orden" value="<?php echo e($valor('orden', '0')); ?>" min="0">
                <span class="ayuda">Deja 0 y el sistema la pone al final.</span>
            </p>
        </div>

        <p class="campo">
            <label for="titulo_leccion">Título</label>
            <input type="text" id="titulo_leccion" name="titulo" value="<?php echo e($valor('titulo')); ?>" maxlength="140" required>
            <?php if (isset($errores['titulo'])): ?><span class="error"><?php echo e($errores['titulo']); ?></span><?php endif; ?>
        </p>

        <p class="campo">
            <label for="descripcion">Descripción corta</label>
            <input type="text" id="descripcion" name="descripcion" value="<?php echo e($valor('descripcion')); ?>" maxlength="255" required>
            <?php if (isset($errores['descripcion'])): ?><span class="error"><?php echo e($errores['descripcion']); ?></span><?php endif; ?>
        </p>

        <p class="campo">
            <label for="contenido">Contenido educativo</label>
            <textarea id="contenido" name="contenido" rows="7" required><?php echo e($valor('contenido')); ?></textarea>
            <?php if (isset($errores['contenido'])): ?><span class="error"><?php echo e($errores['contenido']); ?></span><?php endif; ?>
        </p>

        <p class="campo">
            <label for="actividad">Actividad de práctica (opcional)</label>
            <textarea id="actividad" name="actividad" rows="5"><?php echo e($valor('actividad')); ?></textarea>
        </p>

        <button class="boton boton-corto" type="submit"><?php echo $edita ? 'Guardar cambios' : 'Crear lección'; ?></button>
    </form>
</section>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
