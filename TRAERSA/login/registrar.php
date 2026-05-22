<?php
require 'conexion.php';

/* DATOS */
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';

/* VALIDACIONES */
if (
    empty($nombre) ||
    empty($email) ||
    empty($password) ||
    empty($confirmar)
) {
    die("Todos los campos son obligatorios");
}

/* CONTRASEÑAS */
if ($password !== $confirmar) {
    die("Las contraseñas no coinciden");
}

/* HASH SEGURO */
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

/* ROL CLIENTE */
$rol_id = 2;

/* INSERTAR USUARIO */
$stmt = $conn->prepare("
    INSERT INTO usuarios
    (nombre, email, password, rol_id)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "sssi",
    $nombre,
    $email,
    $passwordHash,
    $rol_id
);

/* EJECUTAR */
if (!$stmt->execute()) {
    die("Error al registrar usuario: " . $stmt->error);
}

/* OBTENER ID */
$user_id = $conn->insert_id;

/* CREAR CARRITO AUTOMÁTICO */
$stmtCart = $conn->prepare("
    INSERT INTO cart (user_id)
    VALUES (?)
");

$stmtCart->bind_param("i", $user_id);
$stmtCart->execute();


header("Location: Inicio_sesion.php?registro=ok");
exit();
?>