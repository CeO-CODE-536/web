CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  especificaciones TEXT NOT NULL,
  precio DECIMAL(10, 2) NOT NULL
);