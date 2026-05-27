<?php
    class Docente_Materia {
        private $iddocente;
        private $idmateria;

        public function GetIdDocente() {
            return $this->iddocente;
        }
        public function SetIdDocente($iddocente) {
            $this->iddocente = $iddocente;
        }

        public function GetIdMateria() {
            return $this->idmateria;
        }
        public function SetIdMateria($idmateria) {
            $this->idmateria = $idmateria;
        }

        public function AgregarDocenteMateria() {
            include_once 'Persistencia/Docente_MateriaBD.php';
            $docente_materiaBD = new Docente_MateriaBD();
            return $docente_materiaBD->Agregar(
                $this->iddocente,
                $this->idmateria
            );
        }

        public function ListarDocentesMaterias() {
            include_once 'Persistencia/Docente_MateriaBD.php';
            $docente_materiaBD = new Docente_MateriaBD();
            return $docente_materiaBD->Listar();
        }
    }