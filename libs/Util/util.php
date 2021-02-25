<?php

    class Util {
        
        # Metodo que redireciona
        public static function redirect($url) {
            header('Location: ' . $url);
            exit();
        }
        
        # Metodo que valida los campos
        public static function validar_campos($data) {
            return ($data != null && strlen($data) > 0 && $data != '') ? true : false;
        }
        
        # Metodo que genera un numero aleatorio
        public static function generar_numero_aleatorio($longitud_numero_aleatorio = 8) {
            
            $cadena = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'; # Se define una cadena de caractares.
            $longitud_cadena = strlen($cadena); # Obtenemos la longitud de la cadena de caracteres
            $numero_aleatorio = ''; # Se define la variable que va a contener la contraseña
            
            # Creamos la contrasenia
			for($i=1 ; $i <= $longitud_numero_aleatorio ; $i++){
				
                # Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres-1
				$pos = rand(0, $longitud_cadena - 1);
			 
				# Vamos formando la contrasenia en cada iteraccion del bucle
				$numero_aleatorio .= substr($cadena, $pos, 1);
			}
            
            return $numero_aleatorio;
        }
        
        # Metodo que valida el tipo de archivo
        public static function validar_archivo($file) {
            
            # Se obtiene la extension
            $extension = strtoupper(end(explode('.', $file['name'])));
            $allowed = array('JPG', 'PNG', 'JPEG');
            
            # Se verifica si la extension del archivo es valida
            if(in_array($extension, $allowed)) {
                
                if($file["error"] > 0) { # Si el archivo contiene un error
                    return 'El archivo contiene errores.';
                }
                
                return '';
            }
            
            return 'Solo puede subir imagenes .jpg, .png y .jpeg';
        }
        
        # Metodo que sube un archivo al servidor
        public static function subir_archivo($file, $nombre_carpeta) {
            
            $path = DIR_ARCHIVOS . '/' . $nombre_carpeta;
            
            # Si no existe la carpeta se crea
            if(!file_exists($path)) {
                
                # Se verifica si la carpeta no fue creada
                if(!mkdir($path, 0777)) {
                    return false;
                }
            }
            
            $nombre_archivo = date('d_F_Y_H_i_s') . '.' . end(explode('.', $file['name']));
            $path .= '/' . $nombre_archivo;
            
            # Se mueve el archivo al path correspondiente
            if(!move_uploaded_file($file['tmp_name'], $path)) {
                return false;
            }
            
            return $nombre_archivo;
        }
        
        # Metodo para encriptar los datos
        public static function encrypt($string) {
            $output = openssl_encrypt($string, METHOD, KEY, 0, IV);
            return base64_encode($output);
        }
        
        # Metodo para desencriptar los datos
        public static function decrypt($string) {
            $string = base64_decode($string);
            return openssl_decrypt($string, METHOD, KEY, 0, IV);
        }
        
        # Metodo que calcula el numero de paginas
        public static function no_paginas($pagina, $num_total_registros) {
            
            $no_elementos = 10;
            
            if(strlen($pagina) == 0) {
                $inicio = 0;
                $pagina = 1;
            } else {
                $inicio = ($pagina - 1) * $no_elementos;
            }
            
            $total_paginas = ceil($num_total_registros / $no_elementos);
            
            return array($inicio, $no_elementos, $total_paginas);
        }
        
        # Metodo que obtiene los datos de la computadora
        public static function informacion_computadora() {
            
            $ip = Util::obtener_ip();
            $ip = ($ip == '::1') ? 'localhost' : $ip;
            $dispositivo = Util::obtener_dispositivo();
            $navegador = Util::obtener_navegador();
            $sistema_operativo = Util::obtener_sistema_operativo();
            
            $response = Util::localizacion_ip();
            $ubicacion = (is_array($response)) ? $response['cityName'] : $response;
            $ubicacion = ($ubicacion == '-')? 'unknown' : $ubicacion;
            
            return array('ip' => $ip, 'dispositivo' => $dispositivo, 'navegador' => $navegador, 'sistema_operativo' => $sistema_operativo, 'ubicacion' => $ubicacion);
        }
        
        # Metodo que obtiene la ip de una computadora
        public static function obtener_ip() {
            
            if (!empty($_SERVER['HTTP_CLIENT_IP']))
                return $_SERVER['HTTP_CLIENT_IP'];

            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];

            return $_SERVER['REMOTE_ADDR'];
        }
        
        # Metodo que obtiene el tipo de dispositivo
        public static function obtener_dispositivo() {
            
            $tablet_browser = 0;
            $mobile_browser = 0;
            $body_class = 'desktop';

            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                $tablet_browser++;
                $body_class = "tablet";
            }

            if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                $mobile_browser++;
                $body_class = "mobile";
            }

            if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
                $mobile_browser++;
                $body_class = "mobile";
            }

            $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
            $mobile_agents = array(
                'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
                'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
                'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
                'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
                'newt','noki','palm','pana','pant','phil','play','port','prox',
                'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
                'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
                'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
                'wapr','webc','winw','winw','xda ','xda-');

            if (in_array($mobile_ua,$mobile_agents)) {
                $mobile_browser++;
            }

            if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'opera mini') > 0) {
                $mobile_browser++;
                //Check for tablets on opera mini alternative headers
                $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
                if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
                  $tablet_browser++;
                }
            }
            
            if ($tablet_browser > 0) {
               return 'Tablet';
            } else if ($mobile_browser > 0) {
               return 'Móvil';
            } else {
               return 'Computadora de escritorio';
            } 
        }
        
        # Metodo que obtiene el tipo de navegador
        public static function obtener_navegador() {

            $agente = $_SERVER['HTTP_USER_AGENT'];
            $navegador = 'Unknown';

            if(preg_match('/MSIE/i',$agente) && !preg_match('/Opera/i', $agente)) {
                $navegador = 'Internet Explorer';
            } else if(preg_match('/Firefox/i', $agente)) {
                $navegador = 'Mozilla Firefox';
            } else if(preg_match('/Chrome/i', $agente) || preg_match('/CriOS/i', $agente)) {
                $navegador = 'Google Chrome';
            } else if(preg_match('/Safari/i', $agente)) {
                $navegador = 'Apple Safari';
            } else if(preg_match('/Opera/i', $agente) || preg_match('/OPR/i', $agente)) {
                $navegador = 'Opera';
            } else if(preg_match('/Netscape/i',$agente)) {
                $navegador = 'Netscape';
            }

            return $navegador;
        }
        
        # Metodo que obtiene el sistema operativo
        public static function obtener_sistema_operativo() {
            
            $agente = $_SERVER['HTTP_USER_AGENT'];
            $platforma = 'Unknown';
            
            if (preg_match('/linux/i', $agente)) {
                $platforma = 'Linux';
            }
            
            if (preg_match('/macintosh|mac os x/i', $agente)) {
                $platforma = 'Mac';
            }
            
            if (preg_match('/windows|win32/i', $agente)) {
                $platforma = 'Windows';
            }
            
            if (preg_match('/Android/i', $agente)) {
                $platforma = 'Android';
            }
            
            if (preg_match('/iPad/i', $agente)) {
                $platforma = 'iPad';
            }
            
            if (preg_match('/iPhone/i', $agente)) {
                $platforma = 'iPhone';
            }
            
            return $platforma;
        }
        
        # Metodo que obtiene la localizacion de la ip
        public static function localizacion_ip() {
            
            $ip = Util::obtener_ip();
            $key = 'ae2f31a867d2308b595f93573c8b7b511366dba053bc1a9259ad5ac3db38400c';
            $url = 'https://api.ipinfodb.com/v3/ip-city/?key=' . $key . '&ip=' . $ip . '&format=json';
            
            $data = file_get_contents($url);
            $data = json_decode($data , true);

            if($data['statusCode'] == 'OK') {
                return $data;
            }
            
            return 'unknown';
        }
    }
?>