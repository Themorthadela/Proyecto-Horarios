<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1 style="color: red;">Tabla de restricciones de horario del docente</h1>

    <form method="post">

        <label>Docente:</label>
        <?php
        include_once "Logica/Docente.php";
        $docente = new Docente();
        $docentes = $docente->GradoDocente();
        ?>
        <select name="docente" required>
            <option value="">Seleccione un docente</option>
            <?php foreach ($docentes as $d) { ?>
                <option value="<?php echo $d['ID_D']; ?>">
                    <?php echo $d['Nombre'] . " " . $d['Apellido']; ?>
                </option>
            <?php } ?>
        </select>

        <input type="submit" name="enviar" value="Generar Tabla">        
    </form>

    <a href="index.php">Volver</a>

    <?php
        if (isset($_POST['enviar'])) {

            $idDocente = $_POST['docente'];
            $idCurso = $_POST['curso'];

            $docente = new Docente();
            $datosDocente = $docente->ObtenerDocentePorId($idDocente);

            // Obtener restricciones del docente para el curso seleccionado
            include_once "Logica/Restriccion.php";
            $restriccion = new Restriccion_Docente();
            $restriccionesDocente = $restriccion->ListarRestricciones($idDocente);

            $estado = [];
            foreach ($restriccionesDocente as $r) {
                $estado[$r['Dia']][$r['Hora']] = $r['Disponible'];
            }
            
        // Mapear de Horas
            $mapeoHoras = [
                1 => "Primera",
                2 => "Segunda",
                3 => "Tercera",
                4 => "Cuarta",
                5 => "Quinta",
                6 => "Sexta",
                7 => "Séptima",
                8 => "Octava"
            ];
        // Mapear días a nombres
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
            echo "<p>Docente: " . $datosDocente['Nombre'] . " " . $datosDocente['Apellido'] . "</p>";

            echo "<table border='1' cellpadding='10'>";

            // Encabezado
            echo "<tr><th>Hora/Día</th>";
            for ($d=1; $d <= $dias; $d++) {
                echo "<th>{$mapeoDias[$d]}</th>";
            }
            echo "</tr>";
            // Filas de horas
            for ($h=1; $h <= $horas; $h++) {
                echo "<tr>";
                echo "<td>{$mapeoHoras[$h]}</td>";
                for ($d=1; $d <= $dias; $d++) {

                    $estado = $estado[$d][$h] ?? 0; // Si no hay restricción, se asume 0 (disponible)
                    $color = ($estado == 1) ? "red" : "green";
                    echo "<td>
                        <button 
                            type='button' 
                            class='bloque' 
                            data-dia='$d' 
                            data-hora='$h'
                            style='width: 100%; height: 100%; background-color: $color; border: none; cursor: pointer;'
                        ></button>
                    </td>";
                }
                echo "</tr>";
            }
            echo "</table>";

                // Botón confirmar
                echo "<br><br><button type='button' id='confirmar'>Confirmar</button>";
        }
    ?>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            let botones = document.querySelectorAll(".bloque");
            let idDocente = <?php echo isset($idDocente) ? (int)$idDocente : 0; ?>;
            let idCurso = <?php echo isset($idCurso) ? (int)$idCurso : 0; ?>;

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
                    let estado = btn.style.backgroundColor === "red" ? 1 : 0;

                    datos.push({
                        dia: btn.dataset.dia,
                        hora: btn.dataset.hora,
                        estado: estado
                    });
                });

                let formData = new FormData();
                formData.append("datos", JSON.stringify(datos));
                formData.append("docente", idDocente);
                formData.append("curso", idCurso);

                fetch("guardar_restricciones.php", {
                    method: "POST",
                    body: formData
                })
                .then(async response => {
                    const text = await response.text();
                    try {
                        return JSON.parse(text);
                    } catch (err) {
                        console.error('Respuesta inválida de guardar_restricciones.php:', text);
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