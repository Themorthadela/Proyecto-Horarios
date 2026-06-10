<?php    
    class Restriccion_Curso {
        private $id_c;
        private $dia;
        private $hora;
        private $disponible;

        public function SetIdCurso($id_c) {
            $this->id_c = $id_c;
        }
        public function GetIdCurso() {
            return $this->id_c;
        }

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

        public function AgregarRestriccion() {
        include_once 'Persistencia/RestriccionBD.php';
        $restriccionBD = new RestriccionBD();
        $restriccionBD->AgregarCurso(
            $this->id_c,                
            $this->dia,
            $this->hora,
            $this->disponible
            );
        }

        public function ListarRestricciones($idCurso) {
            include_once 'Persistencia/RestriccionBD.php';
            $restriccionBD = new RestriccionBD();
            return $restriccionBD->ListarCurso($idCurso);
        }



    }
