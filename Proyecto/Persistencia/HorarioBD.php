<?php
include_once 'Conexion.php';
class HorarioBD extends Conexion {
    private $conexion;

    public function Agregar($idcurso, $iddocente, $idmateria, $año, $dia, $hora, $disponible) {
        $this->Conectar();

        $sql = "INSERT INTO Horario (ID_C, ID_D, ID_M, Año, Dia, Hora, Disponible) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $this->con->error);
        }

        // tipos: i (idcurso), i (iddocente), i (idmateria), i (año), i (dia), s (hora), i (disponible)
        $stmt->bind_param("iiiiiis", $idcurso, $iddocente, $idmateria, $año, $dia, $hora, $disponible);
        if (!$stmt->execute()) {
            die("Error al ejecutar la consulta: " . $stmt->error);
        }
        $stmt->close();
        $this->Desconectar();
        return true;
    }

    public function Listar() {
        $this->Conectar();
        $sql = 'SELECT * FROM Horario';
        $result= $this->con->query($sql);

        $horarios = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $horarios[] = $row;
            }
            $result->close();
        } elseif ($result) {
            $result->close();
        }

        $this->Desconectar();
        return $horarios;
    }
}