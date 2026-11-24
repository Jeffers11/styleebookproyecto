<?php

// Configuración general del sistema.
date_default_timezone_set('America/Guayaquil');

$directorioUrl = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
$archivoEntrada = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');

// Las páginas de auth, cliente y peluquero están un nivel bajo la portada.
if ($archivoEntrada && dirname($archivoEntrada, 2) === dirname(__DIR__)) {
    $directorioUrl = dirname($directorioUrl);
}

define('BASE_URL', rtrim(str_replace('\\', '/', $directorioUrl), '/'));

$ajustes = require __DIR__ . '/ajustes.php';
$host = $ajustes['host'];
$baseDatos = $ajustes['base_datos'];
$usuario = $ajustes['usuario'];
$contrasena = $ajustes['contrasena'];
$contrasenaInicial = $ajustes['contrasena_inicial_peluquero'];

if ($contrasenaInicial === '' && $host === 'localhost' && $usuario === 'root') {
    $contrasenaInicial = 'peluqueroadmin123';
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4",
        $usuario,
        $contrasena,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '-05:00'"
        ]
    );

    if (empty($omitirAltaInicialPeluquero) && strlen($contrasenaInicial) >= 12
        && strlen($contrasenaInicial) <= 72 && strpos($contrasenaInicial, chr(0)) === false) {
        require_once __DIR__ . '/inicializar_peluquero.php';
        inicializarPeluquero($pdo, $contrasenaInicial);
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "No se pudo conectar con la base de datos.\n");
        exit(1);
    }

    http_response_code(503);
    header('Retry-After: 60');
    exit('El sistema de citas no está disponible en este momento. Intenta nuevamente en unos minutos.');
}
