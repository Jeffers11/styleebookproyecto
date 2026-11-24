<?php

require_once __DIR__ . '/../config/conexion.php';

set_exception_handler(function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'No se pudo completar la solicitud. Intenta nuevamente en unos minutos.';
});

// Configuración segura de sesiones.
if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE_URL . '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Evitar almacenar páginas privadas en caché.
header('Cache-Control: no-store, private');

// Comprobar si existe una sesión.
function autenticado(): bool
{
    return isset($_SESSION['usuario_id'], $_SESSION['rol']);
}

// Proteger páginas según el rol requerido.
function proteger(string $rol): void
{
    if (!autenticado()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }

    if ($_SESSION['rol'] !== $rol) {
        http_response_code(403);
        exit('Acceso no autorizado.');
    }
}

// Generar token de seguridad CSRF.
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

// Validar solicitudes POST.
function validarPost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Método no permitido.');
    }

    $token = $_POST['csrf'] ?? '';

    if (
        !is_string($token) ||
        empty($_SESSION['csrf']) ||
        !hash_equals($_SESSION['csrf'], $token)
    ) {
        http_response_code(403);
        exit('Solicitud no válida.');
    }
}
