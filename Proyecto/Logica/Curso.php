<?php
class Curso {
    private $nombre;
    private $horas;
    private $dias;
    private $semanal;
    private $activo;
    private $duracion = 1;

    public function GetNombre() {
        return $this->nombre;
    }
    public function SetNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function GetHoras() {
        return $this->horas;
    }
    public function SetHoras($horas) {
        $this->horas = $horas;
    }
    
    public function GetDias() {
        return $this->dias;
    }
    public function SetDias($dias) {
        $this->dias = $dias;
    }

    public function GetActivo() {
        return $this->activo;
    }
    public function SetActivo($activo) {
        $this->activo = $activo;
    }

    public function GetSemanal() {
        return $this->semanal;
    }
    public function SetSemanal($semanal) {
        $this->semanal = $semanal;
    }

    public function GetDuracion() { return $this->duracion; }
    public function SetDuracion($duracion) { $this->duracion = $duracion; }

    public function AgregarCurso() {
        include_once __DIR__ . "/../Persistencia/CursoBD.php";
        $cursoBD = new CursoBD();
        $cursoBD->Agregar(
            $this->nombre,
            $this->horas,
            $this->dias,
            $this->semanal,
            $this->activo,
            $this->duracion
        );
    }

    public function AgregarCursoCompleto($datosTurno, $materias = []) {
        include_once __DIR__ . '/../Persistencia/CursoBD.php';
        return (new CursoBD())->AgregarCompleto([
            'nombre' => $this->nombre, 'horas' => $this->horas,
            'dias' => $this->dias, 'semanal' => $this->semanal,
            'duracion' => $this->duracion
        ], $datosTurno, $materias);
    }

    public function ListarMateriasPorCurso($idCurso) {
        include_once __DIR__ . '/../Persistencia/CursoBD.php';
        return (new CursoBD())->ListarMateriasPorCurso($idCurso);
    }

    public function ListarCursos() {
        include_once __DIR__ . "/../Persistencia/CursoBD.php";
        $cursoBD = new CursoBD();
        return $cursoBD->Listar();
    }

    public function ObtenerCursoPorId($id) {
        include_once __DIR__ . "/../Persistencia/CursoBD.php";
        $cursoBD = new CursoBD();
        return $cursoBD->IdCurso($id);
    }
}
