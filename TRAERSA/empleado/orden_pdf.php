<?php
/**
 * orden_pdf.php — TRAERSA
 * Genera la orden de entrega como PDF real usando FPDF.
 *
 * REGLA CRÍTICA FPDF: NO puede existir ningún output (echo, espacio,
 * BOM, header extra) antes de $pdf->Output(). Por eso:
 *   - session_start() se llama aquí directamente (no via auth.php que
 *     además envía headers Cache-Control que rompen el stream binario).
 *   - El buffer se limpia con ob_clean() antes de Output().
 */

ob_start();

session_start();

if (($_SESSION['rol_id'] ?? null) != 3) {
    header("Location: ../login/Inicio_sesion.php");
    exit();
}

require '../conexion.php';
require 'fpdf/fpdf.php';

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
$envioId   = (int) ($_GET['id'] ?? 0);

if ($envioId <= 0) {
    die('Orden no especificada.');
}

/* Verificar tipo de empleado */
$stmtTipo = $conn->prepare("SELECT tipo_empleado FROM usuarios WHERE id = ?");
if (!$stmtTipo) {
    die('Error: columna tipo_empleado no existe. Ejecuta cambios_base_datos_empleado.sql en phpMyAdmin.');
}
$stmtTipo->bind_param("i", $usuarioId);
$stmtTipo->execute();
$tipoEmpleado = ($stmtTipo->get_result()->fetch_assoc())['tipo_empleado'] ?? null;

/* Consulta del envío */
$sql = "SELECT e.*,
               c.titulo     AS servicio_titulo,
               c.tipo_entrega,
               v.placa,
               v.tipo       AS vehiculo_tipo,
               u.nombre     AS conductor_nombre
        FROM envios e
        LEFT JOIN categoria    c  ON c.id            = e.servicio_id
        LEFT JOIN asignaciones a  ON a.envio_id       = e.id_envio
        LEFT JOIN vehiculos    v  ON v.id_vehiculo    = a.vehiculo_id
        LEFT JOIN conductores  cd ON cd.id_conductor  = a.conductor_id
        LEFT JOIN usuarios     u  ON u.id             = cd.usuario_id
        WHERE e.id_envio = ?";

if ($tipoEmpleado === 'transportista') {
    $sql .= " AND cd.usuario_id = ?";
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Error al preparar la consulta: ' . $conn->error);
}

if ($tipoEmpleado === 'transportista') {
    $stmt->bind_param("ii", $envioId, $usuarioId);
} else {
    $stmt->bind_param("i", $envioId);
}
$stmt->execute();
$e = $stmt->get_result()->fetch_assoc();

if (!$e) {
    die('No se encontró esa orden, o no tienes permiso para verla.');
}

/* Helper: convierte UTF-8 a latin-1 (FPDF no soporta UTF-8 de serie) */
function t(string $s): string {
    return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s);
}

/* Shortcuts legibles */
$guia        = $e['numero_guia'] ?: '#' . $e['id_envio'];
$cliente     = $e['nombre_cliente'] ?: 'Sin nombre';
$telefono    = $e['telefono_cliente'] ?: '—';
$ruta        = ($e['origen'] ?: '—') . '  ->  ' . ($e['destino'] ?: '—');
$servicio    = ($e['servicio_titulo'] ?: '—') . '  (' . ($e['tipo_entrega'] ?: '—') . ')';
$costo       = 'Q' . number_format((float) $e['costo'], 2);
$peso        = $e['peso'] ? $e['peso'] . ' Kg' : '—';
$paquetes    = $e['paquetes'] ?: '—';
$notas       = $e['notas'] ?: 'Sin notas adicionales.';
$transportista = $e['conductor_nombre'] ?: 'Sin asignar';
$vehiculo    = trim(($e['placa'] ?: '—') . '  ' . ($e['vehiculo_tipo'] ?: ''));
$hoy         = date('d/m/Y H:i');

/* ================================================================
   COLORES TRAERSA
   ================================================================ */
$RED   = [225, 37,  27];
$NAVY  = [11,  27,  51];
$WHITE = [255, 255, 255];
$GRAY  = [120, 130, 145];
$LGRAY = [240, 240, 240];

/* ================================================================
   PDF
   ================================================================ */
$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->SetMargins(18, 18, 18);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFillColor(...$WHITE);

/* ---- BANDA SUPERIOR ROJA ---- */
$pdf->SetFillColor(...$RED);
$pdf->Rect(0, 0, 216, 22, 'F');

/* Logo (si existe el archivo en el server) */
$logoPath = __DIR__ . '/imagenes/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 5, 2, 18, 18);
}

/* Nombre empresa en la banda */
$pdf->SetTextColor(...$WHITE);
$pdf->SetFont('Helvetica', 'B', 18);
$pdf->SetXY(26, 5);
$pdf->Cell(100, 10, 'TRAERSA', 0, 0, 'L');

/* Subtítulo empresa */
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetXY(26, 14);
$pdf->Cell(100, 5, t('Logística y Transporte de Guatemala'), 0, 0, 'L');

/* Número de guía (derecha, en la banda) */
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->SetXY(130, 4);
$pdf->Cell(80, 7, t('ORDEN DE ENTREGA'), 0, 1, 'R');
$pdf->SetFont('Courier', 'B', 14);
$pdf->SetXY(130, 11);
$pdf->Cell(80, 8, $guia, 0, 1, 'R');

