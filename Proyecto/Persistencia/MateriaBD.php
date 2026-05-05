<?php
require_once "Conexion.php";

class MateriaBD extends Conexion {
    private $conexion;

    public function Agregar($nombre, $cargaHoraria, $activo, $tipoSalon, $area) {
        $this->Conectar();

        $sql = "INSERT INTO Materia (ID_A, Nombre, CargaHoraria, activo, TipoSalon) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            echo 'Error al preparar statement: ' . $this->con->error;
            $this->Desconectar();
            return false;
        }

        // tipos: i (area), s (nombre), i (cargaHoraria), i (activo), s (tipoSalon)
        $stmt->bind_param('isiis', $area, $nombre, $cargaHoraria, $activo, $tipoSalon);
        if ($stmt->execute()) {
            echo 'registro exitoso';
        } else {
            echo 'Error: '.$stmt->error;
        }

        $stmt->close();
        $this->Desconectar();
        return true;
    } 


    public function Listar() {
        $this->Conectar();
        $sql = 'SELECT * FROM Materia';
        $result= $this->con->query($sql);

        $materia = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $materia[] = $row;
            }
            $result->close();
        } elseif ($result) {
            $result->close();
        }

        $this->Desconectar();
        return $materia;
    }
    
}
?>