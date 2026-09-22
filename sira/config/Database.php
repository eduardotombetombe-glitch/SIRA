<?php
/**
 * SIRA-LSC | Conexión centralizada a MySQL mediante PDO.
 * Es el único punto del proyecto donde se instancia PDO.
 */

define('DB_HOST', 'localhost');
define('DB_NOMBRE', 'sira_lsc');
define('DB_USUARIO', 'root');
define('DB_CLAVE', '');           // XAMPP: contraseña vacía por defecto

class Database
{
    private static $pdo = null;

    /** Devuelve siempre la misma instancia de PDO (patrón singleton). */
    public static function conexion()
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NOMBRE . ';charset=utf8mb4';
            try {
                self::$pdo = new PDO($dsn, DB_USUARIO, DB_CLAVE, array(
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ));
            } catch (PDOException $e) {
                self::mostrarError($e->getMessage());
            }
        }
        return self::$pdo;
    }

    /** Pantalla de error legible en lugar de un volcado de excepción. */
    private static function mostrarError($mensaje)
    {
        http_response_code(500);
        $mensaje = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
           . '<title>SIRA-LSC · Base de datos no disponible</title>'
           . '<link rel="stylesheet" href="css/estilos.css"></head><body class="pantalla-error">'
           . '<div class="aviso aviso-error"><h1>No hay conexión con la base de datos</h1>'
           . '<p>Revisa que MySQL esté iniciado en XAMPP y que la base <strong>sira_lsc</strong> ya esté importada.</p>'
           . '<ol><li>Abre el panel de XAMPP e inicia Apache y MySQL.</li>'
           . '<li>Entra a phpMyAdmin e importa el archivo <strong>database.sql</strong> del proyecto.</li>'
           . '<li>Vuelve a cargar esta página.</li></ol>'
           . '<p class="detalle-error">' . $mensaje . '</p></div></body></html>';
        exit;
    }
}
