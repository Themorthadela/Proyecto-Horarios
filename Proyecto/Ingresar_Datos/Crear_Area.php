<?php require_once __DIR__ . '/../Requerir_Sesion.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../Estilo/General.css">
    <link rel="stylesheet" href="../Estilo/Formularios.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <nav><a href="../Panel_Admin.php">Volver al panel</a></nav>
    <h1>Crear Área</h1>

    <form method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre">

        <input type="submit" name="enviar" value="Crear Área">
        <input type="submit" name="mostrar" value="Mostrar Áreas">
    </form>

    <?php
        include_once __DIR__ . "/../Logica/Area.php";
            $area = new Area();

        if (isset($_POST['enviar'])) {
            $area->setNombre($_POST['nombre']);
            $area->AgregarArea();
        }

        if (isset($_POST['mostrar'])) {
            $areas = $area->ListarAreas();
                while ($a = $areas->fetch_assoc()) {
                    echo "<p>" . $a['ID_A'] . " - " . $a['Nombre'] . "</p>";
        }
        }
    ?>
    
</body>
</html>
