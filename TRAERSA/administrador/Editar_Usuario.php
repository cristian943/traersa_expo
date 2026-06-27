<?php
require '../BackEnd/auth.php';

// valida que el usuario sea administrador
if ($_SESSION['rol_id'] != 1) {

    header("Location: ../login/login.php");
    exit();

}
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

    <!-- cuerpo principal de la pagina -->
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

            <a href="Cotizaciones_admin.php" class="menu-item ">
                <i class="fa-solid fa-tags"></i>
                <span>Cotizaciones</span>
            </a>

            <a href="Ejecucion_admin.php" class="menu-item">
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

            <a href="Editar_Usuario.php" class="menu-item active">
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
    <!-- contenido principal del panel administrativo -->
    <main class="main-content fade-up">

        <header class="header">

            <div class="header-title">
                <h2>Bienvenido Administrador</h2>

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

  <?php
require '../conexion.php';

// carga los usuarios y sus roles para mostrar en la tabla de administracion
$sql = "
SELECT
u.id,
u.nombre,
u.email,
u.estado,
u.rol_id,
u.tipo_empleado,
r.nombre AS rol
FROM usuarios u
INNER JOIN roles r
ON u.rol_id = r.id
ORDER BY u.id
";

$resultado = $conn->query($sql);
?>


<!-- empieza la seccion de usuarios -->

<!-- tabla de gestion de usuarios -->

<div class="table-card completados-card">


<div class="table-header services-header">

    <h3>GESTIÓN DE USUARIOS</h3>

</div>

<!-- lista de usuarios con opciones de rol y estado -->
<div class="table-content">

    <table class="completed-table">

        <thead>

            <!-- encabezados de la tabla de usuarios -->
            <tr>
                <th>ID</th>
                <th>NOMBRE</th>
                <th>EMAIL</th>
                <th>ROL</th>
                <th>TIPO</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
            </tr>

        </thead>

        <tbody>

        <?php while($usuario = $resultado->fetch_assoc()): ?>

            <!-- fila de usuario -->
            <tr>

                <!-- id del usuario -->
                <td><?= $usuario['id'] ?></td>

                <td><?= htmlspecialchars($usuario['nombre']) ?></td>

                <td><?= htmlspecialchars($usuario['email']) ?></td>

                <td>

                    <!-- seleccion de rol del usuario -->
                    <select
                        onchange="actualizarRol(this, <?= $usuario['id'] ?>)"
                        class="role-select">

                        <option value="1"
                            <?= $usuario['rol_id']==1 ? 'selected' : '' ?>>
                            Administrador
                        </option>

                        <option value="2"
                            <?= $usuario['rol_id']==2 ? 'selected' : '' ?>>
                            Cliente
                        </option>

                        <option value="3"
                            <?= $usuario['rol_id']==3 ? 'selected' : '' ?>>
                            Empleado
                        </option>

                    </select>

                </td>

                <td>

                    <!-- tipo de empleado solo disponible si el usuario es empleado -->
                    <select
                        onchange="actualizarTipoEmpleado(this, <?= $usuario['id'] ?>)"
                        class="role-select tipo-empleado-select"
                        <?= $usuario['rol_id']==3 ? '' : 'disabled' ?>>

                        <option value=""
                            <?= empty($usuario['tipo_empleado']) ? 'selected' : '' ?>>
                            -- Sin asignar --
                        </option>

                        <option value="oficinista"
                            <?= $usuario['tipo_empleado']=='oficinista' ? 'selected' : '' ?>>
                            Oficinista
                        </option>

                        <option value="transportista"
                            <?= $usuario['tipo_empleado']=='transportista' ? 'selected' : '' ?>>
                            Transportista
                        </option>

                    </select>

                </td>

                <td>

                    <!-- estado activo o inactivo del usuario -->
                    <?= $usuario['estado']
                        ? 'Activo'
                        : 'Inactivo'
                    ?>

                </td>

                <td>

                    <!-- boton para eliminar usuario -->
                    <button
                        onclick="eliminarUsuario(<?= $usuario['id'] ?>)"
                        class="btn-delete">

                        <i class="fa-solid fa-trash"></i>

                    </button>

                </td>

            </tr>

        <?php endwhile; ?>

        </tbody>

    </table>

</div>


</div>

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

        // scripts de interaccion de la pagina
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

        // resalta el item activo del menu
        const menuItems = document.querySelectorAll(".menu-item");

        menuItems.forEach(item => {

            item.addEventListener("click", () => {

                menuItems.forEach(el => {
                    el.classList.remove("active");
                });

                item.classList.add("active");

            });

        });

        // actualiza el rol del usuario cuando cambia el select
function actualizarRol(select,idUsuario){

    let nuevoRol = select.value;

    const filaTipo = select.closest("tr").querySelector(".tipo-empleado-select");
    if(filaTipo){
        filaTipo.disabled = (nuevoRol !== "3");
        if(nuevoRol !== "3") filaTipo.value = "";
    }

    // envia la actualizacion del rol al servidor
    fetch('actualizarRol.php',{

        method:'POST',

    headers:{
        'Content-Type':
        'application/x-www-form-urlencoded'
    },

    body:
    'id=' + idUsuario +
    '&rol=' + nuevoRol

})

.then(res => res.text())

.then(data => {

    alert("Rol actualizado correctamente");

})

.catch(error => {

    alert("Error al actualizar");

});


}

// actualiza el tipo de empleado para usuarios con rol empleado
function actualizarTipoEmpleado(select, idUsuario){

    const nuevoTipo = select.value;

    // envia la actualizacion del tipo de empleado
    fetch('actualizarTipoEmpleado.php', {

        method: 'POST',

        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },

        body: 'id=' + idUsuario + '&tipo=' + encodeURIComponent(nuevoTipo)

    })
    .then(res => res.text())
    .then(() => {
        alert("Tipo de empleado actualizado correctamente");
    })
    .catch(() => {
        alert("Error al actualizar el tipo de empleado");
    });

}

// elimina usuario luego de confirmar la accion
function eliminarUsuario(id){

    if(!confirm(
        "¿Desea eliminar este usuario?"
    )){
        return;
    }

    // solicita eliminacion al servidor
    fetch('eliminarUsuario.php',{

        method:'POST',

    headers:{
        'Content-Type':
        'application/x-www-form-urlencoded'
    },

    body:'id=' + id

})

.then(res => res.text())

.then(data => {

    alert("Usuario eliminado");

    location.reload();

})

.catch(error => {

    alert("Error al eliminar");

});


}

    </script>

</body>

</html>