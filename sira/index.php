<?php
/**
 * SIRA-LSC | Front controller.
 * Todas las peticiones entran por aquí: se cargan modelos y controladores,
 * y la ruta decide qué controlador atiende la petición.
 *
 *   VISTA  ->  CONTROLADOR  ->  MODELO  ->  BASE DE DATOS
 *   BASE DE DATOS  ->  MODELO  ->  CONTROLADOR  ->  VISTA
 */

require_once __DIR__ . '/config/sesion.php';
iniciarSesionSegura();

define('RUTA_BASE', __DIR__);
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');

require_once RUTA_BASE . '/config/Database.php';
require_once RUTA_BASE . '/config/funciones.php';

foreach (array('Usuario', 'Estudiante', 'Tutor', 'Administrador',
               'Grado', 'Curso', 'Leccion', 'Progreso') as $modelo) {
    require_once RUTA_BASE . '/models/' . $modelo . '.php';
}
foreach (array('LoginController', 'RegistroController', 'EstudianteController',
               'TutorController', 'AdministradorController') as $controlador) {
    require_once RUTA_BASE . '/controllers/' . $controlador . '.php';
}

$ruta = isset($_GET['ruta']) ? trim($_GET['ruta'], '/') : '';

// Sin ruta: quien ya entró va a su panel; el resto ve el login.
if ($ruta === '') {
    $ruta = haySesion() ? panelDe(rolActual()) : 'login';
}

switch ($ruta) {

    /* ------------------------- Acceso público ------------------------- */
    case 'login':
        $controlador = new LoginController();
        $controlador->index();
        break;

    case 'login/entrar':
        $controlador = new LoginController();
        $controlador->entrar();
        break;

    case 'salir':
        $controlador = new LoginController();
        $controlador->salir();
        break;

    case 'registro':
        $controlador = new RegistroController();
        $controlador->index();
        break;

    case 'registro/guardar':
        $controlador = new RegistroController();
        $controlador->guardar();
        break;

    /* --------------------------- ESTUDIANTE --------------------------- */
    case 'estudiante/inicio':
        $controlador = new EstudianteController();
        $controlador->inicio();
        break;

    case 'estudiante/cursos':
        $controlador = new EstudianteController();
        $controlador->cursos();
        break;

    case 'estudiante/curso':
        $controlador = new EstudianteController();
        $controlador->curso();
        break;

    case 'estudiante/lecciones':
        $controlador = new EstudianteController();
        $controlador->lecciones();
        break;

    case 'estudiante/leccion':
        $controlador = new EstudianteController();
        $controlador->leccion();
        break;

    case 'estudiante/completar':
        $controlador = new EstudianteController();
        $controlador->completar();
        break;

    case 'estudiante/progreso':
        $controlador = new EstudianteController();
        $controlador->progreso();
        break;

    case 'estudiante/perfil':
        $controlador = new EstudianteController();
        $controlador->perfil();
        break;

    case 'estudiante/grado':
        $controlador = new EstudianteController();
        $controlador->cambiarGrado();
        break;

    case 'estudiante/contrasena':
        $controlador = new EstudianteController();
        $controlador->cambiarContrasena();
        break;

    /* ------------------------------ TUTOR ----------------------------- */
    case 'tutor/inicio':
        $controlador = new TutorController();
        $controlador->inicio();
        break;

    case 'tutor/estudiantes':
        $controlador = new TutorController();
        $controlador->estudiantes();
        break;

    case 'tutor/estudiante':
        $controlador = new TutorController();
        $controlador->estudiante();
        break;

    case 'tutor/registrar':
        $controlador = new TutorController();
        $controlador->registrarEstudiante();
        break;

    case 'tutor/asociar':
        $controlador = new TutorController();
        $controlador->asociar();
        break;

    case 'tutor/desasociar':
        $controlador = new TutorController();
        $controlador->desasociar();
        break;

    case 'tutor/perfil':
        $controlador = new TutorController();
        $controlador->perfil();
        break;

    case 'tutor/contrasena':
        $controlador = new TutorController();
        $controlador->cambiarContrasena();
        break;

    /* -------------------------- ADMINISTRADOR ------------------------- */
    case 'admin/inicio':
        $controlador = new AdministradorController();
        $controlador->inicio();
        break;

    case 'admin/usuarios':
        $controlador = new AdministradorController();
        $controlador->usuarios();
        break;

    case 'admin/usuario':
        $controlador = new AdministradorController();
        $controlador->usuarioForm();
        break;

    case 'admin/usuario/guardar':
        $controlador = new AdministradorController();
        $controlador->guardarUsuario();
        break;

    case 'admin/usuario/eliminar':
        $controlador = new AdministradorController();
        $controlador->eliminarUsuario();
        break;

    case 'admin/grados':
        $controlador = new AdministradorController();
        $controlador->grados();
        break;

    case 'admin/grado/guardar':
        $controlador = new AdministradorController();
        $controlador->guardarGrado();
        break;

    case 'admin/grado/eliminar':
        $controlador = new AdministradorController();
        $controlador->eliminarGrado();
        break;

    case 'admin/lecciones':
        $controlador = new AdministradorController();
        $controlador->lecciones();
        break;

    case 'admin/leccion':
        $controlador = new AdministradorController();
        $controlador->leccionForm();
        break;

    case 'admin/leccion/guardar':
        $controlador = new AdministradorController();
        $controlador->guardarLeccion();
        break;

    case 'admin/leccion/eliminar':
        $controlador = new AdministradorController();
        $controlador->eliminarLeccion();
        break;

    case 'admin/progreso':
        $controlador = new AdministradorController();
        $controlador->progreso();
        break;

    case 'admin/reportes':
        $controlador = new AdministradorController();
        $controlador->reportes();
        break;

    case 'admin/reporte/exportar':
        $controlador = new AdministradorController();
        $controlador->exportarProgresoCsv();
        break;

    default:
        http_response_code(404);
        vista('layouts/error404');
        break;
}
