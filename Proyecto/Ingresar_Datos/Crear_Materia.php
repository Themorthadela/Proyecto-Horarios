<?php require_once __DIR__ . '/../Requerir_Sesion.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../Estilo/General.css">
    <link rel="stylesheet" href="../Estilo/Formularios.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Materia</title>
</head>
<body>
    <nav><a href="../Panel_Admin.php">Volver al panel</a></nav>
    <h1>Crear materia</h1>
    <form action="" method="post">
        <label for="nombre">Nombre de la materia:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="CargaHoraria">Carga Horaria:</label>
        <input type="number" id="CargaHoraria" name="CargaHoraria" required><br><br>

        <label for="HorasSeguidas">Cantidad de Horas Seguidas:</label>
        <input type="number" id="HorasSeguidas" name="HorasSeguidas" required><br><br>

        <label for="TipoSalon">Tipo de Salon:</label>
            <select name="TipoSalon" id="TipoSalon">
                <option value="">Seleccione un tipo de salon</option>
                <option value="Aula">Aula</option>
                <option value="Taller">Taller</option>
                <option value="Laboratorio">Laboratorio</option>
        </select><br><br>

        <input type="submit" name="enviar" value="Crear Materia">
    </form>

    <?php
        include_once __DIR__ . "/../Logica/Materia.php";
        if (isset($_POST['enviar'])) {
            $materia = new Materia();
            $materia->SetNombre($_POST['nombre']);
            $materia->SetCargaHoraria($_POST['CargaHoraria']);
            $materia->SetHorasSeguidas($_POST['HorasSeguidas']);
            $materia->SetTipoSalon($_POST['TipoSalon']);
            $materia->SetActivo(true);
            $materia->AgregarMateria();
        }

        include_once __DIR__ . "/../Logica/Union/Docente_Materia.php";
        if (isset($_POST['asignar'])) {
            $docente_materia = new Docente_Materia();
            $docente_materia->SetIdDocente($_POST['docente']);
            $docente_materia->SetIdMateria($_POST['materia']);
            $docente_materia->AgregarDocenteMateria();
        }
    ?>
</body>
</html>
