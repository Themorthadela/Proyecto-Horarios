<?php
    class Turno {
        private $id_c;
        private $hora_entrada;
        private $hora_salida;
        private $turno;

        public function SetIdCurso($id_c){
            $this->id_c = $id_c;
        }
        public function GetIdCurso() {
            return $this->id_c;
        }

        public function SetHoraEntrada($hora_entrada) {
            $this->hora_entrada = $hora_entrada;
        }
        public function GetHoraEntrada() {
            return $this->hora_entrada;
        }

        public function SetHoraSalida($hora_salida) {
            $this->hora_salida = $hora_salida;
        }
        public function GetHoraSalida() {
            return $this->hora_salida;
        }

        public function SetTurno($turno) {
            $this->turno = $turno;
        }
        public function GetTurno() {
            return $this->turno;
        }

        public function AgregarTurno() {
            include_once __DIR__ . "/../Persistencia/TurnoBD.php";
            $TurnoBD= new TurnoBD();
            $TurnoBD-> Agregar(  
                $this->id_c,
                $this->hora_entrada,
                $this->hora_salida,
                $this->turno
                );
        }

        public function ListarTurnos(){
            include_once __DIR__ . "/../Persistencia/TurnoBD.php";
            $TurnoBD= new TurnoBD();
            return $TurnoBD->Listar();
        }

        public function ObtenerTurnoCurso($idCurso) {
            include_once __DIR__ . "/../Persistencia/TurnoBD.php";
            $turnoBD = new TurnoBD();
            return $turnoBD->ObtenerTurnoCurso($idCurso);
        }

    }