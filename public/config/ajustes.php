<?php

$servidor = $_SERVER['SERVER_NAME'] ?? '';

if (PHP_OS_FAMILY === 'Windows' && (PHP_SAPI === 'cli' || in_array($servidor, ['localhost', '127.0.0.1', '::1'], true))) {
    return [
        'host' => 'localhost',
        'base_datos' => 'stylebook_db',
        'usuario' => 'root',
        'contrasena' => '',
        'contrasena_inicial_peluquero' => '',
    ];
}

return [
    'host' => 'sql107.infinityfree.com',
    'base_datos' => 'if0_43525456_stylebook_db',
    'usuario' => 'if0_43525456',
    'contrasena' => 'fva140PMj3dSW8',
    'contrasena_inicial_peluquero' => 'a7ce87b73bdd63d5787ff8fc',
];
