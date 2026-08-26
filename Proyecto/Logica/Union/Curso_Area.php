<?php
    class Curso_Area {
        private $id_c;
        private $id_a;

        public function GetIdCurso() {
            return $this->id_c;
        }
        public function SetIdCurso($id_c) {
            $this->id_c = $id_c;
        }

        public function GetIdArea() {
            return $this->id_a;
        }
        public function SetIdArea($id_a) {
            $this->id_a = $id_a;
        }

        public function AgregarCursoArea() {
            include_once '../Persistencia/Union/Curso_AreaBD.php';
            $curso_areaBD = new Curso_AreaBD();
            return $curso_areaBD->Agregar(
                $this->id_c,
                $this->id_a
            );
        }

        public function ListarCursosAreas() {
            include_once '../   Persistencia/Union/Curso_AreaBD.php';
            $curso_areaBD = new Curso_AreaBD();
            return $curso_areaBD->Listar();
        }
    }
