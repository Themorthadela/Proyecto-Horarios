<?php
    class Restriccion_Docente {
        private $id_d;
        private $dia;
        private $hora_I;
        private $hora_F;

        
        public function SetID_D($id_d) {
            $this->id_d = $id_d;
        }
        public function GetID_D() {
            return $this->id_d;
        }
        
        public function SetDia($dia) {
            $this->dia = $dia;
        }
        public function GetDia() {
            return $this->dia;
        }

        public function SetHora_I($hora_I) {
            $this->hora_I = $hora_I;
        }
        public function GetHora_I() {
            return $this->hora_I;
        }

        public function SetHora_F($hora_F) {
            $this->hora_F = $hora_F;
        }
        public function GetHora_F() {
            return $this->hora_F;
        }

        public function AgregarRestriccion()    {
        
            include_once __DIR__ . '/../../Persistencia/Restricciones/Restriccion_DocenteBD.php';

        $restriccionBD = new Restriccion_DocenteBD  ();

            return $restriccionBD->Agregar(
                $this->id_d,
                $this->dia,
                $this->hora_I,
                $this->hora_F,
            );
        }
        
        public function ListarRestricciones($idDocente)  { 

            include_once __DIR__ . '/../../Persistencia/Restricciones/Restriccion_DocenteBD.php';

            $restriccionBD = new Restriccion_DocenteBD();
            return $restriccionBD->ListarDocente($idDocente);
        }

        public function ObtenerRestriccionesDocente($idDocente)  {
        
            include_once __DIR__ . '/../../Persistencia/Restricciones/Restriccion_DocenteBD.php';

            $restriccionBD = new Restriccion_DocenteBD();
            return $restriccionBD->ObtenerRestriccionesDocente($idDocente);
        }
        
    }