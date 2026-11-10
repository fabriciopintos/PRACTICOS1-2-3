<?php

require_once __DIR__ . '/rutas.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate');

function usuarioActual() {
    return $_SESSION['usuario'] ?? null;
}

function e($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function destinoPorRol($rol) {
    return $rol === 'administrador' ? RUTA_ADMIN : RUTA_USUARIO;
}

function exigirSesion() {
    if (!usuarioActual()) {
        header('Location: ' . RUTA_LOGIN);
        exit;
    }
}

function exigirAdministrador() {
    $usuario = usuarioActual();
    if (!$usuario) {
        header('Location: ' . RUTA_LOGIN);
        exit;
    }
    if ($usuario['rol'] !== 'administrador') {
        header('Location: ' . RUTA_USUARIO);
        exit;
    }
}
