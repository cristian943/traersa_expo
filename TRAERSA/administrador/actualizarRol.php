<?php

// Conectar a la base de datos
require '../conexion.php';

// Obtener datos enviados desde el formulario
$id = $_POST['id'];
$rol = $_POST['rol'];

// Consulta preparada para actualizar el rol del usuario
$sql = "
UPDATE usuarios
SET rol_id = ?
WHERE id = ?
";

// Preparar la consulta y evitar inyecciones SQL
$stmt = $conn->prepare($sql);

// Enlazar parametros: primero el rol, luego el id del usuario
$stmt->bind_param(
    "ii",
    $rol,
    $id
);

// Ejecutar la actualizacion
$stmt->execute();

// Devolver respuesta simple al cliente
echo "ok";