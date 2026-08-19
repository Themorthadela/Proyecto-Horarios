<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="Estilo/General.css">
    <link rel="stylesheet" href="Estilo/Formularios.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <nav><a href="index.php">Volver al panel</a></nav>
    <h1 style="color: red;">Restricciones de Externas del docente</h1>

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

        <label for="Dia">Dia</label>
        <select name="Dia" required>
            <option value="">Seleccione un día</option>
            <option value="1">Lunes</option>
            <option value="2">Martes</option>
            <option value="3">Miércoles</option>
            <option value="4">Jueves</option>
            <option value="5">Viernes</option>
            <option value="6">Sábado</option>
        </select>

        <label for="HoraInicio">Hora de Inicio</label>
        <input type="time" name="hora_I">

        <label for="HoraFinal">Hora Fin</label>
        <input type="time" name="hora_F">

        <input type="submit" name="enviar" value="Enviar Restricciones">        
    </form>

    <a href="index.php">Volver</a>

    <?php

        include_once "Logica/Restricciones/Restriccion_Docente.php";

        if (isset($_POST['enviar'])) {

            $idDocente = $_POST['docente'];
            $hora_I = $_POST['hora_I'];
            $hora_F = $_POST['hora_F'];
            $Dia = $_POST['Dia'];

            $dias = [
                1 => "Lunes",
                2 => "Martes",
                3 => "Miércoles",
                4 => "Jueves",
                5 => "Viernes",
                6 => "Sábado"
            ];  

            $nombreDia = $dias[$Dia];

            $docente = new Docente();
            $datosDocente = $docente->ObtenerDocentePorId($idDocente);

            $R_Docente = new Restriccion_Docente();
            $R_Docente->SetID_D($idDocente);
            $R_Docente->SetDia($Dia);
            $R_Docente->SetHora_I($hora_I);
            $R_Docente->SetHora_F($hora_F);
            $R_Docente->AgregarRestriccion();



            echo "<p>Ahora el docente " . $datosDocente['Nombre'] . " " . $datosDocente['Apellido'] . 
                    " No puede agarrar horas entre ". $hora_I ." y " . $hora_F . " el dia " . $nombreDia . "</p>" ;

        }
    ?>

</body>
</html>
