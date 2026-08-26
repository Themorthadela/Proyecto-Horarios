<?php
class Area {
    private $nombre;

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

     public function AgregarArea() {
        include_once __DIR__ . "/../Persistencia/AreaBD.php";
        $areaBD = new AreaBD();
        $areaBD->Agregar($this->nombre);
     }

    public function ListarAreas() {
        include_once __DIR__ . "/../Persistencia/AreaBD.php";
        $areaBD = new AreaBD();
        return $areaBD->Listar();
     }     
}
?>