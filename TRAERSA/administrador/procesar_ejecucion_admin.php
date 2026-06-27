<?php
/**
 * procesar_ejecucion_admin.php
 * ------------------------------
 * Maneja la etapa de ejecución de un envío ya aprobado:
 *
 * accion = asignar          -> asigna conductor + vehículo a un envío (tabla `asignaciones`,
 *                               upsert por envio_id) y mueve el estado a "Asignado"
 * accion = estado            -> avanza el estado de ejecución (Asignado -> En ruta -> Entregado)
 * accion = agregar_vehiculo  -> alta rápida de un vehículo nuevo desde la misma pantalla
 *
 * Requiere los cambios de cambios_base_datos_admin.sql (tabla `vehiculos`,
 * llave primaria + UNIQUE(usuario_id) en `conductores`, UNIQUE(envio_id)
 * en `asignaciones`, etc.)
 */

header('Content-Type: application/json; charset=utf-8');

require '../BackEnd/auth.php';

if (($_SESSION['rol_id'] ?? null) != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

require '../conexion.php';
// conexion a la base de datos

function responder(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$accion = $_POST['accion'] ?? '';
// accion enviada desde el formulario de ejecucion

$estadosEjecucion = ['Asignado', 'En ruta', 'Entregado'];

// =========================================================
// Asignar conductor + vehículo a un envío aprobado
// =========================================================
if ($accion === 'asignar') {
    // asignar conductor y vehiculo a un envio aprobado

    $envioId     = (int) ($_POST['envio_id'] ?? 0);
    $usuarioConductorId = (int) ($_POST['conductor_usuario_id'] ?? 0);
    $vehiculoId  = (int) ($_POST['vehiculo_id'] ?? 0);
    $notas       = trim($_POST['notas'] ?? '');
    $estadoDeseado = trim($_POST['estado'] ?? 'Asignado');

    if (!in_array($estadoDeseado, $estadosEjecucion, true)) {
        $estadoDeseado = 'Asignado';
    }

    // validar que se tenga envio, conductor y vehiculo seleccionados
    if ($envioId <= 0 || $usuarioConductorId <= 0 || $vehiculoId <= 0) {
        responder(false, 'Selecciona el envío, el empleado y el vehículo.');
    }

    // El envío debe existir y estar aprobado (o ya en ejecución, para reasignar)
    $stmtEnvio = $conn->prepare("SELECT estado FROM envios WHERE id_envio = ?");
    $stmtEnvio->bind_param("i", $envioId);
    $stmtEnvio->execute();
    $filaEnvio = $stmtEnvio->get_result()->fetch_assoc();

    if (!$filaEnvio) {
        responder(false, 'El envío no existe.');
    }

    if (!in_array($filaEnvio['estado'], ['Aprobada', 'Asignado', 'En ruta'], true)) {
        responder(false, 'Solo se pueden asignar envíos que estén Aprobados.');
    }

    // Aseguramos que el empleado (usuario rol_id=3) tenga su fila en `conductores`
    $stmtCond = $conn->prepare("SELECT id_conductor FROM conductores WHERE usuario_id = ?");
    $stmtCond->bind_param("i", $usuarioConductorId);
    $stmtCond->execute();
    $filaCond = $stmtCond->get_result()->fetch_assoc();

    if ($filaCond) {
        $conductorId = (int) $filaCond['id_conductor'];
    } else {
        $insertCond = $conn->prepare("INSERT INTO conductores (usuario_id) VALUES (?)");
        $insertCond->bind_param("i", $usuarioConductorId);
        if (!$insertCond->execute()) {
            responder(false, 'No se pudo registrar al conductor.');
        }
        $conductorId = $insertCond->insert_id;
    }

    // Upsert en asignaciones (un envío = una asignación activa)
    $upsert = $conn->prepare(
        "INSERT INTO asignaciones (envio_id, vehiculo_id, conductor_id, fecha_asignacion, notas)
         VALUES (?, ?, ?, NOW(), ?)
         ON DUPLICATE KEY UPDATE
            vehiculo_id = VALUES(vehiculo_id),
            conductor_id = VALUES(conductor_id),
            fecha_asignacion = VALUES(fecha_asignacion),
            notas = VALUES(notas)"
    );
    $upsert->bind_param("iiis", $envioId, $vehiculoId, $conductorId, $notas);

    if (!$upsert->execute()) {
        responder(false, 'No se pudo guardar la asignación.');
    }

    $update = $conn->prepare("UPDATE envios SET estado = ? WHERE id_envio = ?");
    $update->bind_param("si", $estadoDeseado, $envioId);
    $update->execute();

    $log = $conn->prepare("INSERT INTO seguimiento_envio (envio_id, estado, observaciones, fecha) VALUES (?, ?, 'Conductor y vehículo asignados', NOW())");
    $log->bind_param("is", $envioId, $estadoDeseado);
    $log->execute();

    $mensaje = $estadoDeseado === 'Entregado'
        ? 'Envío asignado y marcado como entregado. Ya aparece en Completados.'
        : 'Envío asignado correctamente (' . $estadoDeseado . ').';

    responder(true, $mensaje);
}

// =========================================================
// Avanzar / actualizar estado de ejecución
// =========================================================
if ($accion === 'estado') {
    // cambiar estado de ejecucion del envio

    $envioId = (int) ($_POST['envio_id'] ?? 0);
    $nuevo   = trim($_POST['estado'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($envioId <= 0 || !in_array($nuevo, $estadosEjecucion, true)) {
        responder(false, 'Estado no válido.');
    }

    $update = $conn->prepare("UPDATE envios SET estado = ? WHERE id_envio = ?");
    $update->bind_param("si", $nuevo, $envioId);

    if (!$update->execute()) {
        responder(false, 'No se pudo actualizar el estado del envío.');
    }

    $log = $conn->prepare("INSERT INTO seguimiento_envio (envio_id, estado, ubicacion, observaciones, fecha) VALUES (?, ?, ?, ?, NOW())");
    $log->bind_param("isss", $envioId, $nuevo, $ubicacion, $observaciones);
    $log->execute();

    $mensaje = $nuevo === 'Entregado'
        ? 'Envío marcado como entregado. Ya aparece en Completados.'
        : 'Estado actualizado a "' . $nuevo . '".';

    responder(true, $mensaje);
}

// =========================================================
// Alta rápida de un vehículo
// =========================================================
if ($accion === 'agregar_vehiculo') {
    // registrar vehiculo nuevo desde la pantalla de ejecucion

    $placa = trim($_POST['placa'] ?? '');
    $tipo  = trim($_POST['tipo'] ?? '');
    $capacidad = $_POST['capacidad_kg'] !== '' ? (float) $_POST['capacidad_kg'] : null;

    if ($placa === '') {
        responder(false, 'La placa del vehículo es obligatoria.');
    }

    $check = $conn->prepare("SELECT id_vehiculo FROM vehiculos WHERE placa = ?");
    $check->bind_param("s", $placa);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        responder(false, 'Ya existe un vehículo con esa placa.');
    }

    $insert = $conn->prepare("INSERT INTO vehiculos (placa, tipo, capacidad_kg, estado) VALUES (?, ?, ?, 'Disponible')");
    $insert->bind_param("ssd", $placa, $tipo, $capacidad);

    if ($insert->execute()) {
        responder(true, 'Vehículo agregado correctamente.', [
            'id_vehiculo' => $insert->insert_id,
            'placa' => $placa,
            'tipo' => $tipo,
        ]);
    } else {
        responder(false, 'No se pudo agregar el vehículo.');
    }
}

responder(false, 'Solicitud no reconocida.');