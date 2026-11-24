<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if ($argc !== 3) {
    fwrite(STDERR, "Uso: php config/crear_peluquero.php correo nombre\n");
    exit(1);
}

$correo = strtolower(trim($argv[1]));
$nombre = trim($argv[2]);

if (strlen($correo) > 100 || !filter_var($correo, FILTER_VALIDATE_EMAIL)
    || !mb_check_encoding($nombre, 'UTF-8') || mb_strlen($nombre) < 3 || mb_strlen($nombre) > 50) {
    fwrite(STDERR, "Ingresa un correo válido y un nombre de 3 a 50 caracteres.\n");
    exit(1);
}

$omitirAltaInicialPeluquero = true;
require __DIR__ . '/conexion.php';

try {
    $pdo->beginTransaction();
    $rol = $pdo->query("SELECT id FROM roles WHERE nombre = 'Peluquero' FOR UPDATE")->fetchColumn();
    if (!$rol) {
        throw new DomainException('El rol Peluquero no está configurado.');
    }

    $consulta = $pdo->prepare('SELECT id FROM clientes WHERE correo = ?');
    $consulta->execute([$correo]);
    if ($consulta->fetchColumn()) {
        throw new DomainException('El correo ya corresponde a una cuenta de cliente.');
    }

    $peluqueros = $pdo->query('SELECT id, correo FROM peluqueros FOR UPDATE')->fetchAll();
    if (count($peluqueros) > 1 || ($peluqueros && strcasecmp($peluqueros[0]['correo'], $correo) !== 0)) {
        throw new DomainException('Ya existe una cuenta de peluquería con otro correo.');
    }

    $password = bin2hex(random_bytes(16));
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($peluqueros) {
        $consulta = $pdo->prepare('UPDATE peluqueros SET contraseña = ?, nombre = ?, id_rol = ? WHERE id = ?');
        $consulta->execute([$hash, $nombre, $rol, $peluqueros[0]['id']]);
    } else {
        $consulta = $pdo->prepare('INSERT INTO peluqueros (id_rol, nombre, correo, contraseña) VALUES (?, ?, ?, ?)');
        $consulta->execute([$rol, $nombre, $correo, $hash]);
    }

    $pdo->commit();
    fwrite(STDOUT, "Correo: $correo\nContraseña generada: $password\n");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (!$e instanceof DomainException) {
        error_log($e->getMessage());
    }

    fwrite(STDERR, ($e instanceof DomainException ? $e->getMessage() : 'No se pudo configurar la cuenta.') . PHP_EOL);
    exit(1);
}
