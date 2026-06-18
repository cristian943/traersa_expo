<?php

require '../conexion.php';

$id = $_POST['id'];

$sql = "
UPDATE envios
SET estado = 'Cancelado'
WHERE id_envio = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

echo "ok";
?>