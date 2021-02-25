<?php
/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 29/06/2019
 * Time: 10:00 PM
 */
  abstract class controller{
      //VA HACER EL CONTROLADOR PADRE CONTROLA TODO LO DEMAS  A LA CARPETA CONTROLLERS
      protected $_view;
      public  function __construct(){
          $this->_view=new view(new request());
      }

      abstract public function index();

      protected function loadModel($model){
          $model=$model.'Model';
          //C://XAMPP/WWW/... /memberModel.php
          $routeModel=ROOT.'models'.DS.$model.'.php';
          //solo comprueba si existe el documento esta o no esta
          if (is_readable($routeModel)){
              require_once $routeModel;
              return new $model;
          }else{
              throw new Exception('Modelo no encontrad en: '.$routeModel);
          }
      }
      //para acceder solamente a las librerias
      protected function getLibrary($library){
          $routeLibrary=ROOT.'libs'.DS.$library.'.php';
          if (is_readable($routeLibrary)){
              require_once $routeLibrary;
          }else{
              throw new  Exception('Libreria no encontrada en: '.$routeLibrary);
          }
      }
      protected function is_ajax()
      {
          return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
      }
  }