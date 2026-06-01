<?php
include_once 'Conexion.php';
    class TurnoBD extends Conexion {

        public function Agregar($idcurso, $turno) {
            $this->Conectar();

            $sql = "INSERT INTO turno (ID_C, Turno) VALUES ('$idcurso', '$turno')";
            
            $stmt = $this->con->prepare($sql);
            if (!$stmt) {
                echo 'Error al preparar statement: ' . $this->con->error;
                $this->Desconectar();
            return false;
        }
        }

        public function Listar() {
            $sql = "SELECT c.*, t.Turno 
                    FROM Curso c, Turno t 
                    WHERE c.ID_C = t.ID_C";
            $result = $this->con->query($sql);

            $turnos = [];
            if ($result && $result->num_rows > 0) { 
                while ($row = $result->fetch_assoc()) {
                    $turnos[] = $row;
                }
                $result->close();
            } elseif ($result) {
                $result->close();
            }
            return $turnos;
        }
    }

