<?php
$edita   = isset($usuario) && $usuario;
$titulo  = $edita ? 'Editar usuario' : 'Crear usuario';
$activo  = 'usuarios';
$errores = isset($errores) ? $errores : array();
$datos   = isset($datos) ? $datos : array();

/** Valor del formulario: primero lo que se envió, después lo guardado. */
$valor = function ($campo, $porDefecto = '') use ($datos, $usuario) {
    if (isset($datos[$campo]) && $datos[$campo] !== '') { return $datos[$campo]; }
    if ($usuario && isset($usuario[$campo]))            { return $usuario[$campo]; }
    return $porDefecto;
};
require RUTA_BASE . '/views/layouts/header.php';
?>
<p class="miga"><a href="<?php echo url('admin/usuarios'); ?>">Usuarios</a></p>

<header class="titular">
    <h1><?php echo e($titulo); ?></h1>
    <p><?php echo $edita
        ? 'Cambia los datos de la cuenta. Deja la contraseña vacía si no quieres cambiarla.'
        : 'Crea la cuenta de un estudiante, un tutor o un administrador.'; ?></p>
</header>

<section class="tarjeta-form">
    <form class="formulario" method="post" action="<?php echo url('admin/usuario/guardar'); ?>">
        <input type="hidden" name="id_usuario" value="<?php echo $edita ? (int) $usuario['id_usuario'] : 0; ?>">

        <div class="fila-doble">
            <p class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo e($valor('nombre')); ?>" required>
                <?php if (isset($errores['nombre'])): ?><span class="error"><?php echo e($errores['nombre']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" value="<?php echo e($valor('apellido')); ?>" required>
                <?php if (isset($errores['apellido'])): ?><span class="error"><?php echo e($errores['apellido']); ?></span><?php endif; ?>
            </p>
        </div>

        <div class="fila-doble">
            <p class="campo">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" value="<?php echo e($valor('correo')); ?>" required>
                <?php if (isset($errores['correo'])): ?><span class="error"><?php echo e($errores['correo']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" value="<?php echo e($valor('usuario')); ?>" required>
                <?php if (isset($errores['usuario'])): ?><span class="error"><?php echo e($errores['usuario']); ?></span><?php endif; ?>
            </p>
        </div>

        <div class="fila-doble">
            <p class="campo">
                <label for="rol">Rol</label>
                <?php $rolActualCampo = $valor('rol', 'estudiante'); ?>
                <select id="rol" name="rol" required>
                    <option value="estudiante"    <?php echo $rolActualCampo === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                    <option value="tutor"         <?php echo $rolActualCampo === 'tutor' ? 'selected' : ''; ?>>Tutor</option>
                    <option value="administrador" <?php echo $rolActualCampo === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                </select>
                <?php if (isset($errores['rol'])): ?><span class="error"><?php echo e($errores['rol']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="id_grado">Grado (solo estudiantes)</label>
                <select id="id_grado" name="id_grado">
                    <option value="">Sin grado</option>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?php echo (int) $grado['id_grado']; ?>"
                            <?php echo ((string) $valor('id_grado') === (string) $grado['id_grado']) ? 'selected' : ''; ?>>
                            <?php echo e($grado['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['id_grado'])): ?><span class="error"><?php echo e($errores['id_grado']); ?></span><?php endif; ?>
            </p>
        </div>

        <div class="fila-doble">
            <p class="campo">
                <label for="contrasena"><?php echo $edita ? 'Contraseña nueva (opcional)' : 'Contraseña'; ?></label>
                <input type="password" id="contrasena" name="contrasena" <?php echo $edita ? '' : 'required minlength="6"'; ?>>
                <?php if (isset($errores['contrasena'])): ?><span class="error"><?php echo e($errores['contrasena']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="id_tutor">Asociar a un tutor (solo estudiantes)</label>
                <select id="id_tutor" name="id_tutor">
                    <option value="0">Sin tutor</option>
                    <?php foreach ($tutores as $tutorFila): ?>
                        <option value="<?php echo (int) $tutorFila['id_usuario']; ?>">
                            <?php echo e($tutorFila['nombre'] . ' ' . $tutorFila['apellido']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
        </div>

        <p class="campo campo-casilla">
            <label>
                <input type="checkbox" name="activo" value="1"
                    <?php echo (!$edita || (int) $usuario['activo'] === 1) ? 'checked' : ''; ?>>
                Cuenta activa (puede iniciar sesión)
            </label>
        </p>

        <button class="boton boton-corto" type="submit"><?php echo $edita ? 'Guardar cambios' : 'Crear usuario'; ?></button>
    </form>
</section>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
