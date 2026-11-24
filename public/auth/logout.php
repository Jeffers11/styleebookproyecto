<?php

require_once __DIR__ . '/../includes/funciones.php';

validarPost();

// Eliminar las variables de sesión.
$_SESSION = [];

// Eliminar la cookie de sesión.
if (ini_get('session.use_cookies')) {

    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros['path'],
        $parametros['domain'],
        $parametros['secure'],
        $parametros['httponly']
    );
}

// Destruir la sesión.
session_destroy();

// Regresar a la página de inicio de sesión.
redireccionar('auth/login.php');