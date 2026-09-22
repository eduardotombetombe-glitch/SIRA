<?php
/**
 * Controlador del actor ESTUDIANTE.
 * Reúne lo que antes estaba repartido en InicioController, CursoController,
 * LeccionController y UsuarioController.
 */
class EstudianteController
{
    private $modeloUsuario;
    private $modeloCurso;
    private $modeloLeccion;
    private $modeloProgreso;
    private $modeloGrado;
    private $idUsuario;

    public function __construct()
    {
        exigirRol('estudiante');
        $this->modeloUsuario  = new Usuario();
        $this->modeloCurso    = new Curso();
        $this->modeloLeccion  = new Leccion();
        $this->modeloProgreso = new Progreso();
        $this->modeloGrado    = new Grado();
        $this->idUsuario      = (int) $_SESSION['id_usuario'];
    }

    /** Panel principal del estudiante. */
    public function inicio()
    {
        $estudiante = $this->modeloUsuario->obtenerPorId($this->idUsuario);
        if (!$estudiante) {
            redirigir('salir');
        }
        if (!$estudiante['id_grado']) {
            guardarAviso('error', 'Todavía no tienes un grado asignado. Elígelo en tu perfil.');
            redirigir('estudiante/perfil');
        }

        vista('estudiante/inicio', array(
            'estudiante'   => $estudiante,
            'resumenGrado' => $this->modeloProgreso->resumenGrado($this->idUsuario, $estudiante['id_grado']),
            'cursos'       => $this->modeloProgreso->resumenPorCursos($this->idUsuario, $estudiante['id_grado']),
            'cursoActual'  => $this->modeloProgreso->cursoActual($this->idUsuario, $estudiante['id_grado']),
            'totalGlobal'  => $this->modeloProgreso->totalCompletadas($this->idUsuario),
        ));
    }

    /** Listado de unidades de todos los grados. */
    public function cursos()
    {
        $estudiante = $this->modeloUsuario->obtenerPorId($this->idUsuario);
        $porGrado   = array();

        foreach ($this->modeloGrado->obtenerTodos() as $grado) {
            $porGrado[] = array(
                'grado'   => $grado,
                'cursos'  => $this->modeloProgreso->resumenPorCursos($this->idUsuario, $grado['id_grado']),
                'resumen' => $this->modeloProgreso->resumenGrado($this->idUsuario, $grado['id_grado']),
            );
        }

        vista('estudiante/cursos', array(
            'estudiante' => $estudiante,
            'porGrado'   => $porGrado,
        ));
    }

    /** Detalle de una unidad con sus lecciones. */
    public function curso()
    {
        $idCurso = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $curso   = $this->modeloCurso->obtenerPorId($idCurso);

        if (!$curso) {
            guardarAviso('error', 'Esa unidad no existe.');
            redirigir('estudiante/cursos');
        }

        vista('estudiante/curso', array(
            'estudiante' => $this->modeloUsuario->obtenerPorId($this->idUsuario),
            'curso'      => $curso,
            'lecciones'  => $this->modeloLeccion->porCurso($idCurso, $this->idUsuario),
            'resumen'    => $this->modeloProgreso->resumenCurso($this->idUsuario, $idCurso),
        ));
    }

    /** Lecciones pendientes y completadas del grado actual. */
    public function lecciones()
    {
        $estudiante = $this->modeloUsuario->obtenerPorId($this->idUsuario);

        vista('estudiante/lecciones', array(
            'estudiante' => $estudiante,
            'pendientes' => $this->modeloLeccion->pendientesPorGrado($this->idUsuario, $estudiante['id_grado'], 8),
            'recientes'  => $this->modeloProgreso->ultimasCompletadas($this->idUsuario, 6),
            'resumen'    => $this->modeloProgreso->resumenGrado($this->idUsuario, $estudiante['id_grado']),
        ));
    }

