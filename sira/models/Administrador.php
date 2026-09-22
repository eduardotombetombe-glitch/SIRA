<?php
/**
 * Modelo Administrador | Cifras generales del sistema para el panel de gestión.
 */
class Administrador
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    /** Totales de usuarios, contenidos y progreso. */
    public function resumenGeneral()
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM usuarios WHERE rol = 'estudiante')    AS estudiantes,
                    (SELECT COUNT(*) FROM usuarios WHERE rol = 'tutor')         AS tutores,
                    (SELECT COUNT(*) FROM usuarios WHERE rol = 'administrador') AS administradores,
                    (SELECT COUNT(*) FROM grados)    AS grados,
                    (SELECT COUNT(*) FROM cursos)    AS cursos,
                    (SELECT COUNT(*) FROM lecciones) AS lecciones,
                    (SELECT COUNT(*) FROM progreso)  AS completadas";
        return $this->db->query($sql)->fetch();
    }

    /** Avance de cada estudiante, para la tabla de seguimiento. */
    public function progresoEstudiantes()
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.usuario,
                       g.nombre AS grado,
                       (SELECT COUNT(*)
                          FROM lecciones l
                          INNER JOIN cursos c ON c.id_curso = l.id_curso
                         WHERE c.id_grado = u.id_grado) AS total,
                       (SELECT COUNT(*)
                          FROM progreso p
                          INNER JOIN lecciones l2 ON l2.id_leccion = p.id_leccion
                          INNER JOIN cursos c2 ON c2.id_curso = l2.id_curso
                         WHERE p.id_usuario = u.id_usuario AND c2.id_grado = u.id_grado) AS completadas
                FROM usuarios u
                LEFT JOIN grados g ON g.id_grado = u.id_grado
                WHERE u.rol = 'estudiante'
                ORDER BY u.nombre ASC";
        $filas = $this->db->query($sql)->fetchAll();
        foreach ($filas as $i => $fila) {
            $total = (int) $fila['total'];
            $hechas = (int) $fila['completadas'];
            $filas[$i]['pendientes'] = max(0, $total - $hechas);
            $filas[$i]['porcentaje'] = $total > 0 ? (int) round(($hechas / $total) * 100) : 0;
        }
        return $filas;
    }
}
