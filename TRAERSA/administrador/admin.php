<?php

require '../BackEnd/auth.php';

if ($_SESSION['rol_id'] != 1) {

    header("Location: ../login/login.php");
    exit();

}

require '../conexion.php';

// Envíos entregados (historial de servicios completados)
$sql = "SELECT e.*, c.titulo AS servicio_titulo, c.tipo_entrega
        FROM envios e
        LEFT JOIN categoria c ON c.id = e.servicio_id
        WHERE e.estado = 'Entregado'
        ORDER BY e.fecha_solicitud DESC";
$resultado = $conn->query($sql);
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

            <a href="Ejecucion_admin.php" class="menu-item">
                <i class="fa-regular fa-clock"></i>
                <span>En ejecución</span>
            </a>

            <a href="Completados_admin.php" class="menu-item active">
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
                <p>Historial de envíos entregados correctamente.</p>
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

<div class="table-card completados-card">

    <div class="table-header services-header">

        <h3>SERVICIOS COMPLETADOS</h3>

        <span class="unassigned-pill"><?php echo $resultado->num_rows; ?> entregado<?php echo $resultado->num_rows == 1 ? '' : 's'; ?></span>

    </div>

    <div class="table-content">

        <?php if ($resultado->num_rows === 0): ?>

            <div class="empty-row">
                <i class="fa-solid fa-box-open"></i><br>
                Todavía no hay envíos marcados como entregados. Aparecerán aquí en cuanto se completen desde "En ejecución".
            </div>

        <?php else: ?>

        <table class="completed-table">

            <thead>

                <tr>
                    <th>GUÍA</th>
                    <th>CLIENTE</th>
                    <th>ORIGEN → DESTINO</th>
                    <th>TIPO</th>
                    <th>COSTO</th>
                    <th>FECHA SOLICITUD</th>
                    <th>ESTADO</th>
                </tr>

            </thead>

            <tbody>

                <?php while ($fila = $resultado->fetch_assoc()): ?>

                <tr>
                    <td>#<?php echo htmlspecialchars($fila['numero_guia'] ?: $fila['id_envio']); ?></td>
                    <td><?php echo htmlspecialchars($fila['nombre_cliente'] ?: 'Sin nombre'); ?></td>
                    <td><?php echo htmlspecialchars(($fila['origen'] ?: '—') . ' → ' . ($fila['destino'] ?: '—')); ?></td>
                    <td><?php echo htmlspecialchars($fila['tipo_entrega'] ?: '—'); ?></td>
                    <td>Q<?php echo number_format((float) $fila['costo'], 2); ?></td>
                    <td><?php echo $fila['fecha_solicitud'] ? date('d/m/Y', strtotime($fila['fecha_solicitud'])) : '—'; ?></td>
                    <td>
                        <span class="table-status completed">
                            COMPLETADO
                        </span>
                    </td>
                </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

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

</body>

</html>