    /** Contenido y actividad de una lección. */
    public function leccion()
    {
        $idLeccion = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $leccion   = $this->modeloLeccion->obtenerPorId($idLeccion);

        if (!$leccion) {
            guardarAviso('error', 'Esa lección no existe.');
            redirigir('estudiante/cursos');
        }

        vista('estudiante/leccion', array(
            'estudiante' => $this->modeloUsuario->obtenerPorId($this->idUsuario),
            'leccion'    => $leccion,
            'completada' => $this->modeloProgreso->estaCompletada($this->idUsuario, $idLeccion),
            'siguiente'  => $this->modeloLeccion->siguienteEnCurso($leccion['id_curso'], $leccion['orden']),
            'resumen'    => $this->modeloProgreso->resumenCurso($this->idUsuario, $leccion['id_curso']),
        ));
    }

    /** Guarda el avance y lleva a la siguiente lección. */
    public function completar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('estudiante/cursos');
        }

        $idLeccion = isset($_POST['id_leccion']) ? (int) $_POST['id_leccion'] : 0;
        $leccion   = $this->modeloLeccion->obtenerPorId($idLeccion);

        if (!$leccion) {
            guardarAviso('error', 'Esa lección no existe.');
            redirigir('estudiante/cursos');
        }

        $this->modeloProgreso->completar($this->idUsuario, $idLeccion);

        $siguiente = $this->modeloLeccion->siguienteEnCurso($leccion['id_curso'], $leccion['orden']);
        if ($siguiente) {
            guardarAviso('exito', 'Lección completada. Sigues con ' . $siguiente['titulo'] . '.');
            redirigir('estudiante/leccion', array('id' => $siguiente['id_leccion']));
        }

        guardarAviso('exito', 'Terminaste la unidad ' . $leccion['curso'] . '.');
        redirigir('estudiante/curso', array('id' => $leccion['id_curso']));
    }

    /** Página "Mi progreso". */
    public function progreso()
    {
        $estudiante = $this->modeloUsuario->obtenerPorId($this->idUsuario);

        vista('estudiante/progreso', array(
            'estudiante'  => $estudiante,
            'grados'      => $this->modeloProgreso->resumenTodosLosGrados($this->idUsuario),
            'resumen'     => $this->modeloProgreso->resumenGrado($this->idUsuario, $estudiante['id_grado']),
            'cursoActual' => $this->modeloProgreso->cursoActual($this->idUsuario, $estudiante['id_grado']),
            'total'       => $this->modeloProgreso->totalCompletadas($this->idUsuario),
            'recientes'   => $this->modeloProgreso->ultimasCompletadas($this->idUsuario, 8),
        ));
    }

    /** Información personal del estudiante. */
    public function perfil($errores = array())
    {
        $estudiante = $this->modeloUsuario->obtenerPorId($this->idUsuario);
        $tutor      = new Tutor();

        vista('estudiante/perfil', array(
            'estudiante' => $estudiante,
            'grados'     => $this->modeloGrado->obtenerTodos(),
            'tutores'    => $tutor->tutoresDe($this->idUsuario),
            'resumen'    => $this->modeloProgreso->resumenGrado($this->idUsuario, $estudiante['id_grado']),
            'total'      => $this->modeloProgreso->totalCompletadas($this->idUsuario),
            'recientes'  => $this->modeloProgreso->ultimasCompletadas($this->idUsuario, 6),
            'errores'    => $errores,
        ));
    }

    public function cambiarGrado()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('estudiante/perfil');
        }

        $idGrado = isset($_POST['id_grado']) ? (int) $_POST['id_grado'] : 0;
        if (!$this->modeloGrado->existe($idGrado)) {
            $this->perfil(array('id_grado' => 'Selecciona un grado de la lista.'));
            return;
        }

        $this->modeloUsuario->actualizarGrado($this->idUsuario, $idGrado);
        guardarAviso('exito', 'Listo, cambiaste de grado. Tus lecciones completadas se conservan.');
        redirigir('estudiante/perfil');
    }

    public function cambiarContrasena()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('estudiante/perfil');
        }

        $actual    = isset($_POST['actual']) ? $_POST['actual'] : '';
        $nueva     = isset($_POST['nueva']) ? $_POST['nueva'] : '';
        $confirmar = isset($_POST['confirmar']) ? $_POST['confirmar'] : '';
        $errores   = array();

        if (!$this->modeloUsuario->verificarContrasena($this->idUsuario, $actual)) {
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

        $this->modeloUsuario->actualizarContrasena($this->idUsuario, $nueva);
        guardarAviso('exito', 'Contraseña actualizada.');
        redirigir('estudiante/perfil');
    }
}
