<?php
class Materia {
    private $nombre;
    private $cargaHoraria;
    private $horasSeguidas;
    private $tipoSalon;
    private $activo;

    public function GetNombre() {
        return $this->nombre;
    }

    public function SetNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function GetCargaHoraria() {
        return $this->cargaHoraria;
    }

    public function SetCargaHoraria($cargaHoraria) {
        $this->cargaHoraria = $cargaHoraria;
    }

    public function GetHorasSeguidas() {
        return $this->horasSeguidas;
    }

    public function SetHorasSeguidas($horasSeguidas) {
        $this->horasSeguidas = $horasSeguidas;
    }

    public function GetTipoSalon() {
        return $this->tipoSalon;
    }

    public function SetTipoSalon($tipoSalon) {
        $this->tipoSalon = $tipoSalon;
    }

    public function GetActivo() {
        return $this->activo;
    }

    public function SetActivo($activo) {
        $this->activo = $activo;
    }

    public function AgregarMateria() {
        include_once __DIR__ . "/../Persistencia/MateriaBD.php";
        $MateriaBD= new MateriaBD();
        $MateriaBD-> Agregar(  
            $this->nombre,
            $this->cargaHoraria,
            $this->horasSeguidas,
            $this->tipoSalon,
            $this->activo
            );
    }

    public function ListarMaterias(){
        include_once __DIR__ . "/../Persistencia/MateriaBD.php";
        $MateriaBD= new MateriaBD();
        return $MateriaBD->Listar();
  }

}
?>
