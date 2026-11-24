<?php

require_once __DIR__ . '/../includes/funciones.php';

$servicio = servicioSolicitado();
$destinoCliente = $servicio === ''
    ? 'cliente/index.php'
    : 'cliente/reservar.php?servicio=' . rawurlencode($servicio);

if (autenticado()) {

    redireccionar(
        $_SESSION['rol'] === 'Cliente'
        ? $destinoCliente
        : 'peluquero/index.php'
    );
}

$titulo = 'Iniciar sesión';

$error = '';

$correo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarPost();

    $correo = strtolower(trim(campoPost('correo')));
    $password = campoPost('password');
    $intentos = time() - ($_SESSION['ultimo_intento'] ?? 0) < 60
        ? ($_SESSION['intentos_login'] ?? 0)
        : 0;

    if ($intentos >= 5) {
        http_response_code(429);
        $error = 'Demasiados intentos. Intenta nuevamente en un minuto.';
    } elseif (!correoValido($correo) || $password === '' || strlen($password) > 72 || strpos($password, chr(0)) !== false) {
        $error = 'Ingresa un correo y una contraseña válidos.';
    } else {
        try {
            $consulta = $pdo->prepare(
                "SELECT u.id, u.nombre, u.contraseña, 'Peluquero' AS rol
                 FROM peluqueros u JOIN roles r ON r.id = u.id_rol
                 WHERE u.correo = ? AND r.nombre = 'Peluquero'
                 UNION ALL
                 SELECT u.id, u.nombre, u.contraseña, 'Cliente' AS rol
                 FROM clientes u JOIN roles r ON r.id = u.id_rol
                 WHERE u.correo = ? AND r.nombre = 'Cliente'"
            );
            $consulta->execute([$correo, $correo]);
            $cuentas = $consulta->fetchAll();

            foreach ($cuentas as $usuario) {
                if (!password_verify($password, $usuario['contraseña'])) {
                    continue;
                }

                if (!session_regenerate_id(true)) {
                    throw new RuntimeException('No se pudo renovar la sesión de acceso.');
                }

                $_SESSION = [
                    'usuario_id' => (int) $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'rol' => $usuario['rol']
                ];

                mensaje('success', 'Bienvenido, ' . $usuario['nombre']);
                redireccionar($usuario['rol'] === 'Cliente' ? $destinoCliente : 'peluquero/index.php');
            }

            $_SESSION['intentos_login'] = $intentos + 1;
            $_SESSION['ultimo_intento'] = time();
            $error = 'Correo o contraseña incorrectos.';
        } catch (RuntimeException $e) {
            error_log($e->getMessage());
            $error = 'El inicio de sesión no está disponible. Intenta nuevamente.';
        }
    }
}

require __DIR__ . '/../includes/header.php';

?>

<div class="fondo-acceso">
    <div class="container">

        <div class="formulario-card">

            <div class="text-center mb-4">

                <img src="<?= BASE_URL ?>/assets/img/logo.svg"
                     width="85"
                     alt="StyleBook">

                <h2 class="mt-3">Bienvenido</h2>

                <p class="text-muted">
                    Inicia sesión en tu cuenta.
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

                    <label class="form-label" for="correo">
                        Correo electrónico
                    </label>

                    <input type="email"
                           id="correo"
                           name="correo"
                           class="form-control"
                           value="<?= e($correo) ?>"
                           autocomplete="username"
                           maxlength="100"
                           required>
                    <div class="invalid-feedback">Ingresa un correo electrónico válido.</div>

                </div>

                <div class="mb-4">

                    <label class="form-label" for="password">
                        Contraseña
                    </label>

                    <div class="input-group has-validation">

                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control"
                               autocomplete="current-password"
                               maxlength="72"
                               required>

                        <button type="button"
                                class="btn btn-outline-secondary"
                                data-ver-password="password">

                            Mostrar

                        </button>
                        <div class="invalid-feedback">Ingresa tu contraseña.</div>

                    </div>

                </div>

                <button type="submit"
                        class="btn btn-carbon w-100">

                    Iniciar sesión

                </button>

            </form>

            <p class="text-center mt-4 mb-0">

                ¿No tienes cuenta?

                <a href="<?= BASE_URL ?>/auth/registro.php"
                   class="enlace-ciruela">

                    Regístrate

                </a>

            </p>

        </div>
    </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
