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

    public function ListarPorCurso($idCurso, $anio = null, $anioCurso = null)
    {
        return $this->consultarHorarios((int) $idCurso, $anio, null, true, null, $anioCurso);
    }

    public function ListarVisiblesPorCurso($idCurso, $anio, $usuario, $idDocente = 0)
    {
        $rol = $usuario['rol'] ?? '';
        $idUsuarioDocente = $rol === 'Docente' ? (int) ($usuario['id'] ?? 0) : null;
        $mostrarDocentes = in_array($rol, ['Admin', 'Director', 'Secretaria'], true);
        $filtroDocente = $mostrarDocentes && (int) $idDocente > 0 ? (int) $idDocente : null;
        $filtroCurso = ($rol === 'Docente' || $filtroDocente !== null) && (int) $idCurso <= 0 ? null : (int) $idCurso;
        return $this->consultarHorarios($filtroCurso, $anio, $idUsuarioDocente, $mostrarDocentes, $filtroDocente);
    }

    private function consultarHorarios($idCurso = null, $anio = null, $idUsuarioDocente = null, $mostrarDocentes = true, $idDocente = null, $anioCurso = null)
    {
        $this->Conectar();
        $this->asegurarAnioCurso();
        $condiciones = [];
        $tipos = '';
        $valores = [];
        if ($anioCurso !== null) {
            $condiciones[] = 'hf.AnioCurso = ?';
            $tipos .= 'i';
            $valores[] = (int) $anioCurso;
        }

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

        if ($idUsuarioDocente !== null) {
            $condiciones[] = 'd.ID_U = ?';
            $tipos .= 'i';
            $valores[] = $idUsuarioDocente;
        }

        if ($idDocente !== null) {
            $condiciones[] = 'am.ID_D = ?';
            $tipos .= 'i';
            $valores[] = $idDocente;
        }

        $nombreDocente = $mostrarDocentes ? 'CONCAT(u.Nombre, " ", u.Apellido)' : 'NULL';
        $sql = 'SELECT hf.*, am.ID_C, am.ID_D, am.ID_M,
                       c.Nombre AS Curso, m.Nombre AS Materia,
                       ' . $nombreDocente . ' AS Docente
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

    public function GenerarPorCurso($idCurso, $anio, $anioCurso = 1)
    {
        $idCurso = (int) $idCurso;
        $anio = (int) $anio;
        $anioCurso = (int) $anioCurso;
        if ($idCurso <= 0 || $anio < 2000 || $anio > 2100) {
            throw new InvalidArgumentException('Curso o año inválido.');
        }

        $this->Conectar();
        $this->asegurarAnioCurso();
        $this->asegurarRelacionRestriccionHorario();
        $this->con->begin_transaction();

        try {
            $validar = $this->con->prepare('SELECT DuracionAnios FROM Curso WHERE ID_C = ?');
            $validar->bind_param('i', $idCurso);
            $validar->execute();
            $datosCurso = $validar->get_result()->fetch_assoc();
            $validar->close();
            if (!$datosCurso || $anioCurso < 1 || $anioCurso > (int) $datosCurso['DuracionAnios']) {
                throw new InvalidArgumentException('Seleccione un año válido dentro de la duración del curso.');
            }
            $asignaciones = $this->obtenerAsignaciones($idCurso, $anioCurso);
            if (!$asignaciones) {
                throw new RuntimeException('El curso no tiene materias asignadas y activas.');
            }

            $bloques = $this->obtenerBloquesDisponibles($idCurso);
            if (!$bloques) {
                throw new RuntimeException('El curso no tiene bloques disponibles para la duración de su turno. Revisá y guardá las restricciones del curso.');
            }

            $delete = $this->con->prepare(
                'DELETE hf FROM HorarioFinal hf
                 INNER JOIN Asignacion_Materia am ON am.ID_Am = hf.ID_Am
                 WHERE am.ID_C = ? AND YEAR(hf.Año) = ? AND hf.AnioCurso = ?'
            );
            $delete->bind_param('iii', $idCurso, $anio, $anioCurso);
            $delete->execute();
            $delete->close();

            $ocupadosCurso = [];
            $ocupadosDocente = $this->obtenerOcupacionDocente($anio, $idCurso, $anioCurso);
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
                'INSERT INTO HorarioFinal (ID_Am, Año, Dia, HoraEntrada, HoraSalida, Disponible, AnioCurso)
                 VALUES (?, ?, ?, ?, ?, 1, ?)'
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
            $cargaPorDia = [];
            $salidaPorDia = [];
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

                $this->ordenarCandidatosEquilibrados($candidatos, $cargaPorDia, $salidaPorDia,
                    $gruposPorDia[(int) $asignacion['ID_Am']] ?? []);

                $elegidos = $candidatos[0];
                $idAsignacion = (int) $asignacion['ID_Am'];
                $docente = (int) $asignacion['ID_D'];
                $diaGrupo = (int) $elegidos[0]['Dia'];
                $cargaPorDia[$diaGrupo] = ($cargaPorDia[$diaGrupo] ?? 0) + count($elegidos);
                $salidaPorDia[$diaGrupo] = max($salidaPorDia[$diaGrupo] ?? '', $elegidos[count($elegidos) - 1]['HoraSalida']);

                foreach ($elegidos as $elegido) {
                    $dia = (int) $elegido['Dia'];
                    $entrada = $elegido['HoraEntrada'];
                    $salida = $elegido['HoraSalida'];
                    $insert->bind_param('isissi', $idAsignacion, $fecha, $dia, $entrada, $salida, $anioCurso);
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

    private function ordenarCandidatosEquilibrados(&$candidatos, $cargaPorDia, $salidaPorDia, $gruposMateriaPorDia)
    {
        // Reparte la carga semanal; entre días equivalentes, evita salidas tardías
        // y ocupa los primeros bloques disponibles para reducir huecos.
        $puntaje = static function ($grupo) use ($cargaPorDia, $salidaPorDia, $gruposMateriaPorDia) {
            $dia = (int) $grupo[0]['Dia'];
            return [
                ($cargaPorDia[$dia] ?? 0) + count($grupo),
                max($salidaPorDia[$dia] ?? '', $grupo[count($grupo) - 1]['HoraSalida']),
                $grupo[0]['HoraEntrada'],
                $gruposMateriaPorDia[$dia] ?? 0,
                $dia,
            ];
        };
        usort($candidatos, static fn ($a, $b) => $puntaje($a) <=> $puntaje($b));
    }

    private function obtenerAsignaciones($idCurso, $anioCurso)
    {
        $stmt = $this->con->prepare(
            'SELECT am.ID_Am, am.ID_D, m.Nombre AS Materia, m.CargaHoraria, m.HorasSe
             FROM Asignacion_Materia am
             INNER JOIN Materia m ON m.ID_M = am.ID_M
             WHERE am.ID_C = ? AND m.Activo = 1
               AND (EXISTS (SELECT 1 FROM Curso_Materia cm WHERE cm.ID_C = am.ID_C AND cm.ID_M = am.ID_M AND cm.Anio = ?)
                    OR (? = 1 AND NOT EXISTS (SELECT 1 FROM Curso_Materia cm WHERE cm.ID_C = am.ID_C)))'
        );
        $stmt->bind_param('iii', $idCurso, $anioCurso, $anioCurso);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $filas;
    }

    private function obtenerBloquesDisponibles($idCurso)
    {
        $stmt = $this->con->prepare(
            'SELECT c.Dias, t.HoraEntrada, t.HoraSalida, t.Turno
             FROM Curso c INNER JOIN Turno t ON t.ID_C = c.ID_C WHERE c.ID_C = ?'
        );
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $turno = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$turno) { return []; }

        // Solo los intervalos marcados como no disponibles bloquean el turno.
        $stmt = $this->con->prepare(
            'SELECT Dia, HoraEntrada, HoraSalida FROM Restriccion_Curso WHERE ID_C = ? AND Disponible = 1'
        );
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $restricciones = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $fila) {
            $restricciones[(int) $fila['Dia']][] = $fila;
        }
        $stmt->close();

        $duracion = strcasecmp(trim($turno['Turno']), 'Nocturno') === 0 ? 40 : 45;
        $fin = strtotime($turno['HoraSalida']);
        $bloques = [];
        for ($dia = 1; $dia <= min(7, (int) $turno['Dias']); $dia++) {
            $inicio = strtotime($turno['HoraEntrada']);
            $numero = 1;
            while ($inicio + $duracion * 60 <= $fin) {
                $salida = $inicio + $duracion * 60;
                $bloque = ['Dia' => $dia, 'HoraEntrada' => date('H:i:s', $inicio), 'HoraSalida' => date('H:i:s', $salida)];
                if (!$this->intervaloOcupado($restricciones[$dia] ?? [], $bloque)) {
                    $bloques[] = $bloque;
                }
                $inicio = $salida + ($numero % 2 === 0 ? 5 * 60 : 0);
                $numero++;
            }
        }
        return $bloques;
    }

    private function obtenerOcupacionDocente($anio, $cursoExcluido, $anioCurso)
    {
        $stmt = $this->con->prepare(
            'SELECT am.ID_D, hf.Dia, hf.HoraEntrada, hf.HoraSalida
             FROM HorarioFinal hf
             INNER JOIN Asignacion_Materia am ON am.ID_Am = hf.ID_Am
             WHERE YEAR(hf.Año) = ? AND (am.ID_C <> ? OR hf.AnioCurso <> ?)'
        );
        $stmt->bind_param('iii', $anio, $cursoExcluido, $anioCurso);
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
            $inicioOcupado = null;
            $finOcupado = null;
            foreach ($bloquesDia as $bloqueDia) {
                if (isset($ocupadosCurso[$this->claveBloque($bloqueDia)])) {
                    $inicioOcupado = $inicioOcupado === null
                        ? $bloqueDia['HoraEntrada'] : min($inicioOcupado, $bloqueDia['HoraEntrada']);
                    $finOcupado = $finOcupado === null
                        ? $bloqueDia['HoraSalida'] : max($finOcupado, $bloqueDia['HoraSalida']);
                }
            }
            $limite = count($bloquesDia) - $cantidad;
            for ($inicio = 0; $inicio <= $limite; $inicio++) {
                $grupo = array_slice($bloquesDia, $inicio, $cantidad);
                // La jornada crece por sus extremos, sin dejar bloques libres entre materias.
                if ($inicioOcupado !== null) {
                    $entradaGrupo = $grupo[0]['HoraEntrada'];
                    $salidaGrupo = $grupo[count($grupo) - 1]['HoraSalida'];
                    $pausaDespues = strtotime($entradaGrupo) - strtotime($finOcupado);
                    $pausaAntes = strtotime($inicioOcupado) - strtotime($salidaGrupo);
                    if (!(($pausaDespues >= 0 && $pausaDespues <= 600) ||
                          ($pausaAntes >= 0 && $pausaAntes <= 600))) {
                        continue;
                    }
                }
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

    private function asegurarAnioCurso()
    {
        $resultado = $this->con->query("SHOW COLUMNS FROM HorarioFinal LIKE 'AnioCurso'");
        $existe = $resultado && $resultado->num_rows > 0;
        if ($resultado) { $resultado->close(); }
        if (!$existe && !$this->con->query('ALTER TABLE HorarioFinal ADD COLUMN AnioCurso TINYINT UNSIGNED NOT NULL DEFAULT 1')) {
            throw new RuntimeException('No se pudo agregar el año del curso al horario.');
        }
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
