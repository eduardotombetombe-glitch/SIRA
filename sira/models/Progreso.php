<?php
/**
 * Modelo Progreso | Acceso a la tabla `progreso`.
 * Cada fila representa una lección completada por un estudiante.
 */
class Progreso
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    /** Marca una lección como completada sin duplicar filas. */
    public function completar($idUsuario, $idLeccion)
    {
        $sentencia = $this->db->prepare(
            'INSERT INTO progreso (id_usuario, id_leccion, completada)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE completada = 1, fecha_completada = NOW()'
        );
        return $sentencia->execute(array((int) $idUsuario, (int) $idLeccion));
    }

    public function estaCompletada($idUsuario, $idLeccion)
    {
        $sentencia = $this->db->prepare(
            'SELECT 1 FROM progreso WHERE id_usuario = ? AND id_leccion = ?'
        );
        $sentencia->execute(array((int) $idUsuario, (int) $idLeccion));
        return (bool) $sentencia->fetchColumn();
    }

    /** Lecciones completadas y totales de un curso. */
    public function resumenCurso($idUsuario, $idCurso)
    {
        $sentencia = $this->db->prepare(
            'SELECT COUNT(l.id_leccion) AS total,
                    COUNT(p.id_progreso) AS completadas
             FROM lecciones l
             LEFT JOIN progreso p
                    ON p.id_leccion = l.id_leccion AND p.id_usuario = :id_usuario
             WHERE l.id_curso = :id_curso'
        );
        $sentencia->execute(array(
            ':id_usuario' => (int) $idUsuario,
            ':id_curso'   => (int) $idCurso,
        ));
        $fila = $sentencia->fetch();
        return $this->conPorcentaje($fila);
    }

    /** Lecciones completadas y totales de todo un grado. */
    public function resumenGrado($idUsuario, $idGrado)
    {
        $sentencia = $this->db->prepare(
            'SELECT COUNT(l.id_leccion) AS total,
                    COUNT(p.id_progreso) AS completadas
             FROM lecciones l
             INNER JOIN cursos c ON c.id_curso = l.id_curso
             LEFT JOIN progreso p
                    ON p.id_leccion = l.id_leccion AND p.id_usuario = :id_usuario
             WHERE c.id_grado = :id_grado'
        );
        $sentencia->execute(array(
            ':id_usuario' => (int) $idUsuario,
            ':id_grado'   => (int) $idGrado,
        ));
        $fila = $sentencia->fetch();
        return $this->conPorcentaje($fila);
    }

    /** Progreso de cada curso del grado, listo para pintar las tarjetas. */
    public function resumenPorCursos($idUsuario, $idGrado)
    {
        $sentencia = $this->db->prepare(
            'SELECT c.id_curso, c.nombre, c.descripcion, c.icono, c.orden,
                    COUNT(l.id_leccion) AS total,
                    COUNT(p.id_progreso) AS completadas
             FROM cursos c
             LEFT JOIN lecciones l ON l.id_curso = c.id_curso
             LEFT JOIN progreso p
                    ON p.id_leccion = l.id_leccion AND p.id_usuario = :id_usuario
             WHERE c.id_grado = :id_grado
             GROUP BY c.id_curso, c.nombre, c.descripcion, c.icono, c.orden
             ORDER BY c.orden ASC, c.id_curso ASC'
        );
        $sentencia->execute(array(
            ':id_usuario' => (int) $idUsuario,
            ':id_grado'   => (int) $idGrado,
        ));
        $cursos = $sentencia->fetchAll();
        foreach ($cursos as $indice => $curso) {
            $cursos[$indice] = array_merge($curso, $this->conPorcentaje($curso));
        }
        return $cursos;
    }

    /** Progreso de los seis grados, para la página "Mi progreso". */
    public function resumenTodosLosGrados($idUsuario)
    {
        $sentencia = $this->db->prepare(
            'SELECT g.id_grado, g.nombre, g.descripcion, g.orden,
                    COUNT(l.id_leccion) AS total,
                    COUNT(p.id_progreso) AS completadas
             FROM grados g
             LEFT JOIN cursos c ON c.id_grado = g.id_grado
             LEFT JOIN lecciones l ON l.id_curso = c.id_curso
             LEFT JOIN progreso p
                    ON p.id_leccion = l.id_leccion AND p.id_usuario = :id_usuario
             GROUP BY g.id_grado, g.nombre, g.descripcion, g.orden
             ORDER BY g.orden ASC'
        );
        $sentencia->execute(array(':id_usuario' => (int) $idUsuario));
        $grados = $sentencia->fetchAll();
        foreach ($grados as $indice => $grado) {
            $grados[$indice] = array_merge($grado, $this->conPorcentaje($grado));
        }
        return $grados;
    }

    /** Curso en el que va el estudiante: el primero sin terminar de su grado. */
    public function cursoActual($idUsuario, $idGrado)
    {
        $cursos = $this->resumenPorCursos($idUsuario, $idGrado);
        foreach ($cursos as $curso) {
            if ($curso['porcentaje'] < 100) {
                return $curso;
            }
        }
        return count($cursos) ? $cursos[count($cursos) - 1] : null;
    }

    /** Últimas lecciones terminadas, para el historial del perfil. */
    public function ultimasCompletadas($idUsuario, $limite = 6)
    {
        $sentencia = $this->db->prepare(
            'SELECT l.id_leccion, l.titulo, c.nombre AS curso, g.nombre AS grado,
                    p.fecha_completada
             FROM progreso p
             INNER JOIN lecciones l ON l.id_leccion = p.id_leccion
             INNER JOIN cursos c ON c.id_curso = l.id_curso
             INNER JOIN grados g ON g.id_grado = c.id_grado
             WHERE p.id_usuario = :id_usuario
             ORDER BY p.fecha_completada DESC
             LIMIT ' . (int) $limite
        );
        $sentencia->execute(array(':id_usuario' => (int) $idUsuario));
        return $sentencia->fetchAll();
    }

    /** Total de lecciones completadas por el estudiante. */
    public function totalCompletadas($idUsuario)
    {
        $sentencia = $this->db->prepare('SELECT COUNT(*) FROM progreso WHERE id_usuario = ?');
        $sentencia->execute(array((int) $idUsuario));
        return (int) $sentencia->fetchColumn();
    }

    /** Añade porcentaje y pendientes a un conteo. */
    private function conPorcentaje($fila)
    {
        $total       = isset($fila['total']) ? (int) $fila['total'] : 0;
        $completadas = isset($fila['completadas']) ? (int) $fila['completadas'] : 0;
        return array(
            'total'       => $total,
            'completadas' => $completadas,
            'pendientes'  => max(0, $total - $completadas),
            'porcentaje'  => $total > 0 ? (int) round(($completadas / $total) * 100) : 0,
        );
    }
}
