<?php

require_once __DIR__ . '/../includes/funciones.php';

proteger('Cliente');

$titulo = 'Mis citas';
$consulta = $pdo->prepare(
    "SELECT c.id, c.servicio, c.fecha_hora, c.estado, p.nombre AS peluquero
     FROM citas c
     INNER JOIN peluqueros p ON p.id = c.id_peluquero
     WHERE c.id_cliente = ?
     ORDER BY c.fecha_hora DESC, c.id DESC"
);
$consulta->execute([$_SESSION['usuario_id']]);
$citas = $consulta->fetchAll();
$ahora = time();

require __DIR__ . '/../includes/header.php';
?>

<div class="container panel">
    <div class="panel-encabezado panel-fondo-cliente d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <div>
            <h1>Mis citas</h1>
            <p class="text-muted mb-0">Consulta el estado de tus reservas.</p>
        </div>
        <a class="btn btn-dorado" href="<?= BASE_URL ?>/cliente/reservar.php">Nueva reserva</a>
    </div>

    <?php if (!$citas): ?>
        <div class="tarjeta-panel">
            <h2 class="h5">Todavía no tienes reservas</h2>
            <p class="text-muted mb-0">Tus citas aparecerán aquí cuando reserves un servicio.</p>
        </div>
    <?php else: ?>
        <div class="contenedor-tabla table-responsive" tabindex="0" role="region" aria-label="Mis citas">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Servicio</th>
                        <th scope="col">Peluquero</th>
                        <th scope="col">Fecha y hora</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                        <tr>
                            <td><?= e($cita['servicio']) ?></td>
                            <td><?= e($cita['peluquero']) ?></td>
                            <td><?= e(formatoFecha($cita['fecha_hora'])) ?></td>
                            <td>
                                <span class="badge text-bg-<?= e(claseEstado($cita['estado'])) ?>">
                                    <?= e($cita['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (
                                    in_array($cita['estado'], ['Pendiente', 'Confirmada'], true)
                                    && strtotime($cita['fecha_hora']) > $ahora
                                ): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/cliente/cancelar_cita.php"
                                          data-confirmar="¿Deseas cancelar esta cita?">
                                        <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                                        <input type="hidden" name="id" value="<?= e($cita['id']) ?>">
                                        <button class="btn btn-outline-danger btn-sm" type="submit">Cancelar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
