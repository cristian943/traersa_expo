<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../conexion.php';
require('fpdf/fpdf.php');

if (($_SESSION['rol_id'] ?? null) != 3) {
    header("Location: ../login/login.php");
    exit();
}

$usuarioId = (int) $_SESSION['usuario_id'];
$envioId = (int) ($_GET['id'] ?? 0);

if ($envioId <= 0) {
    die('Orden no especificada.');
}

$stmtTipo = $conn->prepare("SELECT tipo_empleado FROM usuarios WHERE id = ?");
$stmtTipo->bind_param("i", $usuarioId);
$stmtTipo->execute();
$tipoEmpleado = ($stmtTipo->get_result()->fetch_assoc())['tipo_empleado'] ?? null;

$sql = "SELECT e.*, c.titulo AS servicio_titulo, c.tipo_entrega,
               a.vehiculo_id, a.conductor_id,
               v.placa, v.tipo AS vehiculo_tipo,
               u.nombre AS conductor_nombre
        FROM envios e
        LEFT JOIN categoria c ON c.id = e.servicio_id
        LEFT JOIN asignaciones a ON a.envio_id = e.id_envio
        LEFT JOIN vehiculos v ON v.id_vehiculo = a.vehiculo_id
        LEFT JOIN conductores cd ON cd.id_conductor = a.conductor_id
        LEFT JOIN usuarios u ON u.id = cd.usuario_id
        WHERE e.id_envio = ?";

if ($tipoEmpleado === 'transportista') {
    $sql .= " AND a.conductor_id = (SELECT id_conductor FROM conductores WHERE usuario_id = ?)";
}

$stmt = $conn->prepare($sql);

if ($tipoEmpleado === 'transportista') {
    $stmt->bind_param("ii", $envioId, $usuarioId);
} else {
    $stmt->bind_param("i", $envioId);
}

$stmt->execute();
$envio = $stmt->get_result()->fetch_assoc();

if (!$envio) {
    die('No se encontró la orden o no tienes permiso.');
}

/* =========================
   PDF CON FPDF
========================= */

$pdf = new FPDF();
$pdf->AddPage();

/* ===== LOGO Y ENCABEZADO ===== */
$pdf->Image('imagenes/logo.png', 10, 8, 30);

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'TRAERSA', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Orden de Entrega #' . ($envio['numero_guia'] ?: $envio['id_envio']), 0, 1, 'C');

$pdf->Ln(5);

/* ===== DATOS CLIENTE ===== */
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Datos del Cliente', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Cliente: ' . ($envio['nombre_cliente'] ?: 'Sin nombre'), 0, 1);
$pdf->Cell(0, 6, 'Telefono: ' . ($envio['telefono_cliente'] ?: '—'), 0, 1);
$pdf->Cell(0, 6, 'Ruta: ' . ($envio['origen'] ?: '—') . ' -> ' . ($envio['destino'] ?: '—'), 0, 1);

$pdf->Ln(3);

/* ===== DATOS ENVIO ===== */
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Datos del Envio', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Servicio: ' . ($envio['servicio_titulo'] ?: '—'), 0, 1);
$pdf->Cell(0, 6, 'Tipo entrega: ' . ($envio['tipo_entrega'] ?: '—'), 0, 1);
$pdf->Cell(0, 6, 'Costo: Q' . number_format($envio['costo'], 2), 0, 1);
$pdf->Cell(0, 6, 'Peso: ' . ($envio['peso'] ?: '—') . ' Kg', 0, 1);
$pdf->Cell(0, 6, 'Paquetes: ' . ($envio['paquetes'] ?: '—'), 0, 1);

$pdf->Ln(3);

$pdf->MultiCell(0, 6, 'Notas: ' . ($envio['notas'] ?: 'Sin notas'));

/* ===== TRANSPORTE ===== */
$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Transporte Asignado', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Transportista: ' . ($envio['conductor_nombre'] ?: 'Sin asignar'), 0, 1);
$pdf->Cell(0, 6, 'Vehiculo: ' . ($envio['placa'] ?: '—') . ' ' . ($envio['vehiculo_tipo'] ?: ''), 0, 1);

/* ===== FIRMA ===== */
$pdf->Ln(15);

$pdf->Cell(90, 10, '_________________________', 0, 0, 'C');
$pdf->Cell(90, 10, '_________________________', 0, 1, 'C');

$pdf->Cell(90, 5, 'Nombre quien recibe', 0, 0, 'C');
$pdf->Cell(90, 5, 'Firma', 0, 1, 'C');

/* ===== SALIDA ===== */
$pdf->Output('I', 'orden_entrega_' . $envioId . '.pdf');