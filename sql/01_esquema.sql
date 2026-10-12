CREATE TABLE bodega (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE sucursal (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    bodega_id INTEGER NOT NULL REFERENCES bodega(id)
);

CREATE TABLE moneda (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE material (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE producto (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(15) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL CHECK (length(nombre) BETWEEN 2 AND 50),
    precio NUMERIC(12, 2) NOT NULL CHECK (precio > 0),
    descripcion TEXT NOT NULL CHECK (length(descripcion) BETWEEN 10 AND 1000),
    bodega_id INTEGER NOT NULL REFERENCES bodega(id),
    sucursal_id INTEGER NOT NULL REFERENCES sucursal(id),
    moneda_id INTEGER NOT NULL REFERENCES moneda(id),
    creado_en TIMESTAMP NOT NULL DEFAULT now()
);

CREATE TABLE producto_material (
    producto_id INTEGER NOT NULL REFERENCES producto(id) ON DELETE CASCADE,
    material_id INTEGER NOT NULL REFERENCES material(id),
    PRIMARY KEY (producto_id, material_id)
);

CREATE INDEX idx_sucursal_bodega ON sucursal (bodega_id);