/* ---- FRANJA AZUL DELGADA ---- */
$pdf->SetFillColor(...$NAVY);
$pdf->Rect(0, 22, 216, 3, 'F');

/* ---- FECHA (debajo de la banda) ---- */
$pdf->SetTextColor(...$GRAY);
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetXY(18, 28);
$pdf->Cell(0, 5, t('Generada el ' . $hoy . '  ·  31 av 2-48 zona 6 de Mixco  ·  traersa2026@gmail.com  ·  +502 7856 2384'), 0, 1, 'L');

$pdf->Ln(3);

/* ================================================================
   Función auxiliar para bloques de sección
   ================================================================ */
$drawSection = function(string $titulo, array $filas) use ($pdf, $RED, $NAVY, $LGRAY, $WHITE) {
    /* Cabecera de sección */
    $pdf->SetFillColor(...$NAVY);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(0, 7, '  ' . t($titulo), 0, 1, 'L', true);
    $pdf->Ln(1);

    /* Filas de datos en 2 columnas */
    $colW = 87;
    $gap  = 4;
    $total = count($filas);
    for ($i = 0; $i < $total; $i += 2) {
        $izq = $filas[$i];
        $der = ($i + 1 < $total) ? $filas[$i + 1] : null;

        $shade = (intdiv($i, 2) % 2 === 0);
        $bg = $shade ? [248, 248, 252] : $WHITE;

        /* Izquierda */
        $pdf->SetFillColor(...$bg);
        $pdf->SetTextColor(100, 110, 125);
        $pdf->SetFont('Helvetica', 'B', 7.5);
        $pdf->Cell($colW, 5, '  ' . t($izq[0]), 0, 0, 'L', true);
        if ($der) {
            $pdf->Cell($gap, 5, '', 0, 0);
            $pdf->Cell($colW, 5, '  ' . t($der[0]), 0, 1, 'L', true);
        } else {
            $pdf->Ln();
        }

        $pdf->SetTextColor(20, 32, 46);
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->Cell($colW, 6, '  ' . t($izq[1]), 0, 0, 'L', true);
        if ($der) {
            $pdf->Cell($gap, 6, '', 0, 0);
            $pdf->Cell($colW, 6, '  ' . t($der[1]), 0, 1, 'L', true);
        } else {
            $pdf->Ln();
        }
        $pdf->Ln(1.5);
    }
    $pdf->Ln(3);
};

/* ================================================================
   SECCIONES DE DATOS
   ================================================================ */
$drawSection('DATOS DEL CLIENTE', [
    ['Cliente',   $cliente],
    ['Teléfono',  $telefono],
    ['Ruta / Destino', $ruta],
    ['Notas',     $notas],
]);

$drawSection('DATOS DEL ENVÍO', [
    ['Servicio',         $servicio],
    ['Costo',            $costo],
    ['Peso',             $peso],
    ['Cantidad de bultos', (string) $paquetes],
]);

$drawSection('TRANSPORTE ASIGNADO', [
    ['Transportista', $transportista],
    ['Vehículo',      $vehiculo],
]);

/* ================================================================
   NOTA INFORMATIVA
   ================================================================ */
$pdf->SetFillColor(230, 240, 255);
$pdf->SetDrawColor(...$NAVY);
$pdf->SetTextColor(...$NAVY);
$pdf->SetFont('Helvetica', 'B', 8);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->RoundedRect = null; // no disponible en FPDF base, usamos Rect
$pdf->SetLineWidth(0.4);
$pdf->Rect(18, $y, 178, 16, 'DF');
$pdf->SetXY(22, $y + 2);
$pdf->Cell(0, 5, t('INSTRUCCIONES DE ENTREGA'), 0, 1, 'L');
$pdf->SetFont('Helvetica', '', 7.5);
$pdf->SetXY(22, $y + 8);
$pdf->Cell(0, 5, t('El cliente debe firmar este documento al recibir el envío. El transportista fotografía la hoja firmada como evidencia en el sistema.'), 0, 1, 'L');

$pdf->Ln(12);

/* ================================================================
   LÍNEAS DE FIRMA
   ================================================================ */
$pdf->SetDrawColor(...$NAVY);
$pdf->SetLineWidth(0.6);

$y = $pdf->GetY();
$pdf->Line(18,  $y, 108, $y);   /* línea izquierda */
$pdf->Line(118, $y, 208, $y);   /* línea derecha   */

$pdf->SetTextColor(...$GRAY);
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetXY(18, $y + 2);
$pdf->Cell(90, 5, t('Nombre de quien recibe'), 0, 0, 'C');
$pdf->SetXY(118, $y + 2);
$pdf->Cell(90, 5, 'Firma', 0, 0, 'C');

/* ================================================================
   PIE DE PÁGINA (franja final)
   ================================================================ */
$pdf->SetY(-20);
$pdf->SetFillColor(...$RED);
$pdf->Rect(0, $pdf->GetY(), 216, 20, 'F');
$pdf->SetTextColor(...$WHITE);
$pdf->SetFont('Helvetica', '', 7.5);
$pdf->SetX(18);
$pdf->Cell(0, 8, t('TRAERSA — Documento generado automáticamente por el sistema interno  ·  ' . $guia), 0, 1, 'C');

/* ================================================================
   SALIDA — limpiar cualquier output previo y enviar el PDF
   ================================================================ */
ob_clean();
$pdf->Output('I', 'orden_entrega_' . $envioId . '.pdf');
exit;