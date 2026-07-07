<?php
/**
 * procesar_proceso_empleado.php
 * ------------------------------
 * El transportista actualiza el estado de UNO de sus propios envíos
 * (verificado siempre contra su sesión, nunca contra lo que mande el
 * formulario). La foto SOLO es obligatoria cuando el nuevo estado es
 * "Entregado" — para "En ruta" o "No entregado" no se pide foto.
 *
 * La fecha y hora de cada cambio queda guardada automáticamente en
 * `seguimiento_envio.fecha` (la pone NOW(), nunca se inventa).
 *
 * Cada consulta valida que prepare() no haya fallado antes de usarla,
 * para nunca devolver un error fatal de PHP en vez de un JSON limpio
 * (eso es lo que producía el "error de conexión" en el navegador).
 */

header('Content-Type: application/json; charset=utf-8');

require '../BackEnd/auth.php';

function responder(bool $success, string $message): void {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

function prepararOFallar($conn, string $sql) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        responder(false, 'Error interno al preparar la consulta. Si el problema sigue, revisa que se haya ejecutado el SQL de actualización (cambios_base_datos_empleado.sql).');
    }
    return $stmt;
}

if (($_SESSION['rol_id'] ?? null) != 3) {
    http_response_code(403);
    responder(false, 'No tienes permisos para realizar esta acción.');
}

require '../conexion.php';

$usuarioId = (int) $_SESSION['id_usuario'];

// Confirmamos que quien hace la petición es un transportista con conductor asignado
$stmtTipo = prepararOFallar($conn, "SELECT tipo_empleado FROM usuarios WHERE id_usuario = ?");
$stmtTipo->bind_param("i", $usuarioId);
$stmtTipo->execute();
$rowTipo = $stmtTipo->get_result()->fetch_assoc();
$tipoEmpleado = $rowTipo['tipo_empleado'] ?? null;

if ($tipoEmpleado !== 'transportista') {
    responder(false, 'Solo un transportista puede actualizar el estado de un envío.');
}

$stmtCond = prepararOFallar($conn, "SELECT id_conductor FROM conductores WHERE id_usuario = ?");
$stmtCond->bind_param("i", $usuarioId);
$stmtCond->execute();
$conductorId = ($stmtCond->get_result()->fetch_assoc())['id_conductor'] ?? null;

if (!$conductorId) {
    responder(false, 'Todavía no tienes ningún envío asignado.');
}

$envioId = (int) ($_POST['envio_id'] ?? 0);
$nuevoEstado = trim($_POST['nuevo_estado'] ?? '');
$ubicacion = trim($_POST['ubicacion'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');

if ($envioId <= 0 || $nuevoEstado === '') {
    responder(false, 'Faltan datos para actualizar el envío.');
}

// El envío debe estar realmente asignado a ESTE conductor (nunca confiar en el id que manda el navegador)
$stmtOwn = prepararOFallar(
    $conn,
    "SELECT e.estado
     FROM envios e
     INNER JOIN asignaciones a ON a.envio_id = e.id_envio
     WHERE e.id_envio = ? AND a.conductor_id = ?"
);
$stmtOwn->bind_param("ii", $envioId, $conductorId);
$stmtOwn->execute();
$filaEnvio = $stmtOwn->get_result()->fetch_assoc();

if (!$filaEnvio) {
    responder(false, 'Ese envío no está asignado a ti.');
}

$estadoActual = $filaEnvio['estado'];

// Transiciones permitidas. "No entregado" = intentó entregar pero no había
// nadie para recibir; desde ahí se puede reintentar (volver a "En ruta")
// o marcar entregado si al final sí lo recibieron.
$transicionesValidas = [
    'Asignado'     => ['En ruta', 'Entregado', 'No entregado'],
    'En ruta'      => ['Entregado', 'No entregado'],
    'No entregado' => ['En ruta', 'Entregado'],
];

if (!isset($transicionesValidas[$estadoActual]) || !in_array($nuevoEstado, $transicionesValidas[$estadoActual], true)) {
    responder(false, 'No puedes cambiar el envío de "' . $estadoActual . '" a "' . $nuevoEstado . '".');
}

$rutaFoto = null;

// La foto SOLO es obligatoria para "Entregado". Para "En ruta" o
// "No entregado" no se pide nada — pero si el transportista decide
// adjuntarla de todos modos (ej. evidencia de que no había nadie), se
// guarda igual.
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

    $archivo = $_FILES['foto'];

    $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tipoReal = function_exists('mime_content_type') ? mime_content_type($archivo['tmp_name']) : $archivo['type'];

    if (!isset($tiposPermitidos[$tipoReal])) {
        responder(false, 'La foto debe ser una imagen JPG, PNG o WEBP.');
    }

    if ($archivo['size'] > 8 * 1024 * 1024) {
        responder(false, 'La foto no puede pesar más de 8 MB.');
    }

    $carpetaDestino = '../uploads/entregas/';
    if (!is_dir($carpetaDestino)) {
        @mkdir($carpetaDestino, 0755, true);
    }

    if (!is_dir($carpetaDestino) || !is_writable($carpetaDestino)) {
        responder(false, 'El servidor no tiene permiso de escritura en uploads/entregas/. Avisa al administrador del hosting.');
    }

    $nombreArchivo = 'entrega_' . $envioId . '_' . time() . '.' . $tiposPermitidos[$tipoReal];
    $rutaCompleta = $carpetaDestino . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        responder(false, 'No se pudo guardar la foto. Intenta de nuevo.');
    }

    $rutaFoto = 'entregas/' . $nombreArchivo;

} elseif ($nuevoEstado === 'Entregado') {
    // Para "Entregado" sí es obligatoria
    responder(false, 'Para marcar como entregado necesitas adjuntar la foto del documento firmado.');
}

$update = prepararOFallar($conn, "UPDATE envios SET estado = ? WHERE id_envio = ?");
$update->bind_param("si", $nuevoEstado, $envioId);

if (!$update->execute()) {
    responder(false, 'No se pudo actualizar el envío. Intenta de nuevo.');
}

$log = prepararOFallar(
    $conn,
    "INSERT INTO seguimiento_envio (envio_id, estado, ubicacion, observaciones, foto_evidencia, fecha)
     VALUES (?, ?, ?, ?, ?, NOW())"
);
$log->bind_param("issss", $envioId, $nuevoEstado, $ubicacion, $observaciones, $rutaFoto);
$log->execute();

$mensajes = [
    'Entregado'    => 'Envío marcado como entregado. La oficina ya puede verlo en Completados.',
    'No entregado' => 'Quedó registrado que no se pudo entregar. La oficina lo verá para decidir el siguiente paso.',
];

responder(true, $mensajes[$nuevoEstado] ?? ('Estado actualizado a "' . $nuevoEstado . '".'));