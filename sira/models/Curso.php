<?php
/**
 * Modelo Curso | Acceso a la tabla `cursos` (unidades de cada grado).
 */
class Curso
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    /** Cursos de un grado con el total de lecciones de cada uno. */
    public function porGrado($idGrado)
    {
        $sentencia = $this->db->prepare(
            'SELECT c.id_curso, c.id_grado, c.nombre, c.descripcion, c.icono, c.orden,
                    (SELECT COUNT(*) FROM lecciones l WHERE l.id_curso = c.id_curso) AS total_lecciones
             FROM cursos c
             WHERE c.id_grado = ?
             ORDER BY c.orden ASC, c.id_curso ASC'
        );
        $sentencia->execute(array((int) $idGrado));
        return $sentencia->fetchAll();
    }

    /** Todos los cursos del sistema con el nombre de su grado. */
    public function obtenerTodos()
    {
        $sql = 'SELECT c.id_curso, c.id_grado, c.nombre, c.descripcion, c.icono, c.orden,
                       g.nombre AS grado, g.orden AS grado_orden,
                       (SELECT COUNT(*) FROM lecciones l WHERE l.id_curso = c.id_curso) AS total_lecciones
                FROM cursos c
                INNER JOIN grados g ON g.id_grado = c.id_grado
                ORDER BY g.orden ASC, c.orden ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPorId($idCurso)
    {
        $sentencia = $this->db->prepare(
            'SELECT c.*, g.nombre AS grado, g.orden AS grado_orden
             FROM cursos c
             INNER JOIN grados g ON g.id_grado = c.id_grado
             WHERE c.id_curso = ?'
        );
        $sentencia->execute(array((int) $idCurso));
        $fila = $sentencia->fetch();
        return $fila ? $fila : null;
    }

    /** Primer curso de un grado: sirve como punto de partida del estudiante. */
    public function primeroDelGrado($idGrado)
    {
        $sentencia = $this->db->prepare(
            'SELECT * FROM cursos WHERE id_grado = ? ORDER BY orden ASC, id_curso ASC LIMIT 1'
        );
        $sentencia->execute(array((int) $idGrado));
        $fila = $sentencia->fetch();
        return $fila ? $fila : null;
    }
}
