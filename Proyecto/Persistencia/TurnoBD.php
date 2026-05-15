<?php
include_once 'Conexion.php';
    class TurnoBD extends Conexion {

        public function Agregar($idcurso, $turno) {
            $sql = "INSERT INTO turno (ID_C, Turno) VALUES ('$idcurso', '$turno')";
            $this->Ejecutar($sql);
        }

        public function Listar() {
            $sql = "SELECT c.*, t.Turno 
                    FROM Curso c, Turno t 
                    WHERE c.ID_C = t.ID_C";
            return $this->Consultar($sql);
        }
    }

?>