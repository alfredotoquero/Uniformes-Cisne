<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Correos{

    private $con;

    function __construct(){
        include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
        $this->con = $con;
    }

    public function enviarCorreo($post){
        try{
            $idtienda = $post["idtienda"];
            $asunto = $post["asunto"];
            $mensaje = $post["mensaje"];
            $correos = $post["correos"];
            $adjuntos = $post["adjuntos"];

            //Datos del emisor para los correos
            $query = "
            select
                smtp_correo,
                aes_decrypt(smtp_password,'smtp') as smtp_password,
                smtp_nombre
            from
                ttiendas
            where
                idtienda = '".$idtienda."'";
            $tienda = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            require_once $_SERVER["DOCUMENT_ROOT"].'/assets/plugins/vendor/autoload.php';

            $mail = new PHPMailer(true);

            $mail->isSMTP();

            $mail->Host = "smtp.dreamhost.com";
            $mail->SMTPAuth = true;
            $mail->Username = $tienda["smtp_correo"];
            $mail->Password = $tienda["smtp_password"];

            $mail->From = $tienda["smtp_correo"];
            $mail->FromName = $tienda["smtp_nombre"];

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port = 587;
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->isHTML(true);

            foreach($adjuntos as $adjunto){
                $mail->AddStringAttachment(base64_decode($adjunto["archivo"]), $adjunto["nombre"]);
            }

            $mail->CharSet = "UTF-8";

            foreach ($correos as $correo) {
                $mail->AddAddress($correo);
            }

            if ($mail->Send()) {
                $respuesta = array(
                    "result" => "success",
                    "mensaje" => "El correo se ha enviado correctamente."
                );
            } else {
                throw new Exception("No se pudo enviar el correo. Error: " . $mail->ErrorInfo);
            }
        }catch(Exception $e) {
            $respuesta = array(
                "result" => "error",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }
}