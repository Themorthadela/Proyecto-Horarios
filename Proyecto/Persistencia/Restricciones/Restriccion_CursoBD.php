<?php
    include_once __DIR__ . '/../Conexion.php';
    class Restriccion_CursoBD extends Conexion {

        public function Agregar($id_c, $dia, $hora_I, $hora_F, $disponible) {
            $this->Conectar();

            $sql = "INSERT INTO Restriccion_Curso (ID_C, Dia, HoraEntrada, HoraSalida, Disponible) VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->con->prepare($sql);
            if (!$stmt) {
                echo 'Error al preparar statement: ' . $this->con->error;
                $this->Desconectar();
            return false;
            }
                // tipos: i (id_c), i (dia), s (hora_I), s (hora_F), i (disponible)
            $stmt->bind_param('iissi', $id_c, $dia, $hora_I, $hora_F, $disponible);
            if ($stmt->execute()) {
                echo 'registro exitoso';
            } else {
                echo 'Error: '.$stmt->error;
            }

            $stmt->close();
            $this->Desconectar();
            return true;
        } 

        public function ListarCurso($idCurso) {
            $this->Conectar();
            
            $sql = 'SELECT * FROM Restriccion_Curso WHERE ID_C = ?';
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('i', $idCurso);
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
            $stmt->close();
            $this->Desconectar();
            return $restriccion;
        }

        public function ObtenerRestriccionesCurso($idCurso) {
        
            $sql = "SELECT Dia, HoraEntrada, HoraSalida, Activo
                    FROM Restriccion_Curso
                    WHERE ID_C = ?
                    ORDER BY HoraEntrada, Dia";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $idCurso);
            $stmt->execute();

            $resultado = $stmt->get_result();

            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }
