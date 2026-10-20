<?php
declare(strict_types=1);
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    header('Location: index.php?error=solicitud');
    exit;
}

$ahora = time();
$intentos = array_values(array_filter($_SESSION['intentos_login'] ?? [], static fn ($momento) => $momento > $ahora - 300));
if (count($intentos) >= 5) {
    $_SESSION['intentos_login'] = $intentos;
    header('Location: index.php?error=bloqueado');
    exit;
}

require_once __DIR__ . '/Logica/Usuario.php';
$correo = $_POST['email'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

try {
    $usuario = Usuario::Autenticar($correo, $contrasena);
} catch (Throwable $e) {
    error_log($e->getMessage());
    header('Location: index.php?error=sistema');
    exit;
}

if (!$usuario) {
    $intentos[] = $ahora;
    $_SESSION['intentos_login'] = $intentos;
    header('Location: index.php?error=credenciales');
    exit;
}

session_regenerate_id(true);
unset($_SESSION['intentos_login']);
$_SESSION['usuario'] = [
    'id' => (int) $usuario['ID_U'],
    'nombre' => $usuario['Nombre'],
    'email' => $usuario['Email'],
    'rol' => $usuario['Rol'],
];
$_SESSION['ultimo_acceso'] = $ahora;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

switch ($usuario['Rol']) {
    case 'Admin':
    case 'Secretaria':
    case 'Director':
        header('Location: Panel_Admin.php');
        exit;

    default:
        header('Location: MostrarHorarios.php');
        exit;
}
