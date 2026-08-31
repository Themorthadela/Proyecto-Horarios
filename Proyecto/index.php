<?php
session_start();
if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header('Location: Panel_Admin.php');
    exit;
}
if (isset($_SESSION['secretaria']) && $_SESSION['secretaria'] === true) {
    header('Location: Panel_Secretaria.php');
    exit;
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$mensajes = [
    'credenciales' => 'El correo o la contraseña no son correctos.',
    'bloqueado' => 'Demasiados intentos. Esperá 5 minutos antes de volver a intentar.',
    'solicitud' => 'La solicitud venció. Volvé a intentarlo.',
    'sistema' => 'No se pudo iniciar sesión en este momento.',
    'sesion' => 'Tu sesión venció. Iniciá sesión nuevamente.',
    'rol' => 'Tu usuario no tiene permisos para ingresar al panel de administración.',
];
$error = $mensajes[$_GET['error'] ?? ''] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Estilo/General.css">
    <link rel="stylesheet" href="Estilo/Formularios.css">
    <title>Login</title>
</head>
<body>
    <form class="login-form" action="Validar_Usuario.php" method="POST">
        <h1>Iniciar sesión</h1>
        <?php if ($error): ?><p class="mensaje error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <label for="email">Correo electrónico:</label>
        <input type="email" id="email" name="email" maxlength="254" autocomplete="username" required autofocus>
        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" maxlength="72" autocomplete="current-password" required>
        <button type="submit">Ingresar</button>
    </form>

<?php include 'Estilo/Pie_de_Pagina.php'; ?>
</body>
</html>
