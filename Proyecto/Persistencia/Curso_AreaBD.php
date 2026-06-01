<?php
include_once 'Conexion.php';
class Curso_AreaBD extends Conexion {

    public function Agregar($id_c, $id_a) {
        $this->Conectar();

        $sql = "INSERT INTO Curso_Area (ID_C, ID_A) VALUES ('$id_c', '$id_a')";
        
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
        $sql = "SELECT c.*, a.Nombre AS Area 
                FROM Curso c, Area a, Curso_Area ca 
                WHERE c.ID_C = ca.ID_C AND a.ID_A = ca.ID_A";
        $result = $this->con->query($sql);

        $cursos_areas = [];
        if ($result && $result->num_rows > 0) { 
            while ($row = $result->fetch_assoc()) {
                $cursos_areas[] = $row;
            }
            $result->close();
        } elseif ($result) {
            $result->close();
        }
        return $cursos_areas;
    }
}