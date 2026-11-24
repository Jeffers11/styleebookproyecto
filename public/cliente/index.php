<?php

require_once __DIR__ . '/../includes/funciones.php';

proteger('Cliente');

$titulo = 'Panel del cliente';

$idCliente = (int) $_SESSION['usuario_id'];

// Consultar estadísticas personales.
$total = contarCitas($pdo, 'Cliente', $idCliente);

$pendientes = contarCitas(
    $pdo, 'Cliente', $idCliente, 'Pendiente'
);

$confirmadas = contarCitas(
    $pdo, 'Cliente', $idCliente, 'Confirmada'
);

$completadas = contarCitas(
    $pdo, 'Cliente', $idCliente, 'Completada'
);

// Consultar la próxima cita activa.
$consulta = $pdo->prepare(
    "SELECT c.*, p.nombre AS peluquero
     FROM citas c
     INNER JOIN peluqueros p
        ON c.id_peluquero = p.id
     WHERE c.id_cliente = ?
     AND c.fecha_hora >= NOW()
     AND c.estado IN ('Pendiente', 'Confirmada')
     ORDER BY c.fecha_hora ASC
     LIMIT 1"
);

$consulta->execute([$idCliente]);

$proximaCita = $consulta->fetch();

require __DIR__ . '/../includes/header.php';

?>

<div class="container panel">

    <div class="panel-encabezado panel-fondo-cliente">

        <h1>Hola, <?= e($_SESSION['nombre']) ?></h1>

        <p class="text-muted">
            Bienvenido a tu panel personal de StyleBook.
        </p>

    </div>

    <div class="row g-3 mb-4">

        <?php

        $estadisticas = [
            'Citas totales' => $total,
            'Pendientes' => $pendientes,
            'Confirmadas' => $confirmadas,
            'Completadas' => $completadas
        ];

        ?>

        <?php foreach ($estadisticas as $nombre => $cantidad): ?>

            <div class="col-6 col-lg-3">

                <div class="tarjeta-estadistica">

                    <p><?= e($nombre) ?></p>

                    <h2><?= e($cantidad) ?></h2>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-6">

            <div class="tarjeta-panel">

                <h4>Reservar una cita</h4>

                <p class="text-muted">
                    Selecciona el servicio y horario
                    de tu próxima cita.
                </p>

                <a href="<?= BASE_URL ?>/cliente/reservar.php"
                   class="btn btn-carbon">

                    Nueva reserva

                </a>

            </div>

        </div>

        <div class="col-md-6">

            <div class="tarjeta-panel">

                <h4>Mis citas</h4>

                <p class="text-muted">
                    Consulta tus reservas y verifica
                    su estado actual.
                </p>

                <a href="<?= BASE_URL ?>/cliente/mis_citas.php"
                   class="btn btn-carbon">

                    Ver mis citas

                </a>

            </div>

        </div>

    </div>

    <div class="tarjeta-panel">

        <h4 class="mb-4">Próxima cita</h4>

        <?php if ($proximaCita): ?>

            <div class="row align-items-center">

                <div class="col-md-8">

                    <h5>
                        <?= e($proximaCita['servicio']) ?>
                    </h5>

                    <p class="mb-1">

                        Peluquero:

                        <?= e($proximaCita['peluquero']) ?>

                    </p>

                    <p class="mb-2">

                        Fecha:

                        <?= e(formatoFecha(
                            $proximaCita['fecha_hora']
                        )) ?>

                    </p>

                </div>

                <div class="col-md-4 text-md-end">

                    <span class="badge text-bg-<?= e(
                        claseEstado($proximaCita['estado'])
                    ) ?>">

                        <?= e($proximaCita['estado']) ?>

                    </span>

                </div>

            </div>

        <?php else: ?>

            <p class="text-muted">
                Todavía no tienes citas próximas.
            </p>

            <a href="<?= BASE_URL ?>/cliente/reservar.php"
               class="btn btn-dorado">

                Reserva tu primera cita

            </a>

        <?php endif; ?>

    </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
