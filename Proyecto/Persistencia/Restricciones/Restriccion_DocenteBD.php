<?php
include_once "Conexion.php";

    class Restriccion_DocenteBD extends Conexion {

        public function Agregar($dia, $hora, $disponible) {
            $this->Conectar();

            $sql = "INSERT INTO Restriccion_Docente (Dia, Hora, Disponible) VALUES (?, ?, ?)";

            $stmt = $this->con->prepare($sql);
            if (!$stmt) {
                echo 'Error al preparar statement: ' . $this->con->error;
                $this->Desconectar();
            return false;
            }
                // tipos: i (dia), i (hora), i (disponible)
            $stmt->bind_param('iii', $dia, $hora, $disponible);
            if ($stmt->execute()) {
                echo 'registro exitoso';
            } else {
                echo 'Error: '.$stmt->error;
            }

            $stmt->close();
            $this->Desconectar();
            return true;
        } 

        public function Listar($idDocente, $idCurso) {
            $this->Conectar();
            
            $sql = 'SELECT * FROM Restriccion_Docente WHERE ID_D = ? AND ID_C = ?';
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('ii', $idDocente, $idCurso);
            $stmt->execute();

            $result = $stmt->get_result();


            $restriccion = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $restriccion[] = $row;
                }
                $result->close();
            } elseif ($result) {
                $result->close();
            }

            $this->Desconectar();
            return $restriccion;
        }
    }
