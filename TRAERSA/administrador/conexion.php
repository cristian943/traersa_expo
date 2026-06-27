<?php
// Información necesaria para conectarse al servidor de base de datos.
$servername = "localhost";
$username = "u561983941_cristian123";
$password = "Valoug456";
$database = "u561983941_ga1_csgp";

// Se establece la conexión con la base de datos usando mysqli.
$conn = new mysqli($servername,$username,$password,$database);

// Si la conexión falla, se detiene la ejecución y se muestra un mensaje claro.
if ($conn->connect_error){
    die("Error en la conexion a la base de datos: " . $conn->connect_error);
}
?>
