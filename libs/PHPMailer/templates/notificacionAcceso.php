<?php
    //include ($_SERVER['DOCUMENT_ROOT']."/cobranzaTR/libs/Util/util.php");
    //$info_acceso = util::informacion_computadora();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Notificacion de acceso</title>
    <style type="text/css">
        .card{
            margin: 20% auto;
        }
        .card-body{
            background-color: #a82b0e;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col">
        <div class="card w-50">
            <img src="http://trnetwork.com.mx/images/logos/trnLogo1.png" class="card-img-top" alt="TR network" style="padding: 20px;">
            <div class="card-body">
                <h5 class="card-title">Se ha detectado un nuevo inicio de sesión</h5>
                <p><?php print_r($info_acceso) ?></p>
                <h6>Detalles del acceso</h6>
                <p>Ip: <b><?php echo $info_acceso['ip']?></b></p>
                <!--<p>Dispositivo: <b><?php echo  $info_acceso['dispositivo']?></b></p>-->
                <!--<p>Navegador: <b><?php  echo $info_acceso['navegador']?></b></p>-->
                <p>Sistema Operativo: <b><?php echo  $info_acceso['sistema_operativo']?></b></p>
                <p>Ubicaación: <b><?php echo  $info_acceso['ubicacion']?></b></p>
            </div>
            <div class="card-footer text-center">
                <small class="text-muted"><b>TR Network:</b> Cobranza</small>
            </div>
        </div>
        </div>
    </div>
</div>
</body>
</html>

