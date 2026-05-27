<?php
    include_once 'Conexion.php';
    class Docente_Materia extends Conexion {
    public function Agregar($iddocente, $idmateria) {
        $this->Conectar();

        $sql = "INSERT INTO Docente_Materia (ID_D, ID_M) VALUES ('$iddocente', '$idmateria')";
        
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            echo 'Error al preparar statement: ' . $this->con->error;
            $this->Desconectar();
        return false;
    }
    }

    public function Listar() {
        $sql = "SELECT d.*, m.Nombre AS Materia 
                FROM Docente d, Materia m, Docente_Materia dm 
                WHERE d.ID_D = dm.ID_D AND m.ID_M = dm.ID_M";
        $result = $this->con->query($sql);

        $docentes_materias = [];
        if ($result && $result->num_rows > 0) { 
            while ($row = $result->fetch_assoc()) {
                $docentes_materias[] = $row;
            }
            $result->close();
        } elseif ($result) {
            $result->close();
        }
        return $docentes_materias;
    }
}