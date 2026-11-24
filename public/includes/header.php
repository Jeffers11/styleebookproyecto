<?php
require_once __DIR__ . '/funciones.php';

$titulo = $titulo ?? 'StyleBook';
?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#272127">

    <meta name="description"
          content="StyleBook - Reserva tus citas de peluquería.">

    <title><?= e($titulo) ?> | StyleBook</title>

    <link rel="manifest"
          href="<?= BASE_URL ?>/manifest.json">

    <link rel="icon"
          href="<?= BASE_URL ?>/assets/img/logo.svg"
          type="image/svg+xml">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="<?= BASE_URL ?>/assets/css/estilos.css">

</head>

<body data-base-url="<?= e(BASE_URL) ?>">

<?php require __DIR__ . '/navbar.php'; ?>

<main class="contenido-principal">

    <div class="container pt-3">
        <?php mostrarMensaje(); ?>
    </div>
