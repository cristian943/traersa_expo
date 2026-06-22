<?php
/**
 * procesar_cuenta.php
 * -------------------
 * Recibe (por fetch/AJAX desde mi_cuenta.php) las actualizaciones de:
 *   - accion = "perfil"   -> actualiza nombre y email en `usuarios`
 *   - accion = "password" -> actualiza la contraseña en `usuarios`
 *   - accion = "empresa"  -> guarda/actualiza nombre_empresa, nit, telefono
 *                            y direccion en `clientes` (upsert por usuario_id)
 *
 * Requisito de base de datos para la acción "empresa":
 *   ALTER TABLE clientes
 *     ADD UNIQUE KEY uq_clientes_usuario (usuario_id),
 *     ADD CONSTRAINT fk_clientes_usuario
 *       FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE;
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

include("../conexion.php");

function responder(bool $success, string $message): void {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    responder(false, 'Tu sesión expiró. Inicia sesión de nuevo.');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    responder(false, 'Método no permitido.');
}

$usuarioId = (int) $_SESSION['usuario_id'];
$accion    = $_POST['accion'] ?? '';

// =========================================================
// Actualizar nombre y correo
// =========================================================
if ($accion === 'perfil') {

    $nombre = trim(filter_var($_POST['nombre'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email  = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if ($nombre === '' || $email === '') {
        responder(false, 'El nombre y el correo son obligatorios.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder(false, 'El correo electrónico no es válido.');
    }

    // `nombre` y `email` son UNIQUE en la tabla: verificamos que no choquen con otra cuenta
    $check = $conn->prepare("SELECT id FROM usuarios WHERE (nombre = ? OR email = ?) AND id != ?");
    $check->bind_param("ssi", $nombre, $email, $usuarioId);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        responder(false, 'Ese nombre de usuario o correo ya está en uso por otra cuenta.');
    }

    $update = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
    $update->bind_param("ssi", $nombre, $email, $usuarioId);

    if ($update->execute()) {
        responder(true, 'Tus datos se actualizaron correctamente.');
    } else {
        responder(false, 'No se pudo actualizar tu información. Intenta de nuevo.');
    }
}

// =========================================================
// Cambiar contraseña
// =========================================================
if ($accion === 'password') {

    $actual    = $_POST['actual'] ?? '';
    $nueva     = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($actual === '' || $nueva === '' || $confirmar === '') {
        responder(false, 'Completa todos los campos de contraseña.');
    }

    if (strlen($nueva) < 6) {
        responder(false, 'La nueva contraseña debe tener al menos 6 caracteres.');
    }

    if ($nueva !== $confirmar) {
        responder(false, 'La confirmación no coincide con la nueva contraseña.');
    }

    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    if (!$fila) {
        responder(false, 'No se encontró tu cuenta.');
    }

    $hashGuardado = $fila['password'];

    // password_verify() cubre las cuentas reales (hasheadas con password_hash).
    // La comparación directa es solo para las 3 cuentas de prueba del seed
    // (admin/cliente/empleado) que se crearon con contraseña en texto plano;
    // en cuanto cambien su contraseña aquí, quedará hasheada correctamente.
    $coincide = password_verify($actual, $hashGuardado) || hash_equals($hashGuardado, $actual);

    if (!$coincide) {
        responder(false, 'La contraseña actual no es correcta.');
    }

    $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);

    $update = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $update->bind_param("si", $nuevoHash, $usuarioId);

    if ($update->execute()) {
        responder(true, 'Tu contraseña se actualizó correctamente.');
    } else {
        responder(false, 'No se pudo actualizar la contraseña. Intenta de nuevo.');
    }
}

// =========================================================
// Guardar datos de la empresa (clientes: nombre_empresa, nit, telefono, direccion)
// =========================================================
if ($accion === 'empresa') {

    $nombreEmpresa = trim(filter_var($_POST['nombre_empresa'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $nit           = trim(filter_var($_POST['nit'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $telefono      = trim(filter_var($_POST['telefono'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $direccion     = trim(filter_var($_POST['direccion'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    // Todos los campos son opcionales (el cliente puede completarlos cuando quiera),
    // pero si llena el teléfono validamos un formato básico.
    if ($telefono !== '' && !preg_match('/^[0-9+\s\-()]{6,20}$/', $telefono)) {
        responder(false, 'El teléfono no tiene un formato válido.');
    }

    // Necesita el correo del usuario para dejarlo sincronizado en `clientes`
    $stmtEmail = $conn->prepare("SELECT email FROM usuarios WHERE id = ?");
    $stmtEmail->bind_param("i", $usuarioId);
    $stmtEmail->execute();
    $filaEmail = $stmtEmail->get_result()->fetch_assoc();
    $email = $filaEmail['email'] ?? '';

    // Upsert: requiere UNIQUE KEY en clientes.usuario_id (ver bloque SQL al inicio del archivo)
    $upsert = $conn->prepare(
        "INSERT INTO clientes (nombre_empresa, nit, direccion, telefono, email, estado, usuario_id)
         VALUES (?, ?, ?, ?, ?, 1, ?)
         ON DUPLICATE KEY UPDATE
            nombre_empresa = VALUES(nombre_empresa),
            nit = VALUES(nit),
            direccion = VALUES(direccion),
            telefono = VALUES(telefono),
            email = VALUES(email)"
    );
    $upsert->bind_param("sssssi", $nombreEmpresa, $nit, $direccion, $telefono, $email, $usuarioId);

    if ($upsert->execute()) {
        responder(true, 'Los datos de tu empresa se guardaron correctamente.');
    } else {
        responder(false, 'No se pudieron guardar los datos de la empresa. Intenta de nuevo.');
    }
}

responder(false, 'Solicitud no reconocida.');