<?php

require_once __DIR__ . '/seguridad.php';

function campoPost(string $nombre): string
{
    $valor = $_POST[$nombre] ?? '';

    return is_string($valor) ? $valor : '';
}

function correoValido(string $correo): bool
{
    return strlen($correo) <= 100 && filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

// Evitar la ejecución de HTML ingresado por usuarios.
function e($valor): string
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Redireccionar a una página interna.
function redireccionar(string $ruta): void
{
    header('Location: ' . BASE_URL . '/' . ltrim($ruta, '/'));
    exit;
}

// Guardar mensajes temporales.
function mensaje(string $tipo, string $texto): void
{
    $_SESSION['mensaje'] = [
        'tipo' => $tipo,
        'texto' => $texto
    ];
}

// Mostrar mensajes temporales.
function mostrarMensaje(): void
{
    if (isset($_SESSION['mensaje'])) {

        $mensaje = $_SESSION['mensaje'];

        echo '<div role="alert" class="alert alert-' .
            e($mensaje['tipo']) . '">' .
            e($mensaje['texto']) . '</div>';

        unset($_SESSION['mensaje']);
    }
}

// Formatear fecha de una cita.
function formatoFecha(string $fecha): string
{
    return date('d/m/Y - H:i', strtotime($fecha));
}

function catalogoServicios(): array
{
    return [
        'Corte clásico' => ['precio' => 5.00, 'imagen' => 'corte-clasico'],
        'Corte y barba' => ['precio' => 8.00, 'imagen' => 'corte-barba'],
        'Peinado' => ['precio' => 10.00, 'imagen' => 'peinado-color'],
        'Tinturado' => ['precio' => 20.00, 'imagen' => 'tinturado'],
        'Tratamiento capilar' => ['precio' => 15.00, 'imagen' => 'tratamiento'],
        'Lavado y brushing' => ['precio' => 9.00, 'imagen' => 'lavado-brushing'],
        'Perfilado de barba' => ['precio' => 4.00, 'imagen' => 'perfilado-barba'],
        'Hidratación profunda' => ['precio' => 18.00, 'imagen' => 'hidratacion-profunda']
    ];
}

function servicios(): array
{
    return array_map(fn (array $servicio): float => $servicio['precio'], catalogoServicios());
}

function servicioSolicitado(): string
{
    $servicio = $_GET['servicio'] ?? '';

    return is_string($servicio) && isset(catalogoServicios()[$servicio]) ? $servicio : '';
}

// Asignar estilo visual según el estado de una cita.
function claseEstado(string $estado): string
{
    $clases = [
        'Pendiente' => 'warning',
        'Confirmada' => 'success',
        'Rechazada' => 'danger',
        'Cancelada' => 'secondary',
        'Completada' => 'primary'
    ];

    return $clases[$estado] ?? 'secondary';
}

// Consultar el total de citas según usuario y estado.
function contarCitas(
    PDO $pdo,
    string $rol,
    int $id,
    ?string $estado = null
): int {

    $columna = $rol === 'Cliente'
        ? 'id_cliente'
        : 'id_peluquero';

    $sql = "SELECT COUNT(*) FROM citas
            WHERE $columna = ?";

    $parametros = [$id];

    if ($estado !== null) {
        $sql .= ' AND estado = ?';
        $parametros[] = $estado;
    }

    $consulta = $pdo->prepare($sql);
    $consulta->execute($parametros);

    return (int) $consulta->fetchColumn();
}

// Actualizar una cita únicamente si pertenece al peluquero
// y se encuentra en el estado requerido.
function actualizarCitaPeluquero(
    PDO $pdo,
    string $nuevoEstado,
    string $estadoAnterior
): void {

    proteger('Peluquero');
    validarPost();

    $id = filter_var(
        $_POST['id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($id < 1) {
        mensaje('danger', 'Cita no válida.');
        redireccionar('peluquero/citas.php');
    }

    $sql = "UPDATE citas
            SET estado = ?
            WHERE id = ?
            AND id_peluquero = ?
            AND estado = ?";

    // Las citas pasadas no pueden confirmarse.
    if ($nuevoEstado === 'Confirmada') {
        $sql .= ' AND fecha_hora > NOW()';
    }

    // Una cita futura no puede marcarse como completada.
    if ($nuevoEstado === 'Completada') {
        $sql .= ' AND fecha_hora <= NOW()';
    }

    $consulta = $pdo->prepare($sql);

    $consulta->execute([
        $nuevoEstado,
        $id,
        $_SESSION['usuario_id'],
        $estadoAnterior
    ]);

    if ($consulta->rowCount() > 0) {
        mensaje('success', 'Estado actualizado correctamente.');
    } else {
        mensaje('danger', 'No fue posible actualizar la cita.');
    }

    redireccionar('peluquero/citas.php');
}
