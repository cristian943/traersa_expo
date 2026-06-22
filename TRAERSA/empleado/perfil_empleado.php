<?php
/**
 * perfil_empleado.php
 * --------------------
 * Página de SOLO LECTURA. Un empleado puede ver sus propios datos
 * (nombre, correo, tipo, y si es transportista, su vehículo actual),
 * pero no puede editarlos desde aquí — eso solo lo hace un
 * administrador desde administrador/Editar_Usuario.php.
 */

require '../BackEnd/auth.php';

if (($_SESSION['rol_id'] ?? null) != 3) {
    header("Location: ../login/login.php");
    exit();
}

require '../conexion.php';

$usuarioId = (int) $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT nombre, email, tipo_empleado, estado FROM usuarios WHERE id = ?");
if (!$stmt) {
    die('Falta ejecutar cambios_base_datos_empleado.sql en la base de datos.');
}
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$perfil = $stmt->get_result()->fetch_assoc();

$tipoEmpleado = $perfil['tipo_empleado'] ?? null;

$vehiculoActual = null;
$totalEntregas = 0;
$totalAsignados = 0;

if ($tipoEmpleado === 'transportista') {

    // Vehículo de la asignación más reciente (la que esté usando ahora mismo)
    $stmtVeh = $conn->prepare(
        "SELECT v.placa, v.tipo, v.capacidad_kg, e.estado AS estado_envio
         FROM conductores cd
         INNER JOIN asignaciones a ON a.conductor_id = cd.id_conductor
         INNER JOIN vehiculos v ON v.id_vehiculo = a.vehiculo_id
         INNER JOIN envios e ON e.id_envio = a.envio_id
         WHERE cd.usuario_id = ?
         ORDER BY a.fecha_asignacion DESC
         LIMIT 1"
    );
    $stmtVeh->bind_param("i", $usuarioId);
    $stmtVeh->execute();
    $vehiculoActual = $stmtVeh->get_result()->fetch_assoc();

    $stmtTotal = $conn->prepare(
        "SELECT
            SUM(CASE WHEN e.estado = 'Entregado' THEN 1 ELSE 0 END) AS entregados,
            COUNT(*) AS total
         FROM envios e
         INNER JOIN asignaciones a ON a.envio_id = e.id_envio
         INNER JOIN conductores cd ON cd.id_conductor = a.conductor_id
         WHERE cd.usuario_id = ?"
    );
    $stmtTotal->bind_param("i", $usuarioId);
    $stmtTotal->execute();
    $filaTotal = $stmtTotal->get_result()->fetch_assoc();
    $totalEntregas = (int) ($filaTotal['entregados'] ?? 0);
    $totalAsignados = (int) ($filaTotal['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi perfil | Empleado TRAERSA</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/estiloo.css" rel="stylesheet">

</head>

<body>

    <div class="menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </div>

    <div class="overlay" id="overlay"></div>

    <aside class="sidebar fade-up" id="sidebar">

        <div class="logo-container">

            <a href="empleado.php">
                <img src="imagenes/logo2.png" alt="Logo">
            </a>

            <h1>TRAERSA</h1>
        </div>

        <?php if ($tipoEmpleado === 'oficinista'): ?>

        <div class="menu-section">

            <div class="menu-title">OFICINA</div>

            <a href="cotizaciones_empleado.php" class="menu-item">
                <i class="fa-solid fa-tags"></i>
                <span>Cotizaciones</span>
            </a>

            <a href="ejecucion_empleado.php" class="menu-item">
                <i class="fa-regular fa-clock"></i>
                <span>En ejecución</span>
            </a>

            <a href="perfil_empleado.php" class="menu-item active">
                <i class="fa-solid fa-id-card"></i>
                <span>Mi perfil</span>
            </a>

        </div>

        <?php else: ?>

        <div class="menu-section">

            <div class="menu-title">TRANSPORTE</div>

            <a href="proceso_empleado.php" class="menu-item">
                <i class="fa-solid fa-truck"></i>
                <span>Mis envíos</span>
            </a>

            <a href="perfil_empleado.php" class="menu-item active">
                <i class="fa-solid fa-id-card"></i>
                <span>Mi perfil</span>
            </a>

        </div>

        <?php endif; ?>

        <div class="sidebar-bottom">

            <form action="../login/logout.php" method="POST">
                <button type="submit" class="btn-logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>

        </div>

    </aside>

    <main class="main-content fade-up">

        <header class="header">

            <div class="header-title">
                <h2>Mi perfil</h2>
                <p>Solo lectura. Si algo está mal o cambió, pide a un administrador que lo actualice desde Usuarios.</p>
            </div>

        </header>

        <div class="table-card">

            <div class="table-header">
                <h3>DATOS DEL EMPLEADO</h3>
            </div>

            <div class="envio-detail-grid">

                <div class="envio-detail-item">
                    <span>Nombre</span>
                    <strong><?php echo htmlspecialchars($perfil['nombre'] ?? '—'); ?></strong>
                </div>

                <div class="envio-detail-item">
                    <span>Correo</span>
                    <strong><?php echo htmlspecialchars($perfil['email'] ?? '—'); ?></strong>
                </div>

                <div class="envio-detail-item">
                    <span>Tipo de empleado</span>
                    <strong><?php echo $tipoEmpleado ? htmlspecialchars(ucfirst($tipoEmpleado)) : 'Sin asignar'; ?></strong>
                </div>

                <div class="envio-detail-item">
                    <span>Estado de la cuenta</span>
                    <strong><?php echo !empty($perfil['estado']) ? 'Activo' : 'Inactivo'; ?></strong>
                </div>

            </div>

            <?php if ($tipoEmpleado === 'transportista'): ?>

                <div class="table-header" style="margin-top:10px;">
                    <h3>VEHÍCULO ACTUAL</h3>
                </div>

                <?php if ($vehiculoActual): ?>

                    <div class="envio-detail-grid">
                        <div class="envio-detail-item">
                            <span>Placa</span>
                            <strong><?php echo htmlspecialchars($vehiculoActual['placa']); ?></strong>
                        </div>
                        <div class="envio-detail-item">
                            <span>Tipo</span>
                            <strong><?php echo htmlspecialchars($vehiculoActual['tipo']); ?></strong>
                        </div>
                        <div class="envio-detail-item">
                            <span>Capacidad</span>
                            <strong><?php echo htmlspecialchars($vehiculoActual['capacidad_kg']); ?> Kg</strong>
                        </div>
                        <div class="envio-detail-item">
                            <span>Estado del envío que lo usa</span>
                            <strong><?php echo htmlspecialchars($vehiculoActual['estado_envio']); ?></strong>
                        </div>
                    </div>

                    <p class="hint" style="margin-top:10px;">El vehículo cambia según el envío que la oficina te asigne; este es el de tu asignación más reciente.</p>

                <?php else: ?>

                    <div class="empty-row">
                        <i class="fa-solid fa-truck"></i><br>
                        Todavía no tienes ningún vehículo asignado.
                    </div>

                <?php endif; ?>

                <div class="table-header" style="margin-top:10px;">
                    <h3>RESUMEN</h3>
                </div>

                <div class="envio-detail-grid">
                    <div class="envio-detail-item">
                        <span>Envíos entregados</span>
                        <strong><?php echo $totalEntregas; ?></strong>
                    </div>
                    <div class="envio-detail-item">
                        <span>Total de envíos asignados</span>
                        <strong><?php echo $totalAsignados; ?></strong>
                    </div>
                </div>

            <?php endif; ?>

        </div>

    </main>

    <script>

        const sidebar = document.getElementById("sidebar");
        const menuToggle = document.getElementById("menuToggle");
        const overlay = document.getElementById("overlay");

        menuToggle.addEventListener("click", () => {
            if (window.innerWidth <= 900) {
                sidebar.classList.toggle("mobile-active");
                overlay.classList.toggle("active");
            } else {
                sidebar.classList.toggle("closed");
            }
        });

        overlay.addEventListener("click", () => {
            sidebar.classList.remove("mobile-active");
            overlay.classList.remove("active");
        });

    </script>

</body>

</html>