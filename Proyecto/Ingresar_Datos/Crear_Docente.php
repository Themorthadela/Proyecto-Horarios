<?php require_once __DIR__ . '/../Requerir_Sesion.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../Estilo/General.css">
    <link rel="stylesheet" href="../Estilo/Formularios.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Docente</title>
</head>
<body>
    <nav><a href="../Panel_Admin.php">Volver al panel</a></nav>
    <h1>Crear docente</h1>
    <form action="" method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="grado">Grado:</label>
        <input type="number" id="grado" name="grado" required><br><br>

        <label>Área:</label>
        <?php
            include_once __DIR__ . "/../Logica/Area.php";
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
include_once __DIR__ . "/../Logica/Docente.php";

if (isset($_POST['enviar'])) {

    include_once __DIR__ . "/../Logica/Usuario.php";
    include_once __DIR__ . "/../Logica/Union/Docente_Area.php";

    $usuario = new Usuario();
    $usuario->SetNombre($_POST['nombre']);
    $usuario->SetApellido($_POST['apellido']);
    $usuario->SetCorreo($_POST['email']);
    $usuario->SetContrasena($_POST['password']);
    $usuario->SetActivo(true);
    $usuario->SetRol('Docente');
    $usuario->AgregarUsuario();

    $email = $_POST['email'];

    $con = new Conexion();
    $conexion = $con->conectar();

    $sql = "SELECT ID_U FROM Usuario WHERE Email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    $id_u = $fila['ID_U'];

    $docente = new Docente();
    $docente->SetID_U($id_u);
    $docente->SetGrado($_POST['grado']);
    $docente->AgregarDocente();

    $sql = "SELECT d.ID_D FROM Docente d INNER JOIN Usuario u ON d.ID_U = u.ID_U WHERE u.Email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    $id_d = $fila['ID_D'];
    $id_a = $_POST['area'];

    $docente_area = new Docente_Area();
    $docente_area->SetIdDocente($id_d);
    $docente_area->SetIdArea($id_a);
    $docente_area->AgregarDocenteArea();
}
?>

</body>
</html>
