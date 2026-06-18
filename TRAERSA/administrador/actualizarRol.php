<?php

require '../conexion.php';

$id = $_POST['id'];
$rol = $_POST['rol'];

$sql = "
UPDATE usuarios
SET rol_id = ?
WHERE id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $rol,
    $id
);

$stmt->execute();

echo "ok";