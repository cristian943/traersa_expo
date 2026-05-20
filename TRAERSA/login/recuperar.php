<?php


require ("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];



    if ($user) {
        
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

     
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'traersa@criangonzalez.com';
            $mail->Password   = 'Traersa159@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 465;

            $mail->setFrom('traersa@criangonzalez.com', 'TRAERSA');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperar tu clave';
            $url = "https://tuweb.com/reset_password.php?token=$token";
            $mail->Body    = "Haz clic en este enlace para cambiar tu clave: <a href='$url'>$url</a>";

            $mail->send();
            echo 'El mensaje ha sido enviado';
        } catch (Exception $e) {
            echo "Error al enviar: {$mail->ErrorInfo}";
        }
    }
}
?>