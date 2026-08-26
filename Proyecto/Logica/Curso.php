<?php
class Curso {
    private $nombre;
    private $horas;
    private $dias;
    private $semanal;
    private $activo;

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

    public function AgregarCurso() {
        include_once __DIR__ . "/../Persistencia/CursoBD.php";
        $cursoBD = new CursoBD();
        $cursoBD->Agregar(
            $this->nombre,
            $this->horas,
            $this->dias,
            $this->semanal,
            $this->activo
        );
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