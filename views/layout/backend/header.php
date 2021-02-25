<?php
#Función que elimina la sessión después de 48 horas de inactividad
function init(){
    if (!isset($_SESSION['tiempo'])) {
        $_SESSION['tiempo']=time();
    }
    else if ((time() - $_SESSION['tiempo']) > 172800) {
        session_unset();
        session_destroy();
        /* Aquí redireccionas a la url especifica */
        header("Location: index");
        die();
    }
    $_SESSION['tiempo']=time(); //Si hay actividad seteamos el valor al tiempo actual
}
//Función que realiza la validación de tiempo para la session
//init();

$admin = new adminController();
$notificaciones = $admin->getNotificaciones();
($notificaciones !=0 ) ? $num_notificaciones = count($notificaciones) : $num_notificaciones = 0;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><!--acentos-->
        <meta http-equiv="X-UA-COMPATIBLE" content="IE=edge"><!--explorer lo reconozca-->
        <meta name="viewport" content="width=device-width, initial-scale=1"><!--para celulares-->

        <title>ADMINISTRADOR</title>
        <link rel="shortout icon" href="<?php echo BASE_URL;?>TR.ico">

        <!--Mandamos a llamar a bootstrap rel-relacion que esxiste es una hoja de estilo-->
        <link href="<?php echo BASE_URL . 'public' . DS; ?>bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo BASE_URL . 'public' . DS; ?>fontawesome-free/css/all.min.css" rel="stylesheet">
        <link href="<?php echo BASE_URL . 'public' . DS;?>sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo BASE_URL . 'public' . DS;?>animate/animate.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo BASE_URL . 'public' . DS;?>datatables/dataTables.bootstrap4.css" rel="stylesheet" type="text/css">

        <!--Cargamos los estilos del panel de sb-admin -->
        <link rel="stylesheet" href="<?php echo BASE_URL."public".DS;?>sb-admin/sb-admin.min.css">

        <!-- Cargamos los estilos comunes para todos desde el layaut -->
        <link href="<?php echo $_layoutParams['route_css']; ?>back.css" rel="stylesheet">

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

        <!-- librerias base -->
        <!-- Bootstrap core JavaScript-->
        <script src="<?php echo BASE_URL.'public'.DS; ?>jquery/jquery.min.js"></script>
        <script src="<?php echo BASE_URL.'public'.DS; ?>popper.js/dist/umd/popper.min.js"></script>
        <script src="<?php echo BASE_URL.'public'.DS; ?>bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

        <!-- Core plugin JavaScript-->
        <script src="<?php echo BASE_URL.'public'.DS; ?>jquery-easing/jquery.easing.min.js" type="text/javascript"></script>

        <!-- Sweetalert2 plugin-->
        <script src="<?php echo BASE_URL.'public'.DS; ?>sweetalert2/sweetalert2.all.min.js"></script>

        <!-- Bootstrap datatables plugin-->
        <script src="<?php echo BASE_URL.'public'.DS; ?>datatables/jquery.dataTables.js"></script>
        <script src="<?php echo BASE_URL.'public'.DS; ?>datatables/dataTables.bootstrap4.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.21.0/moment.min.js" type="text/javascript"></script>
        <script src="<?php echo BASE_URL.'public'.DS; ?>bootstrap-datetimepicker/es.js"></script>
        <script src="<?php echo BASE_URL.'public'.DS; ?>numeral/numeral.min.js"></script>
        <script src="<?php echo BASE_URL.'public'.DS; ?>vue/vue.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.debug.js"></script>


        <!-- se pueden poner scirpts publicos de js -->
        <?php if (isset($_layoutParams['jsPublic']) && count($_layoutParams['jsPublic'])):
            foreach ($_layoutParams['jsPublic'] as $js):?>
                <script src="<?php echo $js; ?>" type="text/javascript"></script>
            <?php endforeach;
        endif; ?>

    </head>
    <body id="page-top">
    <!--=====================================
     CABEZOTE
    ======================================-->
    <nav class="navbar navbar-expand navbar-dark bg-dark static-top" id="cabecera">

        <a class="navbar-brand mr-1" href="home"><img src="<?php echo BASE_URL.'views/img/trnetwork_white.png';?>" alt="TR network" class="img-fluid" id="logo"></a>

        <button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
            <i class="fas fa-bars"></i>
        </button>
        <!--este div se puso para que el dropdown se ubicara hasta la derecha de la página-->
        <div class="d-none d-md-inline-block ml-auto my-md-0">

        </div>
        <!-- Navbar -->
        <ul class="navbar-nav ml-auto ml-md-0">
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <h6 id="nombre-admin"><?php echo $_SESSION['user']?></h6>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="settings">Settings</a>
                    <!--<a class="dropdown-item" href="#">Activity Log</a>-->
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout" data-toggle="modal" data-target="#logoutModal">Salir</a>
                </div>
            </li>
            <li class="nav-item dropdown no-arrow" id="notify">
                <a class="nav-link dropdown-toggle" href="notificaciones" id="notifi" role="button" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-fw fa-bell"></i>
                    <span class="badge badge-danger"><?php echo $num_notificaciones; ?></span>
                </a>
            </li>  
        </ul>
    </nav>

    <!--====  Fin de CABEZOTE  ====-->

    <!--=====================================
    COLUMNA BOTONERA
    ======================================-->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="sidebar navbar-nav">
            <li class="nav-item active" id="index">
                <a class="nav-link" href="index">
                    <i class="fas fa-fw fa-graduation-cap"></i>
                    <span>Registrar generacion</span>
                </a>
            </li>
            <li class="nav-item" id="usuario">
                <a class="nav-link" href="usuario">
                    <i class="fas fa-fw fa-user-plus"></i>
                    <span>Registrar usuario</span>
                </a>
            </li>
            <li class="nav-item" id="busqueda">
                <a class="nav-link" href="busqueda">
                    <i class="fas fa-search"></i>
                    <span>Buscar usuario</span>
                </a>
            </li>
            <li class="nav-item dropdown no-arrow" id="corrida">
                <a class="nav-link" href="corrida">
                    <i class="fas fa-fw fa-money-bill-alt"></i>
                    <span>Corrida de pago</span>
                </a>
            </li>
            <li class="nav-item dropdown no-arrow" id="cuentaBancaria">
                <a class="nav-link" href="cuentaBancaria">
                    <i class="fas fa-fw fa-university"></i>
                    <span>Cuentas bancarias</span></a>
            </li>
            <li class="nav-item" id="nota">
                <a class="nav-link" href="nota">
                    <i class="fas fa-fw fa-sticky-note"></i>
                    <span>Notas</span></a>
            </li>
            <li class="nav-item dropdown" id="accesoUsuario">
                <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-fw fa-key"></i>
                    <span>Acceso usuario</span>
                </a>
                <div class="dropdown-menu menuOpciones" aria-labelledby="pagesDropdown">
                    <a class="dropdown-item" href="deshabilitar_usuario" id="deshabilitar_usuario">
                        <i class="fas fa-fw fa-times"></i>
                        <span>Inhabilitar usuario</span>
                    </a>
                    <a class="dropdown-item" href="habilitar_usuario" id="habilitar_usuario">
                        <i class="fas fa-fw fa-check"></i>
                        <span>Habilitar usuario</span>
                    </a>
                </div>
            </li>
            <li class="nav-item" id="status">
                <a class="nav-link" href="status">
                    <i class="fas fa-user-check"></i>
                    <span>Estado de usuarios</span></a>
            </li>
            <li class="nav-item dropdown" id="accesos">
                <a class="nav-link" href="accesos">
                    <i class="fas fa-fw fa-unlock"></i>
                    <span>Accesos al sistema</span></a>
            </li>
            <li class="nav-item dropdown" id="respaldo">
                <a class="nav-link" href="#">
                    <i class="fas fa-database"></i>
                    <span>Respaldo de BD</span></a>
            </li>

        </ul>
    <!--====  FIn de COLUMNA BOTONERA  ====-->

        <!--- Toasts (notificaciones) --->
        <div class="row">
            <div id="Toasts">
                <transition-group name="toast-list" tag="ul">
                    <article class="message toast-list-item" v-for="toast, index of content" :key="toast.id" :class="type(toast.type)">
                        <div class="message-header">
                            <p>{{ toast.title}}</p>
                            <button class="delete" @click="remove(toast.id)"><i class="fa fa-times" style="color: #FFFFFF"></i></button>
                        </div>
                        <div class="message-body">{{ toast.body}} </div>
                    </article>
                </transition-group>
            </div>
        </div>