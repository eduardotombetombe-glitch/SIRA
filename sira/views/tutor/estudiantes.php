<?php
$titulo  = 'Mis estudiantes';
$activo  = 'estudiantes';
$errores = isset($errores) ? $errores : array();
$datos   = isset($datos) ? $datos : array();
$valor   = function ($campo) use ($datos) {
    return isset($datos[$campo]) ? $datos[$campo] : '';
};
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Mis estudiantes</h1>
    <p>Registra un estudiante nuevo o asocia uno que ya tenga cuenta.</p>
</header>

<section class="tabla-caja">
    <?php if (count($estudiantes) === 0): ?>
        <p class="vacio">Aún no tienes estudiantes asociados.</p>
    <?php else: ?>
    <table class="tabla">
        <thead><tr><th>Estudiante</th><th>Usuario</th><th>Grado</th><th>Desde</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($estudiantes as $estudiante): ?>
            <tr>
                <td><?php echo e($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></td>
                <td>@<?php echo e($estudiante['usuario']); ?></td>
                <td><?php echo e($estudiante['grado'] ? $estudiante['grado'] : 'Sin grado'); ?></td>
                <td><?php echo e(fechaCorta($estudiante['fecha_asociado'])); ?></td>
                <td class="acciones">
                    <a class="boton-mini" href="<?php echo url('tutor/estudiante', array('id' => $estudiante['id_usuario'])); ?>">Ver progreso</a>
                    <form method="post" action="<?php echo url('tutor/desasociar'); ?>">
                        <input type="hidden" name="id_estudiante" value="<?php echo (int) $estudiante['id_usuario']; ?>">
                        <button class="boton-mini boton-peligro" type="submit">Quitar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<section class="dos-columnas">
    <div class="columna tarjeta-form">
        <h2 class="titulo-seccion">Registrar un estudiante nuevo</h2>
        <p class="texto-apoyo">La cuenta queda asociada a la tuya y el estudiante entra con su propio usuario.</p>
        <form class="formulario" method="post" action="<?php echo url('tutor/registrar'); ?>">
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
            <p class="campo">
                <label for="correo">Correo del estudiante</label>
                <input type="email" id="correo" name="correo" value="<?php echo e($valor('correo')); ?>" required>
                <?php if (isset($errores['correo'])): ?><span class="error"><?php echo e($errores['correo']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" value="<?php echo e($valor('usuario')); ?>" required>
                <?php if (isset($errores['usuario'])): ?><span class="error"><?php echo e($errores['usuario']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="contrasena">Contraseña inicial</label>
                <input type="password" id="contrasena" name="contrasena" minlength="6" required>
                <?php if (isset($errores['contrasena'])): ?><span class="error"><?php echo e($errores['contrasena']); ?></span><?php endif; ?>
            </p>
            <p class="campo">
                <label for="id_grado">Grado</label>
                <select id="id_grado" name="id_grado" required>
                    <option value="">Selecciona el grado</option>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?php echo (int) $grado['id_grado']; ?>"
                            <?php echo ((string) $valor('id_grado') === (string) $grado['id_grado']) ? 'selected' : ''; ?>>
                            <?php echo e($grado['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['id_grado'])): ?><span class="error"><?php echo e($errores['id_grado']); ?></span><?php endif; ?>
            </p>
            <button class="boton" type="submit">Registrar y asociar</button>
        </form>
    </div>

    <div class="columna tarjeta-form">
        <h2 class="titulo-seccion">Asociar un estudiante existente</h2>
        <p class="texto-apoyo">Si el estudiante ya se registró por su cuenta, búscalo en la lista.</p>
        <?php if (count($disponibles) === 0): ?>
            <p class="vacio">No hay estudiantes disponibles para asociar.</p>
        <?php else: ?>
        <form class="formulario" method="post" action="<?php echo url('tutor/asociar'); ?>">
            <p class="campo">
                <label for="id_estudiante">Estudiante</label>
                <select id="id_estudiante" name="id_estudiante" required>
                    <?php foreach ($disponibles as $disponible): ?>
                        <option value="<?php echo (int) $disponible['id_usuario']; ?>">
                            <?php echo e($disponible['nombre'] . ' ' . $disponible['apellido'] . ' (@' . $disponible['usuario'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <button class="boton" type="submit">Asociar estudiante</button>
        </form>
        <?php endif; ?>
    </div>
</section>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
