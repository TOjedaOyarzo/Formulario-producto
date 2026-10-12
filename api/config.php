<?php

function obtenerConexion()
{
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        'localhost',
        '5432',
        'productos'
    );

    $usuario = 'app';
    $password = 'app';

    try {
        $conexion = new PDO($dsn, $usuario, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $conexion;
    } catch (PDOException $e) {
        http_response_code(500);
        enviarJson(['exito' => false, 'mensaje' => 'No se pudo conectar con la base de datos.']);
        exit;
    }
}

function enviarJson($datos)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
}

function recibirJson()
{
    $contenido = file_get_contents('php://input');
    $datos = json_decode($contenido, true);

    if (!is_array($datos)) {
        http_response_code(400);
        enviarJson(['exito' => false, 'mensaje' => 'Datos inválidos.']);
        exit;
    }

    return $datos;
}
