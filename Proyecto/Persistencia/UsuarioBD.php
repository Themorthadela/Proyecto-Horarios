<?php
include_once 'Conexion.php';

class UsuarioBD extends Conexion {
    private $conexion;

    public function Agregar($nombre, $apellido, $correo, $contrasena, $activo, $rol) {
        $this->Conectar();

        $sql = "INSERT INTO Usuario (Nombre, Apellido, Email, Contraseña, Activo, Rol) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException("No se pudo preparar el alta de usuario.");
        }
        $activo = $activo ? 1 : 0;
        $stmt->bind_param("ssssis", $nombre, $apellido, $correo, $contrasena, $activo, $rol);
        if (!$stmt->execute()) {
            $codigo = $stmt->errno;
            $stmt->close();
            $this->Desconectar();
            if ($codigo === 1062) {
                throw new DomainException("Ya existe un usuario con ese correo electrónico.");
            }
            throw new RuntimeException("No se pudo crear el usuario.");
        }
        $stmt->close();
        $this->Desconectar();
    }

    public function BuscarPorCorreo($correo) {
        $this->Conectar();
        $sql = 'SELECT ID_U, Nombre, Apellido, Email, Contraseña, Activo, Rol FROM Usuario WHERE Email = ? LIMIT 1';
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            $this->Desconectar();
            throw new RuntimeException("No se pudo consultar el usuario.");
        }

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc() ?: null;
        $stmt->close();
        $this->Desconectar();
        return $usuario;
    }

    public function ActualizarContrasena($id, $hash) {
        $this->Conectar();
        $sql = 'UPDATE Usuario SET Contraseña = ? WHERE ID_U = ?';
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            $this->Desconectar();
            throw new RuntimeException("No se pudo actualizar la contraseña.");
        }
        $stmt->bind_param("si", $hash, $id);
        $stmt->execute();
        $stmt->close();
        $this->Desconectar();
    }

    public function ActualizarRol($id, $rol) {
        $this->Conectar();
        $sql = 'UPDATE Usuario SET Rol = ? WHERE ID_U = ?';
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            $this->Desconectar();
            throw new RuntimeException("No se pudo preparar el cambio de rol.");
        }

        $stmt->bind_param("si", $rol, $id);
        if (!$stmt->execute()) {
            $stmt->close();
            $this->Desconectar();
            throw new RuntimeException("No se pudo cambiar el rol del usuario.");
        }
        $stmt->close();
        $this->Desconectar();
    }

    public function Listar() {
        $this->Conectar();
        $sql = 'SELECT ID_U, Nombre, Apellido, Email, Activo, Rol FROM Usuario ORDER BY Apellido, Nombre';
        $result= $this->con->query($sql);

        $usuarios = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
            $result->close();
        } elseif ($result) {
            $result->close();
        }

        $this->Desconectar();
        return $usuarios;
    }
}
