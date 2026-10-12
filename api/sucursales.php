<?php

require_once __DIR__ . '/config.php';

$idBodega = isset($_GET['id_bodega']) ? $_GET['id_bodega'] : '';

if ($idBodega === '' || !ctype_digit((string) $idBodega)) {
    http_response_code(400);
    enviarJson(['exito' => false, 'mensaje' => 'Debe indicar una bodega válida.']);
    exit;
}

try {
    $conexion = obtenerConexion();
    $sentencia = $conexion->prepare(
        'SELECT id, nombre FROM sucursal WHERE bodega_id = :bodega ORDER BY id'
    );
    $sentencia->execute([':bodega' => (int) $idBodega]);
    enviarJson($sentencia->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    enviarJson(['exito' => false, 'mensaje' => 'No se pudieron cargar las sucursales.']);
}
