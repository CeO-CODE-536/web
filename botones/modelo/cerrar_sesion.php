<?php
// Iniciar sesión
session_start();

// Destruir todas las sesiones activas
session_destroy();

// Redirigir al usuario a la página de inicio de sesión o inicio
header("Location: ../vista/index.html");
exit();
?>
