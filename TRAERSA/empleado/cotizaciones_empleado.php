<?php

require '../BackEnd/auth.php';

if (($_SESSION['rol_id'] ?? null) != 3) {

    header("Location: ../login/login.php");
    exit();

}

require '../conexion.php';

// Solo el empleado de tipo "oficinista" gestiona cotizaciones; un transportista
// que llegue aquí por error se redirige a su propia pantalla de procesos.
$stmtTipo = $conn->prepare("SELECT tipo_empleado FROM usuarios WHERE id = ?");
if (!$stmtTipo) {
    die('Falta ejecutar cambios_base_datos_empleado.sql en la base de datos.');
}
$stmtTipo->bind_param("i", $_SESSION['usuario_id']);
$stmtTipo->execute();
$tipoEmpleado = ($stmtTipo->get_result()->fetch_assoc())['tipo_empleado'] ?? null;

if ($tipoEmpleado !== 'oficinista') {
    header("Location: empleado.php");
    exit();
}

// Cotizaciones activas: todo lo que aún no pasó a ejecución ni se completó
$sql = "SELECT e.*, c.titulo AS servicio_titulo, c.tipo_entrega
        FROM envios e
        LEFT JOIN categoria c ON c.id = e.servicio_id
        WHERE e.estado IN ('Pendiente','En revisión','Aprobada','Rechazada','Cancelado')
        ORDER BY e.fecha_solicitud DESC";
$resultado = $conn->query($sql);

// Clientes registrados, para el buscador del formulario
$clientesRes = $conn->query("SELECT id_cliente, nombre_empresa FROM clientes WHERE estado = 1 ORDER BY nombre_empresa ASC");
$clientesData = [];
while ($cli = $clientesRes->fetch_assoc()) {
    $clientesData[] = ['id' => (int) $cli['id_cliente'], 'label' => $cli['nombre_empresa']];
}

