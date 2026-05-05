<?php
class Docente{
    private $nombre;
    private $apellido;
    private $grado;
    private $area;
    private $activo;
    private $CI;

    public function GetNombre(){
         return $this->nombre;
    }
    public function SetNombre($nombre){
        $this->nombre=$nombre;
    }

    public function GetApellido(){
        return $this-> apellido;
    }
    public function SetApellido($apellido){
        $this->apellido=$apellido;
    }

    public function GetGrado(){
        return $this->grado;
    }
    public function SetGrado($grado){
        $this->grado=$grado;
    }
    public function GetArea(){
        return $this->area;
    }   
    public function SetArea($area){
        $this->area=$area;
    }

    public function GetCI(){
        return $this->CI;
    }
    public function SetCI($CI){
        $this->CI = $CI;
    }

    public function GetActivo(){
        return $this->activo;
    }
    public function SetActivo($activo){
        $this->activo=$activo;
    }

    public function AgregarDocente(){
        include_once 'Persistencia/DocenteBD.php';
        $DocenteBD= new DocenteBD();
        $DocenteBD-> Agregar(  
            $this->nombre,
            $this->apellido,
            $this->CI,
            $this->grado,
            $this->area,
            $this->activo
            );
    }

    public function ListarDocentes(){
        include_once 'Persistencia/DocenteBD.php';
        $DocenteBD= new DocenteBD();
        return $DocenteBD->Listar();
    }

    public  function GradoDocente() {
        include_once 'Persistencia/DocenteBD.php';
        $DocenteBD = new DocenteBD();
        return $DocenteBD->Grado();
    }

    public function ObtenerDocentePorId($id) {
        include_once 'Persistencia/DocenteBD.php';
        $docenteBD = new DocenteBD();
        return $docenteBD->IdDocente($id);
    }
}