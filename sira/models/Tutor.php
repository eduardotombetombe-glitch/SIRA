<?php
/**
 * Modelo Tutor | Relación tutor -> estudiante (tabla `tutor_estudiante`).
 */
class Tutor
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    /** Estudiantes asociados a un tutor, con su grado. */
    public function estudiantesDe($idTutor)
    {
        $sentencia = $this->db->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.usuario,
                    u.id_grado, g.nombre AS grado, te.fecha_asociado
             FROM tutor_estudiante te
             INNER JOIN usuarios u ON u.id_usuario = te.id_estudiante
             LEFT JOIN grados g ON g.id_grado = u.id_grado
             WHERE te.id_tutor = ?
             ORDER BY u.nombre ASC"
        );
        $sentencia->execute(array((int) $idTutor));
        return $sentencia->fetchAll();
    }

    /** Comprueba que un estudiante realmente pertenece a ese tutor. */
    public function tieneEstudiante($idTutor, $idEstudiante)
    {
        $sentencia = $this->db->prepare(
            'SELECT 1 FROM tutor_estudiante WHERE id_tutor = ? AND id_estudiante = ?'
        );
        $sentencia->execute(array((int) $idTutor, (int) $idEstudiante));
        return (bool) $sentencia->fetchColumn();
    }

    /** Asocia un estudiante existente a un tutor. */
    public function asociar($idTutor, $idEstudiante)
    {
        $sentencia = $this->db->prepare(
            'INSERT IGNORE INTO tutor_estudiante (id_tutor, id_estudiante) VALUES (?, ?)'
        );
        return $sentencia->execute(array((int) $idTutor, (int) $idEstudiante));
    }

    public function desasociar($idTutor, $idEstudiante)
    {
        $sentencia = $this->db->prepare(
            'DELETE FROM tutor_estudiante WHERE id_tutor = ? AND id_estudiante = ?'
        );
        return $sentencia->execute(array((int) $idTutor, (int) $idEstudiante));
    }

    /** Tutores de un estudiante (lo usa el administrador). */
    public function tutoresDe($idEstudiante)
    {
        $sentencia = $this->db->prepare(
            'SELECT u.id_usuario, u.nombre, u.apellido, u.correo
             FROM tutor_estudiante te
             INNER JOIN usuarios u ON u.id_usuario = te.id_tutor
             WHERE te.id_estudiante = ?
             ORDER BY u.nombre ASC'
        );
        $sentencia->execute(array((int) $idEstudiante));
        return $sentencia->fetchAll();
    }

    /** Todos los tutores registrados. */
    public function listar()
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.usuario, u.activo,
                       (SELECT COUNT(*) FROM tutor_estudiante te WHERE te.id_tutor = u.id_usuario)
                           AS total_estudiantes
                FROM usuarios u
                WHERE u.rol = 'tutor'
                ORDER BY u.nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }
}
