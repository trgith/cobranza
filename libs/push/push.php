<?php

    # Configuracion para enviar notificaciones con google
    #define('URL_GOOGLE_CLOUD_MESSAGING', 'https://android.googleapis.com/gcm/send'); # URL de google cloud
    #define('GOOGLE_API_KEY', 'AIzaSyBms61yIobbLbms-psRwUV93TSkPyozacU'); # google api
    # ID de los dispositivos registrados para enviar la notificacion
    #define('GOOGLE_ID_DEVICES', 'ee7B0hCqies:APA91bF9xGWH3hapHFONpiJDHTV_tXFRg3atHkWyVOIVu_EJIBzJ1rmQkYrQNz4ccTdhV2KmuJECNNt9ij0IFo3i4pnPBgIBLhKeV1MJH0T46ELyP7IlXRHBwzpuQpcQk5FAtyFd9gJh');
    define('URL_FIREBASE_CLOUD_MESSAGE', 'https://fcm.googleapis.com/fcm/send');
    define('FIREBASE_API_KEY', 'AIzaSyBIyt3Fx4pDs0jWj0bmAwpCGo80DQ6QzFA');
    define('GOOGLE_ID_DEVICES', 'dlZCT_bUn2A:APA91bGNopYKlI-V_wtEV8j29o2WGG4b73sY7-YlQruEP-cXeXWSEEGZRPWGbgjKp69dNRoiMYGz0JIyET9tm21LRnibN5Y31brOqf0obH9DFg-0yguTVfdTFMa1caBm1nQWr9ke4GCg');

    class Push {
        
        # Metodo que envia una notificacion google gcm
        public static function enviar_notificacacion($data) {
            
            #$fields = array('registration_ids' => array(GOOGLE_ID_DEVICES), 'data' => $data,);
            $fields = array('to' => GOOGLE_ID_DEVICES, 'data' => $data);
	        $headers = array('Authorization: key=' . FIREBASE_API_KEY, 'Content-Type: application/json');
            
            $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, URL_FIREBASE_CLOUD_MESSAGE);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);   
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	        $response = curl_exec($ch);
            
	        if ($response === FALSE) {
	            die('Curl failed: ' . curl_error($ch));
	        }
            
	        curl_close($ch);
	        return Push::verificar_notificacion(json_decode($response));
            #return json_decode($response);
        }
        
        # Metodo que notifica si existe error o no al enviar una notificacion
        public static function verificar_notificacion($response) {
            
            $response = get_object_vars($response);
            $result = get_object_vars($response['results'][0]);

            foreach($result as $key => $value) {

                switch($value) {
                    case 'MissingRegistration':
                    case 'InvalidRegistration':
                    case 'NotRegistered':
                    case 'InvalidPackageName':
                            $tipo_mensaje = 'text-danger';
                            $mensaje_text = 'El dispositivo con el que intenta mandar la notificación no se encuentra registrado.';
                        break;
                    case 'MismatchSenderId':
                            $tipo_mensaje = 'text-danger';
                            $mensaje_text = 'No se ha podido enviar la notificación';
                        break;
                    case 'MessageTooBig':
                            $tipo_mensaje = 'text-danger';
                            $mensaje_text = 'La información que trata de enviar supera lo permitido.';
                        break;
                    case 'InvalidDataKey':
                            $tipo_mensaje = 'text-danger';
                            $mensaje_text = 'Por el momento no se puede enviar la información, los datos no son validos.';
                        break;
                    case 'InvalidTtl':
                            $tipo_mensaje = 'text-danger';
                            $mensaje_text = 'Por el momento no se puede enviar la información, no es posible de enviar la notificación.';
                        break;
                    case 'Timeout':
                            $tipo_mensaje = 'text-danger';
                            $mensaje_text = 'No se puede enviar la notificación, inténtelo de nuevo.';
                        break;
                    case 'InternalServerError':
                            $tipo_mensaje = 'text-danger';
                            $mensaje_text = 'Ocurrio un error en el servidor, inténtelo mas tarde.';
                        break;
                    default:
                            $mensaje_text = 'La notificación fue enviada correctamente.';
                            $tipo_mensaje = 'text-info';
                }
            }
            
            return array($tipo_mensaje, $mensaje_text);
        }
    }
?>