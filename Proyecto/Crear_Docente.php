<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Docente</title>
</head>
<body>
    <form action="" method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" required><br><br>

        <label for="CI">Cedula:</label>
        <input type="number" id="CI" name="CI" required><br><br>

        <label for="grado">Grado:</label>
        <input type="number" id="grado" name="grado" required><br><br>

        <label>Área:</label>
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

        <input type="submit" name="enviar" value="Crear Docente">

    </form>

    <?php
    include_once "Logica/Docente.php";

    if (isset($_POST['enviar'])) {
        $docente = new Docente();
        $docente->SetNombre($_POST['nombre']);
        $docente->SetApellido($_POST['apellido']);
        $docente->SetCI($_POST['CI']);
        $docente->SetGrado($_POST['grado']);
        $docente->SetArea($_POST['area']);
        $docente->SetActivo(true);
        $docente->AgregarDocente();
    }
    ?>

</body>
</html>