<?php
    include_once "Conexion.php";

    class Horario extends Conexion {
        private $idcurso;
        private $iddocente;
        private $idmateria;
        private $año;
        private $dia;
        private $hora;
        private $disponible;

        public function SetIdCurso($idcurso) {
            $this->idcurso = $idcurso;
        }
        public function GetIdCurso() {
            return $this->idcurso;
        }

        public function SetIdDocente($iddocente) {
            $this->iddocente = $iddocente;
        }   
        public function GetIdDocente() {
            return $this->iddocente;
        }

        public function SetIdMateria($idmateria) {
            $this->idmateria = $idmateria;
        }
        public function GetIdMateria() {
            return $this->idmateria;
        }

        public function SetAño($año) {
            $this->año = $año;
        }
        public function GetAño() {
            return $this->año;
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


         public function AgregarHorario() {
            include_once 'Persistencia/HorarioBD.php';
            $HorarioBD= new HorarioBD();
            $HorarioBD-> Agregar(  
                $this->idcurso,
                $this->iddocente,
                $this->idmateria,
                $this->año,
                $this->dia,
                $this->hora,
                $this->disponible
                );
        }

        public function ListarHorarios(){
            include_once 'Persistencia/HorarioBD.php';
            $HorarioBD= new HorarioBD();
            return $HorarioBD->Listar();
        }
    }