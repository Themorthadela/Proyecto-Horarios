<?php 
class Asignacion {
    private $id_curso;
    private $id_docente;
    private $id_materia;

    // Getters and Setters
    public function getIdCurso() {
        return $this->id_curso;
    }
    public function setIdCurso($id_curso) {
        $this->id_curso = $id_curso;
    }

    public function getIdDocente() {
        return $this->id_docente;
    }

    public function setIdDocente($id_docente) {
        $this->id_docente = $id_docente;
    }

    public function getIdMateria() {
        return $this->id_materia;
    }

    public function setIdMateria($id_materia) {
        $this->id_materia = $id_materia;
    }

    public function GuardarAsignacion() {
        include_once __DIR__ . '/../Persistencia/AsignacionBD.php';
        $asignacionBD = new AsignacionBD();
        return $asignacionBD->Guardar($this->id_curso, $this->id_docente, $this->id_materia);
    }

    public function ListarAsignaciones() {
        include_once __DIR__ . '/../Persistencia/AsignacionBD.php';
        $asignacionBD = new AsignacionBD();
        return $asignacionBD->Listar();
    }
}
