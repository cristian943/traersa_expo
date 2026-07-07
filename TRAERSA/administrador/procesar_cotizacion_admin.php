<?php
/**
 * procesar_cotizacion_admin.php
 * ------------------------------
 * CRUD de cotizaciones (tabla `envios`) para el panel de administrador.
 *
 * accion = crear            -> crea una nueva cotización (estado inicial: Pendiente)
 * accion = editar           -> actualiza los datos de una cotización existente
 * accion = eliminar         -> elimina una cotización (solo si no está en ejecución)
 * accion = estado           -> cambia únicamente el estado (Pendiente/En revisión/Aprobada/Rechazada/Cancelado)
 *
 * Requiere los cambios de base de datos en cambios_base_datos_admin.sql
 * (llave primaria en `envios`, columnas numero_guia/nombre_cliente/
 * telefono_cliente/paquetes/notas, y las llaves foráneas hacia
 * clientes y categoria).
 */

header('Content-Type: application/json; charset=utf-8');

require '../BackEnd/auth.php';

if (($_SESSION['rol_id'] ?? null) != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

require '../conexion.php';
// conexion lista, responder usa JSON en todos los casos

function responder(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$accion = $_POST['accion'] ?? '';
// accion enviada por el formulario de administracion

// Estados válidos en la etapa de cotización (la ejecución usa su propio set de estados)
$estadosCotizacion = ['Pendiente', 'En revisión', 'Aprobada', 'Rechazada', 'Cancelado'];

// =========================================================
// Crear cotización
// =========================================================
if ($accion === 'crear') {
    // crear nueva cotizacion en estado Pendiente

    $clienteId   = (int) ($_POST['cliente_id'] ?? 0);
    $nombreManual = trim($_POST['nombre_cliente'] ?? '');
    $telefono    = trim($_POST['telefono_cliente'] ?? '');
    $servicioId  = (int) ($_POST['servicio_id'] ?? 0);
    $origen      = trim($_POST['origen'] ?? '');
    $destino     = trim($_POST['destino'] ?? '');
    $peso        = $_POST['peso'] !== '' ? (float) $_POST['peso'] : null;
    $paquetes    = $_POST['paquetes'] !== '' ? (int) $_POST['paquetes'] : null;
    $costo       = $_POST['costo'] !== '' ? (float) $_POST['costo'] : null;
    $fechaEntrega = trim($_POST['fecha_entrega_estimada'] ?? '');
    $notas       = trim($_POST['notas'] ?? '');

    if ($servicioId <= 0 || $destino === '' || $costo === null) {
        responder(false, 'Selecciona el servicio, el destino y el costo de la cotización.');
    }

    if ($clienteId <= 0 && $nombreManual === '') {
        responder(false, 'Selecciona un cliente registrado o escribe el nombre del cliente.');
    }

    // si hay cliente registrado, usar sus datos guardados
    if ($clienteId > 0) {
        $stmtCliente = $conn->prepare("SELECT nombre_empresa, telefono FROM clientes WHERE id_cliente = ?");
        $stmtCliente->bind_param("i", $clienteId);
        $stmtCliente->execute();
        $filaCliente = $stmtCliente->get_result()->fetch_assoc();

        if (!$filaCliente) {
            responder(false, 'El cliente seleccionado no existe.');
        }

        $nombreManual = $filaCliente['nombre_empresa'];
        $telefono     = $filaCliente['telefono'] ?: $telefono;
    }

    $clienteIdParam = $clienteId > 0 ? $clienteId : null;
    $fechaEntregaParam = $fechaEntrega !== '' ? $fechaEntrega : null;

    $insert = $conn->prepare(
        "INSERT INTO envios
            (cliente_id, nombre_cliente, telefono_cliente, servicio_id, origen, destino,
             peso, paquetes, costo, fecha_solicitud, fecha_entrega_estimada, estado, notas)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'Pendiente', ?)"
    );
    $insert->bind_param(
        "issisddidss",
        $clienteIdParam, $nombreManual, $telefono, $servicioId, $origen, $destino,
        $peso, $paquetes, $costo, $fechaEntregaParam, $notas
    );

    if (!$insert->execute()) {
        responder(false, 'No se pudo crear la cotización. Intenta de nuevo.');
    }

    $nuevoId = $insert->insert_id;
    $numeroGuia = 'TRX-' . date('Y') . '-' . str_pad((string) $nuevoId, 3, '0', STR_PAD_LEFT);

    $update = $conn->prepare("UPDATE envios SET numero_guia = ? WHERE id_envio = ?");
    $update->bind_param("si", $numeroGuia, $nuevoId);
    $update->execute();

    // guardar primer registro en la bitacora de seguimiento
    $log = $conn->prepare("INSERT INTO seguimiento_envio (envio_id, estado, observaciones, fecha) VALUES (?, 'Pendiente', 'Cotización creada', NOW())");
    $log->bind_param("i", $nuevoId);
    $log->execute();

    responder(true, 'Cotización ' . $numeroGuia . ' creada correctamente.', ['id_envio' => $nuevoId, 'numero_guia' => $numeroGuia]);
}

// =========================================================
// Editar cotización
// =========================================================
if ($accion === 'editar') {
    // editar datos de una cotizacion existente

    $id          = (int) ($_POST['id_envio'] ?? 0);
    $clienteId   = (int) ($_POST['cliente_id'] ?? 0);
    $nombreManual = trim($_POST['nombre_cliente'] ?? '');
    $telefono    = trim($_POST['telefono_cliente'] ?? '');
    $servicioId  = (int) ($_POST['servicio_id'] ?? 0);
    $origen      = trim($_POST['origen'] ?? '');
    $destino     = trim($_POST['destino'] ?? '');
    $peso        = $_POST['peso'] !== '' ? (float) $_POST['peso'] : null;
    $paquetes    = $_POST['paquetes'] !== '' ? (int) $_POST['paquetes'] : null;
    $costo       = $_POST['costo'] !== '' ? (float) $_POST['costo'] : null;
    $fechaEntrega = trim($_POST['fecha_entrega_estimada'] ?? '');
    $notas       = trim($_POST['notas'] ?? '');

    if ($id <= 0) {
        responder(false, 'Cotización no válida.');
    }

    if ($servicioId <= 0 || $destino === '' || $costo === null) {
        responder(false, 'Selecciona el servicio, el destino y el costo de la cotización.');
    }

    if ($clienteId > 0) {
        $stmtCliente = $conn->prepare("SELECT nombre_empresa, telefono FROM clientes WHERE id_cliente = ?");
        $stmtCliente->bind_param("i", $clienteId);
        $stmtCliente->execute();
        $filaCliente = $stmtCliente->get_result()->fetch_assoc();

        if ($filaCliente) {
            $nombreManual = $filaCliente['nombre_empresa'];
            $telefono     = $filaCliente['telefono'] ?: $telefono;
        }
    }

    $clienteIdParam = $clienteId > 0 ? $clienteId : null;
    $fechaEntregaParam = $fechaEntrega !== '' ? $fechaEntrega : null;

    $update = $conn->prepare(
        "UPDATE envios SET
            cliente_id = ?, nombre_cliente = ?, telefono_cliente = ?, servicio_id = ?,
            origen = ?, destino = ?, peso = ?, paquetes = ?, costo = ?,
            fecha_entrega_estimada = ?, notas = ?
         WHERE id_envio = ?"
    );
    $update->bind_param(
        "issisddidssi",
        $clienteIdParam, $nombreManual, $telefono, $servicioId, $origen, $destino,
        $peso, $paquetes, $costo, $fechaEntregaParam, $notas, $id
    );

    if ($update->execute()) {
        responder(true, 'Cotización actualizada correctamente.');
    } else {
        responder(false, 'No se pudo actualizar la cotización.');
    }
}

// =========================================================
// Eliminar cotización
// =========================================================
if ($accion === 'eliminar') {
    // eliminar cotizacion solo si no esta en ejecucion

    $id = (int) ($_POST['id_envio'] ?? 0);

    if ($id <= 0) {
        responder(false, 'Cotización no válida.');
    }

    $stmt = $conn->prepare("SELECT estado FROM envios WHERE id_envio = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    if (!$fila) {
        responder(false, 'La cotización ya no existe.');
    }

    if (in_array($fila['estado'], ['Asignado', 'En ruta', 'Entregado'], true)) {
        responder(false, 'No se puede eliminar: este envío ya está en ejecución o entregado.');
    }

    // borrar registro de envios
    $delete = $conn->prepare("DELETE FROM envios WHERE id_envio = ?");
    $delete->bind_param("i", $id);

    if ($delete->execute()) {
        responder(true, 'Cotización eliminada correctamente.');
    } else {
        responder(false, 'No se pudo eliminar la cotización.');
    }
}

// =========================================================
// Cambiar estado (cotización)
// =========================================================
if ($accion === 'estado') {
    // cambiar solo el estado de la cotizacion

    $id     = (int) ($_POST['id_envio'] ?? 0);
    $nuevo  = trim($_POST['estado'] ?? '');

    if ($id <= 0 || !in_array($nuevo, $estadosCotizacion, true)) {
        responder(false, 'Estado no válido.');
    }

    $update = $conn->prepare("UPDATE envios SET estado = ? WHERE id_envio = ?");
    $update->bind_param("si", $nuevo, $id);

    if (!$update->execute()) {
        responder(false, 'No se pudo actualizar el estado.');
    }

    // registrar cambio de estado en seguimiento_envio
    $log = $conn->prepare("INSERT INTO seguimiento_envio (envio_id, estado, observaciones, fecha) VALUES (?, ?, 'Cambio de estado desde Cotizaciones', NOW())");
    $log->bind_param("is", $id, $nuevo);
    $log->execute();

    responder(true, 'Estado actualizado a "' . $nuevo . '".');
}

responder(false, 'Solicitud no reconocida.');