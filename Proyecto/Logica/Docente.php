<?php
class Docente{
    private $id_u;
    private $grado;

    public function SetID_U($id_u){
        $this->id_u=$id_u;
    }
    public function GetID_U(){
        return $this->id_u;
    }

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
            $this->id_u,
            $this->grado
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