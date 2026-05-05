<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Horarios</title>

    <style>
        body {
            font-family: 'Lexend', sans-serif;
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
        <h1 style="color: red;">
            
        </h1>

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

        <input type="submit" name="enviar" value="Generar Tabla">        
    </form>

    <a href="index.php">Volver</a>

    <?php
        if (isset($_POST['enviar'])) {

            $idCurso = $_POST['curso'];

            $curso = new Curso();
            $datosCurso = $curso->ObtenerCursoPorId($idCurso);

            $horas = $datosCurso['Horas'];
            $dias = $datosCurso['Dias'];

            include_once "Logica/Docente.php";
            $docente = new Docente();
            $docentes = $docente->ListarDocentes();



        // Mapear horas a nombres
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
            echo "<h2>Tabla de Horarios de " . $datosCurso['Nombre'] . "</h2>";

            echo "<table border='1' cellpadding='10'>";

            // Encabezado
            echo "<tr><th>Hora/Día</th>";
            for ($d=1; $d <= $dias; $d++) {
                echo "<th>{$mapeoDias[$d]}</th>";
            }
            echo "</tr>";
            $index = 0;
            // Filas de horas
            for ($h=1; $h <= $horas; $h++) {
                echo "<tr>";
                echo "<td>{$mapeoHoras[$h]}</td>";
                for ($d=1; $d <= $dias; $d++) {


                
                if (isset($docentes[$index])) {

                $doc = $docentes[$index];

                $color = "#" . dechex(rand(0, 16777215));
                $docenteT = $doc["Nombre"] . " " . $doc["Apellido"];

                    echo "<td>
                        <button 
                            type='button' 
                            class='bloque' 
                            data-dia='$d' 
                            data-hora='$h'
                            style='width: 100%; height: 100%; background-color: $color; border: none; cursor: pointer;font-family: \"Lexend\", sans-serif; font-size: 1rem;'
                        >" ;  
                        echo $docenteT;
                        echo"<br>";
            
            "</button>";
            $index++;
}
            
                "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";

                // Botón confirmar
                echo "<br><br><button type='button' id='confirmar'style='font-family: \"arial\", sans-serif; font-size: 1rem;'>Confirmar</button>";
        }
    ?>
</body>
</html>