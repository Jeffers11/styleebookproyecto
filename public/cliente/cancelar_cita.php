<?php

require_once __DIR__ . '/../includes/funciones.php';

proteger('Cliente');

validarPost();

$idCita = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($idCita < 1) {

    mensaje('danger', 'Identificador de cita no válido.');

    redireccionar('cliente/mis_citas.php');
}

// Solo se permite cancelar citas propias,
// futuras, pendientes o confirmadas.
$consulta = $pdo->prepare(
    "UPDATE citas
     SET estado = 'Cancelada'
     WHERE id = ?
     AND id_cliente = ?
     AND fecha_hora > NOW()
     AND estado IN ('Pendiente', 'Confirmada')"
);

$consulta->execute([
    $idCita,
    $_SESSION['usuario_id']
]);

if ($consulta->rowCount() > 0) {

    mensaje('success', 'La cita fue cancelada correctamente.');

} else {

    mensaje('danger', 'No fue posible cancelar la cita.');
}

redireccionar('cliente/mis_citas.php');
