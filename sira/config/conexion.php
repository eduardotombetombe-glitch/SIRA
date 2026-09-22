<?php
/**
 * Compatibilidad con la versión anterior del proyecto: antes cada archivo
 * incluía config/conexion.php. La conexión real vive ahora en Database.php.
 */
require_once __DIR__ . '/Database.php';

class Conexion
{
    public static function obtener()
    {
        return Database::conexion();
    }
}
