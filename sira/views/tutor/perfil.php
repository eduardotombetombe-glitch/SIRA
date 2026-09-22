<?php
$titulo  = 'Mi perfil';
$activo  = 'perfil';
$errores = isset($errores) ? $errores : array();
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Mi perfil</h1>
    <p>Datos de tu cuenta de tutor.</p>
</header>

<section class="tarjeta-perfil">
    <p class="inicial"><?php echo e(inicial($tutor['nombre'])); ?></p>
    <div>
        <h2><?php echo e($tutor['nombre'] . ' ' . $tutor['apellido']); ?></h2>
        <p class="texto-apoyo">@<?php echo e($tutor['usuario']); ?> · <?php echo e($tutor['correo']); ?></p>
        <p class="texto-apoyo">Tutor · <?php echo count($estudiantes); ?> estudiante(s) asociado(s)</p>
        <p class="texto-apoyo">Cuenta creada el <?php echo e(fechaCorta($tutor['fecha_registro'])); ?></p>
    </div>
</section>

<section class="tarjeta-form">
    <h2 class="titulo-seccion">Cambiar contraseña</h2>
    <form class="formulario formulario-corto" method="post" action="<?php echo url('tutor/contrasena'); ?>">
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
</section>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
