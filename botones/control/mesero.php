<?php
// Configuración de conexión a la base de datos
$servername = "localhost";
$username = "root";  // Cambia esto por tu usuario
$password = "";      // Cambia esto por tu contraseña
$dbname = "restaurante";  // Cambia esto por el nombre de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar si la consulta fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Actualización de estado si se recibe la petición
if (isset($_POST['id']) && isset($_POST['estado'])) {
    $id = $_POST['id'];
    $estado = $_POST['estado'];

    // Actualizar el estado en la base de datos
    $sql = "UPDATE meseros SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $estado, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

    $stmt->close();
    exit;  // Terminamos la ejecución aquí si es una solicitud de actualización
}

// Obtener los datos de los meseros
$sql = "SELECT * FROM meseros";
$result = $conn->query($sql);

if (!$result) {
    die("Error en la consulta SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Lista de Meseros</title>
  <link rel="icon" href="../vista/img/logo2.png" type="image/png">
  <style>
    :root {
      --primary-color: #6F4F1F;
      --secondary-color: #D9C9A2;
      --bg-color: #FAF3E0;
      --white: #FFF;
      --gray: #777;
      --hover-color: #E4D1B9;
    }
    body {
      font-family: Arial, sans-serif;
      background: var(--bg-color);
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 900px;
      margin: 20px auto;
      padding: 20px;
      background: var(--white);
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    header {
      display: flex;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #ddd;
      margin-bottom: 20px;
    }
    header img {
      width: 80px;
      height: auto;
      margin-right: 15px;
    }
    header h2 {
      font-size: 1.8em;
      margin: 0;
      color: var(--primary-color);
    }
    /* Botones de la parte superior */
    .button-container-group {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 20px;
    }
    .btn-link {
      padding: 10px 20px;
      background: var(--secondary-color);
      color: var(--white);
      text-decoration: none;
      border-radius: 5px;
      transition: background 0.3s;
      border: none;
      cursor: pointer;
    }
    .btn-link:hover {
      background: var(--primary-color);
    }
    /* Tabla */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 10px;
      text-align: center;
      border: 1px solid #ddd;
    }
    th {
      background: var(--primary-color);
      color: var(--white);
    }
    tr:nth-child(even) {
      background: var(--hover-color);
    }
    tr:hover {
      background: var(--secondary-color);
      color: var(--white);
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <img src="../vista/img/logo.png" alt="Logo">
      <h2>Lista de Meseros</h2>
    </header>

    <!-- Botones adicionales: Atrás y Atender -->
    <div class="button-container-group">
      <a href="../control/admin.html" class="btn-link">Atrás</a>
      <a href="../modelo/mesas.php" class="btn-link">Atender</a>
      <a href="../modelo/cerrar_sesion.php" class="btn-link">Cerrar Sesión</a>
    </div>

    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>Turno</th>
          <th>Contacto</th>
          <th>Estado</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
            $index = 1;  // Contador para las filas
            while ($row = $result->fetch_assoc()) {
                // Determinar estado con ícono
                $estado = ($row['estado'] == 'activo') ? '🟢 Activo' : '🔴 Inactivo';
                // Estado nuevo para actualizar
                $nuevoEstado = ($row['estado'] == 'activo') ? 'inactivo' : 'activo';
                // Texto del botón
                $textoBoton = "Botón #{$index}";

                echo "<tr>
                        <td>{$index}</td>
                        <td>{$row['nombre']}</td>
                        <td>{$row['turno']}</td>
                        <td>{$row['contacto']}</td>
                        <td>{$estado}</td>
                        <td>
                          <button class='btn-link' onclick='actualizarEstado({$row["id"]}, \"{$nuevoEstado}\")'>
                            {$textoBoton}
                          </button>
                        </td>
                      </tr>";
                $index++;
            }
        } else {
            echo "<tr><td colspan='6'>No hay registros disponibles.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <script>
    function actualizarEstado(id, nuevoEstado) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Estado actualizado a " + nuevoEstado);
                location.reload(); // Recarga la página para reflejar el cambio
            } else {
                alert("Error al actualizar: " + data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    }
  </script>
</body>
</html>

<?php $conn->close(); ?>
