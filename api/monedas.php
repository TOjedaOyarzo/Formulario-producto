<?php

require_once __DIR__ . '/config.php';

try {
    $conexion = obtenerConexion();
    $sentencia = $conexion->query('SELECT id, nombre FROM moneda ORDER BY id');
    enviarJson($sentencia->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    enviarJson(['exito' => false, 'mensaje' => 'No se pudieron cargar las monedas.']);
}
