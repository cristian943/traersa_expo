<?php
session_start();
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

        $loginValido = false;

        /* LOGIN CON CONTRASEÑA */
        if (!empty($password)) {
            if (password_verify($password, $usuario['password'])) {
                $loginValido = true;
            }
        }
        /* LOGIN CON TOKEN */
        elseif (!empty($token)) {
            $stmtToken = $conn->prepare("
                SELECT id
                FROM password_reset_tokens
                WHERE user_id = ?
                AND token = ?
                AND used = 0
                AND expires_at > NOW()
                LIMIT 1
            ");
            $stmtToken->bind_param("is", $usuario['id'], $token);
            $stmtToken->execute();
            $resultadoToken = $stmtToken->get_result();

            if ($resultadoToken->num_rows > 0) {
                $loginValido = true;
                $update = $conn->prepare("
                    UPDATE password_reset_tokens
                    SET used = 1
                    WHERE user_id = ?
                    AND token = ?
                ");
                $update->bind_param("is", $usuario['id'], $token);
                $update->execute();
                $update->close();
            }
            $stmtToken->close();
        }

        if (!$loginValido) {
            $stmt->close();
            $conn->close();
            header("Location: Inicio_sesion.php?error=Contraseña o token incorrecto");
            exit();
        }

        // Login exitoso: guardar sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['email']      = $usuario['email'];
        $_SESSION['rol_id']     = $usuario['rol_id'];

        $stmt->close();
        $conn->close();

        switch ((int)$usuario['rol_id']) {
            case 1:
                header("Location: ../administrador/admin.php");
                break;
            case 2:
                header("Location: ../cliente/cliente.php");
                break;
            case 3:
                header("Location: ../empleado/empleado.php");
                break;
            default:
                header("Location: Inicio_sesion.php?error=Rol no reconocido");
        }
        exit();

    } else {
        $stmt->close();
        $conn->close();
        header("Location: Inicio_sesion.php?error=Usuario no encontrado");
        exit();
    }
}

$error = $_GET['error'] ?? '';
?>