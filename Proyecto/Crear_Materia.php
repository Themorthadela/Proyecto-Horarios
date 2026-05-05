<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Materia</title>
</head>
<body>
    <form action="" method="post">
        <label for="nombre">Nombre de la materia:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="CargaHoraria">Carga Horaria:</label>
        <input type="number" id="CargaHoraria" name="CargaHoraria" required><br><br>

        <label for="TipoSalon">Tipo de Salon:</label>
            <select name="TipoSalon" id="TipoSalon">
                <option value="">Seleccione un tipo de salon</option>
                <option value="Aula">Aula</option>
                <option value="Taller">Taller</option>
                <option value="Laboratorio">Laboratorio</option>
        </select><br><br>

        <label for="area">Tipo de Area:</label>
        <?php
            include_once "Logica/Area.php";
                $area = new Area();
                $areas = $area->ListarAreas();
        ?>

        <select name="area">
            <option value="">Seleccione un área</option>
            <?php 
                foreach ($areas as $a) { ?>
                <option value="<?php echo $a['ID_A']; ?>">
                    <?php echo $a['Nombre']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <input type="submit" name="enviar" value="Crear Materia">
    </form>

    <?php
        include_once "Logica/Materia.php";
        if (isset($_POST['enviar'])) {
            $materia = new Materia();
            $materia->SetNombre($_POST['nombre']);
            $materia->SetCargaHoraria($_POST['CargaHoraria']);
            $materia->SetTipoSalon($_POST['TipoSalon']);
            $materia->SetActivo(true);
            $materia->SetArea($_POST['area']);
            $materia->AgregarMateria();
        }
    ?>
</body>
</html>