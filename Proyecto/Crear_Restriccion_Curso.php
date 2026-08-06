<?php
include_once __DIR__ . "/Logica/Curso.php";
include_once __DIR__ . "/Logica/Turno.php";
include_once __DIR__ . "/Logica/Restricciones/Restriccion_Curso.php";

$curso = new Curso();
$cursos = $curso->ListarCursos();

$cursoSeleccionado = isset($_POST['curso']) ? (int) $_POST['curso'] : 0;
$datosCurso = null;
$mensajeError = '';
$mapeoHoras = [];
$activo = [];
$dias = 0;
$idCurso = $cursoSeleccionado;

$mapeoDias = [
    1 => "Lunes",
    2 => "Martes",
    3 => "Miércoles",
    4 => "Jueves",
    5 => "Viernes",
    6 => "Sábado",
    7 => "Domingo"
];

if ($cursoSeleccionado > 0) {
    $datosCurso = $curso->ObtenerCursoPorId($cursoSeleccionado);

    if (!$datosCurso) {
        $mensajeError = 'Curso no encontrado.';
    } else {
        $dias = min((int) $datosCurso['Dias'], count($mapeoDias));

        $turno = new Turno();
        $datosTurno = $turno->ObtenerTurnoCurso($cursoSeleccionado);

        if (!$datosTurno) {
            $mensajeError = 'No se encontró el turno asociado al curso.';
        } else {
            $horaEntrada = $datosTurno['HoraEntrada'];
            $horaSalida = $datosTurno['HoraSalida'];

            $inicio = strtotime($horaEntrada);
            $finCurso = strtotime($horaSalida);
            $numeroBloque = 1;

            if ($inicio === false || $finCurso === false || $finCurso <= $inicio) {
                $mensajeError = 'El horario del turno no es válido.';
            } else {
                while ($inicio < $finCurso) {
                    $finBloque = $inicio + (45 * 60);

                    if ($finBloque > $finCurso) {
                        break;
                    }

                    $mapeoHoras[$numeroBloque] = [
                        'entrada' => date('H:i', $inicio),
                        'salida' => date('H:i', $finBloque)
                    ];

                    $inicio = $finBloque;

                    if ($numeroBloque % 2 === 0) {
                        $inicio += 5 * 60;
                    }

                    $numeroBloque++;
                }

                $restriccion = new Restriccion_Curso();
                $restriccionesGuardadas = $restriccion->ObtenerRestriccionesCurso($cursoSeleccionado);

                foreach ($restriccionesGuardadas as $fila) {
                    $diaGuardado = isset($fila['Dia']) ? (int) $fila['Dia'] : 0;
                    $horaEntradaGuardada = isset($fila['HoraEntrada'])
                        ? date('H:i', strtotime($fila['HoraEntrada']))
                        : null;
                    $horaSalidaGuardada = isset($fila['HoraSalida'])
                        ? date('H:i', strtotime($fila['HoraSalida']))
                        : null;
                    $disponibleGuardado = isset($fila['Disponible'])
                        ? (int) $fila['Disponible']
                        : 0;

                    if ($diaGuardado <= 0 || $horaEntradaGuardada === null || $horaSalidaGuardada === null) {
                        continue;
                    }

                    foreach ($mapeoHoras as $indiceHora => $bloque) {
                        if (
                            $bloque['entrada'] === $horaEntradaGuardada &&
                            $bloque['salida'] === $horaSalidaGuardada
                        ) {
                            $activo[$diaGuardado][$indiceHora] = $disponibleGuardado;
                            break;
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restricciones de curso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 8px;
            text-align: center;
        }

        .encabezado-dia {
            cursor: pointer;
            user-select: none;
        }

        .bloque {
            width: 100%;
            min-width: 85px;
            height: 40px;
            border: none;
            cursor: pointer;
        }

        .mensaje-error {
            color: #b00020;
            font-weight: bold;
        }

        .leyenda {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <form method="post">
        <label for="curso"><strong>Curso:</strong></label>

        <select id="curso" name="curso" required>
            <option value="">Seleccione un curso</option>

            <?php foreach ($cursos as $c): ?>
                <option
                    value="<?php echo (int) $c['ID_C']; ?>"
                    <?php echo ((int) $c['ID_C'] === $cursoSeleccionado) ? 'selected' : ''; ?>
                >
                    <?php echo htmlspecialchars($c['Nombre'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="enviar">Mostrar</button>
    </form>

    <?php if ($mensajeError !== ''): ?>
        <p class="mensaje-error">
            <?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <?php if ($datosCurso && $mensajeError === ''): ?>
        <h2>Datos del curso</h2>

        <p>
            <strong>ID:</strong> <?php echo (int) $cursoSeleccionado; ?><br>
            <strong>Nombre:</strong> <?php echo htmlspecialchars($datosCurso['Nombre'], ENT_QUOTES, 'UTF-8'); ?><br>
            <strong>Horas diarias:</strong> <?php echo htmlspecialchars($datosCurso['CantidadHoras'], ENT_QUOTES, 'UTF-8'); ?><br>
            <strong>Días:</strong> <?php echo (int) $datosCurso['Dias']; ?><br>
            <strong>Horas semanales:</strong> <?php echo (int) $datosCurso['Semanal']; ?><br>
            <strong>Activo:</strong> <?php echo (int) $datosCurso['Activo']; ?><br>
            <strong>Hora de entrada:</strong> <?php echo htmlspecialchars($datosCurso['HoraEntrada'], ENT_QUOTES, 'UTF-8'); ?><br>
            <strong>Hora de salida:</strong> <?php echo htmlspecialchars($datosCurso['HoraSalida'], ENT_QUOTES, 'UTF-8'); ?><br>
            <strong>Turno:</strong> <?php echo htmlspecialchars($datosCurso['Turno'], ENT_QUOTES, 'UTF-8'); ?>
        </p>

        <h2>Tabla de restricciones</h2>

        <p class="leyenda">
            <strong>Verde:</strong> disponible |
            <strong>Rojo:</strong> restringido
        </p>

        <?php if (empty($mapeoHoras)): ?>
            <p class="mensaje-error">No se pudieron generar bloques horarios para este curso.</p>
        <?php else: ?>
            <table id="tabla-restricciones">
                <thead>
                    <tr>
                        <th>Hora</th>

                        <?php for ($d = 1; $d <= $dias; $d++): ?>
                            <th class="encabezado-dia" data-dia="<?php echo $d; ?>">
                                <?php echo htmlspecialchars($mapeoDias[$d], ENT_QUOTES, 'UTF-8'); ?>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($mapeoHoras as $h => $bloque): ?>
                        <tr>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $bloque['entrada'] . ' - ' . $bloque['salida'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>

                            <?php for ($d = 1; $d <= $dias; $d++): ?>
                                <?php
                                $restringido = $activo[$d][$h] ?? 0;
                                $color = ($restringido === 1) ? 'red' : 'green';
                                ?>

                                <td>
                                    <button
                                        type="button"
                                        class="bloque"
                                        data-dia="<?php echo $d; ?>"
                                        data-entrada="<?php echo htmlspecialchars($bloque['entrada'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-salida="<?php echo htmlspecialchars($bloque['salida'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-activo="<?php echo $restringido; ?>"
                                        style="background-color: <?php echo $color; ?>;"
                                        aria-label="Cambiar restricción del día <?php echo $d; ?>, bloque <?php echo $h; ?>"
                                    ></button>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <br>
            <button type="button" id="confirmar">Confirmar</button>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const botones = document.querySelectorAll(".bloque");
            const encabezados = document.querySelectorAll(".encabezado-dia");
            const idCurso = <?php echo json_encode($idCurso); ?>;

            botones.forEach(function (boton) {
                boton.addEventListener("click", function () {
                    const restringido = this.dataset.activo === "1";

                    this.dataset.activo = restringido ? "0" : "1";
                    this.style.backgroundColor = restringido ? "green" : "red";
                });
            });

            encabezados.forEach(function (encabezado) {
                encabezado.addEventListener("click", function () {
                    const dia = this.dataset.dia;
                    const botonesDia = document.querySelectorAll(
                        ".bloque[data-dia='" + dia + "']"
                    );

                    let existeDisponible = false;

                    botonesDia.forEach(function (boton) {
                        if (boton.dataset.activo === "0") {
                            existeDisponible = true;
                        }
                    });

                    botonesDia.forEach(function (boton) {
                        boton.dataset.activo = existeDisponible ? "1" : "0";
                        boton.style.backgroundColor = existeDisponible ? "red" : "green";
                    });
                });
            });

            const botonConfirmar = document.getElementById("confirmar");

            if (botonConfirmar) {
                botonConfirmar.addEventListener("click", function () {
                    const datos = [];

                    botones.forEach(function (boton) {
                        datos.push({
                            dia: Number.parseInt(boton.dataset.dia, 10),
                            hora_I: boton.dataset.entrada,
                            hora_F: boton.dataset.salida,
                            activo: Number.parseInt(boton.dataset.activo, 10)
                        });
                    });

                    const formData = new FormData();
                    formData.append("datos", JSON.stringify(datos));
                    formData.append("curso", idCurso);

                    fetch("guardar_restricciones_curso.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(async function (response) {
                        const texto = await response.text();

                        let respuesta;

                        try {
                            respuesta = JSON.parse(texto);
                        } catch (error) {
                            console.error("Respuesta recibida:", texto);
                            throw new Error("El servidor no devolvió un JSON válido.");
                        }

                        if (!response.ok) {
                            throw new Error(respuesta.error || "Error HTTP " + response.status);
                        }

                        return respuesta;
                    })
                    .then(function (respuesta) {
                        if (respuesta.success) {
                            alert("Restricciones guardadas correctamente.");
                        } else {
                            alert("Error al guardar: " + (respuesta.error || "Error desconocido"));
                        }
                    })
                    .catch(function (error) {
                        console.error(error);
                        alert("No se pudieron guardar las restricciones: " + error.message);
                    });
                });
            }
        });
    </script>
</body>
</html>
