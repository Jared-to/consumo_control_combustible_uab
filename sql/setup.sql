CREATE DATABASE IF NOT EXISTS combustible_db;
USE combustible_db;

CREATE TABLE IF NOT EXISTS cargas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    kilometraje_actual DECIMAL(10, 2) NOT NULL,
    litros_cargados DECIMAL(10, 2) NOT NULL,
    precio_total DECIMAL(10, 2) NOT NULL,
    surtidor VARCHAR(100) NOT NULL,
    kilometros_recorridos DECIMAL(10, 2) DEFAULT 0,
    rendimiento_kml DECIMAL(10, 2) DEFAULT 0,
    costo_por_km DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);