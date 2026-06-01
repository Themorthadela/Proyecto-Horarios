<?php
include_once 'Conexion.php';

class UsuarioBD extends Conexion {
    private $conexion;

    public function Agregar($nombre, $apellido, $correo, $contrasena, $activo, $rol) {
        $this->Conectar();

        $sql = "INSERT INTO Usuario (Nombre, Apellido, Email, Contraseña, Activo, Rol) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $this->con->error);
        }
        $stmt->bind_param("ssssss", $nombre, $apellido, $correo, $contrasena, $activo, $rol);
        if (!$stmt->execute()) {
            die("Error al ejecutar la consulta: " . $stmt->error);
        }
        $stmt->close();
        $this->Desconectar();
    }

    public function Listar() {
        $this->Conectar();
        $sql = 'SELECT * FROM Usuario';
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