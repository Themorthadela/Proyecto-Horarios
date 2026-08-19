<?php
    class Horario_Final {
        private $id_asignacion;
        private $año;
        private $dia;
        private $hora_entrada;
        private $hora_salida;
        private $disponible;

        // Getters and Setters
        public function getIdAsignacion() {
            return $this->id_asignacion;
        }
        public function setIdAsignacion($id_asignacion) {
            $this->id_asignacion = $id_asignacion;
        }

        public function getAño() {
            return $this->año;
        }
        public function setAño($año) {
            $this->año = $año;
        }

        public function getDia() {
            return $this->dia;
        }
        public function setDia($dia) {
            $this->dia = $dia;
        }

        public function getHoraEntrada() {
            return $this->hora_entrada;
        }
        public function setHoraEntrada($hora_entrada) {
            $this->hora_entrada = $hora_entrada;
        }

        public function getHoraSalida() {
            return $this->hora_salida;
        }
        public function setHoraSalida($hora_salida) {
            $this->hora_salida = $hora_salida;
        }

        public function getDisponible() {
            return $this->disponible;
        }
        public function setDisponible($disponible) {
            $this->disponible = $disponible;
        }

        public function GuardarHorario() {
            include_once __DIR__ . '/../Persistencia/Horario_FinalBD.php';
            $horarioBD = new Horario_FinalBD();
            return $horarioBD->Guardar($this->id_asignacion, $this->año, $this->dia, $this->hora_entrada, $this->hora_salida, $this->disponible);
        }
        
        public function ListarHorarios() {
            include_once __DIR__ . '/../Persistencia/Horario_FinalBD.php';
            $horarioBD = new Horario_FinalBD();
            return $horarioBD->Listar();
        }

        public function ListarHorariosPorCurso($id_Curso, $anio = null) {
            include_once __DIR__ . '/../Persistencia/Horario_FinalBD.php';
            $horarioBD = new Horario_FinalBD();
            return $horarioBD->ListarPorCurso($id_Curso, $anio);
        }

        public function GenerarHorariosPorCurso($id_Curso, $anio) {
            include_once __DIR__ . '/../Persistencia/Horario_FinalBD.php';
            $horarioBD = new Horario_FinalBD();
            return $horarioBD->GenerarPorCurso($id_Curso, $anio);
        }
    }