// Servicios/categorías disponibles para cotizar
$serviciosRes = $conn->query("SELECT id, titulo, tipo_entrega, precio FROM categoria ORDER BY titulo ASC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cotizaciones | Empleado TRAERSA</title>

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

        <div class="menu-section">

            <div class="menu-title">OFICINA</div>

            <a href="cotizaciones_empleado.php" class="menu-item active ">
                <i class="fa-solid fa-tags"></i>
                <span>Cotizaciones</span>
            </a>

            <a href="ejecucion_empleado.php" class="menu-item">
                <i class="fa-regular fa-clock"></i>
                <span>En ejecución</span>
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

        Cerrar sesión

    </button>

</form>

        </div>

    </aside>

    <main class="main-content fade-up">

        <header class="header">

            <div class="header-title">
                <h2>Bienvenido</h2>
                <p>Gestiona las cotizaciones entrantes: crea, edita y decide su estado.</p>
            </div>

            <div class="user-profile">

                <div class="user-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div class="user-info">
                    OFICINISTA
                </div>

            </div>

        </header>

<div class="gallery-container fade-up">

    <!-- FORMULARIO -->

    <div class="gallery-top">

        <h2 id="formTitulo">NUEVA COTIZACIÓN</h2>

        <div class="form-msg" id="formMsg" role="status" aria-live="polite"></div>

        <form id="formCotizacion">

            <input type="hidden" id="idEnvio" value="">

            <div class="gallery-input combo-field">
                <label>CLIENTE REGISTRADO (OPCIONAL):</label>
                <input type="text" id="clienteBuscador" class="combo-input" placeholder="Escribe el nombre del cliente..." autocomplete="off">
                <input type="hidden" id="clienteId" value="">
                <div class="combo-results" id="clienteResultados"></div>
                <div class="combo-selected-pill" id="clientePill">
                    <span class="pill-text"></span>
                    <button type="button" title="Quitar selección"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <small class="hint"><?php echo count($clientesData); ?> cliente<?php echo count($clientesData) == 1 ? '' : 's'; ?> con cuenta registrada. Si no aparece, déjalo vacío y escribe su nombre abajo.</small>
            </div>

            <div class="gallery-input">
                <label>NOMBRE DEL CLIENTE:</label>
                <input type="text" id="nombreCliente" placeholder="Nombre completo o empresa">
            </div>

            <div class="gallery-input">
                <label>TELÉFONO:</label>
                <input type="text" id="telefonoCliente" placeholder="Ej. 5555 5555">
            </div>

            <div class="gallery-input">
                <label>SERVICIO COTIZADO:</label>
                <select id="servicioId">
                    <option value="">-- Selecciona un servicio --</option>
                    <?php
                    $serviciosRes->data_seek(0);
                    while ($srv = $serviciosRes->fetch_assoc()):
                    ?>
                        <option value="<?php echo (int) $srv['id']; ?>" data-precio="<?php echo (float) $srv['precio']; ?>">
                            <?php echo htmlspecialchars($srv['titulo']) . ' — ' . htmlspecialchars($srv['tipo_entrega']) . ' (Q' . number_format($srv['precio'], 2) . ')'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="gallery-input">
                <label>ORIGEN:</label>
                <input type="text" id="origen" placeholder="Ciudad / dirección de origen">
            </div>

            <div class="gallery-input">
                <label>DESTINO:</label>
                <input type="text" id="destino" placeholder="Ciudad / dirección de destino">
            </div>

            <div class="gallery-input">
                <label>PESO (KG):</label>
                <input type="number" id="peso" min="0" step="0.01" placeholder="Ej. 250">
            </div>

            <div class="gallery-input">
                <label>NÚMERO DE PAQUETES:</label>
                <input type="number" id="paquetes" min="0" placeholder="Ej. 12">
            </div>

            <div class="gallery-input">
                <label>COSTO COTIZADO (Q):</label>
                <input type="number" id="costo" min="0" step="0.01" placeholder="Ej. 1250.00">
            </div>

            <div class="gallery-input">
                <label>FECHA ESTIMADA DE ENTREGA:</label>
                <input type="date" id="fechaEntrega">
            </div>

            <div class="gallery-input">
                <label>NOTAS:</label>
                <textarea id="notas" placeholder="Detalles adicionales de la cotización"></textarea>
            </div>

            <button type="submit" class="gallery-btn" id="btnGuardarCotizacion">
                GUARDAR COTIZACIÓN
            </button>

            <button type="button" class="gallery-cancel-edit" id="btnCancelarEdicion" style="display:none;">
                CANCELAR EDICIÓN
            </button>

        </form>

    </div>

    <!-- TABLA -->

    <div class="table-card">

        <div class="table-header services-header">

            <h3>COTIZACIONES ACTIVAS</h3>

            <span class="unassigned-pill"><?php echo $resultado->num_rows; ?> registro<?php echo $resultado->num_rows == 1 ? '' : 's'; ?></span>

        </div>

        <div class="table-content">

            <?php if ($resultado->num_rows === 0): ?>

                <div class="empty-row">
                    <i class="fa-solid fa-inbox"></i><br>
                    Todavía no hay cotizaciones registradas. Usa el formulario para crear la primera.
                </div>

            <?php else: ?>

                <?php while ($fila = $resultado->fetch_assoc()):

                    $clasesEstado = [
                        'Aprobada'    => 'approved',
                        'En revisión' => 'review',
                        'Rechazada'   => 'rejected',
                        'Cancelado'   => 'cancelled',
                    ];
                    $estadoClase = isset($clasesEstado[$fila['estado']]) ? $clasesEstado[$fila['estado']] : 'pending';

                    $clienteMostrado = $fila['nombre_cliente'] ?: 'Cliente sin nombre';
                ?>

                <div class="table-row quote-row"
                     data-id="<?php echo (int) $fila['id_envio']; ?>"
                     data-cliente-id="<?php echo (int) ($fila['cliente_id'] ?? 0); ?>"
                     data-nombre-cliente="<?php echo htmlspecialchars($fila['nombre_cliente'] ?? ''); ?>"
                     data-telefono="<?php echo htmlspecialchars($fila['telefono_cliente'] ?? ''); ?>"
                     data-servicio-id="<?php echo (int) $fila['servicio_id']; ?>"
                     data-origen="<?php echo htmlspecialchars($fila['origen'] ?? ''); ?>"
                     data-destino="<?php echo htmlspecialchars($fila['destino'] ?? ''); ?>"
                     data-peso="<?php echo htmlspecialchars($fila['peso'] ?? ''); ?>"
                     data-paquetes="<?php echo htmlspecialchars($fila['paquetes'] ?? ''); ?>"
                     data-costo="<?php echo htmlspecialchars($fila['costo'] ?? ''); ?>"
                     data-fecha-entrega="<?php echo $fila['fecha_entrega_estimada'] ? date('Y-m-d', strtotime($fila['fecha_entrega_estimada'])) : ''; ?>"
                     data-notas="<?php echo htmlspecialchars($fila['notas'] ?? ''); ?>">

                    <span>#<?php echo htmlspecialchars($fila['numero_guia'] ?: $fila['id_envio']); ?></span>

                    <div>
                        <strong><?php echo htmlspecialchars($fila['servicio_titulo'] ?: 'Servicio eliminado'); ?></strong>
                        <p>Cliente: <?php echo htmlspecialchars($clienteMostrado); ?></p>
                    </div>

                    <div class="quote-actions">

                        <select class="quote-select <?php echo $estadoClase; ?>">
                            <?php foreach (['Pendiente', 'En revisión', 'Aprobada', 'Rechazada', 'Cancelado'] as $opcion): ?>
                                <option <?php echo $opcion === $fila['estado'] ? 'selected' : ''; ?>><?php echo $opcion; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button class="confirm-btn" onclick="actualizarEstadoCotizacion(<?php echo (int) $fila['id_envio']; ?>, this)">
                            GUARDAR
                        </button>

                    </div>

                    <small>Q<?php echo number_format((float) $fila['costo'], 2); ?></small>

                    <div class="row-actions-group">
                        <button class="row-icon-btn edit" title="Editar" onclick="editarCotizacion(this)">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="row-icon-btn delete" title="Eliminar" onclick="eliminarCotizacion(<?php echo (int) $fila['id_envio']; ?>)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>

                </div>

                <?php endwhile; ?>

            <?php endif; ?>

        </div>

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

        const clientesData = <?php echo json_encode($clientesData, JSON_UNESCAPED_UNICODE); ?>;

        /**
         * Componente genérico de "escribe y aparece" (combobox con búsqueda).
         * Se reutiliza para clientes (aquí) y empleados (Ejecución).
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

            return { limpiar, seleccionar };
        }

        const buscadorCliente = crearBuscador({
            inputEl: document.getElementById("clienteBuscador"),
            hiddenEl: document.getElementById("clienteId"),
            resultsEl: document.getElementById("clienteResultados"),
            pillEl: document.getElementById("clientePill"),
            datos: clientesData,
            vacioTexto: "No se encontró ningún cliente con cuenta registrada con ese nombre."
        });

        const formCotizacion = document.getElementById("formCotizacion");
        const formTitulo = document.getElementById("formTitulo");
        const formMsg = document.getElementById("formMsg");
        const btnGuardar = document.getElementById("btnGuardarCotizacion");
        const btnCancelarEdicion = document.getElementById("btnCancelarEdicion");

        function mostrarMensaje(ok, texto){
            formMsg.className = "form-msg show " + (ok ? "ok" : "err");
            formMsg.textContent = texto;
        }

        function limpiarFormulario(){
            document.getElementById("idEnvio").value = "";
            document.getElementById("clienteId").value = "";
            document.getElementById("clienteBuscador").value = "";
            document.getElementById("clientePill").classList.remove("show");
            document.getElementById("nombreCliente").value = "";
            document.getElementById("telefonoCliente").value = "";
            document.getElementById("servicioId").value = "";
            document.getElementById("origen").value = "";
            document.getElementById("destino").value = "";
            document.getElementById("peso").value = "";
            document.getElementById("paquetes").value = "";
            document.getElementById("costo").value = "";
            document.getElementById("fechaEntrega").value = "";
            document.getElementById("notas").value = "";
            formTitulo.textContent = "NUEVA COTIZACIÓN";
            btnGuardar.textContent = "GUARDAR COTIZACIÓN";
            btnCancelarEdicion.style.display = "none";
            formMsg.className = "form-msg";
        }

        function editarCotizacion(boton){

            const fila = boton.closest(".table-row");

            document.getElementById("idEnvio").value = fila.dataset.id;

            const clienteId = fila.dataset.clienteId || "";
            document.getElementById("clienteId").value = clienteId;
            const clientePill = document.getElementById("clientePill");
            if(clienteId && clienteId !== "0"){
                document.getElementById("clienteBuscador").value = fila.dataset.nombreCliente || "";
                clientePill.querySelector(".pill-text").textContent = fila.dataset.nombreCliente || "";
                clientePill.classList.add("show");
            }else{
                document.getElementById("clienteBuscador").value = "";
                clientePill.classList.remove("show");
            }

            document.getElementById("nombreCliente").value = fila.dataset.nombreCliente || "";
            document.getElementById("telefonoCliente").value = fila.dataset.telefono || "";
            document.getElementById("servicioId").value = fila.dataset.servicioId || "";
            document.getElementById("origen").value = fila.dataset.origen || "";
            document.getElementById("destino").value = fila.dataset.destino || "";
            document.getElementById("peso").value = fila.dataset.peso || "";
            document.getElementById("paquetes").value = fila.dataset.paquetes || "";
            document.getElementById("costo").value = fila.dataset.costo || "";
            document.getElementById("fechaEntrega").value = fila.dataset.fechaEntrega || "";
            document.getElementById("notas").value = fila.dataset.notas || "";

            formTitulo.textContent = "EDITAR COTIZACIÓN #" + fila.dataset.id;
            btnGuardar.textContent = "ACTUALIZAR COTIZACIÓN";
            btnCancelarEdicion.style.display = "block";
            formMsg.className = "form-msg";

            formCotizacion.scrollIntoView({ behavior: "smooth", block: "start" });
        }

        btnCancelarEdicion.addEventListener("click", limpiarFormulario);

        formCotizacion.addEventListener("submit", function(e){

            e.preventDefault();

            const id = document.getElementById("idEnvio").value;
            const accion = id ? "editar" : "crear";

            const datos = new URLSearchParams();
            datos.append("accion", accion);
            if (id) datos.append("id_envio", id);
            datos.append("cliente_id", document.getElementById("clienteId").value);
            datos.append("nombre_cliente", document.getElementById("nombreCliente").value);
            datos.append("telefono_cliente", document.getElementById("telefonoCliente").value);
            datos.append("servicio_id", document.getElementById("servicioId").value);
            datos.append("origen", document.getElementById("origen").value);
            datos.append("destino", document.getElementById("destino").value);
            datos.append("peso", document.getElementById("peso").value);
            datos.append("paquetes", document.getElementById("paquetes").value);
            datos.append("costo", document.getElementById("costo").value);
            datos.append("fecha_entrega_estimada", document.getElementById("fechaEntrega").value);
            datos.append("notas", document.getElementById("notas").value);

            btnGuardar.disabled = true;

            fetch("procesar_cotizacion_empleado.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: datos.toString()
            })
            .then(res => res.json())
            .then(data => {
                mostrarMensaje(data.success, data.message);
                if(data.success){
                    setTimeout(() => location.reload(), 900);
                }
            })
            .catch(() => mostrarMensaje(false, "Ocurrió un error de conexión."))
            .finally(() => { btnGuardar.disabled = false; });

        });

        function actualizarEstadoCotizacion(id, boton){

            const select = boton.previousElementSibling;
            const nuevoEstado = select.value;

            boton.disabled = true;

            fetch("procesar_cotizacion_empleado.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "accion=estado&id_envio=" + id + "&estado=" + encodeURIComponent(nuevoEstado)
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if(data.success) location.reload();
            })
            .catch(() => alert("Ocurrió un error de conexión."))
            .finally(() => { boton.disabled = false; });

        }

        function eliminarCotizacion(id){

            if(!confirm("¿Eliminar esta cotización? Esta acción no se puede deshacer.")) return;

            fetch("procesar_cotizacion_empleado.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "accion=eliminar&id_envio=" + id
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if(data.success) location.reload();
            })
            .catch(() => alert("Ocurrió un error de conexión."));

        }

    </script>

</body>

</html>