<?php
require("../conexion.php");
if (!$conn) {
    header("Location: Inicio_sesion.php?error=Error de conexión");
    exit();
}

require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("
        SELECT id, nombre, email
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");

    if (!$stmt) {
        header("Location: Inicio_sesion.php?error=Error interno, intente más tarde");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $user = $resultado->fetch_assoc();
        $token = random_int(100000, 999999);
        $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmtToken = $conn->prepare("
            INSERT INTO password_reset_tokens
            (token, user_id, expires_at)
            VALUES (?, ?, ?)
        ");

        if (!$stmtToken) {
            header("Location: Inicio_sesion.php?error=Error interno, intente más tarde");
            exit();
        }

        $stmtToken->bind_param("sis", $token, $user['id'], $expira);
        $stmtToken->execute();

        if ($stmtToken->affected_rows <= 0) {
            header("Location: Inicio_sesion.php?error=No se pudo generar el código, intente de nuevo");
            exit();
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'traersa@criangonzalez.com';
            $mail->Password = 'Traersa159@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->setFrom('traersa@criangonzalez.com', 'Traersa');
            $mail->addAddress($user['email']);
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de contraseña';
            $mail->Body = "
                <h2>Recuperación de acceso</h2>
                <p>Hola {$user['nombre']}</p>
                <p>Tu código temporal es:</p>
                <h1>$token</h1>
                <p>Este código expirará en 1 hora.</p>
            ";

            $mail->send();

            header("Location: Inicio_sesion.php?exito=Correo enviado con éxito");
            exit();

        } catch (Exception $e) {
            header("Location: Inicio_sesion.php?error=No se pudo enviar el correo, intente más tarde");
            exit();
        }

    } else {
        header("Location: Inicio_sesion.php?error=No existe una cuenta con ese correo");
        exit();
    }
}
?>