<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require ROOT.'libs/PHPMailer/Exception.php';
require ROOT.'libs/PHPMailer/PHPMailer.php';
require ROOT.'libs/PHPMailer/SMTP.php';

class Mailer{
    protected  $mail; //contiene la configuración de PHPMailer

    public function __construct(){
        $this->mail = new PHPMailer(true);
        $this->mail->IsSMTP();
        $this->mail->Host       = HOST_MAIL;   // Specify main and backup SMTP servers
        $this->mail->CharSet    = 'UTF-8';
        $this->mail->SMTPAuth   = true;        // Enable SMTP authentication
        $this->mail->Username   = USER_MAIL;   // SMTP username
        $this->mail->Password   = PSWD_MAIL;   // SMTP password
        $this->mail->SMTPSecure = SMTPSecure;  // Enable TLS encryption, `ssl` also accepted
        $this->mail->Port       = PORT_MAIL;   // TCP port to connect to
    }

    #Metodo que envia el Email notificando  de acceso al sistema
    public function enviar_mail_acceso($template, $data){
        try{
            $this->mail->setFrom(USER_MAIL, 'Notificaciones TR network');
            $this->mail->addAddress("direccion@trnetwork.com.mx", "Dirección");     // Add a recipient

            // Contenido del correo
            /*ob_start();
            include_once($template);
            $template = ob_get_clean();*/
            $html = "<!DOCTYPE html>
<html lang=\"es\">
<head>
    <meta charset=\"utf-8\">
    <link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css\" integrity=\"sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T\" crossorigin=\"anonymous\">
    <title>Notificacion de acceso</title>
    <style type=\"text/css\">
        .card{
            margin: 20% auto;
        }
        .card-body{
            background-color: #a82b0e;
            color: white;
        }
    </style>
</head>
<body>
<div class=\"container\">
    <div class=\"row justify-content-md-center\">
        <div class=\"col\">
        <div class=\"card w-50\">
            <img src=\"http://trnetwork.com.mx/images/logos/trnLogo1.png\" class=\"card-img-top\" alt=\"TR network\" style=\"padding: 20px;\">
            <div class=\"card-body\">
                <h5 class=\"card-title\">Se ha detectado un nuevo inicio de sesión</h5>
                <h6>Detalles del acceso</h6>
                <p>Ip: <b>".$data['ip']."</b></p>
                <p>Dispositivo: <b>".$data['dispositivo']."</b></p>
                <p>Navegador: <b>".$data['navegador']."</b></p>
                <p>Sistema Operativo: <b>".$data['sistema_operativo']."</b></p>
                <p>Ubicaación: <b>".$data['ubicacion']."</b></p>
            </div>
            <div class=\"card-footer text-center\">
                <small class=\"text-muted\"><b>TR network:</b> Cobranza</small>
            </div>
        </div>
        </div>
    </div>
</div>
</body>
</html>";

            $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->Subject = 'Notificación de acceso, sistema de cobranza';
            $this->mail->Body    = $html;
            //$this->mail->AltBody = 'Sistema de cobranza TR Network';

            if ($this->mail->send()) {
                return true;
            }else return false;
        }catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    public function enviar_password($clabe,$usuario){
        try{
            $this->mail->setFrom('desarrollo@trnetwork.com.mx', 'Desarrollo');
            $this->mail->addAddress($usuario['email_usuario'], $usuario['nombre']);     // Add a recipient

            // Contenido del correo
            /*ob_start();
            include_once($template);
            $template = ob_get_clean();*/
            $html = "<!DOCTYPE html>
<html lang=\"es\">
<head>
	<meta charset=\"utf-8\">
	<link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css\" integrity=\"sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T\" crossorigin=\"anonymous\">
	<title>Notificacion de acceso</title>
	<style type=\"text/css\">
		.card{
			margin: 20% 0;
		}
		.card-body{
			background-color: #FA8072;
		}
	</style>
</head>
<body>
	<div class=\"container\">
		<div class=\"row\">
			<div class=\"col\">
				<div class=\"card text-center\">
				  <div class=\"card-header\">
				    <h4>Contraseña de acceso</h4>
				  </div>
				  <div class=\"card-body\">
				    <h5 class=\"card-title\">¡Buen dia!</h5>
				    <p class=\"card-text\">A continuación le proporcionamos la contraseña de acceso al sistema de cobranza</p>
				    <h5>".$clabe."</h5>
				  </div>
				  <div class=\"card-footer text-muted\">
				    <b>TR network:</b> Cobranza
				  </div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>";

            $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->Subject = 'Pass';
            $this->mail->Body    = $html;
            //$this->mail->AltBody = 'Sistema de cobranza TR Network';

            if ($this->mail->send()) {
                return true;
            }else return false;
        }catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    public function enviar_notificacion_pago($info_pago){
        try{
            $this->mail->setFrom(USER_MAIL, 'Notificaciones TR network');
            $this->mail->addAddress($info_pago['email_usuario'], $info_pago['nombre']);
            $html = "<!DOCTYPE html>
<html lang=\"es\">
<head>
	<meta charset=\"utf-8\">
	<link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css\" integrity=\"sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T\" crossorigin=\"anonymous\">
	<title>Notificacion de acceso</title>
	<style type=\"text/css\">
		.card{
			margin: 20% 0;
		}
		.card-body{
			background-color: #FA8072;
		}
	</style>
</head>
<body>
	<div class=\"container\">
		<div class=\"row\">
			<div class=\"col\">
				<div class=\"card text-center\">
				  <div class=\"card-header\">
				    <h3>Notificación de pago proximo</h3>
				  </div>
				  <div class=\"card-body\">
				    <h5 class=\"card-title\">¡Buen dia!</h5>
				    <p class=\"card-text\">El sistema de cobranza te notifica que tu proximo pago esta cerca</p>
				    <p><b>".$info_pago['fecha_pago']."</b></p>
				    <p>Te invitamos a realizar tu Pago.</p>
				  </div>
				  <div class=\"card-footer text-muted\">
				    <b>TR Network:</b> Cobranza
				  </div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>";
            $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->Subject = 'Notificación de próximo pago';
            $this->mail->Body    = $html;
            $this->mail->AltBody = 'Sistema de cobranza TR Network';
            $this->mail->addCC('dcobranza@trnetwork.com.mx');

            if ($this->mail->send()) {
                return true;
            }else return false;
        }catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    public function enviar_recibo_parcial($info){
        try{
            $this->mail->setFrom(USER_MAIL, 'Notificaciones TR network');
            $this->mail->addAddress($info['email_usuario'], $info['nombre_usuario']);
            $html = "<h1>Recibo de pago Parcial</h1>";
            $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->Subject = 'Recibo de pago parcial';
            $this->mail->Body    = $html;
            //adjuntamos un archivo
            $this->mail->AddAttachment($info['ruta_recibo'],$info['nombre_recibo']);
            $this->mail->AltBody = 'Sistema de cobranza TR Network';
            $this->mail->addCC('dcobranza@trnetwork.com.mx');

            if ($this->mail->send()) {
                return true;
            }else return false;
        }catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    public function enviar_reporte_corrida($info){
        try{
            $this->mail->setFrom(USER_MAIL, 'Notificaciones TR network');
            $this->mail->addAddress($info['email_usuario'], $info['nombre_usuario']);
            $html = '<p>'.$info['nombre_usuario'].'<br>
                     PRESENTE</p>
                     <p>Sirvase este medio para saludarle y así mismo enviarle fechas y montos de las retribuciones 
                     correspondientes, esto es en base a la información proporcionada por su actual trabajo.</p>
                     <p>Se le recuerda que cuando usted haga su primera retribución es preciso envíe por este 
                     medio su comprobante de ingresos más valedero, tal como usted lo acordó vía convenio con la 
                     empresa TR network.</p>
                     <p>Cualquier duda y/o aclaración es exclusivamente con departamento de cobranza dcobranza@trnetwork.com.mx 
                     o directamente con el Lic. Melgarejo a su correo direccion@trnetwork.com.mx</p>
                     <p>En caso de requerir factura ponerse en contacto con la C.P. Elizabeth al correo dcontabilidad@trnetwork.com.mx</p>
                     <p>Estamos a la orden por cualquier duda.</p>
                     <p>Gracias por sus atenciones prestadas, quedamos de usted.</p>';

            $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->Subject = 'Reporte de corrida de pagos';
            $this->mail->Body    = $html;
            //adjuntamos un archivo
            $this->mail->AddAttachment($info['ruta_presentacion'], 'Presentacion.pdf');
            $this->mail->AddAttachment($info['ruta_reporte'], 'Reporte Corrida de pagos.pdf');
            $this->mail->AltBody = 'Sistema de cobranza TR Network';
            $this->mail->addCC('dcobranza@trnetwork.com.mx');

            if ($this->mail->send()) {
                return true;
            }else return false;
        }catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}

?>