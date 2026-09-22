<?php
/**
 * Modelo Grado | Acceso a la tabla `grados`.
 */
class Grado
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    /** Todos los grados ordenados de Grado 0 a Grado 5. */
    public function obtenerTodos()
    {
        $sql = 'SELECT id_grado, nombre, descripcion, orden
                FROM grados
                ORDER BY orden ASC, id_grado ASC';
        return $this->db->query($sql)->fetchAll();
    }

    /** Un grado por su identificador. */
    public function obtenerPorId($idGrado)
    {
        $sentencia = $this->db->prepare(
            'SELECT id_grado, nombre, descripcion, orden FROM grados WHERE id_grado = ?'
        );
        $sentencia->execute(array((int) $idGrado));
        $fila = $sentencia->fetch();
        return $fila ? $fila : null;
    }

    /** Comprueba que un id de grado enviado por formulario exista de verdad. */
    public function existe($idGrado)
    {
        $sentencia = $this->db->prepare('SELECT 1 FROM grados WHERE id_grado = ?');
        $sentencia->execute(array((int) $idGrado));
        return (bool) $sentencia->fetchColumn();
    }

    /** Grados con el conteo de unidades y lecciones (panel del administrador). */
    public function listarConConteos()
    {
        $sql = 'SELECT g.id_grado, g.nombre, g.descripcion, g.orden,
                       (SELECT COUNT(*) FROM cursos c WHERE c.id_grado = g.id_grado) AS cursos,
                       (SELECT COUNT(*) FROM lecciones l
                          INNER JOIN cursos c2 ON c2.id_curso = l.id_curso
                         WHERE c2.id_grado = g.id_grado) AS lecciones,
                       (SELECT COUNT(*) FROM usuarios u
                         WHERE u.id_grado = g.id_grado AND u.rol = \'estudiante\') AS estudiantes
                FROM grados g
                ORDER BY g.orden ASC, g.id_grado ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function crear($datos)
    {
        $sentencia = $this->db->prepare(
            'INSERT INTO grados (nombre, descripcion, orden) VALUES (?, ?, ?)'
        );
        $sentencia->execute(array($datos['nombre'], $datos['descripcion'], (int) $datos['orden']));
        return (int) $this->db->lastInsertId();
    }

    public function actualizar($idGrado, $datos)
    {
        $sentencia = $this->db->prepare(
            'UPDATE grados SET nombre = ?, descripcion = ?, orden = ? WHERE id_grado = ?'
        );
        return $sentencia->execute(array(
            $datos['nombre'], $datos['descripcion'], (int) $datos['orden'], (int) $idGrado
        ));
    }

    public function eliminar($idGrado)
    {
        $sentencia = $this->db->prepare('DELETE FROM grados WHERE id_grado = ?');
        return $sentencia->execute(array((int) $idGrado));
    }
}
