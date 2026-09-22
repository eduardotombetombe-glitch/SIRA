<!-- SIRA-LSC registro v2 (incluye campo Grado, sin selector de tutor) -->
<?php
$titulo  = 'Crea tu cuenta';
$sinMenu = true;
$errores = isset($errores) ? $errores : array();
$datos   = isset($datos) ? $datos : array();
$valor   = function ($campo) use ($datos) {
    return isset($datos[$campo]) ? $datos[$campo] : '';
};
require RUTA_BASE . '/views/layouts/header.php';
?>
<section class="tarjeta-acceso tarjeta-registro">
    <p class="logo-acceso">SIRA-LSC</p>
    <h1>Crea tu cuenta</h1>
    <p class="texto-apoyo">Aprende Lengua de Señas Colombiana del Grado 0 al Grado 5.</p>

    <?php if (isset($errores['general'])): ?>
        <p class="aviso aviso-error"><?php echo e($errores['general']); ?></p>
    <?php endif; ?>

    <form class="formulario" method="post" action="<?php echo url('registro/guardar'); ?>" autocomplete="off">

        <input type="hidden" name="rol" value="estudiante">
        <p class="texto-apoyo">Vas a crear una cuenta de <strong>estudiante</strong> para tomar las lecciones y avanzar por grados. ¿Eres tutor o acudiente? Pide a un administrador que te cree la cuenta desde su panel.</p>
        <?php if (isset($errores['rol'])): ?><span class="error"><?php echo e($errores['rol']); ?></span><?php endif; ?>

        <div class="fila-doble">
            <p class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo e($valor('nombre')); ?>" maxlength="80" autocomplete="off" required>
                <?php if (isset($errores['nombre'])): ?><span class="error"><?php echo e($errores['nombre']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" value="<?php echo e($valor('apellido')); ?>" maxlength="80" autocomplete="off" required>
                <?php if (isset($errores['apellido'])): ?><span class="error"><?php echo e($errores['apellido']); ?></span><?php endif; ?>
            </p>
        </div>

        <p class="campo">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" value="<?php echo e($valor('correo')); ?>" maxlength="150" autocomplete="off" required>
            <?php if (isset($errores['correo'])): ?><span class="error"><?php echo e($errores['correo']); ?></span><?php endif; ?>
        </p>

        <p class="campo">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" value="<?php echo e($valor('usuario')); ?>" maxlength="50" autocomplete="off" required>
            <?php if (isset($errores['usuario'])): ?><span class="error"><?php echo e($errores['usuario']); ?></span><?php endif; ?>
        </p>

        <div class="fila-doble">
            <p class="campo">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" minlength="6" autocomplete="new-password" required>
                <?php if (isset($errores['contrasena'])): ?><span class="error"><?php echo e($errores['contrasena']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="confirmar">Repite la contraseña</label>
                <input type="password" id="confirmar" name="confirmar" minlength="6" autocomplete="new-password" required>
                <?php if (isset($errores['confirmar'])): ?><span class="error"><?php echo e($errores['confirmar']); ?></span><?php endif; ?>
            </p>
        </div>

        <p class="campo">
            <label for="id_grado">Grado con el que empiezas</label>
            <select id="id_grado" name="id_grado">
                <option value="">Selecciona tu grado</option>
                <?php foreach ($grados as $grado): ?>
                    <option value="<?php echo (int) $grado['id_grado']; ?>"
                        <?php echo ((string) $valor('id_grado') === (string) $grado['id_grado']) ? 'selected' : ''; ?>>
                        <?php echo e($grado['nombre'] . ' — ' . $grado['descripcion']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errores['id_grado'])): ?><span class="error"><?php echo e($errores['id_grado']); ?></span><?php endif; ?>
        </p>

        <button class="boton" type="submit">Crear cuenta</button>
        <p class="nota-registro">Las cuentas de administrador las crea otro administrador desde su panel.</p>
    </form>

    <p class="enlace-alterno">¿Ya tienes cuenta? <a href="<?php echo url('login'); ?>">Inicia sesión</a></p>
</section>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
