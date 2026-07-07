<?php

require '../BackEnd/auth.php';

if (($_SESSION['rol_id'] ?? null) != 3) {

    header("Location: ../login/login.php");
    exit();

}

require '../conexion.php';

$stmtTipo = $conn->prepare("SELECT tipo_empleado, nombre FROM usuarios WHERE id = ?");
if (!$stmtTipo) {
    die('Falta ejecutar cambios_base_datos_empleado.sql en la base de datos.');
}
$stmtTipo->bind_param("i", $_SESSION['usuario_id']);
$stmtTipo->execute();
$filaUsuario = $stmtTipo->get_result()->fetch_assoc();
$tipoEmpleado = $filaUsuario['tipo_empleado'] ?? null;

if ($tipoEmpleado !== 'transportista') {
    header("Location: empleado.php");
    exit();
}

// Fila del conductor (puede no existir todavía si nunca le han asignado un envío)
$stmtCond = $conn->prepare("SELECT id_conductor FROM conductores WHERE usuario_id = ?");
$stmtCond->bind_param("i", $_SESSION['usuario_id']);
$stmtCond->execute();
$conductorId = ($stmtCond->get_result()->fetch_assoc())['id_conductor'] ?? null;

$envios = [];

if ($conductorId) {

    $sql = "SELECT e.*, c.titulo AS servicio_titulo, c.tipo_entrega,
                   a.notas AS notas_asignacion,
                   v.placa, v.tipo AS vehiculo_tipo, v.capacidad_kg
            FROM envios e
            INNER JOIN asignaciones a ON a.envio_id = e.id_envio
            LEFT JOIN categoria c ON c.id = e.servicio_id
            LEFT JOIN vehiculos v ON v.id_vehiculo = a.vehiculo_id
            WHERE a.conductor_id = ?
            ORDER BY FIELD(e.estado, 'En ruta', 'Asignado', 'Entregado'), e.fecha_solicitud DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $conductorId);
    $stmt->execute();
    $resultadoEnvios = $stmt->get_result();

    while ($fila = $resultadoEnvios->fetch_assoc()) {

        // Historial de seguimiento de este envío (para el timeline del modal)
        $stmtHist = $conn->prepare("SELECT estado, ubicacion, observaciones, foto_evidencia, fecha
                                     FROM seguimiento_envio
                                     WHERE envio_id = ?
                                     ORDER BY fecha ASC");
        $stmtHist->bind_param("i", $fila['id_envio']);
        $stmtHist->execute();
        $fila['historial'] = $stmtHist->get_result()->fetch_all(MYSQLI_ASSOC);

        $envios[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis envíos | Empleado TRAERSA</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">

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

        <div class="menu-section">

            <div class="menu-title">TRANSPORTE</div>

            <a href="proceso_empleado.php" class="menu-item active">
                <i class="fa-solid fa-truck"></i>
                <span>Mis envíos</span>
            </a>

            <a href="perfil_empleado.php" class="menu-item">
                <i class="fa-solid fa-id-card"></i>
                <span>Mi perfil</span>
            </a>

        </div>

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

        <section class="process-section">

            <div class="process-header">

                <div>
                    <small>Hola, <?php echo htmlspecialchars($filaUsuario['nombre'] ?? 'transportista'); ?></small>
                    <h1>MIS <span>ENVÍOS</span></h1>
                </div>

                <div class="search-box">
                    <input type="text" id="buscadorProceso" placeholder="Buscar guía, cliente o servicio">
                    <button class="btn-search" type="button">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>

            </div>

            <div class="process-table" id="processTable">

                <?php if (empty($envios)): ?>

                    <div class="empty-row">
                        <i class="fa-solid fa-truck-fast"></i><br>
                        Todavía no tienes envíos asignados. En cuanto la oficina te asigne uno, aparecerá aquí.
                    </div>

                <?php else: ?>

                    <?php foreach ($envios as $fila):

                        $claseEstado = 'warehouse';
                        if ($fila['estado'] === 'En ruta') $claseEstado = 'running';
                        if ($fila['estado'] === 'Entregado') $claseEstado = 'completed';
                        if ($fila['estado'] === 'No entregado') $claseEstado = 'failed';

                        $textoBusqueda = strtolower(
                            ($fila['numero_guia'] ?? '') . ' ' .
                            ($fila['nombre_cliente'] ?? '') . ' ' .
                            ($fila['servicio_titulo'] ?? '')
                        );
                    ?>

                    <div class="process-row" data-busqueda="<?php echo htmlspecialchars($textoBusqueda); ?>">

                        <div class="process-id">
                            #<?php echo htmlspecialchars($fila['numero_guia'] ?: $fila['id_envio']); ?>
                        </div>

                        <div class="process-client">
                            <strong>Cliente:</strong> <?php echo htmlspecialchars($fila['nombre_cliente'] ?: 'Sin nombre'); ?>
                            <p><?php echo htmlspecialchars($fila['servicio_titulo'] ?: 'Servicio'); ?> · <?php echo htmlspecialchars($fila['placa'] ?: 'Sin vehículo'); ?></p>
                        </div>

                        <div class="process-route">
                            <i class="fa-solid fa-location-dot"></i>
                            <?php echo htmlspecialchars(($fila['origen'] ?: '—') . ' → ' . ($fila['destino'] ?: '—')); ?>
                        </div>

                        <div class="process-price">
                            Q<?php echo number_format((float) $fila['costo'], 2); ?>
                        </div>

                        <div class="process-status <?php echo $claseEstado; ?>">
                            <?php echo htmlspecialchars(strtoupper($fila['estado'])); ?>
                        </div>

                        <div class="row-actions-group">
                            <a class="btn-mini" href="orden_pdf.php?id=<?php echo (int) $fila['id_envio']; ?>" target="_blank" title="Ver orden de entrega">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>
                            <button class="btn-view open-modal" onclick="abrirModal(<?php echo (int) $fila['id_envio']; ?>)">
                                VER PROCESO
                            </button>
                        </div>

                    </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </section>

        <footer class="footer">

            <div class="footer-container">

                <div class="footer-box">
                    <h4>Síguenos en redes sociales</h4>
                    <div class="social">
                        <i class="fa-brands fa-facebook"></i>
                        <i class="fa-brands fa-x-twitter"></i>
                        <i class="fa-brands fa-instagram"></i>
                        <i class="fa-brands fa-linkedin"></i>
                    </div>
                </div>

                <div class="footer-box">
                    <h4>Servicios</h4>
                    <ul>
                        <li>Gestión Aduanal</li>
                        <li>Transporte Terrestre</li>
                        <li>Transporte Marítimo</li>
                        <li>Almacenaje</li>
                        <li>Proyectos Especiales</li>
                    </ul>
                </div>

                <div class="footer-box">
                    <h4>Grupo TRAERSA</h4>
                    <ul>
                        <li>Únete a nuestro equipo</li>
                        <li>Sobre nosotros</li>
                        <li>Deseas ser proveedor</li>
                    </ul>
                </div>

                <div class="footer-box">
                    <h4>Nuestros valores</h4>
                    <ul>
                        <li>Sostenibilidad</li>
                        <li>Garantía total</li>
                        <li>Responsabilidad</li>
                    </ul>
                </div>

            </div>

            <div class="contact">
                <div>
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>+502 78562384</span>
                </div>
                <div>
                    <i class="fa-solid fa-envelope"></i>
                    <span>traersa2026@gmail.com</span>
                </div>
                <div>
                    <i class="fa-solid fa-location-dot"></i>
                    <span>31 av 2-48 zona 6 de mixco</span>
                </div>
            </div>

        </footer>

    </main>

    <!-- MODAL: VER PROCESO -->

    <div class="modal" id="procesoModal">

        <div class="modal-content">

            <button class="close-modal" id="closeProcesoModal">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="modal-body" id="modalBody">
                <!-- Se llena dinámicamente con JS a partir de envíosData -->
            </div>

        </div>

    </div>

    <script>

        const enviosData = <?php echo json_encode($envios, JSON_UNESCAPED_UNICODE); ?>;

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

        // Buscador (filtra en el navegador, sin recargar)
        const buscador = document.getElementById("buscadorProceso");
        buscador.addEventListener("input", () => {
            const termino = buscador.value.trim().toLowerCase();
            document.querySelectorAll(".process-row").forEach(row => {
                const coincide = row.dataset.busqueda.includes(termino);
                row.style.display = coincide ? "" : "none";
            });
        });

        // ============================================================
        // Modal de proceso
        // ============================================================
        const procesoModal = document.getElementById("procesoModal");
        const modalBody = document.getElementById("modalBody");
        const closeProcesoModal = document.getElementById("closeProcesoModal");

        const SIGUIENTE_ESTADO = {
            "Asignado": ["En ruta", "Entregado", "No entregado"],
            "En ruta": ["Entregado", "No entregado"],
            "No entregado": ["En ruta", "Entregado"]
        };

        function formatearFecha(fechaSql){
            if(!fechaSql) return "";
            const d = new Date(fechaSql.replace(" ", "T"));
            if(isNaN(d)) return fechaSql;
            return d.toLocaleString("es-GT", { day:"2-digit", month:"2-digit", year:"numeric", hour:"2-digit", minute:"2-digit" });
        }

        function abrirModal(idEnvio){

            const envio = enviosData.find(e => parseInt(e.id_envio) === idEnvio);
            if(!envio) return;

            const opcionesSiguientes = SIGUIENTE_ESTADO[envio.estado] || [];

            let timelineHtml = "";
            (envio.historial || []).forEach(h => {
                timelineHtml += `
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-text">
                            <strong>${h.estado}</strong>
                            <small>${formatearFecha(h.fecha)}${h.ubicacion ? " · " + h.ubicacion : ""}</small>
                            ${h.observaciones ? `<small>${h.observaciones}</small>` : ""}
                        </div>
                    </div>
                `;
            });

            let formularioHtml = "";

            if(opcionesSiguientes.length > 0){
                formularioHtml = `
                    <form id="formProceso">

                        <input type="hidden" id="envioIdForm" value="${envio.id_envio}">

                        <div class="gallery-input">
                            <label>ACTUALIZAR ESTADO:</label>
                            <select id="nuevoEstado">
                                ${opcionesSiguientes.map(op => `<option value="${op}">${op}</option>`).join("")}
                            </select>
                        </div>

                        <div class="gallery-input">
                            <label>UBICACIÓN ACTUAL (OPCIONAL):</label>
                            <input type="text" id="ubicacionProceso" placeholder="Ej. Km 45 carretera al Atlántico">
                        </div>

                        <div class="gallery-input">
                            <label>OBSERVACIONES (OPCIONAL):</label>
                            <textarea id="observacionesProceso" placeholder="Notas sobre este paso"></textarea>
                        </div>

                        <div class="gallery-input" id="campoFotoWrap" style="display:none;">
                            <label id="labelFoto">FOTO DE EVIDENCIA:</label>
                            <label class="file-input-wrap" id="fileWrap">
                                <i class="fa-solid fa-camera"></i>
                                <span id="fileWrapTexto">Toca para tomar o subir una foto</span>
                                <input type="file" id="fotoEvidencia" accept="image/*" capture="environment">
                            </label>
                        </div>

                        <div class="form-msg" id="formMsgProceso" role="status" aria-live="polite"></div>

                        <button type="submit" class="gallery-btn" id="btnGuardarProceso">
                            GUARDAR
                        </button>

                    </form>
                `;
            }

            modalBody.innerHTML = `
                <h2>#${envio.numero_guia || envio.id_envio}</h2>
                <p class="modal-sub">${envio.servicio_titulo || "Servicio"} · ${envio.tipo_entrega || ""}</p>

                <div class="envio-detail-grid">
                    <div class="envio-detail-item">
                        <span>Cliente</span>
                        <strong>${envio.nombre_cliente || "Sin nombre"}</strong>
                    </div>
                    <div class="envio-detail-item">
                        <span>Teléfono</span>
                        <strong>${envio.telefono_cliente || "—"}</strong>
                    </div>
                    <div class="envio-detail-item full">
                        <span>Ruta</span>
                        <strong>${envio.origen || "—"} → ${envio.destino || "—"}</strong>
                    </div>
                    <div class="envio-detail-item">
                        <span>Vehículo</span>
                        <strong>${envio.placa || "Sin asignar"} ${envio.vehiculo_tipo ? "(" + envio.vehiculo_tipo + ")" : ""}</strong>
                    </div>
                    <div class="envio-detail-item">
                        <span>Peso / Paquetes</span>
                        <strong>${envio.peso || "—"} Kg · ${envio.paquetes || "—"} paq.</strong>
                    </div>
                </div>

                <div class="timeline">${timelineHtml || "<p class='modal-sub'>Sin movimientos registrados todavía.</p>"}</div>

                ${formularioHtml}
            `;

            if(opcionesSiguientes.length > 0){

                const selectEstado = document.getElementById("nuevoEstado");
                const campoFotoWrap = document.getElementById("campoFotoWrap");
                const fileWrap = document.getElementById("fileWrap");
                const fotoEvidencia = document.getElementById("fotoEvidencia");
                const fileWrapTexto = document.getElementById("fileWrapTexto");

                function toggleCampoFoto(){
                    const esEntregado = selectEstado.value === "Entregado";
                    const esNoEntregado = selectEstado.value === "No entregado";
                    campoFotoWrap.style.display = (esEntregado || esNoEntregado) ? "block" : "none";
                    document.getElementById("labelFoto").textContent = esEntregado
                        ? "FOTO DE EVIDENCIA (DOCUMENTO / FIRMA DEL CLIENTE) — OBLIGATORIA:"
                        : "FOTO DE EVIDENCIA — OPCIONAL:";
                }
                toggleCampoFoto();
                selectEstado.addEventListener("change", toggleCampoFoto);

                fotoEvidencia.addEventListener("change", () => {
                    if(fotoEvidencia.files.length > 0){
                        fileWrap.classList.add("has-file");
                        fileWrapTexto.textContent = fotoEvidencia.files[0].name;
                    }else{
                        fileWrap.classList.remove("has-file");
                        fileWrapTexto.textContent = "Toca para tomar o subir la foto del documento firmado";
                    }
                });

                document.getElementById("formProceso").addEventListener("submit", function(e){
                    e.preventDefault();

                    const msg = document.getElementById("formMsgProceso");
                    const btn = document.getElementById("btnGuardarProceso");

                    msg.className = "form-msg";
                    msg.textContent = "";

                    if(selectEstado.value === "Entregado" && fotoEvidencia.files.length === 0){
                        msg.className = "form-msg show err";
                        msg.textContent = "Para marcar como entregado necesitas adjuntar la foto del documento firmado.";
                        return;
                    }

                    const datos = new FormData();
                    datos.append("envio_id", document.getElementById("envioIdForm").value);
                    datos.append("nuevo_estado", selectEstado.value);
                    datos.append("ubicacion", document.getElementById("ubicacionProceso").value);
                    datos.append("observaciones", document.getElementById("observacionesProceso").value);
                    if(fotoEvidencia.files.length > 0){
                        datos.append("foto", fotoEvidencia.files[0]);
                    }

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

                    fetch("procesar_proceso_empleado.php", {
                        method: "POST",
                        body: datos
                    })
                    .then(res => res.json())
                    .then(data => {
                        msg.className = "form-msg show " + (data.success ? "ok" : "err");
                        msg.textContent = data.message;
                        if(data.success){
                            setTimeout(() => location.reload(), 1100);
                        }
                    })
                    .catch(() => {
                        msg.className = "form-msg show err";
                        msg.textContent = "Ocurrió un error de conexión.";
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.textContent = "GUARDAR";
                    });

                });
            }

            procesoModal.classList.add("active");
        }

        closeProcesoModal.addEventListener("click", () => procesoModal.classList.remove("active"));
        procesoModal.addEventListener("click", (e) => {
            if(e.target === procesoModal) procesoModal.classList.remove("active");
        });

    </script>

</body>

</html>