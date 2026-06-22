<?php

require '../BackEnd/auth.php';

if ($_SESSION['rol_id'] != 1) {

    header("Location: ../login/login.php");
    exit();

}

require '../conexion.php';

// Envíos aprobados o ya en marcha (listos para asignar o seguir su curso)
$sql = "SELECT e.*, c.titulo AS servicio_titulo, c.tipo_entrega,
               a.vehiculo_id, a.conductor_id, a.notas AS notas_asignacion,
               v.placa, v.tipo AS vehiculo_tipo,
               u.id AS conductor_usuario_id, u.nombre AS conductor_nombre
        FROM envios e
        LEFT JOIN categoria c ON c.id = e.servicio_id
        LEFT JOIN asignaciones a ON a.envio_id = e.id_envio
        LEFT JOIN vehiculos v ON v.id_vehiculo = a.vehiculo_id
        LEFT JOIN conductores cd ON cd.id_conductor = a.conductor_id
        LEFT JOIN usuarios u ON u.id = cd.usuario_id
        WHERE e.estado IN ('Aprobada','Asignado','En ruta')
        ORDER BY
            FIELD(e.estado, 'En ruta', 'Asignado', 'Aprobada'),
            e.fecha_solicitud DESC";
$resultado = $conn->query($sql);

// Empleados disponibles para transportar (rol_id = 3 = empleado)
$empleadosRes = $conn->query("SELECT id, nombre FROM usuarios WHERE rol_id = 3 AND estado = 1 ORDER BY nombre ASC");
$empleadosData = [];
while ($emp = $empleadosRes->fetch_assoc()) {
    $empleadosData[] = ['id' => (int) $emp['id'], 'label' => $emp['nombre']];
}

