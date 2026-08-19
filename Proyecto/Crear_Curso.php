<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="Estilo/General.css">
    <link rel="stylesheet" href="Estilo/Formularios.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Curso</title>
</head>
<body>
    <nav><a href="index.php">Volver al panel</a></nav>
    <h1>Crear curso</h1>
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

        <label for="semanal">Horas Semanales:</label>
        <input type="number" id="semanal" name="semanal" required><br><br>

        <label for="turno">Turno:</label>
        <select name="turno" id="">
            <option value="Matutino" selected>Matutino</option>
            <option value="Vespertino">Vespertino</option>
            <option value="Nocturno">Nocturno</option>
        </select><br><br>

        <label for="hora_entrada">Hora de Entrada:</label>
        <input type="time" id="hora_entrada" name="hora_entrada" required><br><br>

        <label for="hora_salida">Hora de Salida:</label>
        <input type="time" id="hora_salida" name="hora_salida" required><br><br>

<label>Área:</label>

<?php
include_once "Logica/Area.php";
$area = new Area();
$areas = $area->ListarAreas();
?>

<div id="contenedorAreas">
    <div class="area-item">
        <select name="area[]">
            <option value="">Seleccione un área</option>
            <?php foreach ($areas as $a) { ?>
                <option value="<?php echo $a['ID_A']; ?>">
                    <?php echo $a['Nombre']; ?>
                </option>
            <?php } ?>
        </select>
    </div>
</div>

<button type="button" onclick="agregarArea()">+</button>

<br><br>

<script>
function agregarArea() {
    const contenedor = document.getElementById('contenedorAreas');

    const nuevo = document.createElement('div');
    nuevo.className = 'area-item';

    nuevo.innerHTML = `
        <select name="area[]">
            <option value="">Seleccione un área</option>
            <?php foreach ($areas as $a) { ?>
                <option value="<?php echo $a['ID_A']; ?>">
                    <?php echo $a['Nombre']; ?>
                </option>
            <?php } ?>
        </select>
    `;

    contenedor.appendChild(nuevo);
}
</script>

        <input type="submit" name="enviar" value="Crear Curso">
    </form>

    <?php
        include_once "Logica/Curso.php";
        include_once "Logica/Union/Curso_Area.php";
        include_once "Logica/Turno.php";

        if (isset($_POST['enviar'])) {
            $curso = new Curso();
            $curso->SetNombre($_POST['nombre']);
            $curso->SetHoras($_POST['horas']);
            $curso->SetDias($_POST['dias']);
            $curso->SetSemanal($_POST['semanal']);
            $curso->SetActivo(true);
            $curso->AgregarCurso();

            $nombre = $_POST['nombre'];

            $con = new Conexion();
            $conexion = $con->conectar();

            $sql = "SELECT ID_C FROM Curso WHERE Nombre = '$nombre'";
            $resultado = mysqli_query($conexion, $sql);

            $fila = mysqli_fetch_assoc($resultado);
            $id_c = $fila['ID_C'];

            $curso_area = new Curso_Area();

            if (isset($_POST['area']) && is_array($_POST['area'])) {
                foreach ($_POST['area'] as $id_a) {
                    if (!empty($id_a)) {
                        $curso_area->SetIdCurso($id_c);
                        $curso_area->SetIdArea($id_a);
                        $curso_area->AgregarCursoArea();
                    }
                }
            }

            $turno = new Turno();
            $turno->SetIdCurso($id_c);
            $turno->SetHoraEntrada($_POST['hora_entrada']);
            $turno->SetHoraSalida($_POST['hora_salida']);
            $turno->SetTurno($_POST['turno']);
            $turno->AgregarTurno();


        }
    ?>

</body>
</html>
