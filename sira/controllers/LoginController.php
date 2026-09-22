<?php
/**
 * Controlador de inicio y cierre de sesión.
 * Tras validar las credenciales envía a cada actor a su propio panel.
 */
class LoginController
{
    private $modeloUsuario;

    public function __construct()
    {
        $this->modeloUsuario = new Usuario();
    }

    public function index($errores = array(), $identificador = '')
    {
        if (haySesion()) {
            redirigir(panelDe(rolActual()));
        }
        vista('login/index', array(
            'errores'       => $errores,
            'identificador' => $identificador,
        ));
    }

    public function entrar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('login');
        }

        $identificador = trim(isset($_POST['identificador']) ? $_POST['identificador'] : '');
        $contrasena    = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';
        $errores       = array();

        if ($identificador === '') { $errores['identificador'] = 'Escribe tu usuario o tu correo.'; }
        if ($contrasena === '')    { $errores['contrasena']    = 'Escribe tu contraseña.'; }

        if (count($errores) === 0) {
            $usuario = $this->modeloUsuario->buscarPorUsuarioOCorreo($identificador);

            if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
                $errores['general'] = 'Los datos no coinciden con ninguna cuenta.';
            } elseif ((int) $usuario['activo'] !== 1) {
                $errores['general'] = 'Esta cuenta está desactivada. Habla con el administrador.';
            } else {
                session_regenerate_id(true);
                $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
                $_SESSION['nombre']     = $usuario['nombre'];
                $_SESSION['rol']        = $usuario['rol'];
                redirigir(panelDe($usuario['rol']));
            }
        }

        $this->index($errores, $identificador);
    }

    public function salir()
    {
        $_SESSION = array();
        session_destroy();
        session_start();
        guardarAviso('exito', 'Cerraste sesión. Tu información quedó guardada.');
        redirigir('login');
    }
}