// Vehículos disponibles
$vehiculosRes = $conn->query("SELECT id_vehiculo, placa, tipo, estado FROM vehiculos ORDER BY placa ASC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard TRAERSA</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/AEstilo.css" rel="stylesheet">

</head>

<body>

    <div class="menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </div>

    <div class="overlay" id="overlay"></div>

  
    <aside class="sidebar fade-up" id="sidebar">

        <div class="logo-container">

            <a href="admin.php">
                <img src="imagenes/logo2.png" alt="Logo">
            </a>

            <h1>TRAERSA</h1>
        </div>




        <div class="menu-section">

            <div class="menu-title">OPERACIONES</div>

            <a href="Cotizaciones_admin.php" class="menu-item">
                <i class="fa-solid fa-tags"></i>
                <span>Cotizaciones</span>
            </a>

            <a href="Ejecucion_admin.php" class="menu-item active">
                <i class="fa-regular fa-clock"></i>
                <span>En ejecución</span>
            </a>

            <a href="Completados_admin.php" class="menu-item">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Completados</span>
            </a>



          </div>

         <div class="menu-section">

            <div class="menu-title">CONTENIDOS</div>


          
                        <a href="Editar_categorias.php" class="menu-item">
                <i class="fa-solid fa-border-all"></i>
                <span>Catalogo</span>
            </a>
            

         </div>

         <div class="menu-section">

            <div class="menu-title">ADMINISTRACIÓN</div>

            <a href="Editar_Usuario.php" class="menu-item">
                <i class="fa-solid fa-users"></i>
                <span>Usuarios</span>
            </a>

        </div>

        <div class="sidebar-bottom">

           <form action="../login/logout.php" method="POST">

    <button type="submit" class="btn-logout">

        <i class="fa-solid fa-arrow-right-from-bracket"></i>

        Cerrar sesión

    </button>

</form>

        </div>

    </aside>

    <main class="main-content fade-up">

        <header class="header">

            <div class="header-title">
                <h2>Bienvenido Administrador</h2>
                <p>Asigna conductor y vehículo a cada envío aprobado y da seguimiento a su entrega.</p>
            </div>

            <div class="user-profile">

                <div class="user-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div class="user-info">
                    ADMIN
                </div>

            </div>

        </header>

<div class="gallery-container fade-up">

    <!-- FORMULARIO -->

    <div class="gallery-top">

        <h2 id="formTitulo">ASIGNAR ENVÍO</h2>

        <div class="form-msg" id="formMsg" role="status" aria-live="polite"></div>

        <form id="formEjecucion">

            <div class="gallery-input">
                <label>ENVÍO:</label>
                <select id="envioId" required>
                    <option value="">-- Selecciona un envío --</option>
                    <?php
                    $resultado->data_seek(0);
                    while ($e = $resultado->fetch_assoc()):
                        $etiqueta = '#' . ($e['numero_guia'] ?: $e['id_envio']) . ' — ' . ($e['nombre_cliente'] ?: 'Sin nombre') . ' (' . $e['estado'] . ')';
                    ?>
                        <option value="<?php echo (int) $e['id_envio']; ?>"
                                data-conductor="<?php echo (int) ($e['conductor_usuario_id'] ?? 0); ?>"
                                data-vehiculo="<?php echo (int) ($e['vehiculo_id'] ?? 0); ?>"
                                data-notas="<?php echo htmlspecialchars($e['notas_asignacion'] ?? ''); ?>"
                                data-estado="<?php echo htmlspecialchars($e['estado']); ?>">
                            <?php echo htmlspecialchars($etiqueta); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="gallery-input combo-field">
                <label>EMPLEADO QUE TRANSPORTA:</label>
                <input type="text" id="empleadoBuscador" class="combo-input" placeholder="Escribe el nombre del empleado..." autocomplete="off">
                <input type="hidden" id="conductorUsuarioId" required>
                <div class="combo-results" id="empleadoResultados"></div>
                <div class="combo-selected-pill" id="empleadoPill">
                    <span class="pill-text"></span>
                    <button type="button" title="Quitar selección"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <small class="hint">Solo se listan usuarios con rol "empleado". Se asignan desde Usuarios.</small>
            </div>

            <div class="gallery-input">
                <label>VEHÍCULO:</label>
                <div class="add-vehicle-row">
                    <select id="vehiculoId" required>
                        <option value="">-- Selecciona un vehículo --</option>
                        <?php
                        $vehiculosRes->data_seek(0);
                        while ($v = $vehiculosRes->fetch_assoc()):
                        ?>
                            <option value="<?php echo (int) $v['id_vehiculo']; ?>">
                                <?php echo htmlspecialchars($v['placa'] . ' — ' . $v['tipo'] . ' (' . $v['estado'] . ')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="button" class="btn-mini" id="btnNuevoVehiculo" title="Agregar vehículo nuevo">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>

            <div class="gallery-input" id="panelNuevoVehiculo" style="display:none;">
                <label>PLACA DEL VEHÍCULO NUEVO:</label>
                <input type="text" id="nuevaPlaca" placeholder="Ej. P-123ABC">
                <label>TIPO:</label>
                <input type="text" id="nuevoTipo" placeholder="Ej. Camión, Tráiler, Camioneta">
                <label>CAPACIDAD (KG):</label>
                <input type="number" id="nuevaCapacidad" min="0" step="0.01" placeholder="Ej. 5000">
                <button type="button" class="gallery-btn" id="btnGuardarVehiculo">
                    GUARDAR VEHÍCULO
                </button>
            </div>

            <div class="gallery-input">
                <label>ESTADO DEL ENVÍO:</label>
                <select id="estadoEjecucion">
                    <option value="Asignado">Asignado</option>
                    <option value="En ruta">En ruta</option>
                    <option value="Entregado">Entregado</option>
                </select>
            </div>

            <div class="gallery-input">
                <label>PLAN / NOTAS DE RUTA:</label>
                <textarea id="notasEjecucion" placeholder="Ruta, horarios, indicaciones especiales"></textarea>
            </div>

            <button type="submit" class="gallery-btn" id="btnGuardarAsignacion">
                GUARDAR ASIGNACIÓN
            </button>

            <div class="gallery-date">
                <?php echo date('d/m/Y'); ?>
            </div>

        </form>

    </div>

    <!-- TABLA -->

    <div class="gallery-table">

        <div class="gallery-header">
            <h3>ENVÍOS EN EJECUCIÓN</h3>
        </div>

        <div class="gallery-row gallery-head">
            <span>GUÍA / CLIENTE</span>
            <span>CONDUCTOR / VEHÍCULO</span>
            <span>ESTADO</span>
            <span>ACCIONES</span>
        </div>

        <?php if ($resultado->num_rows === 0): ?>

            <div class="empty-row">
                <i class="fa-solid fa-truck-fast"></i><br>
                No hay envíos aprobados esperando ejecución todavía.
            </div>

        <?php else: ?>

            <?php
            $resultado->data_seek(0);
            while ($fila = $resultado->fetch_assoc()):

                $estadoClase = 'running';
            ?>

            <div class="gallery-row"
                 data-id="<?php echo (int) $fila['id_envio']; ?>"
                 data-conductor-usuario="<?php echo (int) ($fila['conductor_usuario_id'] ?? 0); ?>"
                 data-vehiculo="<?php echo (int) ($fila['vehiculo_id'] ?? 0); ?>"
                 data-notas="<?php echo htmlspecialchars($fila['notas_asignacion'] ?? ''); ?>">

                <div class="gallery-info">
                    <strong>#<?php echo htmlspecialchars($fila['numero_guia'] ?: $fila['id_envio']); ?> — <?php echo htmlspecialchars($fila['servicio_titulo'] ?: ''); ?></strong>
                    <p>Cliente: <?php echo htmlspecialchars($fila['nombre_cliente'] ?: 'Sin nombre'); ?> · Destino: <?php echo htmlspecialchars($fila['destino'] ?: '—'); ?></p>
                </div>

                <div class="gallery-info">
                    <?php if ($fila['conductor_nombre']): ?>
                        <strong><?php echo htmlspecialchars($fila['conductor_nombre']); ?></strong>
                        <p><?php echo htmlspecialchars(($fila['placa'] ?? '—') . ' · ' . ($fila['vehiculo_tipo'] ?? '')); ?></p>
                    <?php else: ?>
                        <span class="unassigned-pill">Sin asignar</span>
                    <?php endif; ?>
                </div>

                <div class="gallery-actions">
                    <button class="status-btn <?php echo $estadoClase; ?>">
                        <?php echo htmlspecialchars(strtoupper($fila['estado'])); ?>
                    </button>
                </div>

                <div class="row-actions-group">
                    <button class="row-icon-btn edit" title="Editar asignación" onclick="editarAsignacion(this)">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                </div>

            </div>

            <?php endwhile; ?>

        <?php endif; ?>

    </div>

</div>


<!-- FOOTER -->

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

        const menuItems = document.querySelectorAll(".menu-item");

        menuItems.forEach(item => {

            item.addEventListener("click", () => {

                menuItems.forEach(el => {
                    el.classList.remove("active");
                });

                item.classList.add("active");

            });

        });

    </script>


    <script>

        const empleadosData = <?php echo json_encode($empleadosData, JSON_UNESCAPED_UNICODE); ?>;

        /**
         * Componente genérico de "escribe y aparece" (combobox con búsqueda).
         * Mismo patrón usado en Cotizaciones para el campo de cliente.
         */
        function crearBuscador({ inputEl, hiddenEl, resultsEl, pillEl, datos, vacioTexto, maxResultados }) {

            maxResultados = maxResultados || 8;

            function renderResultados(filtro){
                const termino = filtro.trim().toLowerCase();
                const coincidencias = termino === ""
                    ? datos.slice(0, maxResultados)
                    : datos.filter(d => d.label.toLowerCase().includes(termino)).slice(0, maxResultados);

                resultsEl.innerHTML = "";

                if(coincidencias.length === 0){
                    const div = document.createElement("div");
                    div.className = "combo-option combo-empty";
                    div.textContent = vacioTexto;
                    resultsEl.appendChild(div);
                }else{
                    coincidencias.forEach(item => {
                        const div = document.createElement("div");
                        div.className = "combo-option";
                        div.textContent = item.label;
                        div.addEventListener("mousedown", (e) => {
                            e.preventDefault();
                            seleccionar(item);
                        });
                        resultsEl.appendChild(div);
                    });
                }

                resultsEl.classList.add("show");
            }

            function seleccionar(item){
                hiddenEl.value = item.id;
                inputEl.value = item.label;
                resultsEl.classList.remove("show");
                if(pillEl){
                    pillEl.querySelector(".pill-text").textContent = item.label;
                    pillEl.classList.add("show");
                }
            }

            function seleccionarPorId(id){
                const item = datos.find(d => String(d.id) === String(id));
                if(item){
                    seleccionar(item);
                }else{
                    limpiar();
                }
            }

            function limpiar(){
                hiddenEl.value = "";
                inputEl.value = "";
                if(pillEl) pillEl.classList.remove("show");
                resultsEl.classList.remove("show");
            }

            inputEl.addEventListener("focus", () => renderResultados(inputEl.value));
            inputEl.addEventListener("input", () => {
                hiddenEl.value = "";
                if(pillEl) pillEl.classList.remove("show");
                renderResultados(inputEl.value);
            });

            document.addEventListener("click", (e) => {
                if(!inputEl.contains(e.target) && !resultsEl.contains(e.target)){
                    resultsEl.classList.remove("show");
                }
            });

            if(pillEl){
                pillEl.querySelector("button").addEventListener("click", limpiar);
            }

            return { limpiar, seleccionar, seleccionarPorId };
        }

        const buscadorEmpleado = crearBuscador({
            inputEl: document.getElementById("empleadoBuscador"),
            hiddenEl: document.getElementById("conductorUsuarioId"),
            resultsEl: document.getElementById("empleadoResultados"),
            pillEl: document.getElementById("empleadoPill"),
            datos: empleadosData,
            vacioTexto: "No se encontró ningún empleado con ese nombre."
        });

        const formEjecucion = document.getElementById("formEjecucion");
        const formTitulo = document.getElementById("formTitulo");
        const formMsg = document.getElementById("formMsg");
        const btnGuardarAsignacion = document.getElementById("btnGuardarAsignacion");
        const envioSelect = document.getElementById("envioId");

        function mostrarMensaje(ok, texto){
            formMsg.className = "form-msg show " + (ok ? "ok" : "err");
            formMsg.textContent = texto;
        }

        // Al elegir un envío en el selector, si ya tenía asignación, precarga los campos
        envioSelect.addEventListener("change", function(){
            const opt = this.options[this.selectedIndex];
            if(!opt || !opt.value) return;

            if(opt.dataset.conductor && opt.dataset.conductor !== "0"){
                buscadorEmpleado.seleccionarPorId(opt.dataset.conductor);
            }
            if(opt.dataset.vehiculo && opt.dataset.vehiculo !== "0"){
                document.getElementById("vehiculoId").value = opt.dataset.vehiculo;
            }
            document.getElementById("notasEjecucion").value = opt.dataset.notas || "";

            if(opt.dataset.estado === "Asignado" || opt.dataset.estado === "En ruta"){
                document.getElementById("estadoEjecucion").value = opt.dataset.estado === "Asignado" ? "En ruta" : "Entregado";
            }
        });

        function editarAsignacion(boton){

            const fila = boton.closest(".gallery-row");
            const id = fila.dataset.id;

            envioSelect.value = id;

            if(fila.dataset.conductorUsuario && fila.dataset.conductorUsuario !== "0"){
                buscadorEmpleado.seleccionarPorId(fila.dataset.conductorUsuario);
            }
            if(fila.dataset.vehiculo && fila.dataset.vehiculo !== "0"){
                document.getElementById("vehiculoId").value = fila.dataset.vehiculo;
            }
            document.getElementById("notasEjecucion").value = fila.dataset.notas || "";

            formTitulo.textContent = "EDITAR ASIGNACIÓN";
            formEjecucion.scrollIntoView({ behavior: "smooth", block: "start" });
        }

        formEjecucion.addEventListener("submit", function(e){

            e.preventDefault();

            const envioId = envioSelect.value;
            const conductorUsuarioId = document.getElementById("conductorUsuarioId").value;
            const vehiculoId = document.getElementById("vehiculoId").value;

            if(!envioId || !conductorUsuarioId || !vehiculoId){
                mostrarMensaje(false, "Selecciona el envío, el empleado y el vehículo.");
                return;
            }

            const datos = new URLSearchParams();
            datos.append("accion", "asignar");
            datos.append("envio_id", envioId);
            datos.append("conductor_usuario_id", conductorUsuarioId);
            datos.append("vehiculo_id", vehiculoId);
            datos.append("estado", document.getElementById("estadoEjecucion").value);
            datos.append("notas", document.getElementById("notasEjecucion").value);

            btnGuardarAsignacion.disabled = true;

            fetch("procesar_ejecucion_admin.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: datos.toString()
            })
            .then(res => res.json())
            .then(data => {
                mostrarMensaje(data.success, data.message);
                if(data.success){
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(() => mostrarMensaje(false, "Ocurrió un error de conexión."))
            .finally(() => { btnGuardarAsignacion.disabled = false; });

        });

        // Alta rápida de vehículo
        const btnNuevoVehiculo = document.getElementById("btnNuevoVehiculo");
        const panelNuevoVehiculo = document.getElementById("panelNuevoVehiculo");

        btnNuevoVehiculo.addEventListener("click", () => {
            panelNuevoVehiculo.style.display = panelNuevoVehiculo.style.display === "none" ? "flex" : "none";
            panelNuevoVehiculo.style.flexDirection = "column";
            panelNuevoVehiculo.style.gap = "10px";
        });

        document.getElementById("btnGuardarVehiculo").addEventListener("click", () => {

            const placa = document.getElementById("nuevaPlaca").value.trim();
            const tipo = document.getElementById("nuevoTipo").value.trim();
            const capacidad = document.getElementById("nuevaCapacidad").value;

            if(!placa){
                mostrarMensaje(false, "Escribe la placa del vehículo.");
                return;
            }

            const datos = new URLSearchParams();
            datos.append("accion", "agregar_vehiculo");
            datos.append("placa", placa);
            datos.append("tipo", tipo);
            datos.append("capacidad_kg", capacidad);

            fetch("procesar_ejecucion_admin.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: datos.toString()
            })
            .then(res => res.json())
            .then(data => {
                mostrarMensaje(data.success, data.message);
                if(data.success){
                    const select = document.getElementById("vehiculoId");
                    const option = document.createElement("option");
                    option.value = data.id_vehiculo;
                    option.textContent = data.placa + " — " + data.tipo + " (Disponible)";
                    select.appendChild(option);
                    select.value = data.id_vehiculo;
                    document.getElementById("nuevaPlaca").value = "";
                    document.getElementById("nuevoTipo").value = "";
                    document.getElementById("nuevaCapacidad").value = "";
                    panelNuevoVehiculo.style.display = "none";
                }
            })
            .catch(() => mostrarMensaje(false, "Ocurrió un error de conexión."));

        });

    </script>

</body>

</html>