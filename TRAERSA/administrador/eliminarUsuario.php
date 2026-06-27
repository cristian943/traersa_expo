<?php

// incluir conexion a base de datos
require '../conexion.php';

// recibir id del usuario enviado por POST
$id = $_POST['id'];

// preparar consulta para borrar el usuario por id
$stmt = $conn->prepare(
"DELETE FROM usuarios
WHERE id = ?"
);

$stmt->bind_param(
    "i",
    $id
);

// ejecutar eliminacion en la base de datos
$stmt->execute();

// responder al cliente con resultado simple
echo "ok";