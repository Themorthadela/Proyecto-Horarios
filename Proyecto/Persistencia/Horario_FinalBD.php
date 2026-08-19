<?php
include_once __DIR__ . '/Conexion.php';

class Horario_FinalBD extends Conexion
{
    public function Guardar($idAsignacion, $anio, $dia, $horaEntrada, $horaSalida, $disponible)
    {
        $this->Conectar();
        $sql = 'INSERT INTO HorarioFinal (ID_Am, Año, Dia, HoraEntrada, HoraSalida, Disponible)
                VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = $this->con->prepare($sql);

        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $this->con->error);
        }

        $fecha = preg_match('/^\d{4}$/', (string) $anio) ? $anio . '-01-01' : $anio;
        $stmt->bind_param('isissi', $idAsignacion, $fecha, $dia, $horaEntrada, $horaSalida, $disponible);
        $resultado = $stmt->execute();
        $error = $stmt->error;
        $stmt->close();
        $this->Desconectar();

        if (!$resultado) {
            throw new Exception('Error al guardar el horario: ' . $error);
        }

        return true;
    }

    public function Listar()
    {
        return $this->consultarHorarios();
    }

    public function ListarPorCurso($idCurso, $anio = null)
    {
        return $this->consultarHorarios((int) $idCurso, $anio);
    }

    private function consultarHorarios($idCurso = null, $anio = null)
    {
        $this->Conectar();
        $condiciones = [];
        $tipos = '';
        $valores = [];

        if ($idCurso !== null) {
            $condiciones[] = 'am.ID_C = ?';
            $tipos .= 'i';
            $valores[] = $idCurso;
        }
        if ($anio !== null) {
            $condiciones[] = 'YEAR(hf.Año) = ?';
            $tipos .= 'i';
            $valores[] = (int) $anio;
        }

        $sql = 'SELECT hf.*, am.ID_C, am.ID_D, am.ID_M,
                       c.Nombre AS Curso, m.Nombre AS Materia,
                       CONCAT(u.Nombre, " ", u.Apellido) AS Docente
                FROM HorarioFinal hf
                INNER JOIN Asignacion_Materia am ON am.ID_Am = hf.ID_Am
                INNER JOIN Curso c ON c.ID_C = am.ID_C
                INNER JOIN Materia m ON m.ID_M = am.ID_M
                INNER JOIN Docente d ON d.ID_D = am.ID_D
                INNER JOIN Usuario u ON u.ID_U = d.ID_U';
        if ($condiciones) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }
        $sql .= ' ORDER BY hf.Dia, hf.HoraEntrada, m.Nombre';

        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $this->con->error);
        }
        if ($valores) {
            $stmt->bind_param($tipos, ...$valores);
        }
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $this->Desconectar();
        return $resultado;
    }

    public function GenerarPorCurso($idCurso, $anio)
    {
        $idCurso = (int) $idCurso;
        $anio = (int) $anio;
        if ($idCurso <= 0 || $anio < 2000 || $anio > 2100) {
            throw new InvalidArgumentException('Curso o año inválido.');
        }

        $this->Conectar();
        $this->con->begin_transaction();

        try {
            $asignaciones = $this->obtenerAsignaciones($idCurso);
            if (!$asignaciones) {
                throw new RuntimeException('El curso no tiene materias asignadas y activas.');
            }

            $bloques = $this->obtenerBloquesDisponibles($idCurso);
            if (!$bloques) {
                throw new RuntimeException('El curso no tiene bloques disponibles configurados.');
            }

            $delete = $this->con->prepare(
                'DELETE hf FROM HorarioFinal hf
                 INNER JOIN Asignacion_Materia am ON am.ID_Am = hf.ID_Am
                 WHERE am.ID_C = ? AND YEAR(hf.Año) = ?'
            );
            $delete->bind_param('ii', $idCurso, $anio);
            $delete->execute();
            $delete->close();

            $ocupadosCurso = [];
            $ocupadosDocente = $this->obtenerOcupacionDocente($anio, $idCurso);
            $restriccionesDocente = $this->obtenerRestriccionesDocente();
            $pendientes = [];

            usort($asignaciones, function ($a, $b) {
                return (int) $b['CargaHoraria'] <=> (int) $a['CargaHoraria'];
            });

            $insert = $this->con->prepare(
                'INSERT INTO HorarioFinal (ID_Am, Año, Dia, HoraEntrada, HoraSalida, Disponible)
                 VALUES (?, ?, ?, ?, ?, 1)'
            );
            $fecha = $anio . '-01-01';

            foreach ($asignaciones as $asignacion) {
                $faltan = (int) $asignacion['CargaHoraria'];
                $usadosPorDia = [];

                while ($faltan > 0) {
                    $elegido = null;
                    $mejorPuntaje = PHP_INT_MAX;

                    foreach ($bloques as $bloque) {
                        $clave = $bloque['Dia'] . '|' . $bloque['HoraEntrada'] . '|' . $bloque['HoraSalida'];
                        $docente = (int) $asignacion['ID_D'];
                        if (isset($ocupadosCurso[$clave]) ||
                            $this->intervaloOcupado($ocupadosDocente[$docente][$bloque['Dia']] ?? [], $bloque) ||
                            $this->intervaloOcupado($restriccionesDocente[$docente][$bloque['Dia']] ?? [], $bloque)) {
                            continue;
                        }

                        $puntaje = ($usadosPorDia[$bloque['Dia']] ?? 0) * 100 + (int) $bloque['Dia'];
                        if ($puntaje < $mejorPuntaje) {
                            $mejorPuntaje = $puntaje;
                            $elegido = $bloque;
                        }
                    }

                    if ($elegido === null) {
                        $pendientes[] = $asignacion['Materia'] . ': ' . $faltan . ' bloque(s)';
                        break;
                    }

                    $idAsignacion = (int) $asignacion['ID_Am'];
                    $dia = (int) $elegido['Dia'];
                    $entrada = $elegido['HoraEntrada'];
                    $salida = $elegido['HoraSalida'];
                    $insert->bind_param('isiss', $idAsignacion, $fecha, $dia, $entrada, $salida);
                    if (!$insert->execute()) {
                        throw new RuntimeException('No se pudo guardar un bloque: ' . $insert->error);
                    }

                    $clave = $dia . '|' . $entrada . '|' . $salida;
                    $ocupadosCurso[$clave] = true;
                    $ocupadosDocente[(int) $asignacion['ID_D']][$dia][] = ['HoraEntrada' => $entrada, 'HoraSalida' => $salida];
                    $usadosPorDia[$dia] = ($usadosPorDia[$dia] ?? 0) + 1;
                    $faltan--;
                }
            }

            $insert->close();
            $this->con->commit();
            $this->Desconectar();
            return $pendientes;
        } catch (Throwable $e) {
            $this->con->rollback();
            $this->Desconectar();
            throw $e;
        }
    }

    private function obtenerAsignaciones($idCurso)
    {
        $stmt = $this->con->prepare(
            'SELECT am.ID_Am, am.ID_D, m.Nombre AS Materia, m.CargaHoraria
             FROM Asignacion_Materia am
             INNER JOIN Materia m ON m.ID_M = am.ID_M
             WHERE am.ID_C = ? AND m.Activo = 1'
        );
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $filas;
    }

    private function obtenerBloquesDisponibles($idCurso)
    {
        $stmt = $this->con->prepare(
            'SELECT Dia, HoraEntrada, HoraSalida
             FROM Restriccion_Curso
             WHERE ID_C = ? AND Disponible = 0
             ORDER BY Dia, HoraEntrada'
        );
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $filas;
    }

    private function obtenerOcupacionDocente($anio, $cursoExcluido)
    {
        $stmt = $this->con->prepare(
            'SELECT am.ID_D, hf.Dia, hf.HoraEntrada, hf.HoraSalida
             FROM HorarioFinal hf
             INNER JOIN Asignacion_Materia am ON am.ID_Am = hf.ID_Am
             WHERE YEAR(hf.Año) = ? AND am.ID_C <> ?'
        );
        $stmt->bind_param('ii', $anio, $cursoExcluido);
        $stmt->execute();
        $mapa = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $fila) {
            $mapa[(int) $fila['ID_D']][(int) $fila['Dia']][] = $fila;
        }
        $stmt->close();
        return $mapa;
    }

    private function obtenerRestriccionesDocente()
    {
        $resultado = $this->con->query('SELECT ID_D, Dia, HoraEntrada, HoraSalida FROM Restriccion_Docente');
        $mapa = [];
        while ($fila = $resultado->fetch_assoc()) {
            $mapa[(int) $fila['ID_D']][(int) $fila['Dia']][] = $fila;
        }
        $resultado->close();
        return $mapa;
    }

    private function intervaloOcupado($intervalos, $bloque)
    {
        foreach ($intervalos as $intervalo) {
            if ($bloque['HoraEntrada'] < $intervalo['HoraSalida'] &&
                $bloque['HoraSalida'] > $intervalo['HoraEntrada']) {
                return true;
            }
        }
        return false;
    }
}
