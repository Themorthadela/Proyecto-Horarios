<?php
require_once "Conexion.php";

class DocenteBD extends Conexion{

    /**
     * Agregar un docente (tabla Docente en la BD)
     * Se espera: $nombre (string), $apellido (string), $CI (int), $grado (int), $area (int ID_A), $activo (int 0/1)
     */
    public function Agregar($nombre, $apellido, $CI, $grado, $area, $activo){
        $this->Conectar();

        // La tabla en el esquema SQL se llama Docente; ID_Se lo dejamos NULL por ahora
        $sql = "INSERT INTO Docente (ID_A, ID_Se, Nombre, Apellido, CI, Grado, Activo)\n                
            VALUES (?, NULL, ?, ?, ?, ?, ?)";

        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            echo 'Error al preparar statement: ' . $this->con->error;
            $this->Desconectar();
            return false;
        }

        // tipos: i (ID_A), s (Nombre), s (Apellido), i (CI), i (Grado), i (Activo)
        $stmt->bind_param('issiii', $area, $nombre, $apellido, $CI, $grado, $activo);
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
        $sql = 'SELECT * FROM Docente';
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
        $sql = "SELECT * FROM Docente ORDER BY Grado DESC";
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
        require_once "./Persistencia/Conexion.php";

        $con = new Conexion();
        $conn = $con->conectar();

        $sql = "SELECT * FROM Docente WHERE ID_D = $id";
        $resultado = $conn->query($sql);

        return $resultado->fetch_assoc();
    }
}