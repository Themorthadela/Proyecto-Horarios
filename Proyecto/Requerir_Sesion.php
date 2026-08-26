<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['usuario'], $_SESSION['ultimo_acceso']) || time() - (int) $_SESSION['ultimo_acceso'] > 1800) {
    $_SESSION = [];
    session_destroy();
    $rutaLogin = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/Ingresar_Datos/') ? '../index.php' : 'index.php';
    header('Location: ' . $rutaLogin . '?error=sesion');
    exit;
}

$_SESSION['ultimo_acceso'] = time();
