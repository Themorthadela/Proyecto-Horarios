<?php
    class Turno {
        private$idcurso;
        private $turno;

        public function SetIdCurso($idcurso) {
            $this->idcurso = $idcurso;
        }
        public function GetIdCurso() {
            return $this->idcurso;
        }

        public function SetTurno($turno) {
            $this->turno = $turno;
        }
        public function GetTurno() {
            return $this->turno;
        }

        public function AgregarTurno() {
            include_once 'Persistencia/TurnoBD.php';
            $TurnoBD= new TurnoBD();
            $TurnoBD-> Agregar(  
                $this->idcurso,
                $this->turno
                );
        }

        public function ListarTurnos(){
            include_once 'Persistencia/TurnoBD.php';
            $TurnoBD= new TurnoBD();
            return $TurnoBD->Listar();
        }

    }