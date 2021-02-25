<?php
/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 29/06/2019
 * Time: 10:00 AM
 */
class request{
    private $_controller;
    private $_method;
    private $_arguments;

    public function __construct()
    {
        if (isset($_GET['url'])) {
            $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);//limpiamos carcateres extraños acentos no todos acepta =?_-...
            $url = explode('/', $url);//los quita los / y los manda a un arreglo
            $url = array_filter($url);//ignora espacios vacios localhost/MMVC/contollers///////metohd toma en cuenta hatas method

            $this->_controller = array_shift($url);//extrae del arreglo el primer elemento localhost
            $this->_method = array_shift($url);
            $this->_arguments = $url;
        }

        if (!$this->_controller) {//si no encuentra te lleva al index
            $this->_controller = DEFAULT_CONTROLLER;
        }
        if (!$this->_method) {
            $this->_method = 'index';
        }
        if (!isset($this->_arguments)) {
            $this->_arguments = [];
        }
    }
        public function getController(){//accede a variebles privadas
            return $this->_controller;
        }
        public function getMethod(){
            return $this->_method;
        }
        public function getArguments(){
            return $this->_arguments;
        }
}