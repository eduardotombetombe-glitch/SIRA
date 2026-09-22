<?php
/**
 * Controlador del actor TUTOR (padre o acudiente).
 * Solo consulta: puede registrar y asociar estudiantes y ver su progreso,
 * pero no gestiona el sistema.
 */
class TutorController
{
    private $modeloUsuario;
    private $modeloTutor;
    private $modeloEstudiante;
    private $modeloProgreso;
    private $modeloGrado;
    private $modeloLeccion;
    private $idTutor;

    public function __construct()
    {
        exigirRol('tutor');
        $this->modeloUsuario    = new Usuario();
        $this->modeloTutor      = new Tutor();
        $this->modeloEstudiante = new Estudiante();
        $this->modeloProgreso   = new Progreso();
        $this->modeloGrado      = new Grado();
        $this->modeloLeccion    = new Leccion();
        $this->idTutor          = (int) $_SESSION['id_usuario'];
    }

    /** Panel del tutor: resumen de cada estudiante asociado. */
    public function inicio()
    {
        $estudiantes = $this->modeloTutor->estudiantesDe($this->idTutor);
        foreach ($estudiantes as $i => $estudiante) {
            $estudiantes[$i]['resumen'] = $this->modeloProgreso->resumenGrado(
                $estudiante['id_usuario'], $estudiante['id_grado']
            );
        }

        vista('tutor/inicio', array(
            'tutor'       => $this->modeloUsuario->obtenerPorId($this->idTutor),
            'estudiantes' => $estudiantes,
        ));
    }

    /** Gestión de estudiantes asociados: registrar uno nuevo o asociar existente. */
    public function estudiantes($errores = array(), $datos = array())
    {
        vista('tutor/estudiantes', array(
            'tutor'        => $this->modeloUsuario->obtenerPorId($this->idTutor),
            'estudiantes'  => $this->modeloTutor->estudiantesDe($this->idTutor),
            'disponibles'  => $this->modeloEstudiante->sinTutor($this->idTutor),
            'grados'       => $this->modeloGrado->obtenerTodos(),
            'errores'      => $errores,
            'datos'        => $datos,
        ));
    }

    /** Detalle del progreso de un estudiante asociado. */
    public function estudiante()
    {
        $idEstudiante = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if (!$this->modeloTutor->tieneEstudiante($this->idTutor, $idEstudiante)) {
            guardarAviso('error', 'Ese estudiante no está asociado a tu cuenta.');
            redirigir('tutor/inicio');
        }

        $estudiante = $this->modeloEstudiante->obtenerPorId($idEstudiante);

        vista('tutor/estudiante', array(
            'tutor'       => $this->modeloUsuario->obtenerPorId($this->idTutor),
            'estudiante'  => $estudiante,
            'resumen'     => $this->modeloProgreso->resumenGrado($idEstudiante, $estudiante['id_grado']),
            'cursos'      => $this->modeloProgreso->resumenPorCursos($idEstudiante, $estudiante['id_grado']),
            'grados'      => $this->modeloProgreso->resumenTodosLosGrados($idEstudiante),
            'completadas' => $this->modeloProgreso->ultimasCompletadas($idEstudiante, 10),
            'pendientes'  => $this->modeloLeccion->pendientesPorGrado($idEstudiante, $estudiante['id_grado'], 10),
            'total'       => $this->modeloProgreso->totalCompletadas($idEstudiante),
        ));
    }

