<?php
class Docente{
    private $grado;

    public function GetGrado(){
        return $this->grado;
    }
    public function SetGrado($grado){
        $this->grado=$grado;
    }

    public function AgregarDocente(){
        include_once 'Persistencia/DocenteBD.php';
        $DocenteBD= new DocenteBD();
        $DocenteBD-> Agregar(  
            $this->grado,
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