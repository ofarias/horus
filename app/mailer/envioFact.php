<?php
    //require_once('app/mailer/class.phpmailer.php'); // Contiene las funciones para envio de correo
    //require_once('app/mailer/class.smtp.php'); // Envia correos mediante servidores SMTP
    require_once('./app/PHPMailer/PHPMailerAutoload.php');
    require_once('./app/PHPMailer/class.smtp.php');
    //$mail = new PHPMailer(); // Se crea una instancia de la clase phpmailer
    //$mail->IsSMTP(true); // Establece el tipo de mensaje html
    $correo = $_SESSION['correo'];
    $docf = $_SESSION['docf'];
    $titulo = $_SESSION['titulo'];
    $mensaje = $_SESSION['mensaje'];
    $email=$_SESSION['user']->USER_EMAIL;
    if(strpos($correo, "@")>1){

    } else {
        echo "No se ha localizado el correo electr&oacute;nico a quien enviar. No se va a enviar el correo.";
        return;
    }
    $asunto = "Envio de Factura Electronica.";  
    $mensaje.= "<p>Gracias por su compra.<br />Atentamente ".$_SESSION['empresa']['nombre']."</p> <br/> <br/>Si usted no es el interesado de este correo le pedimos de favor que lo borre inmediatamente.
     <font color='red'>".$email."</font>";
     try {
        $mail = new PHPMailer();
        $mail->isSMTP(true); // telling the class to use SMTP
        $mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );
        //$mail->SMTPDebug = 2;
        $mail->SMTPSecure="tls";
        $mail->isSMTP();
        $mail->Host = "smtp.mail.yahoo.com";
        $mail->SMTPAuth = true;
        $mail->Port = 587;
        $mail->Username   = "libreriamedicahorus@yahoo.com";  // Nombre del usuario SMTP
        $mail->Password   = "ppvdhyvvalaqxxpk";

        
            if(strpos($correo,",")>0){
                $co=explode(",", $correo);
                for($i=0; $i<count($co);$i++){
                    $mail->AddAddress($co[$i]);//Direccion a la que se envia
                }
            }else{
                    $mail->AddAddress($correo);//Direccion a la que se envia
            }
        $mail->SetFrom('libreriamedicahorus@yahoo.com' , "Facturas Horus"); // Esccribe datos de contacto
        $mail->Subject = 'Factura '.$docf;
        $mail->AltBody = 'Para ver correctamente este mensaje, por favor usa un manejador de correo con compatibilidad HTML !'; // optional - MsgHTML will create an alternate automatically
        $mail->MsgHTML($mensaje); 
        $mail->AddAttachment(realpath('C:\\xampp\\htdocs\\Facturas\\facturaPegaso\\'.$docf.'.pdf'),$docf.'.pdf','base64','application/pdf');
        $mail->AddAttachment(realpath('C:\\xampp\\htdocs\\Facturas\\facturaPegaso\\'.$docf.'.xml'),$docf.'.xml');
        $mail->Send();
        //die(var_dump($mail));
        return;
     } catch (phpmailerException $e) {
        echo $e->errorMessage(); //Pretty error messages from PHPMailer
     } catch (Exception $e) {
        echo $e->getMessage(); //Boring error messages from anything else!
     }
 ?>