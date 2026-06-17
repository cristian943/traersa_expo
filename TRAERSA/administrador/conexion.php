<?php

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "traersa"
);

if ($conexion->connect_error) {
    die("Error: " . $conexion->connect_error);
}