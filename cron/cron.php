<?php
    $ruta_principal = (dirname(dirname(__FILE__)));
    //$ruta_controller = (dirname(dirname(__FILE__))). '/aplication/controller.php';
    $ruta_adminController = (dirname(dirname(__FILE__)))."/controllers/adminController.php";
    require_once $ruta_principal.'/index.php';
    include $ruta_adminController;
    $admin = new adminController();
    $admin->notificarPago();
?>

<?php
/*$ruta_principal = (dirname(dirname(__FILE__)));
$file = fopen($ruta_principal."/cron/archivo.txt", "a");

fwrite($file, "ruta: ". $ruta_principal . PHP_EOL);


fclose($file);*/

?>
