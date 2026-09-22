<?php
/**
 * Modelo Leccion | Acceso a la tabla `lecciones`.
 */
class Leccion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    /** Lecciones de un curso marcando cuáles completó el estudiante. */
    public function porCurso($idCurso, $idUsuario = null)
    {
        if ($idUsuario === null) {
            $sentencia = $this->db->prepare(
                'SELECT l.*, 0 AS completada
                 FROM lecciones l WHERE l.id_curso = ?
                 ORDER BY l.orden ASC, l.id_leccion ASC'
            );
            $sentencia->execute(array((int) $idCurso));
            return $sentencia->fetchAll();
        }

        $sentencia = $this->db->prepare(
            'SELECT l.*, IF(p.id_progreso IS NULL, 0, 1) AS completada
             FROM lecciones l
             LEFT JOIN progreso p
                    ON p.id_leccion = l.id_leccion AND p.id_usuario = :id_usuario
             WHERE l.id_curso = :id_curso
             ORDER BY l.orden ASC, l.id_leccion ASC'
        );
        $sentencia->execute(array(
            ':id_usuario' => (int) $idUsuario,
            ':id_curso'   => (int) $idCurso,
        ));
        return $sentencia->fetchAll();
    }

    public function obtenerPorId($idLeccion)
    {
        $sentencia = $this->db->prepare(
            'SELECT l.*, c.nombre AS curso, c.id_grado, g.nombre AS grado
             FROM lecciones l
             INNER JOIN cursos c ON c.id_curso = l.id_curso
             INNER JOIN grados g ON g.id_grado = c.id_grado
             WHERE l.id_leccion = ?'
        );
        $sentencia->execute(array((int) $idLeccion));
        $fila = $sentencia->fetch();
        return $fila ? $fila : null;
    }

    /** Lección siguiente dentro del mismo curso, o null si era la última. */
    public function siguienteEnCurso($idCurso, $ordenActual)
    {
        $sentencia = $this->db->prepare(
            'SELECT * FROM lecciones
             WHERE id_curso = :id_curso AND orden > :orden
             ORDER BY orden ASC LIMIT 1'
        );
        $sentencia->execute(array(
            ':id_curso' => (int) $idCurso,
            ':orden'    => (int) $ordenActual,
        ));
        $fila = $sentencia->fetch();
        return $fila ? $fila : null;
    }

    /** Lecciones que el estudiante todavía no ha completado en su grado. */
    public function pendientesPorGrado($idUsuario, $idGrado, $limite = 5)
    {
        $sentencia = $this->db->prepare(
            'SELECT l.id_leccion, l.titulo, l.orden, c.id_curso, c.nombre AS curso
             FROM lecciones l
             INNER JOIN cursos c ON c.id_curso = l.id_curso
             LEFT JOIN progreso p
                    ON p.id_leccion = l.id_leccion AND p.id_usuario = :id_usuario
             WHERE c.id_grado = :id_grado AND p.id_progreso IS NULL
             ORDER BY c.orden ASC, l.orden ASC
             LIMIT ' . (int) $limite
        );
        $sentencia->execute(array(
            ':id_usuario' => (int) $idUsuario,
            ':id_grado'   => (int) $idGrado,
        ));
        return $sentencia->fetchAll();
    }

    /* --------------------------------------------------------------------
       Gestión de contenidos (panel del administrador)
       -------------------------------------------------------------------- */

    /** Todas las lecciones con su unidad y su grado, con filtro opcional. */
    public function listarTodas($idGrado = 0, $idCurso = 0)
    {
        $sql = 'SELECT l.id_leccion, l.titulo, l.descripcion, l.orden, l.id_curso,
                       c.nombre AS curso, c.id_grado, g.nombre AS grado
                FROM lecciones l
                INNER JOIN cursos c ON c.id_curso = l.id_curso
                INNER JOIN grados g ON g.id_grado = c.id_grado
                WHERE 1 = 1';
        $parametros = array();
        if ($idGrado > 0) {
            $sql .= ' AND c.id_grado = :id_grado';
            $parametros[':id_grado'] = (int) $idGrado;
        }
        if ($idCurso > 0) {
            $sql .= ' AND l.id_curso = :id_curso';
            $parametros[':id_curso'] = (int) $idCurso;
        }
        $sql .= ' ORDER BY g.orden ASC, c.orden ASC, l.orden ASC';

        $sentencia = $this->db->prepare($sql);
        $sentencia->execute($parametros);
        return $sentencia->fetchAll();
    }

    public function crear($datos)
    {
        $sentencia = $this->db->prepare(
            'INSERT INTO lecciones (id_curso, titulo, descripcion, contenido, actividad, orden)
             VALUES (:id_curso, :titulo, :descripcion, :contenido, :actividad, :orden)'
        );
        $sentencia->execute(array(
            ':id_curso'    => (int) $datos['id_curso'],
            ':titulo'      => $datos['titulo'],
            ':descripcion' => $datos['descripcion'],
            ':contenido'   => $datos['contenido'],
            ':actividad'   => $datos['actividad'] !== '' ? $datos['actividad'] : null,
            ':orden'       => (int) $datos['orden'],
        ));
        return (int) $this->db->lastInsertId();
    }

    public function actualizar($idLeccion, $datos)
    {
        $sentencia = $this->db->prepare(
            'UPDATE lecciones
             SET id_curso = :id_curso, titulo = :titulo, descripcion = :descripcion,
                 contenido = :contenido, actividad = :actividad, orden = :orden
             WHERE id_leccion = :id'
        );
        return $sentencia->execute(array(
            ':id_curso'    => (int) $datos['id_curso'],
            ':titulo'      => $datos['titulo'],
            ':descripcion' => $datos['descripcion'],
            ':contenido'   => $datos['contenido'],
            ':actividad'   => $datos['actividad'] !== '' ? $datos['actividad'] : null,
            ':orden'       => (int) $datos['orden'],
            ':id'          => (int) $idLeccion,
        ));
    }

    public function eliminar($idLeccion)
    {
        $sentencia = $this->db->prepare('DELETE FROM lecciones WHERE id_leccion = ?');
        return $sentencia->execute(array((int) $idLeccion));
    }

    /** Siguiente número de orden libre dentro de una unidad. */
    public function siguienteOrden($idCurso)
    {
        $sentencia = $this->db->prepare(
            'SELECT COALESCE(MAX(orden), 0) + 1 FROM lecciones WHERE id_curso = ?'
        );
        $sentencia->execute(array((int) $idCurso));
        return (int) $sentencia->fetchColumn();
    }
}
