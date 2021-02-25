<?php
/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 29/06/2019
 * Time: 10:00 AM
 */
class Model{
    protected $_db;
    //YA TENEMOS ACCESO A LA BASE DE DATOS
    public function __construct(){
        $dns=DRIVER.':dbname='.DATABASE.';host='.HOST;
        $this->_db=new Database($dns,USER,PSWD);
        $this->_db->exec("set names utf8");
    }
}