<?php

function inicializarPeluquero(PDO $pdo, string $contrasenaInicial): void
{
    if ($pdo->query('SELECT id FROM peluqueros LIMIT 1')->fetchColumn() !== false) {
        return;
    }

    $pdo->beginTransaction();

    try {
        // El alta administrativa y el registro público usan el mismo bloqueo.
        $rol = $pdo->query("SELECT id FROM roles WHERE nombre = 'Peluquero' FOR UPDATE")->fetchColumn();

        if ($rol !== false && $pdo->query('SELECT id FROM peluqueros LIMIT 1 FOR UPDATE')->fetchColumn() === false) {
            $correo = 'peluquero@stylebook.local';
            $consulta = $pdo->prepare('SELECT id FROM clientes WHERE correo = ?');
            $consulta->execute([$correo]);

            if ($consulta->fetchColumn() === false) {
                $consulta = $pdo->prepare(
                    'INSERT INTO peluqueros (id_rol, nombre, correo, contraseña) VALUES (?, ?, ?, ?)'
                );
                $consulta->execute([
                    $rol,
                    'Peluquero StyleBook',
                    $correo,
                    password_hash($contrasenaInicial, PASSWORD_DEFAULT)
                ]);
            }
        }

        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $error;
    }
}
