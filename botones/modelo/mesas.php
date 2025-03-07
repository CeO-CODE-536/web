<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurante";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

// --- LÓGICA PARA DEVOLVER JSON (API) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api'])) {
  // Obtener mesas
  $sql = "SELECT * FROM mesas";
  $result = $conn->query($sql);

  $mesas = [];
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $mesas[] = $row;
    }
  }
  echo json_encode($mesas);
  exit;  // Salimos para no cargar el HTML

} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  // Actualizar tiempo de una mesa
  $data = json_decode(file_get_contents("php://input"), true);
  $id = $data['id'];
  $tiempo = $data['tiempo'];

  $sql = "UPDATE mesas SET tiempo = $tiempo WHERE id = $id";
  if ($conn->query($sql)) {
    echo json_encode(["message" => "Tiempo actualizado"]);
  } else {
    echo json_encode(["error" => "Error al actualizar"]);
  }
  exit;  // Salimos para no cargar el HTML
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Atención de Mesas</title>
  <link rel="icon" href="../vista/img/logo2.png" type="image/png">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --primary-color: #6F4F1F;
      --secondary-color: #D9C9A2;
      --bg-color: #FAF3E0;
      --white: #FFF;
      --gray: #777;
      --hover-color: #E4D1B9;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg-color);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
      min-height: 100vh;
    }
    .site-header {
      width: 100%;
      background: linear-gradient(90deg, #D1C4A1, #A79B72);
      padding: 30px 0;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .header-content {
      max-width: 1100px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    .header-content img.logo {
      position: absolute;
      left: 20px;
      height: 70px;
    }
    .header-content h1 {
      font-size: 36px;
      color: var(--primary-color);
      font-weight: 600;
      text-align: center;
    }
    .container {
      width: 100%;
      max-width: 900px;
      margin-top: 30px;
    }
    #mesas {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .mesa {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: var(--white);
      border: 2px solid var(--primary-color);
      border-radius: 12px;
      padding: 18px;
      box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
      gap: 10px;
    }
    .cronometro {
      font-size: 20px;
      color: var(--primary-color);
      background-color: var(--white);
      padding: 8px 15px;
      border-radius: 6px;
      border: 2px solid var(--primary-color);
      min-width: 80px;
      text-align: center;
    }
    .btn-crono {
      padding: 8px 15px;
      font-size: 16px;
      border: 1px solid var(--primary-color);
      border-radius: 6px;
      background-color: var(--white);
      color: var(--primary-color);
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .btn-crono:hover {
      background-color: var(--hover-color);
    }
    .botones-nav {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 30px;
    }
    .botones-nav a {
      padding: 12px 25px;
      font-size: 18px;
      background-color: var(--primary-color);
      color: var(--white);
      text-decoration: none;
      border-radius: 6px;
      transition: background-color 0.3s ease;
    }
    .botones-nav a:hover {
      background-color: var(--secondary-color);
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="header-content">
      <img src="../vista/img/logo.png" alt="Logo" class="logo">
      <h1>Atención de Mesas</h1>
    </div>
  </header>
  <div class="container">
    <div id="mesas"></div>
  </div>
  <div class="botones-nav">
    <a href="../control/mesero.php">Atrás</a>
    <a href="../modelo/pedidos.html">Pedidos</a>
    <a href="../control/maqueta.html">Mapa</a>
  </div>

  <script>
    // Objeto para guardar las funciones de cada cronómetro
    const cronometros = {};

    // Cargar mesas al iniciar la página
    document.addEventListener('DOMContentLoaded', cargarMesas);

    async function cargarMesas() {
      // Llamamos al mismo archivo PHP, pero con ?api=1 para que devuelva JSON
      const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?api=1');
      const mesas = await response.json();
      const mesasContainer = document.getElementById('mesas');
      mesasContainer.innerHTML = '';

      mesas.forEach(mesa => {
        // Crear contenedor de la mesa
        const mesaElement = document.createElement('div'); 
        mesaElement.classList.add('mesa');

        // HTML interno
        mesaElement.innerHTML = `
          <span>Mesa-${mesa.numero}</span>
          <span class="cronometro" id="cronometro-${mesa.id}">${formatearTiempo(mesa.tiempo)}</span>
          <button class="btn-crono" id="start-${mesa.id}">Iniciar</button>
          <button class="btn-crono" id="stop-${mesa.id}">Detener</button>
        `;

        // Agregarlo al contenedor principal
        mesasContainer.appendChild(mesaElement);

        // Crear el "manejador" del cronómetro
        cronometros[mesa.id] = crearCronometro(`cronometro-${mesa.id}`, mesa.tiempo);

        // Eventos para los botones Iniciar / Detener
        document.getElementById(`start-${mesa.id}`).addEventListener('click', () => {
          cronometros[mesa.id].start();
        });
        document.getElementById(`stop-${mesa.id}`).addEventListener('click', () => {
          cronometros[mesa.id].stop();
          // Al detener, enviamos el tiempo actual al servidor
          const segundosActuales = cronometros[mesa.id].getTime();
          actualizarTiempoMesa(mesa.id, segundosActuales);
        });
      });
    }

    // Función que crea un objeto cronómetro con start/stop
    function crearCronometro(elementId, tiempoInicial) {
      let segundos = parseInt(tiempoInicial, 10) || 0;
      let intervalId = null;

      // Actualiza el texto en pantalla
      function actualizarDisplay() {
        const minutos = Math.floor(segundos / 60);
        const seg = segundos % 60;
        const el = document.getElementById(elementId);
        if (el) {
          el.textContent = (minutos < 10 ? "0" : "") + minutos + ":" + (seg < 10 ? "0" : "") + seg;
        }
      }

      // Empezar el conteo
      function start() {
        // Evitar múltiples intervalos
        if (!intervalId) {
          intervalId = setInterval(() => {
            segundos++;
            actualizarDisplay();
          }, 1000);
        }
      }

      // Detener el conteo
      function stop() {
        if (intervalId) {
          clearInterval(intervalId);
          intervalId = null;
        }
      }

      // Inicializar la vista
      actualizarDisplay();

      return {
        start,
        stop,
        getTime: () => segundos
      };
    }

    // Formatea tiempo en mm:ss si lo necesitas en otros lugares
    function formatearTiempo(segundos) {
      segundos = parseInt(segundos, 10) || 0;
      const minutos = Math.floor(segundos / 60);
      const seg = segundos % 60;
      return (minutos < 10 ? "0" : "") + minutos + ":" + (seg < 10 ? "0" : "") + seg;
    }

    // Enviar al servidor el nuevo tiempo (PUT)
    function actualizarTiempoMesa(idMesa, segundos) {
      fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: idMesa, tiempo: segundos })
      })
      .then(res => res.json())
      .then(data => {
        console.log("Tiempo actualizado:", data);
      })
      .catch(err => console.error("Error al actualizar tiempo:", err));
    }
  </script>
</body>
</html>
