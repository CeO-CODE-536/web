CREATE TABLE IF NOT EXISTS meseros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    turno ENUM('mañana', 'tarde', 'noche') NOT NULL,
    contacto VARCHAR(50) NOT NULL UNIQUE,
    estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
