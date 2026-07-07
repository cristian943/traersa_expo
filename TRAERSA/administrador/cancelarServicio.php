<?php

// Incluir conexion a la base de datos
require '../conexion.php';

// Obtener el id del envio enviado por POST
$id = $_POST['id'];

// Consulta preparada para cambiar el estado a Cancelado
$sql = "
UPDATE envios
SET estado = 'Cancelado'
WHERE id_envio = ?
";

$stmt = $conn->prepare($sql);

// Enlazar el id al parametro de la consulta
$stmt->bind_param(
    "i",
    $id
);

// Ejecutar la actualizacion
$stmt->execute();

// Responder con ok si todo salio bien
echo "ok";
?>