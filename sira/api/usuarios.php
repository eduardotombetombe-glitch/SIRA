<?php
/**
 * SIRA-LSC | API REST de usuarios.
 * Recurso: /api/usuarios.php
 * Métodos: GET, POST, PUT y DELETE.
 * Las operaciones requieren una sesión de administrador.
 */
require_once __DIR__ . '/../config/sesion.php';
iniciarSesionSegura();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Grado.php';

header('Content-Type: application/json; charset=UTF-8');

function respuestaJson($datos, $codigo = 200)
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requerirAdministrador()
{
    if (!isset($_SESSION['id_usuario'])) {
        respuestaJson(array('exito' => false, 'mensaje' => 'Autenticación requerida.'), 401);
    }

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
        respuestaJson(array('exito' => false, 'mensaje' => 'No tienes permisos para usar esta API.'), 403);
    }
}

function cuerpoJson()
{
    $contenido = file_get_contents('php://input');
    if ($contenido === false || trim($contenido) === '') {
        respuestaJson(array('exito' => false, 'mensaje' => 'El cuerpo JSON es obligatorio.'), 400);
    }

    $datos = json_decode($contenido, true);
    if (!is_array($datos)) {
        respuestaJson(array('exito' => false, 'mensaje' => 'El JSON enviado no es válido.'), 400);
    }
    return $datos;
}

function datosUsuario($datos, $requiereContrasena = true)
{
    $resultado = array(
        'nombre'   => trim(isset($datos['nombre']) ? $datos['nombre'] : ''),
        'apellido' => trim(isset($datos['apellido']) ? $datos['apellido'] : ''),
        'correo'   => trim(isset($datos['correo']) ? $datos['correo'] : ''),
        'usuario'  => trim(isset($datos['usuario']) ? $datos['usuario'] : ''),
        'rol'      => isset($datos['rol']) ? $datos['rol'] : 'estudiante',
        'id_grado' => isset($datos['id_grado']) && $datos['id_grado'] !== '' ? (int) $datos['id_grado'] : null,
        'activo'   => isset($datos['activo']) ? (int) ((bool) $datos['activo']) : 1,
    );

    if (array_key_exists('contrasena', $datos)) {
        $resultado['contrasena'] = (string) $datos['contrasena'];
    } elseif ($requiereContrasena) {
        $resultado['contrasena'] = '';
    }

    return $resultado;
}