    /** Registra un estudiante nuevo y lo deja asociado al tutor. */
    public function registrarEstudiante()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('tutor/estudiantes');
        }

        $datos = array(
            'nombre'   => trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
            'apellido' => trim(isset($_POST['apellido']) ? $_POST['apellido'] : ''),
            'correo'   => trim(isset($_POST['correo']) ? $_POST['correo'] : ''),
            'usuario'  => trim(isset($_POST['usuario']) ? $_POST['usuario'] : ''),
            'id_grado' => isset($_POST['id_grado']) ? $_POST['id_grado'] : '',
            'rol'      => 'estudiante',
        );
        $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';
        $errores    = array();

        if ($datos['nombre'] === '')   { $errores['nombre']   = 'Escribe el nombre del estudiante.'; }
        if ($datos['apellido'] === '') { $errores['apellido'] = 'Escribe el apellido.'; }

        if ($datos['correo'] === '' || !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Escribe un correo válido para el estudiante.';
        } elseif ($this->modeloUsuario->correoRegistrado($datos['correo'])) {
            $errores['correo'] = 'Ya hay una cuenta con ese correo.';
        }

        if (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $datos['usuario'])) {
            $errores['usuario'] = 'El usuario necesita entre 4 y 50 caracteres sin espacios.';
        } elseif ($this->modeloUsuario->usuarioRegistrado($datos['usuario'])) {
            $errores['usuario'] = 'Ese usuario ya está ocupado.';
        }

        if (strlen($contrasena) < 6) {
            $errores['contrasena'] = 'La contraseña necesita al menos 6 caracteres.';
        }
        if (!$this->modeloGrado->existe($datos['id_grado'])) {
            $errores['id_grado'] = 'Selecciona el grado del estudiante.';
        }

        if (count($errores) > 0) {
            $this->estudiantes($errores, $datos);
            return;
        }

        $datos['contrasena'] = $contrasena;
        $idEstudiante = $this->modeloUsuario->crear($datos);
        $this->modeloTutor->asociar($this->idTutor, $idEstudiante);

        guardarAviso('exito', 'Estudiante registrado y asociado a tu cuenta.');
        redirigir('tutor/estudiantes');
    }

    /** Asocia un estudiante que ya tenía cuenta. */
    public function asociar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('tutor/estudiantes');
        }

        $idEstudiante = isset($_POST['id_estudiante']) ? (int) $_POST['id_estudiante'] : 0;
        if (!$this->modeloEstudiante->obtenerPorId($idEstudiante)) {
            guardarAviso('error', 'Ese estudiante no existe.');
            redirigir('tutor/estudiantes');
        }

        $this->modeloTutor->asociar($this->idTutor, $idEstudiante);
        guardarAviso('exito', 'Estudiante asociado a tu cuenta.');
        redirigir('tutor/estudiantes');
    }

    /** Quita la asociación (no borra la cuenta del estudiante). */
    public function desasociar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('tutor/estudiantes');
        }

        $idEstudiante = isset($_POST['id_estudiante']) ? (int) $_POST['id_estudiante'] : 0;
        $this->modeloTutor->desasociar($this->idTutor, $idEstudiante);
        guardarAviso('exito', 'Quitaste al estudiante de tu lista. Su cuenta sigue activa.');
        redirigir('tutor/estudiantes');
    }

    /** Datos de la cuenta del tutor. */
    public function perfil($errores = array())
    {
        vista('tutor/perfil', array(
            'tutor'       => $this->modeloUsuario->obtenerPorId($this->idTutor),
            'estudiantes' => $this->modeloTutor->estudiantesDe($this->idTutor),
            'errores'     => $errores,
        ));
    }

    public function cambiarContrasena()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('tutor/perfil');
        }

        $actual    = isset($_POST['actual']) ? $_POST['actual'] : '';
        $nueva     = isset($_POST['nueva']) ? $_POST['nueva'] : '';
        $confirmar = isset($_POST['confirmar']) ? $_POST['confirmar'] : '';
        $errores   = array();

        if (!$this->modeloUsuario->verificarContrasena($this->idTutor, $actual)) {
            $errores['actual'] = 'La contraseña actual no es correcta.';
        }
        if (strlen($nueva) < 6) {
            $errores['nueva'] = 'La nueva contraseña necesita al menos 6 caracteres.';
        }
        if ($nueva !== $confirmar) {
            $errores['confirmar'] = 'Las dos contraseñas no coinciden.';
        }

        if (count($errores) > 0) {
            $this->perfil($errores);
            return;
        }

        $this->modeloUsuario->actualizarContrasena($this->idTutor, $nueva);
        guardarAviso('exito', 'Contraseña actualizada.');
        redirigir('tutor/perfil');
    }
}
