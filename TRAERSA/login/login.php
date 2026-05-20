<?php
session_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log');

require 'conexion1.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        header("Location: login.html?error=Completa todos los campos");
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

            header("Location: login.html?error=Usuario inactivo");
            exit();
        }

        // Verificar contraseña
        if ($password === $usuario['password']) {

            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol_id'] = $usuario['rol_id'];
            $_SESSION['email'] = $usuario['email'];

            $stmt->close();
            $conn->close();

            // Redirección por rol
            switch ((int)$usuario['rol_id']) {

                case 1:
                    header("Location: administrador/admin.php");
                    break;

                case 2:
                    header("Location: cliente/cliente.php");
                    break;

                case 3:
                    header("Location: empleado/empleado.php");
                    break;

                default:
                    header("Location: login.html?error=Rol no reconocido");
                    break;
            }

            exit();

        } else {

            $stmt->close();
            $conn->close();

            header("Location: login.html?error=Contraseña incorrecta");
            exit();
        }

    } else {

        $stmt->close();
        $conn->close();

        header("Location: login.html?error=Usuario no encontrado");
        exit();
    }
}

$error = $_GET['error'] ?? '';
?>