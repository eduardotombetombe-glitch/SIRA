<?php
$titulo  = 'Mi perfil';
$activo  = 'perfil';
$errores = isset($errores) ? $errores : array();
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Mi perfil</h1>
    <p>Tus datos de estudiante y la configuración de tu cuenta.</p>
</header>

<section class="tarjeta-perfil">
    <p class="inicial"><?php
    echo e(inicial($estudiante['nombre']));
?></p>
    <div>
        <h2><?php echo e($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></h2>
        <p class="texto-apoyo">@<?php echo e($estudiante['usuario']); ?> · <?php echo e($estudiante['correo']); ?></p>
        <p class="texto-apoyo">Estudiando <?php echo e($estudiante['grado']); ?> · <?php echo (int) $total; ?> lecciones completadas · <?php echo (int) $resumen['porcentaje']; ?>% del grado</p>
        <p class="texto-apoyo">
            <?php if (count($tutores) === 0): ?>
                Sin tutor asociado
            <?php else: ?>
                Tutor: <?php
                    $nombresTutores = array();
                    foreach ($tutores as $tutorFila) {
                        $nombresTutores[] = $tutorFila['nombre'] . ' ' . $tutorFila['apellido'];
                    }
                    echo e(implode(', ', $nombresTutores));
                ?>
            <?php endif; ?>
        </p>
        <p class="texto-apoyo">Cuenta creada el <?php echo e(date('d/m/Y', strtotime($estudiante['fecha_registro']))); ?></p>
    </div>
</section>

<section class="dos-columnas">
    <div class="columna tarjeta-form">
        <h2 class="titulo-seccion">Cambiar de grado</h2>
        <p class="texto-apoyo">Tus lecciones completadas se conservan aunque cambies de grado.</p>
        <form class="formulario" method="post" action="<?php echo url('estudiante/grado'); ?>">
            <p class="campo">
                <label for="id_grado">Grado</label>
                <select id="id_grado" name="id_grado" required>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?php echo (int) $grado['id_grado']; ?>"
                            <?php echo $grado['id_grado'] == $estudiante['id_grado'] ? 'selected' : ''; ?>>
                            <?php echo e($grado['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['id_grado'])): ?><span class="error"><?php echo e($errores['id_grado']); ?></span><?php endif; ?>
            </p>
            <button class="boton" type="submit">Guardar grado</button>
        </form>
    </div>

    <div class="columna tarjeta-form">
        <h2 class="titulo-seccion">Cambiar contraseña</h2>
        <p class="texto-apoyo">Necesitas escribir tu contraseña actual para confirmarlo.</p>
        <form class="formulario" method="post" action="<?php echo url('estudiante/contrasena'); ?>">
            <p class="campo">
                <label for="actual">Contraseña actual</label>
                <input type="password" id="actual" name="actual" required>
                <?php if (isset($errores['actual'])): ?><span class="error"><?php echo e($errores['actual']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="nueva">Contraseña nueva</label>
                <input type="password" id="nueva" name="nueva" minlength="6" required>
                <?php if (isset($errores['nueva'])): ?><span class="error"><?php echo e($errores['nueva']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="confirmar">Repite la nueva</label>
                <input type="password" id="confirmar" name="confirmar" minlength="6" required>
                <?php if (isset($errores['confirmar'])): ?><span class="error"><?php echo e($errores['confirmar']); ?></span><?php endif; ?>
            </p>
            <button class="boton" type="submit">Actualizar contraseña</button>
        </form>
    </div>
</section>

<h2 class="titulo-seccion">Últimas lecciones completadas</h2>
<?php if (count($recientes) === 0): ?>
    <p class="vacio">Todavía no tienes lecciones completadas.</p>
<?php else: ?>
<ul class="lista-simple lista-hechas">
    <?php foreach ($recientes as $fila): ?>
        <li><a href="<?php echo url('estudiante/leccion', array('id' => $fila['id_leccion'])); ?>">
            <strong><?php echo e($fila['titulo']); ?></strong>
            <small><?php echo e($fila['curso'] . ' · ' . date('d/m/Y', strtotime($fila['fecha_completada']))); ?></small>
        </a></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
