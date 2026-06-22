<?php
/**
 * empleado.php
 * ------------
 * Punto de entrada del portal de empleados. No muestra nada por sí
 * mismo: revisa si el usuario es oficinista o transportista y lo manda
 * a la pantalla que le corresponde. Si todavía no tiene tipo asignado,
 * muestra un aviso en vez de redirigir (para no generar un bucle con
 * las otras páginas).
 */

require '../BackEnd/auth.php';

if (($_SESSION['rol_id'] ?? null) != 3) {
    header("Location: ../login/login.php");
    exit();
}

require '../conexion.php';

$stmt = $conn->prepare("SELECT tipo_empleado, nombre FROM usuarios WHERE id = ?");
if (!$stmt) {
    die('Falta ejecutar cambios_base_datos_empleado.sql en la base de datos.');
}
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
$tipoEmpleado = $fila['tipo_empleado'] ?? null;

if ($tipoEmpleado === 'transportista') {
    header("Location: proceso_empleado.php");
    exit();
}

if ($tipoEmpleado === 'oficinista') {
    header("Location: cotizaciones_empleado.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleado TRAERSA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
</head>

<body>

    <div class="pending-wrap">
        <div class="pending-card">
            <i class="fa-solid fa-user-clock"></i>
            <h1>Hola, <?php echo htmlspecialchars($fila['nombre'] ?? 'empleado'); ?></h1>
            <p>
                Tu cuenta todavía no tiene un tipo de empleado asignado
                (oficinista o transportista). Pide a un administrador que
                te lo asigne desde Usuarios para poder continuar.
            </p>
            <form action="../login/logout.php" method="POST">
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </div>

</body>

</html>