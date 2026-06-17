<?php

require("conexion.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);

    // Buscar usuario
    $stmt = $conexion->prepare("
        SELECT id, nombre, email
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $user = $resultado->fetch_assoc();

        // Generar token
        $token = bin2hex(random_bytes(32));

        // Expira en 1 hora
        $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Guardar token
        $stmtToken = $conexion->prepare("
            INSERT INTO password_reset_tokens
            (token, user_id, expires_at)
            VALUES (?, ?, ?)
        ");

        $stmtToken->bind_param(
            "sis",
            $token,
            $user['id'],
            $expira
        );

        $stmtToken->execute();

        // URL de recuperación
        $url = "https://tudominio.com/reset_password.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();

            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            $mail->Username = 'traersa@criangonzalez.com';
            $mail->Password = 'Traersa159@';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom(
                'traersa@criangonzalez.com',
                'Sistema Logistico'
            );

            $mail->addAddress($user['email']);

            $mail->isHTML(true);

            $mail->Subject = 'Recuperacion de contrasena';

            $mail->Body = "
                <h2>Recuperacion de contrasena</h2>

                <p>Hola {$user['nombre']}</p>

                <p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>

                <p>
                    <a href='$url'>
                        Restablecer contraseña
                    </a>
                </p>

                <p>Este enlace expirará en 1 hora.</p>
            ";

            $mail->send();

            echo "Correo enviado correctamente.";

        } catch (Exception $e) {

            echo "Error al enviar correo: "
                . $mail->ErrorInfo;
        }

    } else {

        echo "No existe una cuenta con ese correo.";
    }
}