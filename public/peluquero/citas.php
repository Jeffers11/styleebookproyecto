<?php

require_once __DIR__ . '/../includes/funciones.php';

proteger('Peluquero');

$titulo = 'Administración de citas';

$estado = $_GET['estado'] ?? '';

$estados = [
    'Pendiente',
    'Confirmada',
    'Rechazada',
    'Cancelada',
    'Completada'
];

$sql = "SELECT c.*, cl.nombre AS cliente,
               cl.telefono, cl.correo
        FROM citas c
        INNER JOIN clientes cl
            ON c.id_cliente = cl.id
        WHERE c.id_peluquero = ?";

$parametros = [
    $_SESSION['usuario_id']
];

// Filtro opcional por estado.
if (in_array($estado, $estados, true)) {

    $sql .= ' AND c.estado = ?';

    $parametros[] = $estado;
}

$sql .= " ORDER BY
          CASE WHEN c.estado = 'Pendiente'
               THEN 0 ELSE 1 END,
          c.fecha_hora ASC";

$consulta = $pdo->prepare($sql);

$consulta->execute($parametros);

$citas = $consulta->fetchAll();

require __DIR__ . '/../includes/header.php';

?>

<div class="container panel">

    <div class="panel-encabezado panel-fondo-peluquero">

        <h1>Administración de citas</h1>

        <p class="text-muted">

            Gestiona las reservas de tus clientes.

        </p>

    </div>

    <div class="contenedor-tabla">

        <div class="row g-3 mb-4">

            <div class="col-md-6">

                <input type="search"
                       id="buscarCita"
                       class="form-control"
                       aria-label="Buscar cita"
                       placeholder="Buscar cliente o servicio...">

            </div>

            <div class="col-md-6">

                <form method="GET"
                      class="d-flex gap-2">

                    <select name="estado"
                            class="form-select"
                            aria-label="Filtrar por estado">

                        <option value="">
                            Todos los estados
                        </option>

                        <?php foreach ($estados as $opcion): ?>

                            <option value="<?= e($opcion) ?>"
                                <?= $estado === $opcion
                                    ? 'selected' : '' ?>>

                                <?= e($opcion) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <button class="btn btn-carbon"
                            type="submit">

                        Filtrar

                    </button>

                </form>

            </div>

        </div>

        <?php if (empty($citas)): ?>

            <div class="text-center py-5">

                <h4>No se encontraron citas</h4>

                <p class="text-muted">

                    Las nuevas solicitudes aparecerán aquí.

                </p>

            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Fecha y hora</th>
                            <th>Observaciones</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($citas as $cita): ?>

                            <tr data-fila-cita>

                                <td>

                                    <strong>
                                        <?= e($cita['cliente']) ?>
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        <?= e($cita['telefono']) ?>
                                    </small>

                                </td>

                                <td>
                                    <?= e($cita['servicio']) ?>
                                </td>

                                <td>

                                    <?= e(formatoFecha(
                                        $cita['fecha_hora']
                                    )) ?>

                                </td>

                                <td>

                                    <?= e(
                                        $cita['observaciones']
                                        ?: 'Sin observaciones'
                                    ) ?>

                                </td>

                                <td>

                                    <span class="badge text-bg-<?= e(
                                        claseEstado($cita['estado'])
                                    ) ?>">

                                        <?= e($cita['estado']) ?>

                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex flex-wrap gap-2">

                                        <?php if (
                                            $cita['estado'] === 'Pendiente'
                                        ): ?>

                                            <?php if (
                                                strtotime($cita['fecha_hora']) > time()
                                            ): ?>

                                                <form method="POST"
                                                      action="<?= BASE_URL ?>/peluquero/confirmar_cita.php"
                                                      data-confirmar="¿Confirmar esta cita?">

                                                    <input type="hidden"
                                                           name="csrf"
                                                           value="<?= e(tokenCsrf()) ?>">

                                                    <input type="hidden"
                                                           name="id"
                                                           value="<?= e($cita['id']) ?>">

                                                    <button class="btn btn-success btn-sm">

                                                        Confirmar

                                                    </button>

                                                </form>

                                            <?php endif; ?>

                                            <form method="POST"
                                                  action="<?= BASE_URL ?>/peluquero/rechazar_cita.php"
                                                  data-confirmar="¿Rechazar esta cita?">

                                                <input type="hidden"
                                                       name="csrf"
                                                       value="<?= e(tokenCsrf()) ?>">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= e($cita['id']) ?>">

                                                <button class="btn btn-danger btn-sm">

                                                    Rechazar

                                                </button>

                                            </form>

                                        <?php elseif (
                                            $cita['estado'] === 'Confirmada' &&
                                            strtotime($cita['fecha_hora']) <= time()
                                        ): ?>

                                            <form method="POST"
                                                  action="<?= BASE_URL ?>/peluquero/completar_cita.php"
                                                  data-confirmar="¿La cita ya fue atendida?">

                                                <input type="hidden"
                                                       name="csrf"
                                                       value="<?= e(tokenCsrf()) ?>">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= e($cita['id']) ?>">

                                                <button class="btn btn-primary btn-sm">

                                                    Completar

                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <span class="text-muted">

                                                Sin acciones

                                            </span>

                                        <?php endif; ?>

                                    </div>

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
