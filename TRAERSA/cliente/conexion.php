<?php

// crear conexion a la base de datos TRAERSA
$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "traersa"
);

// detener si falla la conexion
if ($conexion->connect_error) {
    die("Error: " . $conexion->connect_error);
}