<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Curso</title>
</head>
<body>
    <form action="" method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="horas">Cantidad de Horas Diarias:</label>
        <input type="number" id="horas" name="horas" required><br><br>

        <label for="dias">Dias:</label>
        <select name="dias" id="">
            <option value="5" selected>Lunes a Viernes</option>
            <option value="6">Lunes a Sábado</option>
        </select><br><br>   

        <label for="semanal">Horarios en los que se dan las clases:</label>
        <input type="number" id="semanal" name="semanal" required><br><br>

        <input type="submit" name="enviar" value="Crear Curso">
    </form>

    <?php
        include_once "Logica/Curso.php";

        if (isset($_POST['enviar'])) {
            $curso = new Curso();
            $curso->SetNombre($_POST['nombre']);
            $curso->SetHoras($_POST['horas']);
            $curso->SetDias($_POST['dias']);
            $curso->SetSemanal($_POST['semanal']);
            $curso->SetActivo(true);
            $curso->AgregarCurso();
        }
    ?>

</body>
</html>