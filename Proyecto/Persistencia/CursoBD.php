<?php
include_once "Conexion.php";

class CursoBD extends Conexion {

    public function Agregar($nombre, $horas, $dias, $semanal, $activo) {
        $this->Conectar();

        $sql = "INSERT INTO Curso (Nombre, CantidadHoras, Dias, Semanal, Activo) 
            VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            echo 'Error al preparar statement: ' . $this->con->error;
            $this->Desconectar();
            return false;
        }

        // tipos: s (nombre), s (horas), i (dias), i (semanal), i (activo)
        $stmt->bind_param('ssiii', $nombre, $horas, $dias, $semanal, $activo);
        if ($stmt->execute()) {
            echo 'Registro exitoso';
        } else {
            echo 'Error: ' . $stmt->error;
        }

        $stmt->close();
        $this->Desconectar();
        return true;
    }

    public function Listar() {
        $this->Conectar();
        $sql = "SELECT * FROM Curso";
        $result = $this->con->query($sql);

        $cursos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cursos[] = $row;
            }
            $result->close();
        } elseif ($result) {
            $result->close();
        }

        $this->Desconectar();
        return $cursos;
    }

    public function IdCurso($id) {
    require_once "./Persistencia/Conexion.php";
    $con = new Conexion();
    $conn = $con->conectar();

    $sql = "SELECT c.*, t.* FROM Curso c INNER JOIN Turno t 
            ON c.ID_C = t.ID_C  
            WHERE c.ID_C = $id";
    $resultado = $conn->query($sql);

    return $resultado->fetch_assoc();
}

}   
?>