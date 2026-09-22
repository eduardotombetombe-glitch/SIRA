<?php
/**
 * Modelo Usuario | Acceso a la tabla `usuarios`.
 * Los tres actores (estudiante, tutor, administrador) comparten esta tabla
 * y se diferencian por la columna `rol`.
 */
class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    /** Inserta un usuario con la contraseña ya cifrada. Devuelve su id. */
    public function crear($datos)
    {
        $rol = isset($datos['rol']) ? $datos['rol'] : 'estudiante';
        if (!in_array($rol, array('estudiante', 'tutor', 'administrador'), true)) {
            $rol = 'estudiante';
        }

        $sentencia = $this->db->prepare(
            'INSERT INTO usuarios (nombre, apellido, correo, usuario, contrasena, rol, id_grado, activo)
             VALUES (:nombre, :apellido, :correo, :usuario, :contrasena, :rol, :id_grado, :activo)'
        );
        $sentencia->execute(array(
            ':nombre'     => $datos['nombre'],
            ':apellido'   => $datos['apellido'],
            ':correo'     => $datos['correo'],
            ':usuario'    => $datos['usuario'],
            ':contrasena' => password_hash($datos['contrasena'], PASSWORD_DEFAULT),
            ':rol'        => $rol,
            ':id_grado'   => ($rol === 'estudiante' && !empty($datos['id_grado']))
                                ? (int) $datos['id_grado'] : null,
            ':activo'     => isset($datos['activo']) ? (int) $datos['activo'] : 1,
        ));
        return (int) $this->db->lastInsertId();
    }

    public function correoRegistrado($correo, $excepto = 0)
    {
        $sentencia = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE correo = ? AND id_usuario <> ?'
        );
        $sentencia->execute(array($correo, (int) $excepto));
        return (bool) $sentencia->fetchColumn();
    }

    public function usuarioRegistrado($usuario, $excepto = 0)
    {
        $sentencia = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE usuario = ? AND id_usuario <> ?'
        );
        $sentencia->execute(array($usuario, (int) $excepto));
        return (bool) $sentencia->fetchColumn();
    }

    /** Busca por nombre de usuario o por correo para el inicio de sesión. */
    public function buscarPorUsuarioOCorreo($identificador)
    {
        $sentencia = $this->db->prepare(
            'SELECT * FROM usuarios WHERE usuario = :usuario OR correo = :correo LIMIT 1'
        );
        $sentencia->execute(array(
            ':usuario' => $identificador,
            ':correo'  => $identificador,
        ));
        $fila = $sentencia->fetch();
        return $fila ? $fila : null;
    }

    /** Datos de un usuario junto con el nombre de su grado (si es estudiante). */
    public function obtenerPorId($idUsuario)
    {
        $sentencia = $this->db->prepare(
            'SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.usuario, u.rol,
                    u.id_grado, u.activo, u.fecha_registro,
                    g.nombre AS grado, g.descripcion AS grado_descripcion, g.orden AS grado_orden
             FROM usuarios u
             LEFT JOIN grados g ON g.id_grado = u.id_grado
             WHERE u.id_usuario = ?'
        );
        $sentencia->execute(array((int) $idUsuario));
        $fila = $sentencia->fetch();
        return $fila ? $fila : null;
    }

    /** Listado para el panel del administrador, con filtro opcional por rol. */
    public function listar($rol = null, $busqueda = '')
    {
        $sql = 'SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.usuario, u.rol,
                       u.activo, u.fecha_registro, g.nombre AS grado
                FROM usuarios u
                LEFT JOIN grados g ON g.id_grado = u.id_grado
                WHERE 1 = 1';
        $parametros = array();

        if ($rol !== null && $rol !== '') {
            $sql .= ' AND u.rol = :rol';
            $parametros[':rol'] = $rol;
        }
        if ($busqueda !== '') {
            $sql .= ' AND (u.nombre LIKE :busca OR u.apellido LIKE :busca2
                           OR u.correo LIKE :busca3 OR u.usuario LIKE :busca4)';
            $comodin = '%' . $busqueda . '%';
            $parametros[':busca']  = $comodin;
            $parametros[':busca2'] = $comodin;
            $parametros[':busca3'] = $comodin;
            $parametros[':busca4'] = $comodin;
        }
        $sql .= ' ORDER BY u.rol ASC, u.nombre ASC';

        $sentencia = $this->db->prepare($sql);
        $sentencia->execute($parametros);
        return $sentencia->fetchAll();
    }

    /** Actualiza los datos básicos (usado por el administrador). */
    public function actualizar($idUsuario, $datos)
    {
        $sentencia = $this->db->prepare(
            'UPDATE usuarios
             SET nombre = :nombre, apellido = :apellido, correo = :correo,
                 usuario = :usuario, rol = :rol, id_grado = :id_grado, activo = :activo
             WHERE id_usuario = :id'
        );
        return $sentencia->execute(array(
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':correo'   => $datos['correo'],
            ':usuario'  => $datos['usuario'],
            ':rol'      => $datos['rol'],
            ':id_grado' => ($datos['rol'] === 'estudiante' && !empty($datos['id_grado']))
                              ? (int) $datos['id_grado'] : null,
            ':activo'   => !empty($datos['activo']) ? 1 : 0,
            ':id'       => (int) $idUsuario,
        ));
    }

    public function eliminar($idUsuario)
    {
        $sentencia = $this->db->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
        return $sentencia->execute(array((int) $idUsuario));
    }

    /** Cambia el grado del estudiante. */
    public function actualizarGrado($idUsuario, $idGrado)
    {
        $sentencia = $this->db->prepare('UPDATE usuarios SET id_grado = ? WHERE id_usuario = ?');
        return $sentencia->execute(array((int) $idGrado, (int) $idUsuario));
    }

    public function verificarContrasena($idUsuario, $contrasena)
    {
        $sentencia = $this->db->prepare('SELECT contrasena FROM usuarios WHERE id_usuario = ?');
        $sentencia->execute(array((int) $idUsuario));
        $hash = $sentencia->fetchColumn();
        return $hash && password_verify($contrasena, $hash);
    }

    public function actualizarContrasena($idUsuario, $contrasenaNueva)
    {
        $sentencia = $this->db->prepare('UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?');
        return $sentencia->execute(array(
            password_hash($contrasenaNueva, PASSWORD_DEFAULT),
            (int) $idUsuario,
        ));
    }

    /** Conteo de usuarios por rol, para el tablero del administrador. */
    public function contarPorRol()
    {
        $sql = 'SELECT rol, COUNT(*) AS total FROM usuarios GROUP BY rol';
        $conteo = array('estudiante' => 0, 'tutor' => 0, 'administrador' => 0);
        foreach ($this->db->query($sql)->fetchAll() as $fila) {
            $conteo[$fila['rol']] = (int) $fila['total'];
        }
        return $conteo;
    }
}
