<?php
    class Restriccion_Docente {
        private $dia;
        private $hora;
        private $disponible;
        
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
 
        public function SetDisponible($disponible) {
            $this->disponible = $disponible;
        }
        public function GetDisponible() {
            return $this->disponible;
        }
        
        public function ListarRestricciones($idDocente, $idCurso) {
            include_once 'Persistencia/RestriccionBD.php';
            $restriccionBD = new RestriccionBD();
            return $restriccionBD->Listar($idDocente, $idCurso);
        }
        
    }