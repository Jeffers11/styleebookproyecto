<?php

require_once __DIR__ . '/../includes/funciones.php';

if (autenticado()) {
    redireccionar('index.php');
}

$titulo = 'Registro de clientes';

$error = '';

$nombre = '';
$correo = '';
$telefono = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validarPost();

    $nombre = trim(campoPost('nombre'));
    $correo = strtolower(trim(campoPost('correo')));
    $telefono = trim(campoPost('telefono'));
    $password = campoPost('password');
    $confirmar = campoPost('confirmar_password');

    if (!preg_match('/^[\p{L}\p{M} .\x{27}\x{2019}\-]{3,50}$/u', $nombre) || !preg_match('/\p{L}/u', $nombre)) {
        $error = 'Ingresa un nombre válido de 3 a 50 caracteres.';
    } elseif (!correoValido($correo)) {
        $error = 'Ingresa un correo electrónico válido.';
    } elseif (!preg_match('/^\+?[0-9][0-9 -]{5,14}$/D', $telefono)
        || strlen(preg_replace('/[^0-9]/', '', $telefono)) < 7
        || strlen($telefono) > 15) {
        $error = 'Ingresa un teléfono válido con al menos 7 dígitos.';
    } elseif (!mb_check_encoding($password, 'UTF-8') || mb_strlen($password) < 8
        || strlen($password) > 72 || strpos($password, chr(0)) !== false || trim($password) === '') {
        $error = 'Usa al menos 8 caracteres y una contraseña de hasta 72 bytes.';
    } elseif ($password !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $pdo->beginTransaction();
            // Coordina el registro con el alta administrativa del peluquero.
            $pdo->query("SELECT id FROM roles WHERE nombre = 'Peluquero' FOR UPDATE")->fetchColumn();
            $consulta = $pdo->prepare('SELECT id FROM peluqueros WHERE correo = ?');
            $consulta->execute([$correo]);

            if ($consulta->fetchColumn()) {
                $error = 'El correo electrónico ya está registrado.';
            } else {
                $consulta = $pdo->prepare(
                    "INSERT INTO clientes (id_rol, nombre, correo, contraseña, telefono)
                     VALUES ((SELECT id FROM roles WHERE nombre = 'Cliente'), ?, ?, ?, ?)"
                );
                $consulta->execute([$nombre, $correo, password_hash($password, PASSWORD_DEFAULT), $telefono]);
            }
            $pdo->commit();
            if ($error === '') {
                mensaje('success', 'Cuenta de cliente creada. Ya puedes iniciar sesión.');
                redireccionar('auth/login.php');
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $error = 'El correo electrónico ya está registrado.';
            } else {
                error_log($e->getMessage());
                $error = 'No se pudo crear la cuenta. Intenta nuevamente.';
            }
        }
    }
}

require __DIR__ . '/../includes/header.php';

?>

<div class="container">

    <div class="formulario-card">

        <div class="text-center mb-4">

            <img src="<?= BASE_URL ?>/assets/img/logo.svg"
                 width="80"
                 alt="StyleBook">

            <h2 class="mt-3">Crear cuenta</h2>

            <p class="text-muted">
                Regístrate para reservar tus citas.
            </p>

        </div>

        <?php if ($error): ?>

            <div class="alert alert-danger" role="alert">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST"
              class="needs-validation"
              novalidate>

            <input type="hidden"
                   name="csrf"
                   value="<?= e(tokenCsrf()) ?>">

            <div class="mb-3">

                <label class="form-label" for="nombre">
                    Nombre completo
                </label>

                <input type="text"
                       id="nombre"
                       name="nombre"
                       class="form-control"
                       autocomplete="name"
                       minlength="3"
                       maxlength="50"
                       value="<?= e($nombre) ?>"
                       required>

                <div class="invalid-feedback">
                    Ingresa tu nombre.
                </div>

            </div>

            <div class="mb-3">

                <label class="form-label" for="correo">
                    Correo electrónico
                </label>

                <input type="email"
                       id="correo"
                       name="correo"
                       class="form-control"
                       autocomplete="email"
                       maxlength="100"
                       value="<?= e($correo) ?>"
                       required>

                <div class="invalid-feedback">
                    Ingresa un correo válido.
                </div>

            </div>

            <div class="mb-3">

                <label class="form-label" for="telefono">
                    Teléfono
                </label>

                <input type="tel"
                       id="telefono"
                       name="telefono"
                       class="form-control"
                       autocomplete="tel"
                       minlength="7"
                       maxlength="15"
                       value="<?= e($telefono) ?>"
                       required>

            </div>

            <div class="mb-3">

                <label class="form-label" for="password">
                    Contraseña
                </label>

                <input type="password"
                       id="password"
                       name="password"
                       class="form-control"
                       autocomplete="new-password"
                       minlength="8"
                       maxlength="72"
                       required>

                <div class="invalid-feedback">Ingresa una contraseña de al menos 8 caracteres.</div>
                <small class="text-muted">
                    Mínimo 8 caracteres.
                </small>

            </div>

            <div class="mb-4">

                <label class="form-label"
                       for="confirmar_password">

                    Confirmar contraseña

                </label>

                <input type="password"
                       id="confirmar_password"
                       name="confirmar_password"
                       class="form-control"
                       autocomplete="new-password"
                       required>

                <div class="invalid-feedback">
                    Verifica las contraseñas.
                </div>

            </div>

            <button type="submit"
                    class="btn btn-carbon w-100">

                Crear cuenta

            </button>

        </form>

        <p class="text-center mt-4 mb-0">

            ¿Ya tienes una cuenta?

            <a class="enlace-ciruela"
               href="<?= BASE_URL ?>/auth/login.php">

                Inicia sesión

            </a>

        </p>

    </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
