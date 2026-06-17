<?php

require '../conexion.php';

$id = $_POST['id'];

$stmt = $conexion->prepare(
"DELETE FROM usuarios
WHERE id = ?"
);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

echo "ok";