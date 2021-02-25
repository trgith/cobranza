<?php
/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 29/06/2019
 * Time: 10:59 AM
 */

define('BASE_URL','http://'. $_SERVER['HTTP_HOST'].'/cobranza/');
define('IMG_URL', 'views/img/');
define('DEFAULT_CONTROLLER','index');
define('BACKEND_LAYAUT','backend');
define('FRONTEND_LAYAUT','frontend');

#Clave secreta de ReCaptcha
define('KEY_CAPTCHA', '6LffE88UAAAAAJUs9a5eIkzEsXfG_gCD-BYjugFx');

#Datos de encriptacion 
define('METHOD', 'AES-256-CBC');
define('SECRET_KEY', 'AES-256-CBC');
define('SECRET_IV', 'AES-256-CBC');
define('KEY', hash('sha256', SECRET_KEY));
define('IV', substr(hash('sha256', SECRET_IV), 0, 16));

define('APP_NAME','Cobranza');
define('APP_SLOGAN','Sistema de control de pagos de TR Network');
define('APP_COMPANY','index');//se pone directamente a index

#Datos de la base de datos
define('HOST', 'localhost');
define('USER', 'root');
define('PSWD','trnetwork');
define('DATABASE', 'trnetwork_cobranza');
define('DRIVER','mysql');

#Datos de Email
define("HOST_MAIL", 'mocha3017.mochahost.com');
define("USER_MAIL", 'notificaciones@trnetwork.com.mx');
define("PSWD_MAIL", 'N0T1f1cAc10n3S.');
define("SMTPSecure", 'tls');
define("PORT_MAIL", 587);