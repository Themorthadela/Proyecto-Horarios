<?php
    class Docente_Materia {
        private $id_d;
        private $id_m;

        public function GetIdDocente() {
            return $this->id_d;
        }
        public function SetIdDocente($id_d) {
            $this->id_d = $id_d;
        }

        public function GetIdMateria() {
            return $this->id_m;
        }
        public function SetIdMateria($id_m) {
            $this->id_m = $id_m;
        }

        public function AgregarDocenteMateria() {
            include_once '../Persistencia/Union/Docente_MateriaBD.php';
            $docente_materiaBD = new Docente_MateriaBD();
            return $docente_materiaBD->Agregar(
                $this->id_d,
                $this->id_m
            );
        }

        public function ListarDocentesMaterias() {
            include_once '../Persistencia/Union/Docente_MateriaBD.php';
            $docente_materiaBD = new Docente_MateriaBD();
            return $docente_materiaBD->Listar();
        }
    }
?>