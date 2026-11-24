<?php

require_once __DIR__ . '/../includes/funciones.php';

// Marcar como completada una cita confirmada
// cuya fecha y hora ya hayan llegado.
actualizarCitaPeluquero(
    $pdo,
    'Completada',
    'Confirmada'
);