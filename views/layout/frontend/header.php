 <?php
/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 29/06/2019
 * Time: 07:18 PM
 */
?>

<!DOCTYPE html>
<html lang="'es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><!--acentos-->
        <meta http-equiv="X-UA-COMPATIBLE" content="IE=edge"><!--explorer lo reconozca-->
        <meta name="viewport" content="width=device-width, initial-scale=1"><!--para celulares-->

        <title>TR Network Cobranza</title>
        <link rel="shortout icon" href="<?= BASE_URL;?>TR.ico">

        <!--Mandamos a llamar a bootstrap rel-relacion que esxiste es una hoja de estilo-->
        <link href="<?php echo BASE_URL . 'public' . DS; ?>bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo BASE_URL . 'public' . DS; ?>fontawesome-free/css/all.min.css" rel="stylesheet">
        <link href="<?php echo BASE_URL.'public'.DS;?>sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo $_layoutParams['route_css']; ?>front.css" rel="stylesheet">
        <!--//se pueden poner estilos publicos de css-->
        <?php if (isset($_layoutParams['cssPublic']) && count($_layoutParams['cssPublic'])):
            foreach ($_layoutParams['cssPublic'] as $item):?>
                <link href="<?php echo  $item; ?>" rel="stylesheet" type="text/css">
            <?php endforeach;
        endif; ?>

        <!--//se pueden poner estilos propios de css -->
        <?php if (isset($_layoutParams['css']) && count($_layoutParams['css'])):
            foreach ($_layoutParams['css'] as $item):?>
                <link href="<?php echo $item; ?>" rel="stylesheet" type="text/css">
            <?php endforeach;
        endif; ?>
        
        <!--[if lt IE 9]    para que sea reconocido en explorer-->
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.17.0/TweenMax.min.js"></script>

        <!-- librerias base -->
        <!-- Bootstrap core JavaScript-->
        <script src="<?php echo BASE_URL.'public'.DS; ?>jquery/jquery.min.js"></script>
        <script src="<?php echo BASE_URL.'public'.DS; ?>popper.js/dist/umd/popper.min.js"></script>
        <script src="<?php echo BASE_URL.'public'.DS; ?>bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <!-- Sweetalert2 plugin-->
        <script src="<?php echo BASE_URL.'public'.DS; ?>sweetalert2/sweetalert2.all.min.js"></script>

        <!-- se pueden poner scirpts publicos de js -->
        <?php if (isset($_layoutParams['jsPublic']) && count($_layoutParams['jsPublic'])):
            foreach ($_layoutParams['jsPublic'] as $js):?>
                <script src="<?php echo $js; ?>" type="text/javascript"></script>
            <?php endforeach;
        endif; ?>

    </head>
    <body>