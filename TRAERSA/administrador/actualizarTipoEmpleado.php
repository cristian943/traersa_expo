<?php

// Conectar a la base de datos usando el archivo de conexion
require '../conexion.php';

// Recibir el id y el tipo de empleado enviados por POST
$id = (int) $_POST['id'];
$tipo = trim($_POST['tipo'] ?? '');

// Validar que el tipo sea valido antes de actualizar
if (!in_array($tipo, ['oficinista', 'transportista', ''], true)) {
    http_response_code(400);
    echo "tipo invalido";
    exit;
}

// Si no hay tipo, guardamos NULL en la base de datos
if ($tipo === '') {
    $sql = "UPDATE usuarios SET tipo_empleado = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
} else {
    // Si hay tipo valido, actualizamos el campo correspondiente
    $sql = "UPDATE usuarios SET tipo_empleado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $tipo, $id);
}

// Ejecutar la consulta preparada
$stmt->execute();

// Devolver ok si la consulta fue ejecutada, error si no
if ($stmt->affected_rows >= 0) {
    echo "ok";
} else {
    echo "error";
}

// Cerrar recursos
$stmt->close();
$conn->close();
?>