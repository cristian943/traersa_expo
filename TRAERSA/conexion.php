<?php
// Configuracion de los datos para conectar con la base de datos
$servername = "localhost"; // Nombre del servidor, en este caso local
$username = "u561983941_cristian123"; // Usuario de la base de datos
$password = "Valoug456"; // Contrasena del usuario
$database = "u561983941_ga1_csgp"; // Nombre de la base de datos a usar

// Crear una nueva conexion usando la extension mysqli
$conn = new mysqli($servername, $username, $password, $database);

// Verificar si la conexion fallo y mostrar un mensaje claro
if ($conn->connect_error) {
    die("Error en la conexion a la base de datos: " . $conn->connect_error);
}

// Si llegamos aqui, la conexion se realizo correctamente
?>
