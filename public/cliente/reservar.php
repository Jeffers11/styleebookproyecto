<?php

require_once __DIR__ . '/../includes/funciones.php';

proteger('Cliente');

$titulo = 'Reservar cita';

$error = '';

$idPeluquero = 0;
$servicio = servicioSolicitado();
$fechaHora = '';
$observaciones = '';

// Obtener los peluqueros disponibles.
$consulta = $pdo->query(
    "SELECT p.id, p.nombre, p.especialidad
     FROM peluqueros p JOIN roles r ON r.id = p.id_rol
     WHERE r.nombre = 'Peluquero'
     ORDER BY p.nombre"
);

$peluqueros = $consulta->fetchAll();

$listaServicios = servicios();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validarPost();

    $idPeluquero = filter_var(
        $_POST['id_peluquero'] ?? null,
        FILTER_VALIDATE_INT
    );

    $servicio = campoPost('servicio');

    $fechaHora = campoPost('fecha_hora');

    $observaciones = trim(campoPost('observaciones'));

    // Convertir el valor datetime-local de HTML.
    $fecha = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D', $fechaHora)
        ? DateTimeImmutable::createFromFormat(
            '!Y-m-d\TH:i',
            $fechaHora
        )
        : false;

    if ($idPeluquero < 1) {

        $error = 'Selecciona un peluquero.';
    } elseif (!array_key_exists($servicio, $listaServicios)) {

        $error = 'Selecciona un servicio válido.';
    } elseif (
        !$fecha ||
        $fecha->format('Y-m-d\TH:i') !== $fechaHora
    ) {

        $error = 'Selecciona una fecha válida.';
    } elseif ($fecha <= new DateTime()) {

        $error = 'La cita debe realizarse en una fecha futura.';
    } elseif ($fecha > new DateTime('+90 days')) {

        $error = 'Solo puedes reservar con 90 días de anticipación.';
    } elseif (
        (int) $fecha->format('H') < 9 ||
        (int) $fecha->format('H') >= 18 ||
        !in_array($fecha->format('i'), ['00', '30'], true)
    ) {

        $error = 'El horario debe estar entre 09:00 y 17:30, en intervalos de 30 minutos.';
    } elseif (!mb_check_encoding($observaciones, 'UTF-8') || mb_strlen($observaciones) > 255) {

        $error = 'Las observaciones son demasiado extensas.';
    } else {

        $fechaSQL = $fecha->format('Y-m-d H:i:s');

        try {

            // Iniciar una transacción para evitar reservas
            // simultáneas sobre el mismo horario.
            $pdo->beginTransaction();

            // Bloquear el registro del cliente durante la reserva.
            $consulta = $pdo->prepare(
                'SELECT id FROM clientes
                 WHERE id = ? FOR UPDATE'
            );

            $consulta->execute([
                $_SESSION['usuario_id']
            ]);

            if (!$consulta->fetch()) {
                throw new RuntimeException(
                    'El cliente no existe.'
                );
            }

            // Bloquear temporalmente al peluquero seleccionado.
            $consulta = $pdo->prepare(
                "SELECT id FROM peluqueros
                 WHERE id = ? AND id_rol = (SELECT id FROM roles WHERE nombre = 'Peluquero')
                 FOR UPDATE"
            );

            $consulta->execute([$idPeluquero]);

            if (!$consulta->fetch()) {

                throw new RuntimeException(
                    'El peluquero seleccionado no existe.'
                );
            }

            // Verificar disponibilidad del profesional.
            $consulta = $pdo->prepare(
                "SELECT id FROM citas
                 WHERE id_peluquero = ?
                 AND fecha_hora = ?
                 AND estado IN ('Pendiente', 'Confirmada')
                 LIMIT 1"
            );

            $consulta->execute([
                $idPeluquero,
                $fechaSQL
            ]);

            if ($consulta->fetch()) {

                throw new RuntimeException(
                    'El horario seleccionado ya está reservado.'
                );
            }

            // Evitar que un cliente reserve dos citas
            // para el mismo horario.
            $consulta = $pdo->prepare(
                "SELECT id FROM citas
                 WHERE id_cliente = ?
                 AND fecha_hora = ?
                 AND estado IN ('Pendiente', 'Confirmada')
                 LIMIT 1"
            );

            $consulta->execute([
                $_SESSION['usuario_id'],
                $fechaSQL
            ]);

            if ($consulta->fetch()) {

                throw new RuntimeException(
                    'Ya tienes una cita en ese horario.'
                );
            }

            // Registrar la nueva cita.
            $consulta = $pdo->prepare(
                "INSERT INTO citas
                 (
                    id_cliente,
                    id_peluquero,
                    fecha_hora,
                    servicio,
                    estado,
                    observaciones
                 )
                 VALUES (?, ?, ?, ?, 'Pendiente', ?)"
            );

            $consulta->execute([
                $_SESSION['usuario_id'],
                $idPeluquero,
                $fechaSQL,
                $servicio,
                $observaciones
            ]);

            $pdo->commit();

            mensaje(
                'success',
                'Cita reservada. Espera la confirmación del peluquero.'
            );

            redireccionar('cliente/mis_citas.php');
        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            $error = 'No se pudo registrar la reserva. Intenta nuevamente.';
        } catch (RuntimeException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}

require __DIR__ . '/../includes/header.php';

?>

<div class="container">

    <div class="formulario-card ancho">

        <div class="text-center mb-4">

            <h2 class="mt-3">Reservar una cita</h2>

            <p class="text-muted">
                Completa los datos de tu próxima visita.
            </p>

        </div>

        <?php if ($error): ?>

            <div class="alert alert-danger" role="alert">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <?php if (empty($peluqueros)): ?>

            <div class="alert alert-warning">
                La agenda aún no está disponible. Intenta nuevamente más tarde.
            </div>

        <?php endif; ?>

        <form method="POST"
            class="needs-validation"
            novalidate>

            <input type="hidden"
                name="csrf"
                value="<?= e(tokenCsrf()) ?>">

            <div class="mb-3">

                <label for="id_peluquero"
                    class="form-label">

                    Seleccionar peluquero

                </label>

                <select name="id_peluquero"
                    id="id_peluquero"
                    class="form-select"
                    required>

                    <option value="">
                        Selecciona un profesional
                    </option>

                    <?php foreach ($peluqueros as $peluquero): ?>

                        <option
                            value="<?= e($peluquero['id']) ?>"
                            <?= $idPeluquero == $peluquero['id']
                                ? 'selected' : '' ?>>

                            <?= e($peluquero['nombre']) ?>

                            -

                            <?= e($peluquero['especialidad']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <div class="invalid-feedback">
                    Selecciona un peluquero.
                </div>

            </div>

            <div class="mb-3">

                <label for="servicio" class="form-label">
                    Seleccionar servicio
                </label>

                <select name="servicio"
                    id="servicio"
                    class="form-select"
                    required>

                    <option value="">
                        Selecciona un servicio
                    </option>

                    <?php foreach ($listaServicios as $nombre => $precio): ?>

                        <option value="<?= e($nombre) ?>"
                            data-precio="<?= e($precio) ?>"
                            <?= $servicio === $nombre
                                ? 'selected' : '' ?>>

                            <?= e($nombre) ?>

                            - $<?= number_format($precio, 2) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <div class="invalid-feedback">
                    Selecciona un servicio.
                </div>

            </div>

            <div class="mb-3">

                <label for="fecha_hora"
                    class="form-label">

                    Fecha y hora

                </label>

                <input type="datetime-local"
                    name="fecha_hora"
                    id="fecha_hora"
                    class="form-control"
                    min="<?= date('Y-m-d\TH:i') ?>"
                    max="<?= date(
                                'Y-m-d\TH:i',
                                strtotime('+90 days')
                            ) ?>"
                    value="<?= e($fechaHora) ?>"
                    required>

                <small class="text-muted">

                    Horario: 09:00 a 18:00.
                    Selecciona horas en punto o medias horas.

                </small>

            </div>

            <div class="mb-3">

                <label for="observaciones"
                    class="form-label">

                    Observaciones (opcional)

                </label>

                <textarea name="observaciones"
                    id="observaciones"
                    class="form-control"
                    rows="3"
                    maxlength="255"
                    placeholder="Indica alguna preferencia..."><?= e($observaciones) ?></textarea>

            </div>

            <div class="resumen-reserva">

                <div class="d-flex justify-content-between">

                    <strong>Precio estimado:</strong>

                    <strong class="precio"
                        id="precioServicio">

                        $0.00

                    </strong>

                </div>

                <small class="text-muted">

                    Duración estimada: 30 minutos.

                    El pago se realiza en el establecimiento.

                </small>

            </div>

            <button type="submit"
                class="btn btn-carbon w-100"
                <?= empty($peluqueros) ? 'disabled' : '' ?>>

                Confirmar reserva

            </button>

            <a href="<?= BASE_URL ?>/cliente/index.php"
                class="btn btn-outline-secondary w-100 mt-2">

                Regresar

            </a>

        </form>

    </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
