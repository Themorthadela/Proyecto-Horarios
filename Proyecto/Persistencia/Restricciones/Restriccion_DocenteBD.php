<?php
include_once __DIR__ . '/../Conexion.php';

    class Restriccion_DocenteBD extends Conexion {

        public function Agregar($id_d, $dia, $hora_I, $hora_F) {
            $this->Conectar();

            $sql = "
                INSERT INTO Restriccion_Docente
                    (ID_D, Dia, HoraEntrada, HoraSalida)
                VALUES
                    (?, ?, ?, ?)
            ";

            $stmt = $this->con->prepare($sql);

            if (!$stmt) {
                $error = $this->con->error;
                $this->Desconectar();
                throw new Exception('Error al preparar la consulta: ' . $error);
            }

            $stmt->bind_param('iiss', $id_d, $dia, $hora_I, $hora_F);

            $resultado = $stmt->execute();

            if (!$resultado) {
                $error = $stmt->error;
                $stmt->close();
                $this->Desconectar();
                throw new Exception('Error al guardar la restricción: ' . $error);
            }

            $stmt->close();
            $this->Desconectar();
            return true;
        }

        public function Listar($idDocente) {
            $this->Conectar();
            
            $sql = 'SELECT * FROM Restriccion_Docente WHERE ID_D = ?';
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('i', $idDocente);
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
