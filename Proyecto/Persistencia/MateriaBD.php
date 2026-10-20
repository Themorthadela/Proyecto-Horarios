<?php
require_once "Conexion.php";

class MateriaBD extends Conexion {
    private $conexion;

    public function Agregar($nombre, $cargaHoraria, $horasSeguidas, $tipoSalon, $activo) {
        $this->Conectar();

        $sql = "INSERT INTO Materia (Nombre, CargaHoraria, HorasSe, TipoSalon, Activo) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            echo 'Error al preparar statement: ' . $this->con->error;
            $this->Desconectar();
            return false;
        }

        // nombre, carga horaria, horas seguidas, tipo de salón y activo
        $stmt->bind_param('siisi', $nombre, $cargaHoraria, $horasSeguidas, $tipoSalon, $activo);
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
