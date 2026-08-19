<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="Estilo/General.css">
    <link rel="stylesheet" href="Estilo/Formularios.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Materia a Docente </title>
</head>
<body>
    <nav><a href="index.php">Volver al panel</a></nav>
    <h1>Asignar materia</h1>
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
        <br><br>

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

        <br><br>

        <label>Materia:</label>
        <?php
        include_once "Logica/Materia.php";
        $materia = new Materia();
        $materias = $materia->ListarMaterias();
        ?>
        <select name="materia" required>
            <option value="">Seleccione una materia</option>
            <?php foreach ($materias as $m) { ?>
                <option value="<?php echo $m['ID_M']; ?>">
                    <?php echo $m['Nombre']; ?>
                </option>
            <?php } ?>
        </select>
        <br><br>
        <input type="submit" value="Asignar Materia">
    </form>

    <?php
    if ($_POST) {
        include_once 'Logica/Asignacion.php';
        $asignacion = new Asignacion();
        $asignacion->setIdCurso($_POST['curso']);
        $asignacion->setIdDocente($_POST['docente']);
        $asignacion->setIdMateria($_POST['materia']);
        $asignacion->GuardarAsignacion();
    }
    ?>

</body>
</html>
