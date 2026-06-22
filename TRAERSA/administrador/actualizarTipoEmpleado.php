<?php

require '../conexion.php';

$id = (int) $_POST['id'];
$tipo = trim($_POST['tipo'] ?? '');

if (!in_array($tipo, ['oficinista', 'transportista', ''], true)) {
    http_response_code(400);
    echo "tipo invalido";
    exit;
}

if ($tipo === '') {
    $sql = "UPDATE usuarios SET tipo_empleado = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
} else {
    $sql = "UPDATE usuarios SET tipo_empleado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $tipo, $id);
}

$stmt->execute();

if ($stmt->affected_rows >= 0) {
    echo "ok";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>