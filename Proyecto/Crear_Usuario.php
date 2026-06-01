<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" name="enviar" value="Crear Usuario">
    </form>

    <?php
    include_once "Logica/Usuario.php";
    if (isset($_POST['enviar'])) {
        $usuario = new Usuario();
        $usuario->SetNombre($_POST['nombre']);
        $usuario->SetApellido($_POST['apellido']);
        $usuario->SetCorreo($_POST['email']);
        $usuario->SetContrasena($_POST['password']);
        $usuario->SetActivo(true);
        $usuario->SetRol('Usuario');
        $usuario->AgregarUsuario();
    }
    ?>
</body>
</html>