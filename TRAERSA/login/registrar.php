<?php
session_start();

/* MOSTRAR ERRORES */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../conexion.php';

/* SOLO POST */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registro.php?error=Acceso inválido");
    exit();
}

/* DATOS */
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';

/* VALIDAR CAMPOS */
if (
    empty($nombre) ||
    empty($email) ||
    empty($password) ||
    empty($confirmar)
) {
    header("Location: registro.php?error=Todos los campos son obligatorios");
    exit();
}

/* VALIDAR EMAIL */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: registro.php?error=Correo inválido");
    exit();
}

/* VALIDAR CONTRASEÑAS */
if ($password !== $confirmar) {
    header("Location: registro.php?error=Las contraseñas no coinciden");
    exit();
}

/* VERIFICAR CORREO REPETIDO */
$check = $conn->prepare("
    SELECT id 
    FROM usuarios 
    WHERE email = ?
");

$check->bind_param("s", $email);
$check->execute();

$resultado = $check->get_result();

if ($resultado->num_rows > 0) {
    header("Location: registro.php?error=El correo ya está registrado");
    exit();
}

/* HASH */
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

/* ROL CLIENTE */
$rol_id = 2;

/* INSERTAR */
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

    header("Location: registro.php?error=Error al registrar usuario");
    exit();
}

/* OBTENER ID */
$user_id = $conn->insert_id;

/* CREAR CARRITO */
$stmtCart = $conn->prepare("
    INSERT INTO cart (user_id)
    VALUES (?)
");

$stmtCart->bind_param("i", $user_id);

if (!$stmtCart->execute()) {

    header("Location: registro.php?error=Usuario creado pero carrito falló");
    exit();
}

/* ÉXITO */
header("Location: Inicio_sesion.php?success=Cuenta creada correctamente");
exit();
?>