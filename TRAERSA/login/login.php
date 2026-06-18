<?php
session_start();

/* MOSTRAR ERRORES */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
$token = trim($_POST['token'] ?? '');
   if ($email === '') {

    header("Location: Inicio_sesion.php?error=Ingrese su correo");
    exit();

}

if ($password === '' && $token === '') {

    header("Location: Inicio_sesion.php?error=Ingrese contraseña o token");
    exit();

}

    $stmt = $conn->prepare("SELECT id, email, password, rol_id, estado FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $usuario = $resultado->fetch_assoc();

        // Verificar estado
        if ((int)$usuario['estado'] !== 1) {

            $stmt->close();
            $conn->close();

            header("Location: Inicio_sesion.php?error=Usuario inactivo");
            exit();
        }

       
if (password_verify($password, $usuario['password'])) {

            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol_id'] = $usuario['rol_id'];
            $_SESSION['email'] = $usuario['email'];

            $stmt->close();
            $conn->close();

            // Redirección por rol
            switch ((int)$usuario['rol_id']) {

                case 1:
                    header("Location: ../administrador/admin.php");
                    break;

                case 2:
                    header("Location: ../cliente/cliente.html");
                    break;

                case 3:
                    header("Location: ../empleado/empleado.html");
                    break;

                default:
                    header("Location: Inicio_sesion.php?error=Rol no reconocido");
                    break;
            }

            exit();

        } else {

            $stmt->close();
            $conn->close();

            header("Location: Inicio_sesion.php?error=Contraseña incorrecta");
            exit();
        }

    } else {

        $stmt->close();
        $conn->close();

        header("Location: Inicio_sesion.php?error=Usuario no encontrado");
        exit();
    }
}

$error = $_GET['error'] ?? '';
?>