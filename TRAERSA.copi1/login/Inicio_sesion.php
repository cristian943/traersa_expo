<?php if(isset($_GET['error'])): ?>
<div class="alert alert-danger text-center">
    <?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión - TRAERSA</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ICONOS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- FUENTES -->
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- VIDEO -->
    <div class="video-background">
        <video autoplay muted loop playsinline id="bg-video">
            <source src="Video/AdminiStrador.mp4" type="video/mp4">
        </video>

        <div class="overlay"></div>
    </div>

    <!-- DECORACIÓN -->
    <div class="decoration-line line-left"></div>
    <div class="decoration-line line-right"></div>

    <!-- VOLVER -->
    <a href="../inicio/index.html" class="btn-volver">VOLVER</a>

   
    <main class="login-card">

        <h2>INICIO DE SESIÓN</h2>

     <form action="login.php" method="POST">

    <div class="input-group">

        <label>Correo Electrónico</label>

        <div class="input-field">

            <i class="fa-solid fa-circle-user"></i>

            <input
                type="email"
                name="email"
                placeholder="Correo Electrónico"
                required>

        </div>

    </div>

    <div class="input-group">

        <label>Contraseña</label>

        <div class="input-field">

            <i class="fa-solid fa-lock"></i>

            <input
                type="password"
                name="password"
                placeholder="Contraseña">

            <i class="fa-solid fa-eye eye-toggle"></i>

        </div>

    </div>

    <div class="input-group">

        <label>Código Temporal</label>

        <div class="input-field">

            <i class="fa-solid fa-key"></i>

            <input
                type="text"
                name="token"
                placeholder="Código recibido por correo">

        </div>

    </div>

    <p class="token-info">

        Ingrese su contraseña o un código temporal enviado por correo.

    </p>

    <?php if(isset($_GET['error'])): ?>

        <div class="alert alert-danger text-center">

            <?= htmlspecialchars($_GET['error']) ?>

        </div>

    <?php endif; ?>

    <button
        type="submit"
        class="btn-submit">

        INICIAR SESIÓN

    </button>

    <p class="footer-form">

        ¿No tienes cuenta?

        <a href="registro.php">
            Regístrate.
        </a>

    </p>

    <p class="footer-form">

        ¿Olvidaste tu contraseña?

        <a href="recuperar_contra.html">
            Recupérala.
        </a>

    </p>

</form>

    </main>

  

    <script>
        document.querySelectorAll('.eye-toggle').forEach(eye => {

            eye.addEventListener('click', function() {

                const input = this.previousElementSibling;

                if (input.type === "password") {

                    input.type = "text";

                    this.classList.replace(
                        'fa-eye',
                        'fa-eye-slash'
                    );

                } else {

                    input.type = "password";

                    this.classList.replace(
                        'fa-eye-slash',
                        'fa-eye'
                    );
                }
            });

        });
    </script>

    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>