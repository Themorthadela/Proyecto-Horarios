<?php    
    class Restriccion_Curso {
        private $id_c;
        private $dia;
        private $hora_I;
        private $hora_F;
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

        public function SetHoraI($hora_I) {
            $this->hora_I = $hora_I;
        }
        public function GetHoraI() {
            return $this->hora_I;
        }

        public function SetHoraF($hora_F) {
            $this->hora_F = $hora_F;
        }
        public function GetHoraF() {
            return $this->hora_F;
        }

        public function SetDisponible($disponible) {
            $this->disponible = $disponible;
        }
        public function GetDisponible() {
            return $this->disponible;
        }

        public function AgregarRestriccion() {
        include_once 'Persistencia/Restriccion/Restriccion_CursoBD.php';
        $restriccionBD = new RestriccionBD();
        $restriccionBD->AgregarCurso(
            $this->id_c,                
            $this->dia,
            $this->hora_I,
            $this->hora_F,
            $this->disponible
            );
        }

        public function ListarRestricciones($idCurso) {
            include_once 'Persistencia/Restricciones/Restriccion_CursoBD.php';
            $restriccionBD = new RestriccionBD();
            return $restriccionBD->ListarCurso($idCurso);
        }

        public function ObtenerRestriccionesCurso($idCurso) {
            include_once 'Persistencia/Restricciones/Restriccion_CursoBD.php';
            $restriccionBD = new RestriccionBD();
            return $restriccionBD->ObtenerRestriccionesCurso($idCurso);
        }


    }
