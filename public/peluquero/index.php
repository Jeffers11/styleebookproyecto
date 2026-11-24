<?php

require_once __DIR__ . '/../includes/funciones.php';

proteger('Peluquero');

$titulo = 'Panel del peluquero';

$idPeluquero = (int) $_SESSION['usuario_id'];

$total = contarCitas(
    $pdo, 'Peluquero', $idPeluquero
);

$pendientes = contarCitas(
    $pdo, 'Peluquero', $idPeluquero, 'Pendiente'
);

$confirmadas = contarCitas(
    $pdo, 'Peluquero', $idPeluquero, 'Confirmada'
);

$completadas = contarCitas(
    $pdo, 'Peluquero', $idPeluquero, 'Completada'
);

// Consultar las citas correspondientes al día actual.
$inicioDia = date('Y-m-d 00:00:00');

$finDia = date(
    'Y-m-d 00:00:00',
    strtotime('+1 day')
);

$consulta = $pdo->prepare(
    "SELECT c.*, cl.nombre AS cliente
     FROM citas c
     INNER JOIN clientes cl
        ON c.id_cliente = cl.id
     WHERE c.id_peluquero = ?
     AND c.fecha_hora >= ?
     AND c.fecha_hora < ?
     AND c.estado IN ('Pendiente', 'Confirmada', 'Completada')
     ORDER BY c.fecha_hora ASC"
);

$consulta->execute([
    $idPeluquero,
    $inicioDia,
    $finDia
]);

$citasHoy = $consulta->fetchAll();

require __DIR__ . '/../includes/header.php';

?>

<div class="container panel">

    <div class="panel-encabezado panel-fondo-peluquero">

        <h1>Panel del peluquero</h1>

        <p class="text-muted">

            Bienvenido, <?= e($_SESSION['nombre']) ?>.

            Administra tu agenda de trabajo.

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

    <div class="tarjeta-panel mb-4">

        <div class="row align-items-center">

            <div class="col-md-8">

                <h4>Gestionar agenda</h4>

                <p class="text-muted mb-md-0">

                    Consulta las solicitudes de tus clientes,
                    confirma o rechaza las citas pendientes.

                </p>

            </div>

            <div class="col-md-4 text-md-end">

                <a href="<?= BASE_URL ?>/peluquero/citas.php"
                   class="btn btn-carbon">

                    Administrar citas

                </a>

            </div>

        </div>

    </div>

    <div class="contenedor-tabla">

        <h4 class="mb-4">

            Agenda de hoy -

            <?= date('d/m/Y') ?>

        </h4>

        <?php if (empty($citasHoy)): ?>

            <div class="text-center py-4">

                <p class="text-muted mt-3">

                    No tienes citas programadas para hoy.

                </p>

            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($citasHoy as $cita): ?>

                            <tr>

                                <td>

                                    <?= date(
                                        'H:i',
                                        strtotime($cita['fecha_hora'])
                                    ) ?>

                                </td>

                                <td>
                                    <?= e($cita['cliente']) ?>
                                </td>

                                <td>
                                    <?= e($cita['servicio']) ?>
                                </td>

                                <td>

                                    <span class="badge text-bg-<?= e(
                                        claseEstado($cita['estado'])
                                    ) ?>">

                                        <?= e($cita['estado']) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
