<?php
include_once __DIR__ . '/../Conexion.php';

class Restriccion_CursoBD extends Conexion
{
    public function Agregar($id_c, $dia, $hora_I, $hora_F, $disponible)
    {
        $this->Conectar();

        $sql = "
            INSERT INTO Restriccion_Curso
                (ID_C, Dia, HoraEntrada, HoraSalida, Disponible)
            VALUES
                (?, ?, ?, ?, ?)
        ";

        $stmt = $this->con->prepare($sql);

        if (!$stmt) {
            $error = $this->con->error;
            $this->Desconectar();
            throw new Exception('Error al preparar la consulta: ' . $error);
        }

        $stmt->bind_param('iissi', $id_c, $dia, $hora_I, $hora_F, $disponible);

        $resultado = $stmt->execute();

        if (!$resultado) {
            $error = $stmt->error;
            $stmt->close();
            $this->Desconectar();
            throw new Exception('Error al guardar la restricción: ' . $error);
        }

        $stmt->close();
        $this->Desconectar();

        return true;
    }

    public function ListarCurso($idCurso)
    {
        $this->Conectar();

        $sql = "
            SELECT ID_Rc, ID_C, Dia, HoraEntrada, HoraSalida, Disponible
            FROM Restriccion_Curso
            WHERE ID_C = ?
            ORDER BY Dia, HoraEntrada
        ";

        $stmt = $this->con->prepare($sql);

        if (!$stmt) {
            $error = $this->con->error;
            $this->Desconectar();
            throw new Exception('Error al preparar la consulta: ' . $error);
        }

        $stmt->bind_param('i', $idCurso);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $restricciones = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $this->Desconectar();

        return $restricciones;
    }

    public function ObtenerRestriccionesCurso($idCurso)
    {
        return $this->ListarCurso($idCurso);
    }
}
