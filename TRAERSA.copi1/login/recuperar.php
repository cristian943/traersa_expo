<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../conexion.php");

if (!$conn) {
    die("Error de conexión");
}

echo "Conexión OK<br>";
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
echo "Entró al POST<br>";
    $email = trim($_POST['email']);

    // Buscar usuario
    $stmt = $conn->prepare("
        SELECT id, nombre, email
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");
if(!$stmt){
    die("Error SQL: " . $conn->error);
}
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();
echo "Usuarios encontrados: " . $resultado->num_rows . "<br>";
    if ($resultado->num_rows > 0) {

        $user = $resultado->fetch_assoc();

       $token = random_int(100000, 999999);
        // Expira en 1 hora
        $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Guardar token
        $stmtToken = $conn->prepare("
            INSERT INTO password_reset_tokens
            (token, user_id, expires_at)
            VALUES (?, ?, ?)
        ");
if(!$stmtToken){
    die("Error SQL INSERT: " . $conn->error);
}
        $stmtToken->bind_param(
            "sis",
            $token,
            $user['id'],
            $expira
        );

        $stmtToken->execute();

     if($stmtToken->affected_rows > 0){

    echo "Token guardado correctamente<br>";

}else{

    echo "Error guardando token: "
         . $stmtToken->error;

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

            $mail->setFrom(
                'traersa@criangonzalez.com',
                'Sistema Logistico'
            );

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
$mail->SMTPDebug = 2;
$mail->Debugoutput = 'html';
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