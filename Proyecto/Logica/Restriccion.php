<?php
    class Restriccion {
        private $dia;
        private $hora;
        private $estado;
        
        public function SetDia($dia) {
            $this->dia = $dia;
        }
        public function GetDia() {
            return $this->dia;
        }

        public function SetHora($hora) {
            $this->hora = $hora;
        }
        public function GetHora() {
            return $this->hora;
        }
 
        public function SetEstado($estado) {
            $this->estado = $estado;
        }
        public function GetEstado() {
            return $this->estado;
        }
        
        public function ListarRestricciones($idDocente, $idCurso) {
            include_once 'Persistencia/RestriccionBD.php';
            $restriccionBD = new RestriccionBD();
            return $restriccionBD->Listar($idDocente, $idCurso);
        }
        
    }