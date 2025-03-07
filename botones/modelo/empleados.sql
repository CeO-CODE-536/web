CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    mesas_atendidas INT NOT NULL,
    bono VARCHAR(255) DEFAULT 'Ninguno'
);
