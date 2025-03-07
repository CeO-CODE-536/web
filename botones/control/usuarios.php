<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root"; // Cambia si es necesario
$password = "";     // Cambia si tienes contraseña
$dbname = "restaurante"; // Cambia al nombre correcto de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar acciones CRUD solo si existe la clave 'action' en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case "agregar":
            agregarEmpleado($conn);
            break;
        case "editar":
            editarEmpleado($conn);
            break;
        case "eliminar":
            eliminarEmpleado($conn);
            break;
        case "descargar_bitacora":
            descargarBitacora($conn);
            break;
        case "cambiar_estado":
            cambiarEstado($conn);
            break;
    }
    exit(); // Evita cargar el HTML después de ejecutar una acción
}

// Función para agregar empleado (se aprovecha el valor por defecto en la columna 'estado' de la BD)
function agregarEmpleado($conn) {
    if (!empty(trim($_POST['nombre'])) && !empty(trim($_POST['turno'])) && !empty(trim($_POST['contacto']))) {
        $nombre = trim($_POST['nombre']);
        $turno = trim($_POST['turno']);
        $contacto = trim($_POST['contacto']);

        $stmt = $conn->prepare("INSERT INTO meseros (nombre, turno, contacto) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $turno, $contacto);

        if ($stmt->execute()) {
            echo "Empleado agregado correctamente.";
        } else {
            echo "Error al agregar el empleado: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Debe proporcionar un nombre, turno y contacto válidos.";
    }
}

// Función para editar empleado
function editarEmpleado($conn) {
    if (isset($_POST['id']) && !empty(trim($_POST['nombre'])) &&
        !empty(trim($_POST['turno'])) && !empty(trim($_POST['contacto']))) {

        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $turno = trim($_POST['turno']);
        $contacto = trim($_POST['contacto']);

        $stmt = $conn->prepare("UPDATE meseros SET nombre = ?, turno = ?, contacto = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre, $turno, $contacto, $id);

        if ($stmt->execute()) {
            echo "Empleado actualizado correctamente.";
        } else {
            echo "Error al actualizar el empleado: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Debe proporcionar un nombre, turno y contacto válidos.";
    }
}

// Función para eliminar empleado
function eliminarEmpleado($conn) {
    if (isset($_POST['id'])) {
        $id = intval($_POST['id']);

        $stmt = $conn->prepare("DELETE FROM meseros WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Empleado eliminado correctamente.";
        } else {
            echo "Error al eliminar el empleado: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Debe proporcionar un ID válido.";
    }
}

// Función para descargar la bitácora
function descargarBitacora($conn) {
    // Obtener las fechas de la semana anterior (desde el lunes hasta el domingo pasado)
    $fechaInicioSemana = date('Y-m-d', strtotime('last Monday'));
    $fechaFinSemana = date('Y-m-d', strtotime('last Sunday'));

    $sql = "SELECT * FROM meseros WHERE fecha_registro BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $fechaInicioSemana, $fechaFinSemana);
    $stmt->execute();
    $result = $stmt->get_result();

    // Encabezados para la descarga del archivo CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="bitacora_semana.csv"');

    // Abrir un archivo temporal para la descarga del CSV
    $csvFile = fopen('php://output', 'w');
    
    // Escribir los encabezados de las columnas en el CSV
    fputcsv($csvFile, ['ID', 'Nombre', 'Turno', 'Contacto', 'Fecha de Registro', 'Estado']);
    
    // Escribir los registros de la base de datos en el archivo CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($csvFile, $row);
    }
    
    fclose($csvFile);
    exit();
}

// Función para cambiar el estado del empleado (activar/desactivar)
function cambiarEstado($conn) {
    if (isset($_POST['id']) && isset($_POST['estado'])) {
        $id = intval($_POST['id']);
        $nuevoEstado = $_POST['estado']; // 'activo' o 'inactivo'

        $stmt = $conn->prepare("UPDATE meseros SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevoEstado, $id);

        if ($stmt->execute()) {
            echo "Estado actualizado correctamente.";
        } else {
            echo "Error al actualizar el estado: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Faltan parámetros para cambiar el estado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios - Panel Administrativo</title>
  
  <!-- Favicon con una imagen PNG -->
  <link rel="icon" href="../vista/img/logo2.png" type="image/png">

  <style>
      * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
      }
      body {
          font-family: 'Arial', sans-serif;
          display: flex;
          min-height: 100vh;
          background-color: rgb(208, 210, 213);
      }
      .sidebar {
          width: 250px;
          background-color: #2C3E50;
          color: white;
          padding: 20px;
          position: fixed;
          top: 0;
          left: 0;
          height: 100%;
      }
      .sidebar h2 {
          margin-bottom: 30px;
          color: #ECF0F1;
          font-size: 1.4em;
          display: flex;
          align-items: center;
      }
      .sidebar h2 img {
          border-radius: 50%;
          width: 40px;
          height: 40px;
          margin-right: 10px;
      }
      .sidebar a {
          display: block;
          padding: 12px;
          text-decoration: none;
          color: #BDC3C7;
          margin-bottom: 10px;
          border-radius: 5px;
          font-size: 1.1em;
          transition: background-color 0.3s;
      }
      .sidebar a:hover {
          background-color: #34495E;
      }
      .main-content {
          margin-left: 250px;
          padding: 20px;
          width: 100%;
          background-color: #fff;
          display: flex;
          flex-direction: column;
          align-items: center;
      }
      .main-content h1 {
          margin-bottom: 20px;
          font-size: 2em;
          color: #2C3E50;
      }
      .button-container {
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 10px;
          margin-top: 20px;
      }
      .download-btn {
          background-color: #3498DB;
          color: white;
          padding: 12px 24px;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          font-size: 16px;
          transition: background-color 0.3s;
      }
      .download-btn:hover {
          background-color: #2980B9;
      }
      table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 20px;
      }
      th, td {
          border: 1px solid #ddd;
          padding: 12px;
          text-align: left;
      }
      th {
          background-color: #2C3E50;
          color: white;
      }
      tr:nth-child(even) {
          background-color: #f2f2f2;
      }
      .btn-edit, .btn-delete, .btn-state {
          padding: 8px 12px;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          font-size: 14px;
          margin: 2px;
      }
      .btn-edit {
          background-color: #3498DB;
          color: white;
      }
      .btn-edit:hover {
          background-color: #2980B9;
      }
      .btn-delete {
          background-color: #E74C3C;
          color: white;
      }
      .btn-delete:hover {
          background-color: #C0392B;
      }
      .btn-state {
          background-color: #27AE60;
          color: white;
      }
      .btn-state:hover {
          background-color: #1E8449;
      }
      .footer {
          position: fixed;
          bottom: 0;
          left: 250px;
          width: calc(100% - 250px);
          background-color: #2C3E50;
          color: white;
          text-align: center;
          padding: 15px;
      }
  </style>
</head>
<body>
  <!-- Barra lateral -->
  <div class="sidebar">
      <h2>
          <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_640.png" alt="Foto de perfil">
          Panel administrador
      </h2>
      <a href="../control/admin.html">Inicio</a>
      <a href="#usuarios">Usuarios</a>
  </div>

  <!-- Contenido principal -->
  <div class="main-content">
      <h1>Usuarios</h1>

      <div class="button-container">
          <button class="download-btn" onclick="agregarEmpleado()">Agregar</button>
          <a href="../control/admin.html" class="download-btn">Atrás</a>
          <button class="download-btn" onclick="descargarBitacora()">Descargar Bitácora</button>
      </div>

      <table>
          <thead>
              <tr>
                  <th>Empleado</th>
                  <th>Turno</th>
                  <th>Contacto</th>
                  <th>Fecha de Registro</th>
                  <th>Estado</th>
                  <th>Acciones</th>
              </tr>
          </thead>
          <tbody>
              <?php
              $sql = "SELECT * FROM meseros";
              $result = $conn->query($sql);

              if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      // Mostrar un ícono o texto para el estado
                      $estadoIcono = ($row['estado'] === 'activo') ? '🟢 Activo' : '🔴 Inactivo';
                      $nuevoEstado = ($row['estado'] === 'activo') ? 'inactivo' : 'activo';
                      $textoBotonEstado = ($row['estado'] === 'activo') ? 'Desactivar' : 'Activar';

                      echo "<tr>
                              <td>".$row['nombre']."</td>
                              <td>".$row['turno']."</td>
                              <td>".$row['contacto']."</td>
                              <td>".$row['fecha_registro']."</td>
                              <td>".$estadoIcono."</td>
                              <td>
                                  <button class='btn-edit' onclick='editarEmpleado(".$row['id'].")'>Editar</button>
                                  <button class='btn-delete' onclick='eliminarEmpleado(".$row['id'].")'>Eliminar</button>
                                  <button class='btn-state' onclick='cambiarEstado(".$row['id'].", \"".$nuevoEstado."\")'>".$textoBotonEstado."</button>
                              </td>
                            </tr>";
                  }
              } else {
                  echo "<tr><td colspan='6'>No hay registros disponibles.</td></tr>";
              }
              ?>
          </tbody>
      </table>
  </div>

  <!-- Pie de página -->
  <div class="footer">
      © 2025 CEO & WORKS CONSULTORES
  </div>

  <script>
      function agregarEmpleado() {
          const nombre = prompt("Ingrese el nombre del empleado:");
          const turno = prompt("Ingrese el turno del empleado:");
          const contacto = prompt("Ingrese el contacto del empleado:");

          if (nombre && turno && contacto) {
              fetch('', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: `action=agregar&nombre=${encodeURIComponent(nombre)}&turno=${encodeURIComponent(turno)}&contacto=${encodeURIComponent(contacto)}`
              })
              .then(response => response.text())
              .then(data => {
                  alert(data);
                  location.reload();
              })
              .catch(error => console.error('Error:', error));
          }
      }

      function editarEmpleado(id) {
          const nuevoNombre = prompt("Nuevo nombre:");
          const nuevoTurno = prompt("Nuevo turno:");
          const nuevoContacto = prompt("Nuevo contacto:");

          if (nuevoNombre && nuevoTurno && nuevoContacto) {
              fetch('', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: `action=editar&id=${id}&nombre=${encodeURIComponent(nuevoNombre)}&turno=${encodeURIComponent(nuevoTurno)}&contacto=${encodeURIComponent(nuevoContacto)}`
              })
              .then(response => response.text())
              .then(data => {
                  alert(data);
                  location.reload();
              })
              .catch(error => console.error('Error:', error));
          }
      }

      function eliminarEmpleado(id) {
          if (confirm("¿Estás seguro de eliminar este empleado?")) {
              fetch('', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: `action=eliminar&id=${id}`
              })
              .then(response => response.text())
              .then(data => {
                  alert(data);
                  location.reload();
              })
              .catch(error => console.error('Error:', error));
          }
      }

      function cambiarEstado(id, nuevoEstado) {
          fetch('', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `action=cambiar_estado&id=${id}&estado=${encodeURIComponent(nuevoEstado)}`
          })
          .then(response => response.text())
          .then(data => {
              alert(data);
              location.reload();
          })
          .catch(error => console.error('Error:', error));
      }

      function descargarBitacora() {
          fetch('', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'action=descargar_bitacora'
          })
          .then(response => response.blob())
          .then(blob => {
              const link = document.createElement('a');
              link.href = URL.createObjectURL(blob);
              link.download = 'bitacora_semana.csv';
              link.click();
          })
          .catch(error => console.error('Error:', error));
      }
  </script>
</body>
</html>
<?php $conn->close(); ?>
