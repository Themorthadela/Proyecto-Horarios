<?php
include_once __DIR__ . "/../Conexion.php";
class Docente_AreaBD extends Conexion {

    public function Agregar($id_d, $id_a) {
        $this->Conectar();

        $sql = "INSERT INTO Docente_Area (ID_D, ID_A) VALUES ('$id_d', '$id_a')";
        
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            echo 'Error al preparar statement: ' . $this->con->error;
            $this->Desconectar();
            return false;
        }
        $stmt->execute();
        $this->Desconectar();
        return true;
    }

    public function Listar() {
        $sql = "SELECT d.*, a.Nombre AS Area 
                FROM Docente d, Area a, Docente_Area da 
                WHERE d.ID_D = da.ID_D AND a.ID_A = da.ID_A";
        $result = $this->con->query($sql);

        $docentes_areas = [];
        if ($result && $result->num_rows > 0) { 
            while ($row = $result->fetch_assoc()) {
                $docentes_areas[] = $row;
            }
            $result->close();
        } elseif ($result) {
            $result->close();
        }
        return $docentes_areas;
    }
}