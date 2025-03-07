<?php
// Habilitar la visualización de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurante";

// Crear conexión con la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesamiento de formularios para agregar o eliminar empleados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'agregar':
            $nombre = $_POST['nombre'] ?? '';
            $mesasAtendidas = $_POST['mesasAtendidas'] ?? 0;

            if (!empty($nombre) && is_numeric($mesasAtendidas) && $mesasAtendidas >= 0) {
                // Verificar si el empleado califica para bono
                $bono = '';
                if ($mesasAtendidas >= 15 && $mesasAtendidas <= 40) {
                    $bono = '¡Felicidades! El empleado ha calificado para un bono extra.';
                }

                $stmt = $conn->prepare("INSERT INTO empleados (nombre, mesas_atendidas, bono) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $nombre, $mesasAtendidas, $bono);
                if ($stmt->execute()) {
                    echo json_encode(["success" => "Empleado agregado correctamente", "bono" => $bono]);
                } else {
                    echo json_encode(["error" => "Error al agregar empleado"]);
                }
                $stmt->close();
            } else {
                echo json_encode(["error" => "Datos inválidos"]);
            }
            break;
        
        case 'eliminar':
            $id = $_POST['id'] ?? 0;
            if ($id > 0) {
                $stmt = $conn->prepare("DELETE FROM empleados WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    echo json_encode(["success" => "Empleado eliminado"]);
                } else {
                    echo json_encode(["error" => "Error al eliminar"]);
                }
                $stmt->close();
            } else {
                echo json_encode(["error" => "ID inválido"]);
            }
            break;
    }
    exit();
}

// Obtener lista de empleados
$sql = "SELECT * FROM empleados";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados</title>
    <link rel="icon" href="../vista/img/logo2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            background-color: #2C3E50; /* Azul oscuro */
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
            font-size: 1.4em; /* Tamaño de fuente reducido */
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
            background-color: #34495E; /* Gris oscuro */
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

        .card {
            background-color: #FFF;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 700px;
            text-align: center;
        }

        .card h3 {
            margin-bottom: 10px;
            color: #2C3E50;
        }

        .chart-container {
            width: 70%; /* Tamaño mediano */
            max-width: 700px; /* Tamaño máximo */
            height: 400px; /* Altura mediana */
            margin: 20px auto;
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

        .download-btn {
            background-color: #3498DB; /* Azul brillante */
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 5px; /* Ajustado para que esté más cerca */
            transition: background-color 0.3s;
        }

        .download-btn:hover {
            background-color: #2980B9; /* Azul oscuro */
        }

        /* Estilos adicionales para el panel */
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

        .btn-edit, .btn-delete {
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
    </style>
    <script defer>
        document.addEventListener("DOMContentLoaded", function () {
            // Mostrar la fecha actual
            const fecha = new Date().toLocaleDateString('es-ES');
            document.getElementById('fechaActual').textContent = "Fecha actual: " + fecha;

            document.getElementById("agregarEmpleadoForm").addEventListener("submit", function (event) {
                event.preventDefault();
                let formData = new FormData(this);
                formData.append("accion", "agregar");
                fetch("", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.success || data.error);
                    if (data.success && data.bono) {
                        alert(data.bono); // Notificación del bono extra
                    }
                    if (data.success) location.reload();
                })
                .catch(error => console.error("Error:", error));
            });
        });
        
        function eliminarEmpleado(id) {
            if (confirm("¿Seguro que deseas eliminar este empleado?")) {
                let formData = new FormData();
                formData.append("accion", "eliminar");
                formData.append("id", id);
                fetch("", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.success || data.error);
                    if (data.success) location.reload();
                })
                .catch(error => console.error("Error:", error));
            }
        }
    </script>
</head>
<body>
    <!-- Barra lateral -->
    <div class="sidebar">
    <h2>
        <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_640.png" alt="Foto de perfil">
      Panel administrador
    </h2>
    <a href="../control/admin.html">Inicio</a>
        <a href="#productos">Productos</a>
        
    </div>

    <!-- Contenido principal -->
    <div class="main-content">
        <h1>Gestión de Empleados</h1>

        <!-- Mostrar la fecha actual -->
        <p id="fechaActual" class="text-center text-muted"></p>

        <form id="agregarEmpleadoForm" method="post">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Empleado</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="mesasAtendidas" class="form-label">Mesas Atendidas</label>
                <input type="number" id="mesasAtendidas" name="mesasAtendidas" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Agregar</button>
            <a href="../control/admin.html" class="btn btn-secondary">Atrás</a>
        </form>
        <h3 class="mt-4">Empleados Registrados</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Mesas Atendidas</th>
                    <th>Bono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= $row['mesas_atendidas'] ?></td>
                    <td><?= $row['bono'] ?: 'Ninguno' ?></td>
                    <td>
                        <button class='btn btn-danger btn-sm' onclick='eliminarEmpleado(<?= $row['id'] ?>)'>Eliminar</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Pie de página -->
    <div class="footer">
    © 2025 CEO & WORKS CONSULTORES
    </div>
</body>
</html>

<?php $conn->close(); ?>