function validarUsuarioApi($modeloUsuario, $modeloGrado, $datos, $id = 0, $requiereContrasena = true)
{
    $errores = array();

    if ($datos['nombre'] === '') { $errores['nombre'] = 'El nombre es obligatorio.'; }
    if ($datos['apellido'] === '') { $errores['apellido'] = 'El apellido es obligatorio.'; }

    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] = 'El correo no tiene un formato válido.';
    } elseif ($modeloUsuario->correoRegistrado($datos['correo'], $id)) {
        $errores['correo'] = 'El correo ya está registrado.';
    }

    if (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $datos['usuario'])) {
        $errores['usuario'] = 'El usuario debe tener entre 4 y 50 caracteres sin espacios.';
    } elseif ($modeloUsuario->usuarioRegistrado($datos['usuario'], $id)) {
        $errores['usuario'] = 'El usuario ya está registrado.';
    }

    if (!in_array($datos['rol'], array('estudiante', 'tutor', 'administrador'), true)) {
        $errores['rol'] = 'El rol no es válido.';
    }

    if ($datos['rol'] === 'estudiante') {
        if (empty($datos['id_grado']) || !$modeloGrado->existe($datos['id_grado'])) {
            $errores['id_grado'] = 'El estudiante debe tener un grado válido.';
        }
    } else {
        $datos['id_grado'] = null;
    }

    if ($requiereContrasena && strlen($datos['contrasena']) < 6) {
        $errores['contrasena'] = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif (!$requiereContrasena && isset($datos['contrasena']) && $datos['contrasena'] !== '' && strlen($datos['contrasena']) < 6) {
        $errores['contrasena'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
    }

    return array($errores, $datos);
}

requerirAdministrador();
$modeloUsuario = new Usuario();
$modeloGrado = new Grado();
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    if ($metodo === 'GET') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id > 0) {
            $usuario = $modeloUsuario->obtenerPorId($id);
            if (!$usuario) {
                respuestaJson(array('exito' => false, 'mensaje' => 'Usuario no encontrado.'), 404);
            }
            respuestaJson(array('exito' => true, 'datos' => $usuario));
        }

        $rol = isset($_GET['rol']) ? $_GET['rol'] : null;
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
        if ($rol !== null && !in_array($rol, array('estudiante', 'tutor', 'administrador'), true)) {
            respuestaJson(array('exito' => false, 'mensaje' => 'El filtro de rol no es válido.'), 400);
        }
        $usuarios = $modeloUsuario->listar($rol, $busqueda);
        respuestaJson(array('exito' => true, 'cantidad' => count($usuarios), 'datos' => $usuarios));
    }

    if ($metodo === 'POST') {
        $datos = datosUsuario(cuerpoJson(), true);
        list($errores, $datos) = validarUsuarioApi($modeloUsuario, $modeloGrado, $datos, 0, true);
        if ($errores) {
            respuestaJson(array('exito' => false, 'mensaje' => 'Datos inválidos.', 'errores' => $errores), 422);
        }
        $id = $modeloUsuario->crear($datos);
        respuestaJson(array('exito' => true, 'mensaje' => 'Usuario creado.', 'id_usuario' => $id), 201);
    }

    if ($metodo === 'PUT') {
        $datosEntrada = cuerpoJson();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($datosEntrada['id_usuario']) ? (int) $datosEntrada['id_usuario'] : 0);
        if ($id <= 0 || !$modeloUsuario->obtenerPorId($id)) {
            respuestaJson(array('exito' => false, 'mensaje' => 'Debes indicar un usuario existente.'), 404);
        }

        $actual = $modeloUsuario->obtenerPorId($id);
        $datosEntrada = array_merge(array(
            'nombre' => $actual['nombre'], 'apellido' => $actual['apellido'], 'correo' => $actual['correo'],
            'usuario' => $actual['usuario'], 'rol' => $actual['rol'], 'id_grado' => $actual['id_grado'],
            'activo' => (int) $actual['activo'],
        ), $datosEntrada);
        $datos = datosUsuario($datosEntrada, false);
        list($errores, $datos) = validarUsuarioApi($modeloUsuario, $modeloGrado, $datos, $id, false);
        if ($errores) {
            respuestaJson(array('exito' => false, 'mensaje' => 'Datos inválidos.', 'errores' => $errores), 422);
        }
        $modeloUsuario->actualizar($id, $datos);
        if (isset($datos['contrasena']) && $datos['contrasena'] !== '') {
            $modeloUsuario->actualizarContrasena($id, $datos['contrasena']);
        }
        respuestaJson(array('exito' => true, 'mensaje' => 'Usuario actualizado.', 'id_usuario' => $id));
    }

    if ($metodo === 'DELETE') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            respuestaJson(array('exito' => false, 'mensaje' => 'Debes indicar el id del usuario.'), 400);
        }
        if ($id === (int) $_SESSION['id_usuario']) {
            respuestaJson(array('exito' => false, 'mensaje' => 'No puedes eliminar tu propia cuenta.'), 409);
        }
        if (!$modeloUsuario->obtenerPorId($id)) {
            respuestaJson(array('exito' => false, 'mensaje' => 'Usuario no encontrado.'), 404);
        }
        $modeloUsuario->eliminar($id);
        respuestaJson(array('exito' => true, 'mensaje' => 'Usuario eliminado.', 'id_usuario' => $id));
    }

    respuestaJson(array('exito' => false, 'mensaje' => 'Método HTTP no permitido. Usa GET, POST, PUT o DELETE.'), 405);
} catch (PDOException $e) {
    respuestaJson(array('exito' => false, 'mensaje' => 'Error de base de datos.', 'detalle' => $e->getCode()), 500);
}
