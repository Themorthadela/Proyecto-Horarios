<?php
require_once "Conexion.php";

class DocenteBD extends Conexion{

    public function Agregar($id_u, $grado){
        $this->Conectar();

        
        $sql = "INSERT INTO Docente (ID_U, Grado)                
            VALUES (?, ?)";

        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            echo 'Error al preparar statement: ' . $this->con->error;
            $this->Desconectar();
            return false;
        }

        $stmt->bind_param('ii',$id_u, $grado);
        if ($stmt->execute()) {
            echo 'registro exitoso';
        } else {
            echo 'Error: '.$stmt->error;
        }

        $stmt->close();
        $this->Desconectar();
        return true;
    }

    public function Listar(){
        $this->Conectar();
        $sql = 'SELECT u.*, d.*
                FROM Usuario u
                INNER JOIN Docente d ON u.ID_U = d.ID_U';
        $result= $this->con->query($sql);

        $docente = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $docente[] = $row;
            }
            $result->close();
        } elseif ($result) {
            $result->close();
        }

        $this->Desconectar();
        return $docente;
    }

    public function Grado(){
        $this->Conectar();
        $sql = "SELECT u.*, d.*
                FROM Usuario u
                INNER JOIN Docente d ON u.ID_U = d.ID_U
                ORDER BY d.Grado DESC";
        $result= $this->con->query($sql);

        $docente = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $docente[] = $row;
            }
            $result->close();
        } elseif ($result) {
            $result->close();
        }

        $this->Desconectar();
        return $docente;
    }

    public function IdDocente($id) {
        require_once __DIR__ . "/../Persistencia/Conexion.php";

        $con = new Conexion();
        $conn = $con->conectar();

        $sql = "SELECT u.*, d.*
                FROM Usuario u
                INNER JOIN Docente d ON u.ID_U = d.ID_U
                WHERE d.ID_D = $id";
        $resultado = $conn->query($sql);

        return $resultado->fetch_assoc();
    }
}