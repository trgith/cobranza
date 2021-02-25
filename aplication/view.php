<?php

/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 29/06/2019
 * Time: 10:01 AM
 */
class view
{
    private $_controller;
    private $_jsPublic;
    private $_cssPublic;
    private $_js;
    private $_css;
    private $_routes;

    public function __construct(request $request)
    {
        $this->_controller = $request->getController();
        $this->_routes['js'] = BASE_URL . 'views/' . $this->_controller . '/js/';
        $this->_routes['css'] = BASE_URL . 'views/' . $this->_controller . '/css/';
    }

    //para mandar a llamar un archivo que tiene html para vista del usuario
    public function rendering($view, $item = false)
    {

        $js = [];
        if (count($this->_js)) {
            $js = $this->_js;
        }
        $css = [];
        if (count($this->_css)) {
            $css = $this->_css;
        }
        if($item){
            $_layoutParams = [
                'route_css' => BASE_URL . 'views/layout/' . BACKEND_LAYAUT . '/css/',
                'route_js' => BASE_URL . 'views/layout/' . BACKEND_LAYAUT . '/js/',
                'route_img' => BASE_URL . 'views/layout/' . BACKEND_LAYAUT . '/img/',
                'css' => $css,
                'js' => $js,
                'jsPublic' => $this->_jsPublic,
                'cssPublic' => $this->_cssPublic
            ];
        }
        else{
            $_layoutParams = [
                'route_css' => BASE_URL . 'views/layout/' . FRONTEND_LAYAUT . '/css/',
                'route_js' => BASE_URL . 'views/layout/' . FRONTEND_LAYAUT . '/js/',
                'route_img' => BASE_URL . 'views/layout/' . FRONTEND_LAYAUT . '/img/',
                'css' => $css,
                'js' => $js,
                'jsPublic' => $this->_jsPublic,
                'cssPublic' => $this->_cssPublic
            ];
        }
        //va a ir a buscar a la carpeta view una carpeta llamda como el controlador
        $routView = ROOT . 'views' . DS . $this->_controller . DS . $view . '.phtml';
        //si existe la pantallla
        if (is_readable($routView)) {
            //mandamos a ejecutar y cambia el tipo de letra y se completa el codigo html
            if ($item){
                include_once ROOT . 'views' . DS . 'layout' . DS . BACKEND_LAYAUT . DS . 'header.php';
                include_once $routView;
                include_once ROOT . 'views' . DS . 'layout' . DS . BACKEND_LAYAUT . DS . 'footer.php';
            }
            else{
                include_once ROOT . 'views' . DS . 'layout' . DS . FRONTEND_LAYAUT . DS . 'header.php';
                include_once $routView;
                include_once ROOT . 'views' . DS . 'layout' . DS . FRONTEND_LAYAUT . DS . 'footer.php';
            }
        } else {
            throw new Exception('Vista no encontrada en: ' . $routView);
        }
    }

    public function setJsPublic(array $js)
    {
        if (is_array($js) && count($js)) {
            foreach ($js as $item) {
                //$this->_jsPublic[] = BASE_URL . 'public' . '/' . 'js' . '/' . $item . '.js';
                $pos = strpos($item,'.min');
                if ($pos !== false) {
                    $item = $bodytag = str_replace(".min", "", $item);
                    $this->_jsPublic[] = BASE_URL . 'public' . '/' . $item . '/' . $item . '.min.js';
                }else{
                    $this->_jsPublic[] = BASE_URL . 'public' . '/' . $item . '/' . $item . '.js';
                }
            }
        } else {
            throw new Exception('Error de js');
        }
    }

    public function setCssPublic(array $js)
    {
        if (is_array($js) && count($js)) {
            foreach ($js as $item) {
                //$this->_cssPublic[] = BASE_URL . 'public' . '/' . 'css' . '/' . $item . '.css';
                $pos = strpos($item,'min');
                if ($pos !== false) {
                    $item = $bodytag = str_replace(".min", "", $item);
                    $this->_cssPublic[] = BASE_URL . 'public' . '/' . $item . '/' . $item . '.min.css';
                }else{
                    $this->_cssPublic[] = BASE_URL . 'public' . '/' . $item . '/' . $item . '.css';
                }
            }
        } else {
            throw new Exception('Error de js');
        }
    }

    public function setJs(array $js)
    {
        if (is_array($js) && count($js)) {
            foreach ($js as $item) {
                $this->_js[] = $this->_routes['js'] . $item . '.js';
            }
        } else {
            throw new Exception('Error js');
        }
    }

    public function setCss(array $css)
    {
        if (is_array($css) && count($css)) {
            foreach ($css as $item) {
                $this->_css[] = $this->_routes['css'] . $item . '.css';
            }
        } else {
            throw new Exception('Error css');
        }
    }
}