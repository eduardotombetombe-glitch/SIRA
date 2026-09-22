<?php
$titulo = 'Usuarios';
$activo = 'usuarios';
require RUTA_BASE . '/views/layouts/header.php';
?>
<header class="titular">
    <h1>Usuarios</h1>
    <p>Estudiantes, tutores y administradores del sistema.</p>
</header>

<section class="barra-acciones">
    <form class="filtros" method="get" action="<?php echo BASE_URL; ?>index.php">
        <input type="hidden" name="ruta" value="admin/usuarios">
        <label class="campo-en-linea">
            <span>Rol</span>
            <select name="rol">
                <option value="" <?php echo $rol === '' ? 'selected' : ''; ?>>Todos</option>
                <option value="estudiante" <?php echo $rol === 'estudiante' ? 'selected' : ''; ?>>Estudiantes (<?php echo (int) $conteo['estudiante']; ?>)</option>
                <option value="tutor" <?php echo $rol === 'tutor' ? 'selected' : ''; ?>>Tutores (<?php echo (int) $conteo['tutor']; ?>)</option>
                <option value="administrador" <?php echo $rol === 'administrador' ? 'selected' : ''; ?>>Administradores (<?php echo (int) $conteo['administrador']; ?>)</option>
            </select>
        </label>
        <label class="campo-en-linea">
            <span>Buscar</span>
            <input type="text" name="busqueda" value="<?php echo e($busqueda); ?>" placeholder="Nombre, correo o usuario">
        </label>
        <button class="boton-mini" type="submit">Filtrar</button>
    </form>

    <a class="boton boton-corto" href="<?php echo url('admin/usuario'); ?>">Crear usuario</a>
</section>

<?php if (count($usuarios) === 0): ?>
    <p class="vacio">No hay usuarios que coincidan con el filtro.</p>
<?php else: ?>
<table class="tabla" id="tabla-usuarios" data-base-url="<?php echo e(BASE_URL); ?>">
    <thead><tr><th>Nombre</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Grado</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($usuarios as $fila): ?>
        <tr>
            <td><?php echo e($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
            <td>@<?php echo e($fila['usuario']); ?></td>
            <td><?php echo e($fila['correo']); ?></td>
            <td><span class="etiqueta-rol rol-<?php echo e($fila['rol']); ?>"><?php echo e(nombreRol($fila['rol'])); ?></span></td>
            <td><?php echo e($fila['grado'] ? $fila['grado'] : '—'); ?></td>
            <td>
                <?php if ((int) $fila['id_usuario'] !== (int) $_SESSION['id_usuario']): ?>
                    <!-- Botón conectado a la API REST (api/usuarios.php, PUT) vía js/admin-usuarios.js -->
                    <button type="button"
                            class="boton-estado <?php echo ((int) $fila['activo'] === 1) ? 'estado-activo' : 'estado-inactivo'; ?>"
                            data-id="<?php echo (int) $fila['id_usuario']; ?>"
                            data-activo="<?php echo (int) $fila['activo']; ?>">
                        <?php echo ((int) $fila['activo'] === 1) ? 'Activo' : 'Inactivo'; ?>
                    </button>
                <?php else: ?>
                    <span class="etiqueta-rol">Activo</span>
                <?php endif; ?>
            </td>
            <td class="acciones">
                <a class="boton-mini" href="<?php echo url('admin/usuario', array('id' => $fila['id_usuario'])); ?>">Editar</a>
                <?php if ((int) $fila['id_usuario'] !== (int) $_SESSION['id_usuario']): ?>
                <form method="post" action="<?php echo url('admin/usuario/eliminar'); ?>">
                    <input type="hidden" name="id_usuario" value="<?php echo (int) $fila['id_usuario']; ?>">
                    <button class="boton-mini boton-peligro" type="submit">Eliminar</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<script src="<?php echo BASE_URL; ?>js/admin-usuarios.js"></script>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
