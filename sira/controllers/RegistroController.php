<?php
/**
 * Controlador de registro público.
 * Solo permite crear cuentas de estudiante: los roles de tutor y
 * administrador los crea un administrador desde su panel.
 */
class RegistroController
{
    private $modeloUsuario;
    private $modeloGrado;

    public function __construct()
    {
        $this->modeloUsuario = new Usuario();
        $this->modeloGrado   = new Grado();
    }

    /** Muestra el formulario; los grados salen del modelo Grado. */
    public function index($errores = array(), $datos = array())
    {
        if (haySesion()) {
            redirigir(panelDe(rolActual()));
        }
        vista('registro/index', array(
            'grados'  => $this->modeloGrado->obtenerTodos(),
            'errores' => $errores,
            'datos'   => $datos,
        ));
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('registro');
        }

        $datos = array(
            'nombre'   => trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
            'apellido' => trim(isset($_POST['apellido']) ? $_POST['apellido'] : ''),
            'correo'   => trim(isset($_POST['correo']) ? $_POST['correo'] : ''),
            'usuario'  => trim(isset($_POST['usuario']) ? $_POST['usuario'] : ''),
            'rol'      => 'estudiante', // El registro público solo crea estudiantes, sin importar lo que llegue por POST.
            'id_grado' => isset($_POST['id_grado']) ? $_POST['id_grado'] : '',
        );
        $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';
        $confirmar  = isset($_POST['confirmar']) ? $_POST['confirmar'] : '';

        $errores = $this->validar($datos, $contrasena, $confirmar);

        if (count($errores) > 0) {
            $this->index($errores, $datos);
            return;
        }

        $datos['contrasena'] = $contrasena;
        $this->modeloUsuario->crear($datos);

        guardarAviso('exito', 'Cuenta creada. Ya puedes iniciar sesión con tu usuario o tu correo.');
        redirigir('login');
    }

    /** Validaciones del formulario público de registro. */
    private function validar($datos, $contrasena, $confirmar)
    {
        $errores = array();

        if ($datos['nombre'] === '')   { $errores['nombre']   = 'Escribe tu nombre.'; }
        if ($datos['apellido'] === '') { $errores['apellido'] = 'Escribe tu apellido.'; }

        if ($datos['rol'] !== 'estudiante') {
            $errores['rol'] = 'Solo se pueden crear cuentas de estudiante desde este formulario.';
        }

        if ($datos['correo'] === '') {
            $errores['correo'] = 'Escribe tu correo electrónico.';
        } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Ese correo no tiene un formato válido.';
        } elseif ($this->modeloUsuario->correoRegistrado($datos['correo'])) {
            $errores['correo'] = 'Ya hay una cuenta con este correo.';
        }

        if ($datos['usuario'] === '') {
            $errores['usuario'] = 'Elige un nombre de usuario.';
        } elseif (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $datos['usuario'])) {
            $errores['usuario'] = 'Usa entre 4 y 50 caracteres: letras, números, punto, guion o guion bajo.';
        } elseif ($this->modeloUsuario->usuarioRegistrado($datos['usuario'])) {
            $errores['usuario'] = 'Ese usuario ya está ocupado. Prueba con otro.';
        }

        if ($contrasena === '') {
            $errores['contrasena'] = 'Escribe una contraseña.';
        } elseif (strlen($contrasena) < 6) {
            $errores['contrasena'] = 'La contraseña necesita al menos 6 caracteres.';
        }

        if ($confirmar === '') {
            $errores['confirmar'] = 'Repite la contraseña.';
        } elseif ($contrasena !== $confirmar) {
            $errores['confirmar'] = 'Las dos contraseñas no coinciden.';
        }

        if ($datos['id_grado'] === '') {
            $errores['id_grado'] = 'Selecciona el grado con el que vas a empezar.';
        } elseif (!$this->modeloGrado->existe($datos['id_grado'])) {
            $errores['id_grado'] = 'Ese grado no existe.';
        }

        return $errores;
    }
}
