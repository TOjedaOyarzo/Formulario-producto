<?php

require_once __DIR__ . '/config.php';

$datos = recibirJson();

$codigo = isset($datos['codigo']) ? trim($datos['codigo']) : '';
$nombre = isset($datos['nombre']) ? trim($datos['nombre']) : '';
$bodega = isset($datos['bodega']) ? $datos['bodega'] : '';
$sucursal = isset($datos['sucursal']) ? $datos['sucursal'] : '';
$moneda = isset($datos['moneda']) ? $datos['moneda'] : '';
$precio = isset($datos['precio']) ? trim($datos['precio']) : '';
$materiales = isset($datos['materiales']) && is_array($datos['materiales']) ? $datos['materiales'] : [];
$descripcion = isset($datos['descripcion']) ? $datos['descripcion'] : '';

function responder($exito, $mensaje, $codigoHttp = 200)
{
    http_response_code($codigoHttp);
    enviarJson(['exito' => $exito, 'mensaje' => $mensaje]);
    exit;
}

$regexCodigo = '/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9]+$/';
$regexPrecio = '/^\d+(\.\d{1,2})?$/';

if ($codigo === '') {
    responder(false, 'El código del producto no puede estar en blanco.');
}

if (!preg_match($regexCodigo, $codigo)) {
    responder(false, 'El código del producto debe contener letras y números');
}

if (strlen($codigo) < 5 || strlen($codigo) > 15) {
    responder(false, 'El código del producto debe tener entre 5 y 15 caracteres.');
}

if ($nombre === '') {
    responder(false, 'El nombre del producto no puede estar en blanco.');
}

if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 50) {
    responder(false, 'El nombre del producto debe tener entre 2 y 50 caracteres.');
}

if ($precio === '') {
    responder(false, 'El precio del producto no puede estar en blanco.');
}

if (!preg_match($regexPrecio, $precio)) {
    responder(false, 'El precio del producto debe ser un número positivo con hasta dos decimales.');
}

if (count($materiales) < 2) {
    responder(false, 'Debe seleccionar al menos dos materiales para el producto.');
}

if ($bodega === '' || !ctype_digit((string) $bodega)) {
    responder(false, 'Debe seleccionar una bodega.');
}

if ($sucursal === '' || !ctype_digit((string) $sucursal)) {
    responder(false, 'Debe seleccionar una sucursal para la bodega seleccionada.');
}

if ($moneda === '' || !ctype_digit((string) $moneda)) {
    responder(false, 'Debe seleccionar una moneda para el producto.');
}

if ($descripcion === '') {
    responder(false, 'La descripción del producto no puede estar en blanco.');
}

if (mb_strlen($descripcion) < 10 || mb_strlen($descripcion) > 1000) {
    responder(false, 'La descripción del producto debe tener entre 10 y 1000 caracteres.');
}

try {
    $conexion = obtenerConexion();

    $sentencia = $conexion->prepare(
        'SELECT COUNT(*) FROM producto WHERE UPPER(codigo) = UPPER(:codigo)'
    );
    $sentencia->execute([':codigo' => $codigo]);
    if ((int) $sentencia->fetchColumn() > 0) {
        responder(false, 'El código del producto ya está registrado.');
    }

    $sentencia = $conexion->prepare(
        'SELECT COUNT(*) FROM sucursal WHERE id = :sucursal AND bodega_id = :bodega'
    );
    $sentencia->execute([':sucursal' => (int) $sucursal, ':bodega' => (int) $bodega]);
    if ((int) $sentencia->fetchColumn() === 0) {
        responder(false, 'La sucursal seleccionada no pertenece a la bodega seleccionada.');
    }

    $conexion->beginTransaction();

    $sentencia = $conexion->prepare(
        'INSERT INTO producto (codigo, nombre, precio, descripcion, bodega_id, sucursal_id, moneda_id)
         VALUES (:codigo, :nombre, :precio, :descripcion, :bodega, :sucursal, :moneda)
         RETURNING id'
    );
    $sentencia->execute([
        ':codigo' => $codigo,
        ':nombre' => $nombre,
        ':precio' => $precio,
        ':descripcion' => $descripcion,
        ':bodega' => (int) $bodega,
        ':sucursal' => (int) $sucursal,
        ':moneda' => (int) $moneda
    ]);
    $idProducto = (int) $sentencia->fetchColumn();

    $materiales = array_unique(array_map('strval', $materiales));
    $insertarMaterial = $conexion->prepare(
        'INSERT INTO producto_material (producto_id, material_id)
         SELECT :producto, m.id FROM material m WHERE m.nombre = :nombre'
    );

    foreach ($materiales as $material) {
        $insertarMaterial->execute([
            ':producto' => $idProducto,
            ':nombre' => $material
        ]);
    }

    $conexion->commit();
    responder(true, 'Producto guardado correctamente.');
} catch (PDOException $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    if ($e->getCode() === '23505') {
        responder(false, 'El código del producto ya está registrado.');
    }

    responder(false, 'No se pudo guardar el producto.', 500);
}
