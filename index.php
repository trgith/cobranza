<?php
/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 25/01/2017
 * Time: 03:43 PM
 */
//ds son siglas seprador de directrio
define('DS', DIRECTORY_SEPARATOR);//es un /slash
define('ROOT', realpath(dirname(__FILE__)) . DS); //direccion de donde esta tu proyecto
define('APP_PATH', ROOT . 'aplication' . DS);//el root mas la carpeta aplication

//carga a todos de golpe
require_once APP_PATH . 'config.php';
require_once APP_PATH . 'request.php';
require_once APP_PATH . 'controller.php';
require_once APP_PATH . 'Model.php';
require_once APP_PATH . 'view.php';
require_once APP_PATH . 'bootstrap.php';
require_once APP_PATH . 'Database.php';

try {//para que cuando caiga en error no se detenga el codigo
    bootstrap::run(new request());
} catch (Exception $e) {
    echo $e->getMessage();
}
