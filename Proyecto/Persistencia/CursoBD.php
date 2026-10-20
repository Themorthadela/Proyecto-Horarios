<?php
require_once __DIR__ . '/Conexion.php';

class CursoBD extends Conexion
{
    private function asegurarEstructura()
    {
        $columna = $this->con->query("SHOW COLUMNS FROM Curso LIKE 'DuracionAnios'");
        if (!$columna || $columna->num_rows === 0) {
            if (!$this->con->query('ALTER TABLE Curso ADD COLUMN DuracionAnios TINYINT UNSIGNED NOT NULL DEFAULT 1')) {
                throw new RuntimeException($this->con->error);
            }
        }
        $sql = "CREATE TABLE IF NOT EXISTS Curso_Materia (
            ID_CM INT AUTO_INCREMENT PRIMARY KEY, ID_C INT NOT NULL, ID_M INT NOT NULL,
            Anio TINYINT UNSIGNED NOT NULL,
            Periodo ENUM('Anual', 'Semestre 1', 'Semestre 2') NOT NULL DEFAULT 'Anual',
            UNIQUE KEY uq_curso_materia_periodo (ID_C, ID_M, Anio, Periodo),
            KEY idx_cm_materia (ID_M),
            CONSTRAINT fk_cm_curso FOREIGN KEY (ID_C) REFERENCES Curso(ID_C) ON DELETE CASCADE,
            CONSTRAINT fk_cm_materia FOREIGN KEY (ID_M) REFERENCES Materia(ID_M) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if (!$this->con->query($sql)) { throw new RuntimeException($this->con->error); }
    }

    public function Agregar($nombre, $horas, $dias, $semanal, $activo, $duracion = 1)
    {
        $this->Conectar();
        try {
            $this->asegurarEstructura();
            $stmt = $this->con->prepare('INSERT INTO Curso (Nombre, CantidadHoras, Dias, Semanal, Activo, DuracionAnios) VALUES (?, ?, ?, ?, ?, ?)');
            if (!$stmt) { throw new RuntimeException($this->con->error); }
            $stmt->bind_param('siiiii', $nombre, $horas, $dias, $semanal, $activo, $duracion);
            if (!$stmt->execute()) { throw new RuntimeException($stmt->error); }
            return $this->con->insert_id;
        } finally { $this->Desconectar(); }
    }

    public function AgregarCompleto($datosCurso, $datosTurno, $materias)
    {
        $this->Conectar();
        try {
            $this->asegurarEstructura();
            $this->con->begin_transaction();
            $stmtCurso = $this->con->prepare('INSERT INTO Curso (Nombre, CantidadHoras, Dias, Semanal, Activo, DuracionAnios) VALUES (?, ?, ?, ?, 1, ?)');
            $nombre = $datosCurso['nombre']; $horas = $datosCurso['horas']; $dias = $datosCurso['dias'];
            $semanal = $datosCurso['semanal']; $duracion = $datosCurso['duracion'];
            $stmtCurso->bind_param('siiii', $nombre, $horas, $dias, $semanal, $duracion);
            if (!$stmtCurso->execute()) { throw new RuntimeException($stmtCurso->error); }
            $idCurso = $this->con->insert_id;

            $stmtTurno = $this->con->prepare('INSERT INTO Turno (ID_C, HoraEntrada, HoraSalida, Turno) VALUES (?, ?, ?, ?)');
            $entrada = $datosTurno['entrada']; $salida = $datosTurno['salida']; $turno = $datosTurno['turno'];
            $stmtTurno->bind_param('isss', $idCurso, $entrada, $salida, $turno);
            if (!$stmtTurno->execute()) { throw new RuntimeException($stmtTurno->error); }

            if ($materias) {
                $stmtMateria = $this->con->prepare('INSERT INTO Curso_Materia (ID_C, ID_M, Anio, Periodo) VALUES (?, ?, ?, ?)');
                foreach ($materias as $materia) {
                    $idMateria = $materia['id']; $anioMateria = $materia['anio']; $periodo = $materia['periodo'];
                    $stmtMateria->bind_param('iiis', $idCurso, $idMateria, $anioMateria, $periodo);
                    if (!$stmtMateria->execute()) { throw new RuntimeException($stmtMateria->error); }
                }
            }
            $this->con->commit();
            return $idCurso;
        } catch (Throwable $e) {
            $this->con->rollback();
            throw $e;
        } finally { $this->Desconectar(); }
    }

    public function Listar()
    {
        $this->Conectar();
        try {
            $this->asegurarEstructura();
            $result = $this->con->query('SELECT * FROM Curso');
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } finally { $this->Desconectar(); }
    }

    public function IdCurso($id)
    {
        $this->Conectar();
        try {
            $this->asegurarEstructura();
            $stmt = $this->con->prepare('SELECT c.*, t.* FROM Curso c INNER JOIN Turno t ON c.ID_C = t.ID_C WHERE c.ID_C = ?');
            $stmt->bind_param('i', $id); $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } finally { $this->Desconectar(); }
    }

    public function ListarMateriasPorCurso($idCurso)
    {
        $this->Conectar();
        try {
            $this->asegurarEstructura();
            $stmt = $this->con->prepare('SELECT DISTINCT m.* FROM Curso_Materia cm INNER JOIN Materia m ON m.ID_M = cm.ID_M WHERE cm.ID_C = ? AND m.Activo = 1 ORDER BY m.Nombre');
            $stmt->bind_param('i', $idCurso); $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally { $this->Desconectar(); }
    }
}
