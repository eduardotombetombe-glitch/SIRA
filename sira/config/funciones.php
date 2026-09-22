<?php
/**
 * SIRA-LSC | Funciones de apoyo compartidas por vistas y controladores.
 */

/** Escapa texto antes de imprimirlo en HTML. */
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Construye una URL interna del front controller. */
function url($ruta = 'inicio', $parametros = array())
{
    $destino = BASE_URL . 'index.php?ruta=' . urlencode($ruta);
    foreach ($parametros as $clave => $valor) {
        $destino .= '&' . urlencode($clave) . '=' . urlencode($valor);
    }
    return $destino;
}

/** Redirige a una ruta interna y detiene la ejecución. */
function redirigir($ruta, $parametros = array())
{
    header('Location: ' . url($ruta, $parametros));
    exit;
}

/** Carga una vista pasándole variables como arreglo asociativo. */
function vista($archivo, $datos = array())
{
    extract($datos, EXTR_SKIP);
    require RUTA_BASE . '/views/' . $archivo . '.php';
}

/* -------------------------------------------------------------------------
   Sesión y control de acceso por rol
   ------------------------------------------------------------------------- */

/** ¿Hay una sesión iniciada? */
function haySesion()
{
    return isset($_SESSION['id_usuario']);
}

/** Rol de quien está conectado: estudiante, tutor o administrador. */
function rolActual()
{
    return isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
}

/** Ruta del panel que corresponde a cada rol. */
function panelDe($rol)
{
    switch ($rol) {
        case 'administrador': return 'admin/inicio';
        case 'tutor':         return 'tutor/inicio';
        default:              return 'estudiante/inicio';
    }
}

/** Bloquea el acceso a cualquier página privada. */
function exigirSesion()
{
    if (!haySesion()) {
        guardarAviso('error', 'Inicia sesión para entrar a tu panel.');
        redirigir('login');
    }
}

/**
 * Exige un rol concreto. Si el usuario tiene otro rol, se le devuelve a su
 * propio panel: así un estudiante no puede abrir el panel del administrador
 * escribiendo la ruta a mano.
 */
function exigirRol($rol)
{
    exigirSesion();
    if (rolActual() !== $rol) {
        guardarAviso('error', 'Esa sección no corresponde a tu tipo de usuario.');
        redirigir(panelDe(rolActual()));
    }
}

/** Guarda un mensaje que se mostrará una sola vez. */
function guardarAviso($tipo, $texto)
{
    $_SESSION['aviso'] = array('tipo' => $tipo, 'texto' => $texto);
}

/** Devuelve y borra el aviso pendiente. */
function tomarAviso()
{
    if (!isset($_SESSION['aviso'])) {
        return null;
    }
    $aviso = $_SESSION['aviso'];
    unset($_SESSION['aviso']);
    return $aviso;
}

/** Nombre legible de un rol. */
function nombreRol($rol)
{
    $nombres = array(
        'estudiante'    => 'Estudiante',
        'tutor'         => 'Tutor',
        'administrador' => 'Administrador',
    );
    return isset($nombres[$rol]) ? $nombres[$rol] : $rol;
}

/** Inicial del nombre para los avatares, sin depender de mbstring. */
function inicial($texto)
{
    return function_exists('mb_substr')
        ? mb_substr($texto, 0, 1, 'UTF-8')
        : substr($texto, 0, 1);
}

/** Fecha corta para las tablas. */
function fechaCorta($fecha)
{
    return $fecha ? date('d/m/Y', strtotime($fecha)) : '';
}
