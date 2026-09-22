<?php
/**
 * Encabezado común a todo el sistema.
 * El menú cambia según el rol de quien tenga la sesión abierta.
 * Si la vista define $sinMenu = true (login / registro) se dibuja el fondo
 * degradado sin barra de navegación.
 */
$titulo  = isset($titulo) ? $titulo : 'SIRA-LSC';
$activo  = isset($activo) ? $activo : '';
$sinMenu = isset($sinMenu) ? $sinMenu : false;
$aviso   = tomarAviso();
$rol     = rolActual();

// Persona conectada: cada controlador envía su variable con otro nombre.
$conectado = null;
$prioridad = array('estudiante', 'tutor', 'admin');
if ($rol === 'tutor')              { $prioridad = array('tutor', 'admin', 'estudiante'); }
elseif ($rol === 'administrador')  { $prioridad = array('admin', 'tutor', 'estudiante'); }

foreach ($prioridad as $posible) {
    if (isset($$posible) && is_array($$posible) && isset($$posible['nombre'])) {
        $conectado = $$posible;
        break;
    }
}

// Menú de cada actor: etiqueta => array(ruta, clave activa)
$menus = array(
    'estudiante' => array(
        'Inicio'       => array('estudiante/inicio',    'inicio'),
        'Mis cursos'   => array('estudiante/cursos',    'cursos'),
        'Mis lecciones'=> array('estudiante/lecciones', 'lecciones'),
        'Mi progreso'  => array('estudiante/progreso',  'progreso'),
        'Mi perfil'    => array('estudiante/perfil',    'perfil'),
    ),
    'tutor' => array(
        'Inicio'           => array('tutor/inicio',      'inicio'),
        'Mis estudiantes'  => array('tutor/estudiantes', 'estudiantes'),
        'Mi perfil'        => array('tutor/perfil',      'perfil'),
    ),
    'administrador' => array(
        'Tablero'   => array('admin/inicio',    'inicio'),
        'Usuarios'  => array('admin/usuarios',  'usuarios'),
        'Grados'    => array('admin/grados',    'grados'),
        'Lecciones' => array('admin/lecciones', 'lecciones'),
        'Progreso'  => array('admin/progreso',  'progreso'),
        'Reportes'   => array('admin/reportes',  'reportes'),
    ),
);
$menu = isset($menus[$rol]) ? $menus[$rol] : array();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($titulo); ?> · SIRA-LSC</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/estilos.css">
</head>
<body class="<?php echo $sinMenu ? 'cuerpo-acceso' : 'cuerpo-panel'; ?>">
<?php if ($sinMenu): ?>
<main class="acceso">
<?php else: ?>
<div class="aplicacion">
    <header class="barra-movil">
        <a class="marca-movil" href="<?php echo url(panelDe($rol)); ?>">SIRA-LSC</a>
        <a class="salir-movil" href="<?php echo url('salir'); ?>">Cerrar sesión</a>
    </header>

    <nav class="menu-lateral">
        <a class="marca" href="<?php echo url(panelDe($rol)); ?>">
            <span class="marca-sigla">LSC</span>
            <span class="marca-texto">
                <strong>SIRA-LSC</strong>
                <small>Lengua de Señas Colombiana</small>
            </span>
        </a>

        <ul class="menu">
            <?php foreach ($menu as $etiqueta => $destino): ?>
                <li>
                    <a href="<?php echo url($destino[0]); ?>"
                       class="<?php echo $activo === $destino[1] ? 'activo' : ''; ?>">
                        <?php echo e($etiqueta); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="menu-pie">
            <?php if ($conectado): ?>
                <p class="menu-nombre"><?php echo e($conectado['nombre'] . ' ' . $conectado['apellido']); ?></p>
                <p class="menu-grado">
                    <?php
                    echo e(nombreRol($rol));
                    if ($rol === 'estudiante' && !empty($conectado['grado'])) {
                        echo ' · ' . e($conectado['grado']);
                    }
                    ?>
                </p>
            <?php endif; ?>
            <a class="boton-salir" href="<?php echo url('salir'); ?>">Cerrar sesión</a>
        </div>
    </nav>

    <main class="contenido">
<?php endif; ?>

<?php if ($aviso && !$sinMenu): ?>
    <p class="aviso aviso-<?php echo e($aviso['tipo']); ?>"><?php echo e($aviso['texto']); ?></p>
<?php endif; ?>
