<?php
$servername = "localhost";
$username = "u561983941_cristian123";
$password = "Valoug456";
$database = "u561983941_ga1_csgp";

$conn = new mysqli($servername,$username,$password,$database);

if ($conn->connect_error){
    die("Error en la conexion a la base de datos: " . $conn->connect_error);
}
?>
