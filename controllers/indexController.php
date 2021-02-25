<?php


class indexController extends controller
{
    private $_index;

    //todos los que eredan deben de llevar el metodo index
    public function __construct()
    {
        parent::__construct();//llame al constructor de la clase padre
        $this->_index = $this->loadModel('frontend');

    }

    public function index(){
        session_start();
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            header('Location: /cobranza/admin/panel');
        }else {
            $this->_view->setCss(['index']);
            $this->_view->setJs(['index']);
            $this->_view->rendering('index');
        }
    }

    public function login(){
        if ($this->is_ajax()) {
            $notificacionError = [
                "tipo_mensaje" => "danger",
                "mensaje" => ""
            ];
            if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
                $this->getLibrary("Captcha/autoload");
                $this->getLibrary("Util/util");
                $this->getLibrary("push/push");
                $this->getLibrary("PHPMailer/Mailer");

                $util = new Util();
                $recaptcha = new \ReCaptcha\ReCaptcha(KEY_CAPTCHA);
                $respuesta = $recaptcha->verify($_POST['g-recaptcha-response'], $util->obtener_ip());
                if ($respuesta->isSuccess()) {
                    $mensaje = "";
                    $notificacionExitosa = [
                        "tipo_mensaje" => "success",
                        "mensaje" => $mensaje
                    ];
                    $notificacionAlerta = [
                        "tipo_mensaje" => "warning",
                        "mensaje" => $mensaje
                    ];
                    $correo = isset($_POST['correo']) ? $_POST['correo'] : '';
                    $contrasena = isset($_POST['contrasena']) ? $util->encrypt($_POST['contrasena']) : '';
                    //$contrasena = $util->decrypt($_POST['contrasena']);
                    //echo $contrasena;
                    //print_r(json_encode($contrasena));
                    //exit();
                    #Si los campos no estan vacios
                    if ($util->validar_campos($correo) && $util->validar_campos($contrasena)) {
                        $respuestaUsuario = $this->_index->verificarUsuario(['correo'=>$correo, 'contrasena'=>$contrasena]);
                        #Se verifica que el usuario existe en la base de datos
                        if (!is_null($respuestaUsuario) && $respuestaUsuario['id_rol_usuario']) {

                            #Se genera la clave de acceso
                            $clabe = $util->generar_numero_aleatorio();

                            #Se registra la clave de acceso en la BD
                            $respuestaClave = $this->_index->actualizarClaveAcceso(["correo" => $correo, "clave" => $clabe]);

                            #Se obtiene la imformación del equipo donde se esta accediendo
                            $info = $util->informacion_computadora();
                            date_default_timezone_set('America/Mexico_City');
                            setlocale(LC_TIME, 'es_MX.UTF-8');
                            $data = [
                                        'fecha_hora' => date('Y-m-d H:i:s'),
                                        'ip' => $info['ip'],
                                        'navegador' => $info['navegador'],
                                        'sistema_operativo' => $info['sistema_operativo'],
                                        'ubicacion' => $info['ubicacion'],
                                        'id_usuario' => $respuestaUsuario['id_usuario']
                                    ];
                            #Se registra en la BD el Acceso
                            $resultadoRegistro = $this->_index->registrar_acceso($data);
                            if($resultadoRegistro != 1){
                                $error_registro = "Ocurrio un error al registrar el acceso";
                                #Aqui va la función para rregistrar el error en un log
                            }

                            #Si la clave de  acceso se registró la enviamos por mensaje
                            if ($respuestaClave) {
                                $mail = new Mailer();
                                #Enviamos la clave de acceso al celular
                                $push = new Push();
                                #$response = $push->enviar_notificacacion(['telefono' => $respuestaUsuario['telefono_antiguo'], 'password' => $clabe]);
                                $respuestaMailClabe = $mail->enviar_password($clabe,$respuestaUsuario);
                                if (/*$response[0] == 'text-info' ||*/ $respuestaMailClabe){
                                    $mensaje = "Se envio correctamente la clave al celular";
                                    $infoAcceso = $util->informacion_computadora();
                                    $template = BASE_URL."libs/PHPMailer/templates/notificacionAcceso.php";
                                    #$respuestaMail = $mail->enviar_mail_acceso($template,$infoAcceso);
                                    //$respuestaMailClabe = $mail->enviar_password($clabe,$respuestaUsuario);
                                    if (true) {
                                        ini_set("session.cookie_httponly", True);
                                        session_start();
                                        $_SESSION['user'] = $respuestaUsuario['nombre'];
                                        $_SESSION['email'] = $respuestaUsuario['email_usuario'];
                                        $_SESSION['telefono'] = $respuestaUsuario['telefono_antiguo'];
                                        $_SESSION['rol'] = $respuestaUsuario['id_rol_usuario'];
                                        $_SESSION['id_usuario'] = $respuestaUsuario['id_usuario'];
                                        $_SESSION['codigo'] = $respuestaUsuario['codigo_acceso'];
                                        //$_SESSION['tiempo'] = time();
                                        $_SESSION['sistema'] = $info['sistema_operativo'];
                                        $notificacionExitosa["mensaje"] = "Clave de acceso enviada correctamente";
                                        print_r(json_encode($notificacionExitosa));
                                    }else{
                                        $notificacionAlerta["mensaje"] = "Eror al enviar la clave de acceso";
                                        print_r(json_encode($notificacionAlerta));
                                    }
                                }else {
                                    $notificacionError["mensaje"] = "No se pudo enviar la contraseña";
                                    print_r(json_encode($notificacionError));
                                }

                            }else{
                                $notificacionError["mensaje"] = "Ocurrio un error al generar la clave de acceso";
                                print_r(json_encode($notificacionError));
                            }
                        }else {
                            $notificacionAlerta["mensaje"] = "Usuario o contraseña no encontrados";
                            print_r(json_encode($notificacionAlerta));
                        }
                    }else{
                        $notificacionError["mensaje"] = "El contenido de los campos no es correcto";
                        print_r(json_encode($notificacionError));
                    }
                }else{
                    $notificacionError["mensaje"] = "Es necesario verificar el captcha";
                    print_r(json_encode($notificacionError));
                }
            }else{
                $notificacionError["mensaje"] = "Error al obtener los datos del captcha";
                print_r(json_encode($notificacionError));
            }
        }
    }

    public function access(){
        //print_r($data);
        session_start();
        if (isset($_SESSION['user'])) {
            $this->_view->setCss(['index']);
            $this->_view->setJs(['access']);
            $this->_view->rendering('access');
        }else header('Location: /cobranza/index');
        
    }

    public function validateAccess(){
        if ($this->is_ajax()) {
            $respuestaClave = $this->_index->verificarClave($_POST['clave']);
            if (!is_null($respuestaClave)) {
                session_start();
                $_SESSION['validate'] = true;
                $mensaje = "Validación de acceso correcta";
                $notificacionExitosa = [
                    "tipo_mensaje" => "success", 
                    "mensaje" => $mensaje
                ];
                print_r(json_encode($notificacionExitosa));
            }else{
                $mensaje = "Clave de acceso incorrecta";
                $notificacionAlerta = [
                    "tipo_mensaje" => "warning", 
                    "mensaje" => $mensaje
                ];
                print_r(json_encode($notificacionAlerta));
            }
        }    
    }

    public function Error404(){
        session_start();
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            header('Location: /cobranza/admin');
        }else {
            $this->_view->setCss(['error']);
            $this->_view->setJs(['error']);
            $this->_view->rendering('Error404');
        }
    }

    public function aviso(){

            $this->_view->setCss(['aviso']);
            $this->_view->setJs(['error']);
            $this->_view->rendering('aviso');

    }
    /*public function logout(){
        session_start();
        session_destroy();
        header("location:".BASE_URL);
    }*/
}