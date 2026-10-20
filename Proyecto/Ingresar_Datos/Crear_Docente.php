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
        <input type="password" id="password" name="password" placeholder="Ingrese la cedula del docente" required><br><br>

        <label for="grado">Grado (opcional):</label>
        <input type="number" id="grado" name="grado" min="1"><br><br>

        <input type="submit" name="enviar" value="Crear Docente">

    </form>

<?php
include_once __DIR__ . "/../Logica/Docente.php";

if (isset($_POST['enviar'])) {

    include_once __DIR__ . "/../Logica/Usuario.php";
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
    $grado = trim($_POST['grado'] ?? '');
    $docente->SetGrado($grado === '' ? null : (int) $grado);
    $docente->AgregarDocente();
}
?>

</body>
</html>
