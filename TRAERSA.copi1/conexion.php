<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "traersa";

$conn = new mysqli($servername,$username,$password,$database);

if ($conn->connect_error){
    die("Error en la conexion a la base de datos: " . $conn->connect_error);
}
?>
