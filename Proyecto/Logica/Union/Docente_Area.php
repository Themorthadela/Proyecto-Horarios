<?php
    class Docente_Area {
        private $id_d;
        private $id_a;

        public function GetIdDocente() {
            return $this->id_d;
        }
        public function SetIdDocente($id_d) {
            $this->id_d = $id_d;
        }

        public function GetIdArea() {
            return $this->id_a;
        }
        public function SetIdArea($id_a) {
            $this->id_a = $id_a;
        }

        public function AgregarDocenteArea() {
            include_once 'Persistencia/Docente_AreaBD.php';
            $docente_areaBD = new Docente_AreaBD();
            return $docente_areaBD->Agregar(
                $this->id_d,
                $this->id_a
            );
        }

        public function ListarDocentesAreas() {
            include_once 'Persistencia/Docente_AreaBD.php';
            $docente_areaBD = new Docente_AreaBD();
            return $docente_areaBD->Listar();
        }
    }