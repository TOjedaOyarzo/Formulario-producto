<?php

require_once __DIR__ . '/config.php';

$codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';

if ($codigo === '') {
    http_response_code(400);
    enviarJson(['exito' => false, 'mensaje' => 'Debe indicar un código.']);
    exit;
}

try {
    $conexion = obtenerConexion();
    $sentencia = $conexion->prepare(
        'SELECT COUNT(*) FROM producto WHERE UPPER(codigo) = UPPER(:codigo)'
    );
    $sentencia->execute([':codigo' => $codigo]);
    $existe = (int) $sentencia->fetchColumn() > 0;

    enviarJson(['existe' => $existe]);
} catch (PDOException $e) {
    http_response_code(500);
    enviarJson(['exito' => false, 'mensaje' => 'No se pudo verificar el código.']);
}
