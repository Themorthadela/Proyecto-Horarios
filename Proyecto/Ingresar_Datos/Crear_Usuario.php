<?php
require_once __DIR__ . '/../Requerir_Sesion.php';
require_once __DIR__ . '/../Logica/Usuario.php';
$mensaje = null;
$tipoMensaje = 'error';
$rolSesion = $_SESSION['usuario']['rol'] ?? '';

if (!in_array($rolSesion, ['Admin', 'Secretaria', 'Director'], true)) {
    header('Location: ../index.php?error=rol');
    exit;
}

$rolesPermitidos = in_array($rolSesion, ['Secretaria', 'Director'], true)
    ? ['Docente', 'Usuario']
    : ['Admin', 'Director', 'Secretaria', 'Docente', 'Usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $mensaje = 'La solicitud venció. Volvé a intentarlo.';
    } elseif (($_POST['accion'] ?? 'crear') === 'cambiar_rol') {
        $nuevoRol = $_POST['rol'] ?? '';
        if (!in_array($nuevoRol, $rolesPermitidos, true)) {
            $mensaje = 'No tenés permiso para asignar ese rol.';
        } else {
            try {
                Usuario::CambiarRol($_POST['usuario_id'] ?? null, $nuevoRol);
                $mensaje = 'Rol actualizado correctamente.';
                $tipoMensaje = '';
            } catch (InvalidArgumentException $e) {
                $mensaje = $e->getMessage();
            } catch (Throwable $e) {
                error_log($e->getMessage());
                $mensaje = 'No se pudo actualizar el rol.';
            }
        }
    } elseif (($_POST['password'] ?? '') !== ($_POST['confirmar_password'] ?? '')) {
        $mensaje = 'Las contraseñas no coinciden.';
    } else {
        try {
            $usuario = new Usuario();
            $usuario->SetNombre($_POST['nombre'] ?? '');
            $usuario->SetApellido($_POST['apellido'] ?? '');
            $usuario->SetCorreo($_POST['email'] ?? '');
            $usuario->SetContrasena($_POST['password'] ?? '');
            $usuario->SetActivo(true);
            $usuario->SetRol('Usuario');
            $usuario->AgregarUsuario();
            $mensaje = 'Usuario creado correctamente.';
            $tipoMensaje = '';
            $_POST = [];
        } catch (InvalidArgumentException | DomainException $e) {
            $mensaje = $e->getMessage();
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $mensaje = 'No se pudo crear el usuario.';
        }
    }
}

$mostrarUsuarios = isset($_GET['mostrar']) || (($_POST['accion'] ?? '') === 'cambiar_rol');
$usuarios = [];
if ($mostrarUsuarios) {
    try {
        $usuarios = (new Usuario())->ListarUsuarios();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $mensaje = $mensaje ?: 'No se pudo cargar la lista de usuarios.';
        $tipoMensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../Estilo/General.css">
    <link rel="stylesheet" href="../Estilo/Formularios.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear y gestionar usuarios</title>
</head>
<body>
    <nav><a href="../Panel_Admin.php">Volver al panel</a></nav>
    <h1>Crear usuario</h1>
    <?php if ($mensaje): ?><p class="mensaje <?php echo $tipoMensaje; ?>" role="alert"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" minlength="2" maxlength="50" value="<?php echo htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" minlength="2" maxlength="50" value="<?php echo htmlspecialchars($_POST['apellido'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" maxlength="254" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" minlength="8" maxlength="72" pattern="(?=.*[A-Za-z])(?=.*\d).{8,72}" title="Entre 8 y 72 caracteres, con al menos una letra y un número" autocomplete="new-password" required><br><br>

        <label for="confirmar_password">Confirmar contraseña:</label>
        <input type="password" id="confirmar_password" name="confirmar_password" minlength="8" maxlength="72" autocomplete="new-password" required><br><br>

        <input type="submit" name="enviar" value="Crear Usuario">
        <input type="hidden" name="mostrar" value="1">
        <p><a class="boton-enlace" href="?mostrar=1">Mostrar usuarios</a></p>
            
    </form>


    <?php if ($mostrarUsuarios): ?>
        <h2>Usuarios</h2>
        <div class="tabla">
            <table>
                <thead><tr><th>Nombre</th><th>Email</th><th>Estado</th><th>Rol</th><th>Acción</th></tr></thead>
                <tbody>
                <?php if (!$usuarios): ?>
                    <tr><td colspan="5">No hay usuarios registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $fila): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['Nombre'] . ' ' . $fila['Apellido'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($fila['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo (bool) $fila['Activo'] ? 'Activo' : 'Inactivo'; ?></td>
                            <td><?php echo htmlspecialchars($fila['Rol'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <form method="post" class="form-rol">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="accion" value="cambiar_rol">
                                    <input type="hidden" name="usuario_id" value="<?php echo (int) $fila['ID_U']; ?>">
                                    <label>
                                        <span class="sr-only">Nuevo rol para <?php echo htmlspecialchars($fila['Nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <select name="rol" required>
                                            <?php foreach ($rolesPermitidos as $rol): ?>
                                                <option value="<?php echo $rol; ?>" <?php echo $fila['Rol'] === $rol ? 'selected' : ''; ?>><?php echo $rol; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <button type="submit">Cambiar rol</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</body>
</html>
