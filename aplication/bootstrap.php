<?php
/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 29/06/2019
 * Time: 10:01 AM
 */
//despachador de las solicitudes de url se hacen la solicitud de request y boot despacha
class bootstrap{
    public static function run(request $request){//pasa un objeto de tipo request
        $controller=$request->getController().'Controller';        //
        $routeController=ROOT.'controllers'.DS.$controller.'.php'; //indicaa toda la ruta de C:/.../controllers/indexcontroller.php
        $method=$request->getMethod();
        $args=$request->getArguments();

        if(is_readable($routeController)){//si no lo enciuentra retorna false por si lo direccionamos mal
            require_once $routeController;
            $controller=new $controller;
            //3ntra al archivo que ya existe y pregunta si tal metodo existe en la clase si si returna true sino false
            if(is_callable([$controller,$method])){
                $method=$request->getMethod();
            }else{
                $method='index';
            }
            if (isset($args)){
                //existe la clse, el metodo y que el numero de argumentos coincida con el numero de argumentos que mandan
                call_user_func_array([$controller,$method],$args);
            }else{
                call_user_func([$controller,$method]);
            }
        }else{
            //para que veas si es correcta tu ruta
            throw new Exception('Controlador no encontrado en: '.$routeController);
        }
    }
}
