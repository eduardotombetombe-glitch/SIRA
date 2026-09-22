<?php
/**
 * Controlador del actor ADMINISTRADOR.
 * Gestiona usuarios (estudiantes, tutores y administradores), grados,
 * lecciones y consulta el progreso de todos los estudiantes.
 */
class AdministradorController
{
    private $modeloUsuario;
    private $modeloAdmin;
    private $modeloGrado;
    private $modeloCurso;
    private $modeloLeccion;
    private $modeloTutor;
    private $modeloEstudiante;

    public function __construct()
    {
        exigirRol('administrador');
        $this->modeloUsuario    = new Usuario();
        $this->modeloAdmin      = new Administrador();
        $this->modeloGrado      = new Grado();
        $this->modeloCurso      = new Curso();
        $this->modeloLeccion    = new Leccion();
        $this->modeloTutor      = new Tutor();
        $this->modeloEstudiante = new Estudiante();
    }

    /** Tablero con las cifras generales. */
    public function inicio()
    {
        vista('administrador/inicio', array(
            'admin'    => $this->modeloUsuario->obtenerPorId($_SESSION['id_usuario']),
            'resumen'  => $this->modeloAdmin->resumenGeneral(),
            'avances'  => array_slice($this->modeloAdmin->progresoEstudiantes(), 0, 5),
        ));
    }

    /* ------------------------------ USUARIOS ------------------------------ */

    public function usuarios()
    {
        $rol      = isset($_GET['rol']) ? $_GET['rol'] : '';
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

        if (!in_array($rol, array('', 'estudiante', 'tutor', 'administrador'), true)) {
            $rol = '';
        }

        vista('administrador/usuarios', array(
            'admin'    => $this->modeloUsuario->obtenerPorId($_SESSION['id_usuario']),
            'usuarios' => $this->modeloUsuario->listar($rol === '' ? null : $rol, $busqueda),
            'rol'      => $rol,
            'busqueda' => $busqueda,
            'conteo'   => $this->modeloUsuario->contarPorRol(),
        ));
    }

    /** Formulario para crear o editar un usuario. */
    public function usuarioForm($errores = array(), $datos = array())
    {
        $idUsuario = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $usuario   = $idUsuario > 0 ? $this->modeloUsuario->obtenerPorId($idUsuario) : null;

        if ($idUsuario > 0 && !$usuario) {
            guardarAviso('error', 'Ese usuario no existe.');
            redirigir('admin/usuarios');
        }

        vista('administrador/usuario_form', array(
            'admin'   => $this->modeloUsuario->obtenerPorId($_SESSION['id_usuario']),
            'usuario' => $usuario,
            'grados'  => $this->modeloGrado->obtenerTodos(),
            'tutores' => $this->modeloTutor->listar(),
            'errores' => $errores,
            'datos'   => $datos,
        ));
    }

