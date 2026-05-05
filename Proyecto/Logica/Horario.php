<?php
    include_once "Conexion.php";

    class Horario extends Conexion {
        private $horario;

        public function SetHorario($horario) {
            $this->horario = $horario;
        }
        public function GetHorario() {
            return $this->horario;
        }
    }