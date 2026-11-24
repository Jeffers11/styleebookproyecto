<?php

require_once __DIR__ . '/../includes/funciones.php';

// Confirmar una cita pendiente perteneciente
// al peluquero autenticado.
actualizarCitaPeluquero(
    $pdo,
    'Confirmada',
    'Pendiente'
);