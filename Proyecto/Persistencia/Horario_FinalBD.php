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
        $this->asegurarRelacionRestriccionHorario();
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

            $grupos = [];
            foreach ($asignaciones as $asignacion) {
                $faltan = (int) $asignacion['CargaHoraria'];
                $horasSeguidas = max(1, min((int) $asignacion['HorasSe'], $faltan));
                $ordenGrupo = 0;

                while ($faltan > 0) {
                    $cantidad = min($horasSeguidas, $faltan);
                    $grupos[] = [
                        'asignacion' => $asignacion,
                        'cantidad' => $cantidad,
                        'orden' => $ordenGrupo++,
                    ];
                    $faltan -= $cantidad;
                }
            }

            // Los grupos más largos son los más difíciles de ubicar.
            usort($grupos, function ($a, $b) {
                $porCantidad = $b['cantidad'] <=> $a['cantidad'];
                if ($porCantidad !== 0) {
                    return $porCantidad;
                }
                return $a['orden'] <=> $b['orden'];
            });

            $insert = $this->con->prepare(
                'INSERT INTO HorarioFinal (ID_Am, Año, Dia, HoraEntrada, HoraSalida, Disponible)
                 VALUES (?, ?, ?, ?, ?, 1)'
            );
            $insertRestriccion = $this->con->prepare(
                'INSERT INTO Restriccion_Docente
                    (ID_D, Dia, HoraEntrada, HoraSalida, ID_Hc)
                 VALUES (?, ?, ?, ?, ?)'
            );
            if (!$insert || !$insertRestriccion) {
                throw new RuntimeException('No se pudieron preparar las consultas para guardar el horario.');
            }
            $fecha = $anio . '-01-01';

            $gruposPorDia = [];
            foreach ($grupos as $grupo) {
                $asignacion = $grupo['asignacion'];
                $cantidad = (int) $grupo['cantidad'];
                $candidatos = $this->buscarGruposDisponibles(
                    $bloques,
                    $cantidad,
                    $asignacion,
                    $ocupadosCurso,
                    $ocupadosDocente,
                    $restriccionesDocente
                );

                if (!$candidatos) {
                    $pendientes[] = $asignacion['Materia'] . ': grupo de ' . $cantidad . ' bloque(s)';
                    continue;
                }

                usort($candidatos, function ($a, $b) use ($gruposPorDia, $asignacion) {
                    $id = (int) $asignacion['ID_Am'];
                    $puntajeA = ($gruposPorDia[$id][$a[0]['Dia']] ?? 0) * 100 + (int) $a[0]['Dia'];
                    $puntajeB = ($gruposPorDia[$id][$b[0]['Dia']] ?? 0) * 100 + (int) $b[0]['Dia'];
                    return $puntajeA <=> $puntajeB;
                });

                $elegidos = $candidatos[0];
                $idAsignacion = (int) $asignacion['ID_Am'];
                $docente = (int) $asignacion['ID_D'];
                $diaGrupo = (int) $elegidos[0]['Dia'];

                foreach ($elegidos as $elegido) {
                    $dia = (int) $elegido['Dia'];
                    $entrada = $elegido['HoraEntrada'];
                    $salida = $elegido['HoraSalida'];
                    $insert->bind_param('isiss', $idAsignacion, $fecha, $dia, $entrada, $salida);
                    if (!$insert->execute()) {
                        throw new RuntimeException('No se pudo guardar un bloque: ' . $insert->error);
                    }

                    $idHorario = (int) $this->con->insert_id;
                    $insertRestriccion->bind_param('iissi', $docente, $dia, $entrada, $salida, $idHorario);
                    if (!$insertRestriccion->execute()) {
                        throw new RuntimeException(
                            'No se pudo guardar la ocupación del docente: ' . $insertRestriccion->error
                        );
                    }

                    $clave = $this->claveBloque($elegido);
                    $ocupadosCurso[$clave] = $idAsignacion;
                    $ocupadosDocente[$docente][$dia][] = ['HoraEntrada' => $entrada, 'HoraSalida' => $salida];
                }
                $gruposPorDia[$idAsignacion][$diaGrupo] = ($gruposPorDia[$idAsignacion][$diaGrupo] ?? 0) + 1;
            }

            $insert->close();
            $insertRestriccion->close();
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
            'SELECT am.ID_Am, am.ID_D, m.Nombre AS Materia, m.CargaHoraria, m.HorasSe
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
        $resultado = $this->con->query(
            'SELECT ID_D, Dia, HoraEntrada, HoraSalida
             FROM Restriccion_Docente
             WHERE ID_Hc IS NULL'
        );
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

    private function buscarGruposDisponibles(
        $bloques,
        $cantidad,
        $asignacion,
        $ocupadosCurso,
        $ocupadosDocente,
        $restriccionesDocente
    ) {
        $porDia = [];
        foreach ($bloques as $bloque) {
            $porDia[(int) $bloque['Dia']][] = $bloque;
        }

        $candidatos = [];
        $idAsignacion = (int) $asignacion['ID_Am'];
        $docente = (int) $asignacion['ID_D'];

        foreach ($porDia as $dia => $bloquesDia) {
            $limite = count($bloquesDia) - $cantidad;
            for ($inicio = 0; $inicio <= $limite; $inicio++) {
                $grupo = array_slice($bloquesDia, $inicio, $cantidad);
                $disponible = true;

                foreach ($grupo as $indice => $bloque) {
                    if ($indice > 0) {
                        $finAnterior = strtotime($grupo[$indice - 1]['HoraSalida']);
                        $inicioActual = strtotime($bloque['HoraEntrada']);
                        $pausa = $inicioActual - $finAnterior;
                        if ($pausa < 0 || $pausa > 600) {
                            $disponible = false;
                            break;
                        }
                    }
                    $clave = $this->claveBloque($bloque);
                    if (isset($ocupadosCurso[$clave]) ||
                        $this->intervaloOcupado($ocupadosDocente[$docente][$dia] ?? [], $bloque) ||
                        $this->intervaloOcupado($restriccionesDocente[$docente][$dia] ?? [], $bloque)) {
                        $disponible = false;
                        break;
                    }
                }

                if (!$disponible) {
                    continue;
                }

                // Impide unir dos grupos de la misma materia y superar HorasSe.
                $anterior = $inicio > 0 ? $bloquesDia[$inicio - 1] : null;
                $siguiente = ($inicio + $cantidad) < count($bloquesDia)
                    ? $bloquesDia[$inicio + $cantidad]
                    : null;
                if (($anterior && ($ocupadosCurso[$this->claveBloque($anterior)] ?? null) === $idAsignacion) ||
                    ($siguiente && ($ocupadosCurso[$this->claveBloque($siguiente)] ?? null) === $idAsignacion)) {
                    continue;
                }

                $candidatos[] = $grupo;
            }
        }

        return $candidatos;
    }

    private function claveBloque($bloque)
    {
        return $bloque['Dia'] . '|' . $bloque['HoraEntrada'] . '|' . $bloque['HoraSalida'];
    }

    private function asegurarRelacionRestriccionHorario()
    {
        $resultado = $this->con->query("SHOW COLUMNS FROM Restriccion_Docente LIKE 'ID_Hc'");
        if (!$resultado) {
            throw new RuntimeException('No se pudo comprobar la estructura de Restriccion_Docente.');
        }
        $existe = $resultado->num_rows > 0;
        $resultado->close();

        if ($existe) {
            return;
        }

        $sql = 'ALTER TABLE Restriccion_Docente
                ADD COLUMN ID_Hc INT NULL AFTER ID_D,
                ADD UNIQUE KEY UQ_Restriccion_Docente_Horario (ID_Hc),
                ADD CONSTRAINT FK_Restriccion_Docente_Horario
                    FOREIGN KEY (ID_Hc) REFERENCES HorarioFinal (ID_Hc)
                    ON DELETE CASCADE ON UPDATE CASCADE';
        if (!$this->con->query($sql)) {
            throw new RuntimeException(
                'No se pudo actualizar Restriccion_Docente para vincular el horario: ' . $this->con->error
            );
        }
    }
}
