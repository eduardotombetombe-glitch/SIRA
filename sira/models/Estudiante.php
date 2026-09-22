<?php
/**
 * Modelo Estudiante | Consultas propias del actor estudiante.
 * Trabaja sobre `usuarios` filtrando por rol = 'estudiante'.
 */
class Estudiante
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    /** Todos los estudiantes con su grado (para el administrador). */
    public function listar()
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.usuario,
                       u.id_grado, u.activo, g.nombre AS grado
                FROM usuarios u
                LEFT JOIN grados g ON g.id_grado = u.id_grado
                WHERE u.rol = 'estudiante'
                ORDER BY u.nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Un estudiante concreto, o null si ese id no es de un estudiante. */
    public function obtenerPorId($idEstudiante)
    {
        $sentencia = $this->db->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.usuario,
                    u.id_grado, u.activo, u.fecha_registro,
                    g.nombre AS grado, g.descripcion AS grado_descripcion
             FROM usuarios u
             LEFT JOIN grados g ON g.id_grado = u.id_grado
             WHERE u.id_usuario = ? AND u.rol = 'estudiante'"
        );
        $sentencia->execute(array((int) $idEstudiante));
        $fila = $sentencia->fetch();
        return $fila ? $fila : null;
    }

    /** Estudiantes que todavía no están asociados a un tutor concreto. */
    public function sinTutor($idTutor)
    {
        $sentencia = $this->db->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.usuario, g.nombre AS grado
             FROM usuarios u
             LEFT JOIN grados g ON g.id_grado = u.id_grado
             LEFT JOIN tutor_estudiante te
                    ON te.id_estudiante = u.id_usuario AND te.id_tutor = :id_tutor
             WHERE u.rol = 'estudiante' AND te.id_estudiante IS NULL
             ORDER BY u.nombre ASC"
        );
        $sentencia->execute(array(':id_tutor' => (int) $idTutor));
        return $sentencia->fetchAll();
    }
}
