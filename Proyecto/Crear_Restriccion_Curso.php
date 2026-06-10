<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="post">
    <label>Curso:</label>
    <?php
    include_once "Logica/Curso.php";
    $curso = new Curso();
    $cursos = $curso->ListarCursos();
    ?>
    <select name="curso" required>
        <option value="">Seleccione un curso</option>
        <?php foreach ($cursos as $c) { ?>
            <option value="<?php echo $c['ID_C']; ?>">
                <?php echo $c['Nombre']; ?>
            </option>
        <?php } ?>
    </select>

    <button type="submit" name="enviar">Enviar</button>

    <?php
    include_once "Logica/Curso.php";

    $cursoSeleccionado = null;
    if (isset($_POST['curso'])) {
        $cursoSeleccionado = $_POST['curso'];
    }
    if (isset($_POST['enviar'])) {
    
    echo "Curso: " . $cursoSeleccionado; 
    echo "<br>";
    $cursos = $curso->ObtenerCursoPorId($cursoSeleccionado);
    if ($cursos) {
        echo "Nombre: " . $cursos['Nombre'] . "<br>";
        echo "Horas Diarias: " . $cursos['CantidadHoras'] . "<br>";
        echo "Dias: " . $cursos['Dias'] . "<br>";
        echo "Horas Semanales: " . $cursos['Semanal'] . "<br>";
        echo "Activo: " . $cursos['Activo'] . "<br>";
        echo "Hora de Entrada: " . $cursos['HoraEntrada'] . "<br>";
        echo "Hora de Salida: " . $cursos['HoraSalida'] . "<br>";
        echo "Turno: " . $cursos['Turno'] . "<br>";
    } else {
        echo "Curso no encontrado.";
    }

    }

    if ($cursoSeleccionado) {

        $cursos = $curso->ObtenerCursoPorId($cursoSeleccionado);

        $horas = $cursos['CantidadHoras'];
        $dias = $cursos['Dias'];
        $horaEntrada = $cursos['HoraEntrada'];


$idCurso = $cursoSeleccionado ?? 0;

// Generar bloques horarios de 45 minutos
$mapeoHoras = [];

$inicio = strtotime($horaEntrada);

for ($i = 1; $i <= $horas; $i++) {

    $fin = $inicio + (45 * 60);

    $mapeoHoras[$i] =
        date("H:i", $inicio) .
        " - " .
        date("H:i", $fin);

    $inicio = $fin;

    // Recreo de 5 minutos cada 2 bloques
    if ($i % 2 == 0 && $i < $horas) {
        $inicio += 5 * 60;
    }
}

// Mapear días
$mapeoDias = [
    1 => "Lunes",
    2 => "Martes",
    3 => "Miércoles",
    4 => "Jueves",
    5 => "Viernes",
    6 => "Sábado"
];

// Mostrar tabla de restricciones
echo "<h2>Tabla de restricciones</h2>";

echo "<table border='1' cellpadding='10' cellspacing='0'>";

// Encabezado
echo "<tr>";
echo "<th>Hora</th>";

for ($d = 1; $d <= $dias; $d++) {
    echo "<th>{$mapeoDias[$d]}</th>";
}

echo "</tr>";

// Filas
for ($h = 1; $h <= $horas; $h++) {

    echo "<tr>";

    echo "<td>{$mapeoHoras[$h]}</td>";

    for ($d = 1; $d <= $dias; $d++) {

        $disponible = $activo[$d][$h] ?? 0;

        $color = ($disponible == 1) ? "red" : "green";

        echo "
        <td>
            <button
                type='button'
                class='bloque'
                data-dia='{$d}'
                data-hora='{$h}'
                style='width:100%; height:40px; background-color:{$color}; border:none; cursor:pointer;'
            ></button>
        </td>";
    }
}

    echo "</tr>";
}

echo "</table>";

echo "<br><br>";
echo "<button type='button' id='confirmar'>Confirmar</button>";

    ?>

    
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            let botones = document.querySelectorAll(".bloque");
            let idCurso = <?php echo $idCurso; ?>;

            botones.forEach(btn => {
                btn.addEventListener("click", function () {
                    this.style.backgroundColor =
                        this.style.backgroundColor === "green" ? "red" : "green";
                });
            });

            // Click en encabezado (columna completa)
            let headers = document.querySelectorAll("th");
            headers.forEach((th, index) => {
                if (index === 0) return;

                th.style.cursor = "pointer";
                th.addEventListener("click", function () {
                    let filas = document.querySelectorAll("table tr");
                    let anyGreen = false;

                    filas.forEach((fila, i) => {
                        if (i === 0) return;
                        let boton = fila.children[index].querySelector("button");
                        if (boton && boton.style.backgroundColor === "green") {
                            anyGreen = true;
                        }
                    });

                    filas.forEach((fila, i) => {
                        if (i === 0) return;
                        let boton = fila.children[index].querySelector("button");
                        if (boton) {
                            boton.style.backgroundColor = anyGreen ? "red" : "green";
                        }
                    });
                });
            });

            // Click en botón confirmar
            document.getElementById("confirmar")?.addEventListener("click", function () {
                let datos = [];

                botones.forEach(btn => {
                    let activo = btn.style.backgroundColor === "red" ? 1 : 0;

                    datos.push({
                        dia: btn.dataset.dia,
                        hora: btn.dataset.hora,
                        activo: activo
                    });
                });

                let formData = new FormData();
                formData.append("datos", JSON.stringify(datos));
                formData.append("curso", idCurso);

                fetch("guardar_restricciones_curso.php", {
                    method: "POST",
                    body: formData
                })
                .then(async response => {
                    const text = await response.text();
                    try {
                        return JSON.parse(text);
                    } catch (err) {
                        console.error('Respuesta inválida de guardar_restricciones_curso.php:', text);
                        throw new Error('Respuesta inválida: ' + text);
                    }
                })
                .then(data => {
                    if (data.success) {
                        alert("Restricciones guardadas correctamente.");
                    } else {
                        alert("Error al guardar: " + data.error);
                    }
                })
                .catch(error => {
                    alert("Error de conexión: " + error.message);
                });
            });
        });
    </script>


</body>
</html>