    /** Guarda el alta o la edición de un usuario. */
    public function guardarUsuario()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('admin/usuarios');
        }

        $idUsuario = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;
        $datos = array(
            'nombre'   => trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
            'apellido' => trim(isset($_POST['apellido']) ? $_POST['apellido'] : ''),
            'correo'   => trim(isset($_POST['correo']) ? $_POST['correo'] : ''),
            'usuario'  => trim(isset($_POST['usuario']) ? $_POST['usuario'] : ''),
            'rol'      => isset($_POST['rol']) ? $_POST['rol'] : 'estudiante',
            'id_grado' => isset($_POST['id_grado']) ? $_POST['id_grado'] : '',
            'activo'   => isset($_POST['activo']) ? 1 : 0,
            'id_tutor' => isset($_POST['id_tutor']) ? (int) $_POST['id_tutor'] : 0,
        );
        $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';
        $errores    = array();

        if ($datos['nombre'] === '')   { $errores['nombre']   = 'Escribe el nombre.'; }
        if ($datos['apellido'] === '') { $errores['apellido'] = 'Escribe el apellido.'; }

        if (!in_array($datos['rol'], array('estudiante', 'tutor', 'administrador'), true)) {
            $errores['rol'] = 'Selecciona un rol válido.';
        }

        if ($datos['correo'] === '' || !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Escribe un correo válido.';
        } elseif ($this->modeloUsuario->correoRegistrado($datos['correo'], $idUsuario)) {
            $errores['correo'] = 'Ese correo ya está en uso.';
        }

        if (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $datos['usuario'])) {
            $errores['usuario'] = 'El usuario necesita entre 4 y 50 caracteres sin espacios.';
        } elseif ($this->modeloUsuario->usuarioRegistrado($datos['usuario'], $idUsuario)) {
            $errores['usuario'] = 'Ese usuario ya está ocupado.';
        }

        if ($datos['rol'] === 'estudiante' && !$this->modeloGrado->existe($datos['id_grado'])) {
            $errores['id_grado'] = 'Selecciona el grado del estudiante.';
        }

        if ($idUsuario === 0 && strlen($contrasena) < 6) {
            $errores['contrasena'] = 'La contraseña necesita al menos 6 caracteres.';
        } elseif ($idUsuario > 0 && $contrasena !== '' && strlen($contrasena) < 6) {
            $errores['contrasena'] = 'La contraseña nueva necesita al menos 6 caracteres.';
        }

        if (count($errores) > 0) {
            $_GET['id'] = $idUsuario;
            $this->usuarioForm($errores, $datos);
            return;
        }

        if ($idUsuario === 0) {
            $datos['contrasena'] = $contrasena;
            $idUsuario = $this->modeloUsuario->crear($datos);
            guardarAviso('exito', 'Usuario creado.');
        } else {
            $this->modeloUsuario->actualizar($idUsuario, $datos);
            if ($contrasena !== '') {
                $this->modeloUsuario->actualizarContrasena($idUsuario, $contrasena);
            }
            guardarAviso('exito', 'Usuario actualizado.');
        }

        // Asociación opcional con un tutor cuando el usuario es estudiante
        if ($datos['rol'] === 'estudiante' && $datos['id_tutor'] > 0) {
            $this->modeloTutor->asociar($datos['id_tutor'], $idUsuario);
        }

        redirigir('admin/usuarios');
    }

    public function eliminarUsuario()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('admin/usuarios');
        }

        $idUsuario = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;

        if ($idUsuario === (int) $_SESSION['id_usuario']) {
            guardarAviso('error', 'No puedes eliminar tu propia cuenta mientras la estás usando.');
            redirigir('admin/usuarios');
        }

        $this->modeloUsuario->eliminar($idUsuario);
        guardarAviso('exito', 'Usuario eliminado junto con su progreso.');
        redirigir('admin/usuarios');
    }

    /* ------------------------------- GRADOS ------------------------------- */

    public function grados($errores = array(), $datos = array())
    {
        vista('administrador/grados', array(
            'admin'   => $this->modeloUsuario->obtenerPorId($_SESSION['id_usuario']),
            'grados'  => $this->modeloGrado->listarConConteos(),
            'edita'   => isset($_GET['id']) ? $this->modeloGrado->obtenerPorId((int) $_GET['id']) : null,
            'errores' => $errores,
            'datos'   => $datos,
        ));
    }

    public function guardarGrado()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('admin/grados');
        }

        $idGrado = isset($_POST['id_grado']) ? (int) $_POST['id_grado'] : 0;
        $datos = array(
            'nombre'      => trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
            'descripcion' => trim(isset($_POST['descripcion']) ? $_POST['descripcion'] : ''),
            'orden'       => isset($_POST['orden']) ? (int) $_POST['orden'] : 0,
        );
        $errores = array();

        if ($datos['nombre'] === '')      { $errores['nombre']      = 'Escribe el nombre del grado.'; }
        if ($datos['descripcion'] === '') { $errores['descripcion'] = 'Escribe una descripción corta.'; }

        if (count($errores) > 0) {
            if ($idGrado > 0) { $_GET['id'] = $idGrado; }
            $this->grados($errores, $datos);
            return;
        }

        if ($idGrado > 0) {
            $this->modeloGrado->actualizar($idGrado, $datos);
            guardarAviso('exito', 'Grado actualizado.');
        } else {
            $this->modeloGrado->crear($datos);
            guardarAviso('exito', 'Grado creado.');
        }
        redirigir('admin/grados');
    }

    public function eliminarGrado()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('admin/grados');
        }

        $idGrado = isset($_POST['id_grado']) ? (int) $_POST['id_grado'] : 0;
        $this->modeloGrado->eliminar($idGrado);
        guardarAviso('exito', 'Grado eliminado junto con sus unidades y lecciones.');
        redirigir('admin/grados');
    }

    /* ------------------------------ LECCIONES ----------------------------- */

    public function lecciones()
    {
        $idGrado = isset($_GET['id_grado']) ? (int) $_GET['id_grado'] : 0;
        $idCurso = isset($_GET['id_curso']) ? (int) $_GET['id_curso'] : 0;

        vista('administrador/lecciones', array(
            'admin'     => $this->modeloUsuario->obtenerPorId($_SESSION['id_usuario']),
            'lecciones' => $this->modeloLeccion->listarTodas($idGrado, $idCurso),
            'grados'    => $this->modeloGrado->obtenerTodos(),
            'cursos'    => $this->modeloCurso->obtenerTodos(),
            'idGrado'   => $idGrado,
            'idCurso'   => $idCurso,
        ));
    }

    public function leccionForm($errores = array(), $datos = array())
    {
        $idLeccion = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $leccion   = $idLeccion > 0 ? $this->modeloLeccion->obtenerPorId($idLeccion) : null;

        if ($idLeccion > 0 && !$leccion) {
            guardarAviso('error', 'Esa lección no existe.');
            redirigir('admin/lecciones');
        }

        vista('administrador/leccion_form', array(
            'admin'   => $this->modeloUsuario->obtenerPorId($_SESSION['id_usuario']),
            'leccion' => $leccion,
            'cursos'  => $this->modeloCurso->obtenerTodos(),
            'errores' => $errores,
            'datos'   => $datos,
        ));
    }

    public function guardarLeccion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('admin/lecciones');
        }

        $idLeccion = isset($_POST['id_leccion']) ? (int) $_POST['id_leccion'] : 0;
        $datos = array(
            'id_curso'    => isset($_POST['id_curso']) ? (int) $_POST['id_curso'] : 0,
            'titulo'      => trim(isset($_POST['titulo']) ? $_POST['titulo'] : ''),
            'descripcion' => trim(isset($_POST['descripcion']) ? $_POST['descripcion'] : ''),
            'contenido'   => trim(isset($_POST['contenido']) ? $_POST['contenido'] : ''),
            'actividad'   => trim(isset($_POST['actividad']) ? $_POST['actividad'] : ''),
            'orden'       => isset($_POST['orden']) ? (int) $_POST['orden'] : 0,
        );
        $errores = array();

        if (!$this->modeloCurso->obtenerPorId($datos['id_curso'])) {
            $errores['id_curso'] = 'Selecciona la unidad a la que pertenece la lección.';
        }
        if ($datos['titulo'] === '')      { $errores['titulo']      = 'Escribe el título.'; }
        if ($datos['descripcion'] === '') { $errores['descripcion'] = 'Escribe una descripción corta.'; }
        if ($datos['contenido'] === '')   { $errores['contenido']   = 'Escribe el contenido educativo.'; }

        if (count($errores) > 0) {
            if ($idLeccion > 0) { $_GET['id'] = $idLeccion; }
            $this->leccionForm($errores, $datos);
            return;
        }

        if ($datos['orden'] <= 0) {
            $datos['orden'] = $this->modeloLeccion->siguienteOrden($datos['id_curso']);
        }

        if ($idLeccion > 0) {
            $this->modeloLeccion->actualizar($idLeccion, $datos);
            guardarAviso('exito', 'Lección actualizada.');
        } else {
            $this->modeloLeccion->crear($datos);
            guardarAviso('exito', 'Lección creada.');
        }
        redirigir('admin/lecciones');
    }

    public function eliminarLeccion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('admin/lecciones');
        }

        $idLeccion = isset($_POST['id_leccion']) ? (int) $_POST['id_leccion'] : 0;
        $this->modeloLeccion->eliminar($idLeccion);
        guardarAviso('exito', 'Lección eliminada.');
        redirigir('admin/lecciones');
    }

    /* ------------------------------ PROGRESO ------------------------------ */

    public function progreso()
    {
        vista('administrador/progreso', array(
            'admin'   => $this->modeloUsuario->obtenerPorId($_SESSION['id_usuario']),
            'avances' => $this->modeloAdmin->progresoEstudiantes(),
        ));
    }

    /** Reporte tabular del estado general y del progreso de estudiantes. */
    public function reportes()
    {
        vista('administrador/reportes', array(
            'admin'    => $this->modeloUsuario->obtenerPorId($_SESSION['id_usuario']),
            'resumen'  => $this->modeloAdmin->resumenGeneral(),
            'avances'  => $this->modeloAdmin->progresoEstudiantes(),
            'fecha'    => date('d/m/Y H:i'),
        ));
    }

    /** Exporta el reporte de progreso a CSV compatible con Excel. */
    public function exportarProgresoCsv()
    {
        $avances = $this->modeloAdmin->progresoEstudiantes();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="reporte_progreso_sira.csv"');
        header('Pragma: no-cache');

        $salida = fopen('php://output', 'w');
        fprintf($salida, "\xEF\xBB\xBF");
        fputcsv($salida, array('ID', 'Estudiante', 'Usuario', 'Grado', 'Total lecciones', 'Completadas', 'Pendientes', 'Avance %'), ';');

        foreach ($avances as $fila) {
            fputcsv($salida, array(
                $fila['id_usuario'],
                $fila['nombre'] . ' ' . $fila['apellido'],
                $fila['usuario'],
                $fila['grado'] ? $fila['grado'] : 'Sin grado',
                $fila['total'],
                $fila['completadas'],
                $fila['pendientes'],
                $fila['porcentaje'],
            ), ';');
        }

        fclose($salida);
        exit;
    }

}
