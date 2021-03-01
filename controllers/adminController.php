<?php

class adminController extends controller{
    private $_admin;
    private $notificacion = [
        "tipo_mensaje" => "success",
        "mensaje" => ""
    ];
    public $dias_mes = 30;
    //todos los que eredan deben de llevar el metodo index
    /****** MONEY FORMAT *******/
    #Para que las cantidades se muestren sin signo de pesos el formato es: %!n
    #Para que las cantidades se muestren con el signo de pesos %n
    public function __construct(){
        #llame al constructor de la clase padre
        parent::__construct();
        #Se carga el modelo
        $this->_admin = $this->loadModel('backend');
        setlocale(LC_MONETARY, 'en_US');
        if(!isset($_SESSION))
        {
            session_start();
        }
    }

    public function index(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $generaciones = $this->_admin->getGeneraciones();
            if (!is_null($generaciones)){
                $this->_view->generaciones = $generaciones;
            }
            $this->_view->setCssPublic(['bootstrap-datetimepicker.min']);
            $this->_view->setJsPublic(['bootstrap-datetimepicker.min', 'jquery.mask.min', 'jspdf.min', 'moment.min']);
            $this->_view->setCss(['corrida', 'index']);
            $this->_view->setJs(['index']);
            $this->_view->rendering('index',true);
        }else header('Location: /cobranza');
    }

    public function registrarUsuario(){
        if ($this->is_ajax()) {
            $resultado = $this->_admin->verificarUsuario($_POST);
            if (!is_null($resultado)) {
                $this->notificacion['tipo_mensaje'] = 'warning';
                $this->notificacion['mensaje'] = "El usuario ya se encuentra registrado";
                print_r(json_encode($this->notificacion));
            }else{
                $this->getLibrary("Util/util");
                $util = new Util();
                $password = $util->generar_numero_aleatorio();
                $password = $util->encrypt($password);
                $fecha_registro = date('Y-m-d H:i:s');
                $resultado = $this->_admin->registrarUsuario($_POST,$password,$fecha_registro);
                if ($resultado != 1) {
                    $this->notificacion['tipo_mensaje'] = 'danger';
                    $this->notificacion['mensaje'] = "Error al registrar los datos";
                    print_r(json_encode($this->notificacion));
                }else{
                    $idUsuario = $this->_admin->getIdUsuario();
                    $ruta = ROOT."files/usuario_".$idUsuario;
                    #Se genera el directorio para el usuario
                    if(!mkdir($ruta, 0777, true)) {
                        #die('Fallo al crear la carpeta...');
                        $this->notificacion['tipo_mensaje'] = 'warning';
                        $this->notificacion['mensaje'] = "Directorio de usuario exitente!!";
                        print_r(json_encode($this->notificacion));
                    }
                    $this->notificacion['tipo_mensaje'] = 'success';
                    $this->notificacion['mensaje'] = "Usuario registrado exitosamente";
                    print_r(json_encode($this->notificacion));
                }
            }
        }else header('Location: /cobranza');
    }

    public function corrida(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $this->_view->setCssPublic(['bootstrap-datetimepicker.min']);
            $this->_view->setJsPublic(['bootstrap-datetimepicker.min', 'jquery.mask.min']);
            $this->_view->setCss(['corrida', 'index']);
            $this->_view->setJs(['corrida']);
            $this->_view->rendering('corrida',true);
        }else {
            header('Location: /cobranza');
        }
    }

    public function generarCorrida(){
        if ($this->is_ajax()) {
            #Verificamos si el usuario cuenta con una corrida
            $result = $this->_admin->corridas_existentes($_POST['usuario']);
            $corrida_existente = false;
            if ($result != 0){
                $corrida_existente = true;
            }
            $semanas = ($_POST['periodo'] == 'Quincenal') ? 12 : 6;
            $cantidad = intval(str_replace(",", "", $_POST['montoAPagar']));
            $cantidad = $cantidad / $semanas;
            $cantidad = round($cantidad);
            $cantidad = number_format($cantidad,2,'.',',');
            $fechaIngreso = $_POST['fechaIngreso'];
            /* separamos la fecha por sus componentes */
            $fechaIngresoDescompuesta = explode("-",$fechaIngreso);
            $pago = ($semanas == 6) ? ' mes.' : ' qna.';
            $dias_trabajados = $this->dias_trabajados($fechaIngreso,$semanas);
            $band = false;
            //Aplicación de la regla de los días trabajados para (quincenas)
            if (($dias_trabajados < 4) && $_POST['periodo'] == 'Quincenal'){
                $semanas = 13;
                $cantidad_dia = 15;
                $cantidad_dia = $this->sanitizarCantidad($cantidad) / $cantidad_dia;
                $cantidad_dt = $dias_trabajados * $cantidad_dia;
                $cantidad_dt = number_format(round($cantidad_dt),2,'.',',');
                $cantidad_res = (15 - $dias_trabajados)* $cantidad_dia;
                $cantidad_res = number_format(round($cantidad_res),2,'.',',');
                $band = true;
            }
            //Aplicación de la regla de los días trabajados para (mensualidades)
            if (($dias_trabajados < 19) && $_POST['periodo'] == 'Mensual'){
                $semanas = 7;
                $cantidad_dia = $this->dias_mes;
                $cantidad_dia = $this->sanitizarCantidad($cantidad) / $cantidad_dia;
                $cantidad_dt = $dias_trabajados * $cantidad_dia;
                $cantidad_dt = number_format(round($cantidad_dt),2,'.',',');
                $cantidad_res = ($this->dias_mes - $dias_trabajados)* $cantidad_dia;
                $cantidad_res = number_format(round($cantidad_res),2,'.',',');
                $band = true;
            }
            $fechas = $this->calcular_fechas($fechaIngreso,$semanas);


            /* Aqui realiza la regla de los 12 dias */
            $fecha1 = date_create($fechaIngreso);/* fecha de ingreso */
            $fecha2 = date_create($fechas[0]); /* siguiente quincena/inmediato */
            $diferencia = date_diff($fecha1,$fecha2);
            $diferencia = $diferencia->format('%d');/* formato para obtener solo el numero de dias */


            if(intval($diferencia) < 12){
                /* entra en la siguiente quincena */
                /* Se borra la primera quincena, puesto que entrara en la siguiente */
                unset($fechas[0]);
                sort($fechas);

                if(sizeof($fechas) == 11 OR sizeof($fechas) == 5){
                    /* Y se anade otra quincena mas, puesto que quedo solo con 11 en la accion anterior */
                    $fechaPrueba = $fechas[sizeof($fechas) - 1]; /* se obtiene la ultima fecha del arreglo */
                    $siguientesQuincenasMeses = $this->calcular_fechas($fechaPrueba, $semanas);
                    /*Se anade la primera fecha siguiente al array de fechas que ya tenemos */
                    array_push($fechas, $siguientesQuincenasMeses[1]);
                }
            }

            $cont = 0;
            foreach ($fechas as $fecha){
                $fechas[$cont] = $this->convertirFecha($fecha);
                $cont++;
            }
            if ($band)
                $corrida = [
                    'semanas' => $semanas,
                    'cantidad' => $cantidad,
                    'cantidad_dt' => $cantidad_dt,
                    'cantidad_res' => $cantidad_res,
                    'pago' => $pago,
                    'fechas' => $fechas,
                    'corrida_existente' => $corrida_existente
                ];
            else
                $corrida = [
                    'semanas' => $semanas,
                    'cantidad' => $cantidad,
                    'pago' => $pago,
                    'fechas' => $fechas,
                    'corrida_existente' => $corrida_existente
                ];
            print_r(json_encode($corrida));
        }else header('Location: /cobranza');
    }

    public function registrarDatosCorrida(){
        if ($this->is_ajax()){
            if ($_POST['informacionPago']){
                $informacionPago = $_POST['informacionPago'];
                #se verifica si hay datos y se convierten  en arreglos
                $informacionPago = (isset($informacionPago)) ? json_decode($informacionPago) : '';
                #So obtienen los datos de la clase
                $informacionPago = (is_object($informacionPago)) ? get_object_vars($informacionPago) : '';
                #Se eliminan los espacios en blanco
                $informacionPago = (is_array($informacionPago)) ? array_filter($informacionPago) : '';
                if (!is_null($informacionPago) &&  count($informacionPago) > 0 ){
                    $informacionPago['total_pagar'] = $informacionPago['montoAPagar'];
                    $resultado = $this->_admin->registrarCorrida($informacionPago);
                    #Si se guardo la info de corrida en la BD notificamos lo notificamos
                    if ($resultado) {
                        $this->notificacion['tipo_mensaje'] = 'success';
                        $this->notificacion['mensaje'] = "Datos de corrida registrados";
                        print_r(json_encode($this->notificacion));
                    }
                    else{
                        $this->notificacion['tipo_mensaje'] = 'danger';
                        $this->notificacion['mensaje'] = "Error al registrar los datos";
                        print_r(json_encode($this->notificacion));
                    }
                }
            }
        }
    }

    public function registrarCorrida(){
        if ($this->is_ajax()){
            $total_pagar = 0;
            $cantidad_total = 0;
            $indice = 0;
            $es_parcial = false;
            if ($_POST['pago']){
                $pago = $_POST['pago'];
                #se verifica si hay datos y se convierten  en arreglos
                $pago = (isset($pago)) ? json_decode($pago) : '';

                #Se eliminan los espacios en blanco
                $pago = (is_array($pago)) ? array_filter($pago) : '';

                #Se obtienen los datos de la imformación de pago
                $informacionPago = $this->_admin->getInformacionPago($_POST['id_usuario']);

                #Se obtiene el último elemento del arreglo
                $informacionPago = end($informacionPago);

                if (!is_null($pago) && count($pago) > 0){
                    for ($i = 0; $i < count($pago); $i++){
                        $pago[$i] = (is_object($pago[$i])) ? get_object_vars($pago[$i]) : '';
                        if ($pago[$i]['status'] != 'acreditado' && $pago[$i]['status'] != 'parcial'){
                            $cantidad = $this->sanitizarCantidad($pago[$i]['cantidad']);
                            $total_pagar += $cantidad;
                        }else{
                            $cantidad = $this->sanitizarCantidad($pago[$i]['cantidad']);
                        }
                        $cantidad_total += $cantidad;
                    }
                    #se actualiza el total a pagar en la información de pago (se agrega un item total para utilizar en la BD)
                    $informacionPago['total'] = money_format("%!n",$total_pagar);
                    $respuesta = $this->_admin->actualizarMonto($informacionPago);
                    #if el monto pendiente es mayor al monto a pagar (por pago extra)
                    $monto_pagar = $this->sanitizarCantidad($informacionPago['monto_pagar']);
                    if ($monto_pagar < $total_pagar){
                        $this->_admin->actualizarMontoPagar([
                            "id_informacion_pago" => $informacionPago['id_informacion_pago'],
                            "total_pago" => $informacionPago['total']
                        ]);
                    }
                    #Obtiene el ID del último registro que se inserto
                    $id = $this->_admin->getId();
                    if (true){
                        $dataPago = [
                            'no_pago' => '',
                            'fecha_pago' => '',
                            'cantidad' => '',
                            'consultora' => $informacionPago['consultora'],
                            'status' => '',
                            'forma_pago' => '',
                            'descuento_porcentaje' => '',
                            'descuento_cantidad' => '',
                            'id_informacion_pago' => $id
                        ];
                        for ($i = 0; $i < count($pago); $i++){
                            //$pago[$i] = (is_object($pago[$i])) ? get_object_vars($pago[$i]) : '';

                            foreach ($dataPago as $key => $value) {
                                if ($key != 'consultora' && $key != 'id_informacion_pago') {
                                    $dataPago[$key] = $pago[$i][$key];
                                }
                            }
                            if (!strpos($dataPago['cantidad'], ',')){
                                $dataPago['cantidad'] = money_format('%!n',$dataPago['cantidad']);
                            }
                            $respuesta = $this->_admin->registrarPagos($dataPago);
                        }
                        $this->notificacion['tipo_mensaje'] = 'success';
                        $this->notificacion['mensaje'] = "Registro de corrida de pago exitoso";
                        $this->notificacion['id'] = $id;
                        print_r(json_encode($this->notificacion));
                    }else{
                        $this->notificacion['tipo_mensaje'] = 'danger';
                        $this->notificacion['mensaje'] = "Error al registrar la información";
                        print_r(json_encode($this->notificacion));
                    }
                }
            }
        }else header('Location: /cobranza');
    }

    # Metodo que calculas las fechas
    private function calcular_fechas($fecha_inicial, $semanas) {
        $fechas = null;
        for($i = 0; $i < $semanas; $i++) {
            $dias = '15';
            if($semanas >= 12 ) {
                $aux = $this->calcular_quincenas($fecha_inicial);
                if($aux[1] != 2) {
                    $dias = '15';
                } else {
                    if((!($aux[0]%4) && ($aux[0]%100)) || !($aux[0]%400)) {
                        $dias = '14';
                    } else{
                        $dias = '13';
                    }
                }
            } else {
                $aux = $this->calcular_mes($fecha_inicial);
                if($aux[1] != 1) {
                    $dias = '30';
                } else {
                    if((!($aux[0]%4) && ($aux[0]%100)) || !($aux[0]%400)) {
                        $dias = '29';
                    } else {
                        $dias = '28';
                    }
                }
            }
            $fechas[] = implode('-', $aux);
            $fecha_inicial = date_create($fechas[$i]);
            date_add($fecha_inicial, date_interval_create_from_date_string($dias . ' days'));
            $fecha_inicial = date_format($fecha_inicial, 'Y-m-d');
        }
        return $fechas;
    }

    # Metodo que calcula la fecha en base a la quincena
    private function calcular_quincenas($fecha) {
        $fecha_ingreso = explode('-', $fecha);
        $anio = $fecha_ingreso[0];
        $mes = $fecha_ingreso[1];
        if($fecha_ingreso[2] <= 15) {
            $dia = '15';
        } else {
            $dia = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        }
        return array($anio, $mes, $dia);
    }

    #Metodo que calculas la fecha en base al mes
    private function calcular_mes($fecha) {
        $fecha_ingreso = explode('-', $fecha);
        $anio = (int)$fecha_ingreso[0];
        $mes = (int)$fecha_ingreso[1];
        $dia = (int)$fecha_ingreso[2];
        $dias_de_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        return array($anio, $mes, $dias_de_mes);
    }

    private function dias_trabajados($fecha,$periodo) {
        $fecha = explode('-', $fecha);
        $anio = (int)$fecha[0];
        $mes = (int)$fecha[1];
        $dia = (int)$fecha[2];
        if ($periodo == 12){
            $diferencia = 15 - $dia;
            if ($diferencia < 0){
                $diferencia = 15 + $diferencia;
            }
        }else{
            $this->dias_mes = (int)cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
            $diferencia = $this->dias_mes - $dia;
        }
        return $diferencia;
    }

    public function cuentaBancaria(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $cuentasBancarias = $this->_admin->getCuentasBancarias();
            if (!is_null($cuentasBancarias)){
                $this->_view->cuentasBancarias = $cuentasBancarias;
            }
            $this->_view->setCss(['corrida', 'cuentaBancaria']);
            $this->_view->setJs(['cuentaBancaria']);
            $this->_view->rendering('cuentaBancaria',true);
        }else header('Location: /cobranza');
    }

    public function actualizarCuentaBancaria(){
        if ($this->is_ajax()){
            if (isset($_POST['datosCuenta'])){
                $datosCuenta = json_decode($_POST['datosCuenta']);
                $datosCuenta = (is_object($datosCuenta[0])) ? get_object_vars($datosCuenta[0]) : '';
                $result = $this->_admin->actualizarCuenta($datosCuenta);
                if ($result){
                    $this->notificacion['tipo_mensaje'] = 'success';
                    $this->notificacion['mensaje'] = 'Actualización de cuenta exitoso';
                    print_r(json_encode($this->notificacion));
                }else{
                    $this->notificacion['tipo_mensaje'] = 'danger';
                    $this->notificacion['mensaje'] = 'Ocurrio un error en la BD';
                    print_r(json_encode($this->notificacion));
                }
            }
        }else header('Location: /cobranza');
    }

    public function eliminarCuentaBancaria(){
        if ($this->is_ajax()){
            if (isset($_POST['idCuenta'])){
                $result = $this->_admin->eliminarCuentaBancaria($_POST['idCuenta']);
                if (!$result){
                    $this->notificacion['tipo_mensaje'] = 'success';
                    $this->notificacion['mensaje'] = 'Cuenta bancaria eliminada correctamente';
                    print_r(json_encode($this->notificacion));
                }else{
                    $this->notificacion['tipo_mensaje'] = "warning";
                    $this->notificacion['mensaje'] = "No se pudo eliminar la cuenta";
                    print_r(json_encode($this->notificacion));
                }
            }
        }else header('Location: /cobranza');
    }

    public function registrarCuentaBancaria(){
        if ($this->is_ajax()){
            $resultado = $this->_admin->registrarCuentaBancaria($_POST);
            if ($resultado == 1){
                $this->notificacion['tipo_mensaje'] = 'success';
                $this->notificacion['mensaje'] = "Se registro la cuenta bancaria exitosamente";
                print_r(json_encode($this->notificacion));
            }else{
                $this->notificacion['tipo_mensaje'] = 'danger';
                $this->notificacion['mensaje'] ="Ocurrio un error al resgistrar la cuenta bancaria";
                print_r(json_encode($this->notificacion));
            }
        }else header('Location: /cobranza');
    }

    public function nota(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $notas = $this->_admin->getNotas();
            if (!is_null($notas)){
                $this->_view->notas = $notas;
            }
            $this->_view->setCss(['corrida', 'nota']);
            $this->_view->setJs(['nota']);
            $this->_view->rendering('nota',true);
        }else header('Location: /cobranza');
    }

    public function registrarNota(){
        if ($this->is_ajax()){
            if (isset($_POST['nota'])){
                $resultado = $this->_admin->registrarNota($_POST['nota']);
                if ($resultado == 1){
                    $this->notificacion['tipo_mensaje'] = 'success';
                    $this->notificacion['mensaje'] = "Nota registrada exitosamente";
                    print_r(json_encode($this->notificacion));
                }else{
                    $this->notificacion['tipo_mensaje'] = 'danger';
                    $this->notificacion['mensaje'] = "Ocurrio un error al registrar la nota";
                    print_r(json_encode($this->notificacion));
                }
            }
        }else header('Location: /cobranza');
    }

    public function actualizarNota(){
        if ($this->is_ajax()){
            $datosNota = json_decode($_POST['datosNota']);
            $datosNota = (is_object($datosNota)) ? get_object_vars($datosNota) : '';
            $result = $this->_admin->actualizarNota($datosNota);
            if ($result){
                $this->notificacion['tipo_mensaje'] = 'success';
                $this->notificacion['mensaje'] = "Nota actualizada correctamente";
                print_r(json_encode($this->notificacion));
            }else{
                $this->notificacion['tipo_mensaje'] = "warning";
                $this->notificacion['mensaje'] = "Ocurrio un error en la base de datos";
                print_r(json_encode($this->notificacion));
            }
        }else header('Location: /cobranza');
    }

    public function eliminarNota(){
        if ($this->is_ajax()){
            if (isset($_POST['idNota'])){
                $result = $this->_admin->eliminarNota($_POST['idNota']);
                if (!$result){
                    $this->notificacion['tipo_mensaje'] = 'success';
                    $this->notificacion['mensaje'] = "Nota eliminada correctamente";
                    print_r(json_encode($this->notificacion));
                }else{
                    $this->notificacion['tipo_mensaje'] = "warning";
                    $this->notificacion['mensaje'] = "No se pudo eliminar la nota";
                    print_r(json_encode($this->notificacion));
                }
            }
        }else header('Location: /cobranza');
    }

    public function usuario(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $generaciones = $this->_admin->getGeneraciones();
            if (!is_null($generaciones)) {
                $this->_view->generaciones = $generaciones;
            }
            $this->_view->setCss(['admin']);
            $this->_view->setJs(['admin']);
            $this->_view->rendering('usuario',true);
        }else {
            header('Location: /cobranza');
        }
    }

    public function mostrarUsuariosGeneracion(){
        if ($this->is_ajax()){
            if (isset($_POST['idGeneracion'])){
                $usuarios = $this->_admin->usuariosGeneracion($_POST['idGeneracion']);
                if (!is_null($usuarios)){
                    print_r(json_encode($usuarios));
                }else{
                    echo 0;
                }
            }
        }else header('Location: /cobranza');
    }

    public function registrarGeneracion(){
        if ($this->is_ajax()) {
            if ($_POST['generacion'] && $_POST['generacion'] != ""){
                $resultado = $this->_admin->registrarGeneracion($_POST);
                if ($resultado == 1){
                    $this->notificacion['tipo_mensaje'] = 'success';
                    $this->notificacion['mensaje'] = "La generación se ha registrado exitosamente";
                    print_r(json_encode($this->notificacion));
                }else {
                    $this->notificacion['tipo_mensaje'] = 'danger';
                    $this->notificacion['mensaje'] = "Ocurrio un error al registrar la generación";
                    print_r(json_encode($this->notificacion));
                }
            }
        }else{
            header('Location: /cobranza');
        }
    }

    public function cambiarNombreGeneracion(){
        if ($this->is_ajax()) {
            if ($_POST['idGeneracion'] && $_POST['idGeneracion'] != ""){
                $resultado = $this->_admin->cambiarNombreGeneracion($_POST);
                $this->notificacion['idGen'] = $_POST['idGeneracion'];
                $this->notificacion['nomGen'] = $_POST['nombreGeneracion'];
                $this->notificacion['result'] = $resultado;
                print_r(json_encode($this->notificacion));
            }
        }else{
            header('Location: /cobranza');
        }
    }

    public function mostrarDatosUsuario(){
        if ($this->is_ajax()){
            if (isset($_POST['idUsuario'])){
                $cont = 0;
                $infoPago = $this->_admin->getInformacionPago($_POST['idUsuario']);
                if (!is_null($infoPago)){
                    foreach ($infoPago as $info){
                        $infoPago[$cont]['fecha_ingreso'] = $this->convertirFecha($info['fecha_ingreso']);
                        $cont++;
                    }
                    print_r(json_encode($infoPago));
                }else{
                    $this->notificacion['tipo_mensaje'] = "warning";
                    $this->notificacion['mensaje'] = "No hay corridas generadas para el usuario";
                    print_r(json_encode($this->notificacion));
                }
            }
        }else header('Location: /cobranza');
    }

    public  function  mostrarCorrida(){
        if ($this->is_ajax()){
            $cont = 0;
            $infoCorrida = $this->_admin->getInformacionCorrida($_POST['id_informacion_pago']);
            if ($infoCorrida != null){
                foreach ($infoCorrida as $pago){
                    #Verifica si la fecha es del formato 'YYYY-MM-DD' para convertirla
                    $temp = explode('-', $pago['fecha_pago']);
                    if (strlen($temp[0]) == 4)
                        $infoCorrida[$cont]['fecha_pago'] = $this->convertirFecha($pago['fecha_pago']);
                    $cont++;
                }
            }
            print_r(json_encode($infoCorrida));
        }
    }

    public function actualizarPagoUsuario(){
        if ($this->is_ajax()){
            if ($_POST['datosPago']){


                $datos = json_decode($_POST['datosPago']);
                $datosPago = get_object_vars($datos[0]);


                if ($datosPago['id_pago'] != "0"){

                    //print_r("entroooo");
                    //print_r($this->_admin->actualizarPago($datosPago));

                    $result = $this->_admin->actualizarPago($datosPago);
                    if ($result == 1 ){ //modificado
                        $monto_pendiente = $this->_admin->getMontoPendiente($datosPago['id_informacion_pago']);
                        $monto_pendiente = $this->sanitizarCantidad($monto_pendiente);
                        $cantidad = $this->sanitizarCantidad($datosPago['cantidad']);
                        if (isset($datosPago['descuento']) && $datosPago['status'] == 'acreditado'){
                            //$cantidad += $datosPago['descuento'];
                            $datosPago['total'] = money_format('%!n',$monto_pendiente - $cantidad);
                        }else if ($datosPago['status'] == 'acreditado' || $datosPago['status'] == 'parcial')
                            $datosPago['total'] = money_format('%!n',$monto_pendiente - $cantidad);
                        else if ($datosPago['status'] == 'aplazado' || $datosPago['status'] == 'pendiente')
                            $datosPago['total'] = money_format('%!n',$monto_pendiente);
                        else
                            $datosPago['total'] = money_format('%!n',$monto_pendiente);
                        $result = $this->_admin->actualizarMonto($datosPago); //modificado
                        $this->notificacion['tipo_mensaje'] = 'success';
                        $this->notificacion['mensaje'] = "El pago se ha modificado correctamente";
                        print_r(json_encode($this->notificacion));
                    }else{
                        #Si no se encontro el id del pago se registra
                        $this->notificacion['tipo_mensaje'] = 'warning';
                        $this->notificacion['mensaje'] = "No se realizó ningún cambio";
                        print_r(json_encode($this->notificacion));
                    }
                }else{
                    $datosPago['id_informacion_pago'] = $id_pago['id_info_pago'];
                    $result = $this->_admin->registrarPagos($datosPago);
                    if ($result == 1){
                        $this->notificacion['tipo_mensaje'] = 'success';
                        $this->notificacion['mensaje'] = "Nuevo pago registrado correctamente";
                        print_r(json_encode($this->notificacion));
                    }else{
                        $this->notificacion['tipo_mensaje'] = 'warning';
                        $this->notificacion['mensaje'] = "Ocurrio un error al registrar el pago";
                        print_r(json_encode($this->notificacion));
                    }
                }
                #se actualiza el estatus de usuario
                $this->actualizaStatusUsuario($datosPago['id_informacion_pago']);
            }
        }else header('Location: /cobranza');
    }

    public function habilitar_usuario(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $result = $this->_admin->getUsuariosInactivos();
            if ($result != 0){
                $this->_view->usuarios = $result;
            }
            $this->_view->setCss(['corrida','habilitarUsuario']);
            $this->_view->setJs(['habilitarUsuario']);
            $this->_view->rendering('habilitar_usuario',true);
        }else header('Location: /cobranza');
    }

    public function deshabilitar_usuario(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $result = $this->_admin->getUsuariosActivos();
            if ($result != 0){
                $this->_view->usuarios = $result;
            }
            $this->_view->setCss(['corrida','deshabilitarUsuario']);
            $this->_view->setJs(['deshabilitarUsuario']);
            $this->_view->rendering('deshabilitar_user',true);
        }else header('Location: /cobranza');
    }

    public function buscarUsuario(){
        if ($this->is_ajax()){
            if (isset($_POST['termino'])){
                #Busca al usuario en la BD
                $result = $this->_admin->buscarUsuario($_POST);
                if ($result != 0){
                    print_r(json_encode($result));
                }else{
                    echo 0;
                }
            }
        }else header('Location: /cobranza');
    }

    public function verificarInfoCorrida(){
        if ($this->is_ajax()){
            #Si encontró el usuario, se verifica si tiene corrida generada
            $corrida = $this->_admin->getInformacionPago($_POST["id_usuario"]);
            if (!is_null($corrida))
                print_r(json_encode(end($corrida))); //Se obtiene el ultimo registro
            else
                echo 0;
        }
    }

    public function activarUsuario(){
        if ($this->is_ajax()){
            if (isset($_POST['id_usuario'])){
                $result = $this->_admin->activarUsuario($_POST['id_usuario']);
                if ($result == 1){
                    $this->notificacion['tipo_mensaje'] = 'success';
                    $this->notificacion['mensaje'] = "Se habilito exitosamente el usuario";
                    print_r(json_encode($this->notificacion));
                }else{
                    $this->notificacion['tipo_mensaje'] = 'danger';
                    $this->notificacion['mensaje'] = "Ocurrio un error al habilitar al usuario";
                    print_r(json_encode($this->notificacion));
                }
            }
        }else header('Location: /cobranza');
    }

    public function desactivarUsuario(){
        if ($this->is_ajax()){
            if (isset($_POST['id_usuario'])){
                $result = $this->_admin->desactivarUsuario($_POST['id_usuario']);
                if ($result == 1){
                    $this->notificacion['tipo_mensaje'] = 'success';
                    $this->notificacion['mensaje'] = "Se deshabilito exitosamente el usuario";
                    print_r(json_encode($this->notificacion));
                }else{
                    $this->notificacion['tipo_mensaje'] = 'danger';
                    $this->notificacion['mensaje'] = "Ocurrio un error al deshabilitar el usuario";
                    print_r(json_encode($this->notificacion));
                }
            }
        }else header('Location: /cobranza');
    }

    public function accesos(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $result = $this->_admin->getAccesos();
            if ($result != 0){
                $this->_view->accesos = $result;
            }
            $this->_view->setCss(['corrida']);
            $this->_view->setJs(['accesos']);
            $this->_view->rendering('accesos',true);
        }else header('Location: /cobranza');
    }

    public function logout(){
        session_destroy();
        header('Location: /cobranza');
    }

    public function notificarPago(){
        $fecha_actual = new DateTime("now");
        $fechas_pago = $this->_admin->getFechas();
        if (!is_null($fechas_pago)){
            foreach ($fechas_pago as $clave => $valor){
                //convertir la fecha
                $fecha_convertida = $this->restablecerFecha($valor['fecha_pago']);
                $fecha_cliente = new DateTime($fecha_convertida);
                $diferencia = $fecha_actual->diff($fecha_cliente);
                if ($diferencia->days == 4 && $diferencia->m == 0 && $diferencia->invert == 0){
                    $info_pago = $this->_admin->getInfoPago($valor['id_informacion_pago'], $valor['fecha_pago']);
                    if ($info_pago != 0){
                        $descripcion = "El cliente: ".$info_pago['nombre']." tiene un pago proximo en la fecha: ".$info_pago['fecha_pago'];
                        $notificacion = [
                            "descripcion" => $descripcion,
                            "fecha_notificacion" => date('Y-m-d H:i:s'),
                            "id_usuario" => 1
                        ];
                        $this->_admin->insertarNotificacion($notificacion);
                        #Se envian las notificaciones por correo
                        $this->getLibrary("PHPMailer/Mailer");
                        $mail = new Mailer();
                        $respuestaMail = $mail->enviar_notificacion_pago($info_pago);
                        if (!$respuestaMail){
                            #Se guarda en un log el error
                            error_log("¡No se pudo enviar la notificación de pago por correo!", 0);
                        }
                    }else{
                        error_log("¡Ocurrio un error al insertar la notificación en la BD!", 0);
                    }
                }
            }
        }
    }

    public function getNotificaciones(){
        $notificaciones = $this->_admin->getNotificaciones();
        if (!is_null($notificaciones)){
            return $notificaciones;
        }else return 0;
    }

    public function notificaciones(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $result = $this->_admin->getNotificaciones();
            if (!is_null($result)){
                $this->_view->notificaciones = $result;
            }
            $this->_view->setCss(['corrida', 'notificaciones']);
            $this->_view->setJs(['notificaciones']);
            $this->_view->rendering('notificaciones',true);
        }else header('Location: /cobranza');
    }

    public function actualizarVistoNotificacion(){
        if ($this->is_ajax()){
            $result = $this->_admin->actualizarVistoNotificacion($_POST['id_notificacion']);
            if ($result == 1){
                $this->notificacion['mensaje'] = "Estado de notificacion actualizado";
                print_r(json_encode($this->notificacion));
            }else{
                $this->notificacion['tipo_mensaje'] = "warning";
                $this->notificacion['mensaje'] = "No se  pudo actualizar la notificación";
                print_r(json_encode($this->notificacion));
            }
        }
    }

    public function generarRecibo(){
        $datospago = json_decode($_POST['datos']);
        $datospago = get_object_vars($datospago);
        $info = $this->_admin->getInfoPagoRecibo($datospago['id_pago']);
        $num_pagos = $this->_admin->contarPagos($datospago['id_info_pago']);
        $cantidad = $this->sanitizarCantidad($datospago['cantidad']);
        $porcentaje = $datospago['descuento_porcentaje'];
        $descuento = ($datospago['descuento_cantidad'] !=0) ? $this->sanitizarCantidad($datospago['descuento_cantidad']) : "";
        $status = $datospago['status'];
        $monto_pagar = $this->sanitizarCantidad($info['monto_pagar']);
        $cantidad_con_descuento  = ($descuento != 0) ? money_format('%(#2n', $cantidad-$descuento) : "";
        $monto_pendiente = ($datospago['status']=='pendiente') ? $this->sanitizarCantidad($info['monto_pendiente']) : ($this->sanitizarCantidad($info['monto_pendiente']) + $cantidad);
        $monto_sin_cantidad = ($datospago['status']=='pendiente')? $this->sanitizarCantidad($monto_pendiente) - $this->sanitizarCantidad($cantidad) : $this->sanitizarCantidad($info['monto_pendiente']);
        $mensaje_descuento = ($descuento != 0) ? "Pago con Descuento Aplicado" : "";
        $mensaje_porcentaje = ($descuento != 0) ? $porcentaje.'% Descuento promocional' : "";
        $descuento = ($descuento != 0) ? money_format('%(#2n', $descuento) : $descuento;
        $fecha_pago = $this->convertirFecha(date("Y-m-d"));
        $data = [
            "id_usuario" => $info['id_usuario'],
            "num_pago" => substr($info['no_pago'], 0, 2),
            "fecha_pago" => $fecha_pago,
            "nombre" => $info['nombre'],
            "cantidad" => money_format('%(#2n', $cantidad),
            "num_pagos" => $num_pagos['num_pagos'],
            "descuento" => $descuento,
            "porcentaje" => $mensaje_porcentaje,
            "cantidad_con_descuento" => $cantidad_con_descuento,
            "mensaje_descuento" => $mensaje_descuento,
            "deuda_previa" => money_format('%(#2n', $monto_pendiente),
            "deuda_restante" => money_format('%(#2n', $monto_sin_cantidad),
            "sistema" => $_SESSION['sistema']
        ];
        print_r(json_encode($data));
    }

    public function guardarPago(){
        if ($this->is_ajax()){
            $datospago = json_decode($_POST['datos_pago']);
            $datospago = get_object_vars($datospago);
            $cantidad = $this->sanitizarCantidad($datospago['cantidad']);
            $result1 = $this->_admin->registrarPagos($datospago);
            $monto_pendiente = $this->_admin->getMontoPendiente($datospago['id_informacion_pago']);
            $monto_pendiente = $this->sanitizarCantidad($monto_pendiente)+$cantidad;
            $monto_pendiente = money_format('%!n',$monto_pendiente);
            if ($datospago['solicitud'] != 'parcial' && $datospago['status'] != 'pendiente'){
                //Actualiza el monto pendiente
                $result2 = $this->_admin->actualizarMonto(['id_informacion_pago' => $datospago['id_informacion_pago'], 'total' => $monto_pendiente]);
                if ($result1 == 1 && $result2 == 1){
                    $this->notificacion['tipo_mensaje'] = "success";
                    $this->notificacion['mensaje'] = "Pago registrado corretamente!!";
                    print_r(json_encode($this->notificacion));
                }else{
                    $this->notificacion['tipo_mensaje'] = "warning";
                    $this->notificacion['mensaje'] = "Error al registrar el pago!!";
                    print_r(json_encode($this->notificacion));
                }
            }elseif ($datospago['solicitud'] != 'parcial' && $datospago['solicitud'] != 'aplazado'){ //Caso para cuando es un nuevo pago
                $montoPagar = $this->_admin->getMontoPagar($datospago['id_informacion_pago']);
                if (!is_null($montoPagar))
                    $montoPagar = $this->sanitizarCantidad($montoPagar)+$cantidad;
                $result3 = $this->_admin->actualizarMontoPagar(["id_informacion_pago" => $datospago['id_informacion_pago'],"total_pago" => money_format("%!n",$montoPagar)]);
                //Actualiza el monto pendiente
                $result2 = $this->_admin->actualizarMonto(['id_informacion_pago' => $datospago['id_informacion_pago'], 'total' => $monto_pendiente]);
                if ($result1 == 1 && $result3 == 1 && $result2 == 1){
                    $this->notificacion['tipo_mensaje'] = "success";
                    $this->notificacion['mensaje'] = "Pago registrado corretamente!!";
                    print_r(json_encode($this->notificacion));
                }else{
                    $this->notificacion['tipo_mensaje'] = "warning";
                    $this->notificacion['mensaje'] = "Error al registrar el pago!!";
                    print_r(json_encode($this->notificacion));
                }
            }elseif ($datospago['solicitud'] == 'nuevo'){
                $result2 = $this->_admin->actualizarMonto(['id_informacion_pago' => $datospago['id_informacion_pago'], 'total' => $monto_pendiente]);
                if ($result1 && $result2){
                    $this->notificacion['tipo_mensaje'] = "success";
                    $this->notificacion['mensaje'] = "Pago registrado corretamente!!";
                    print_r(json_encode($this->notificacion));
                }
            }elseif ($result1 == 1){
                $this->notificacion['tipo_mensaje'] = "success";
                $this->notificacion['mensaje'] = "Pago registrado corretamente!!";
                print_r(json_encode($this->notificacion));
            }else{
                $this->notificacion['tipo_mensaje'] = "warning";
                $this->notificacion['mensaje'] = "Error al registrar el pago!!";
                print_r(json_encode($this->notificacion));
            }
        }
    }

    public function actualizarPagosAuto(){
        if ($this->is_ajax()){
            $datos_pago = [];
            $datospago = json_decode($_POST['datosPago']);
            foreach ($datospago as $pago){
                $datos_pago = get_object_vars($pago);
                if (!isset($datos_pago['total']) && !isset($datos_pago['id_informacion_pado']) && !isset($datos_pago['monto_nuevo']) && !isset($datos_pago['total_pago']) )
                    $this->_admin->actualizarPagoAuto($datos_pago);
            }
            $datos_pago = get_object_vars(end($datospago));
            $datos_pago['total_pago'] = $datos_pago['total'];
            $this->_admin->actualizarMonto($datos_pago);
            $this->_admin->actualizarMontoPercibir($datos_pago);

            $this->_admin->actualizarMontoPagar($datos_pago);
            //$datospago = get_object_vars($datospago);
            $this->notificacion['tipo_mensaje'] = "success";
            $this->notificacion['mensaje'] = "Actualizasación realizada correctameente";
            print_r(json_encode($this->notificacion));
        }
    }

    public function eliminarPago(){
        if ($this->is_ajax()){
            $this->_admin->eliminarPago($_POST['id_pago']);
            $_POST['total'] = number_format((float)$_POST['total'], 2, '.', '');
            if ($_POST['status'] == 'pendiente'){
                $_POST['total_pago'] = number_format($_POST['total'],2,'.',',');
                $_POST['total'] = number_format($_POST['monto_pendiente'] - $_POST['cantidad'],2,'.', ',');
                $this->_admin->actualizarMonto($_POST);
                $this->_admin->actualizarMontoPagar($_POST);
            }
            else{
                $_POST['total_pago'] = number_format($_POST['total'],2,'.',',');
                $this->_admin->actualizarMontoPagar($_POST);
            }
            $this->notificacion['tipo_mensaje'] = 'success';
            $this->notificacion['mensaje'] = 'Pago eliminado exitosamente';
            print_r(json_encode($this->notificacion));
        }
    }

    public function verReciboParcial(){
        $id_usuario = $this->_admin->retornaInfoPago($_GET['id_info']);
        $id_usuario = $id_usuario['id_usuario'];
        $nombre_fichero = ROOT.'files/usuario_'.$id_usuario.'/Recibo Pago Parcial'.$_GET['id_pago'].'.pdf';
        if (!file_exists($nombre_fichero)) {
            $pdf = $this->generarReciboPagoParcial();
            $pdf->Output($nombre_fichero, 'F');
        }
        $respuesta = BASE_URL."files/usuario_".$id_usuario."/Recibo Pago Parcial".$_GET['id_pago'].'.pdf';
        print_r($respuesta);
    }

    public function generarReciboPagoParcial(){
        $pagos = $this->_admin->getInformacionCorrida($_GET['id_info']);
        $infoCliente = $this->_admin->getInfoPagoRecibo($_GET['id_pago']);
        $numPago = substr($infoCliente['no_pago'], 0,2);
        $numPago =  intval($numPago);
        $totalPagos = $this->_admin->contarPagos($_GET['id_info']);
        $pagosRestantes = intval($totalPagos['num_pagos']) - intval($numPago);
        $cliente = $infoCliente['nombre'];
        $arrayNombre = explode(" ", $cliente);
        $iniciales = "";
        for ($i=0; $i< count($arrayNombre); $i++){
            $iniciales .= substr($arrayNombre[$i],0,1);
        }
        $folio = "TR".date('y').date('m').date('d')."-".$iniciales;
        if (!is_null($pagos)){
            $this->getLibrary("tcpdf/tcpdf");
            $this->getLibrary("tcpdf/MYPDF");
            $this->getLibrary("PHPMailer/Mailer");
            // create new PDF document
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator('TR network');
            $pdf->SetAuthor('TR network');
            $pdf->SetTitle('Recibo Pago Parcial');
            $pdf->SetSubject('Sistema de cobranza');
            $pdf->SetKeywords('cobranza, PDF, recibo, parcial');
            $pdf->setPrintFooter(false);
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            // set font
            $pdf->SetFont('times', 'BI', 12);
            // add a page
            $pdf->AddPage();
            $pdf->Ln(25);
            // set cell padding
            $pdf->setCellPaddings(1, 1, 1, 1);
            $pdf->SetFont('helvetica', '', 10);
            $txt = <<<EOT
Blvd. San Felipe 224
Puebla, Puebla 72040
Teléfono: (01) 222.8.89.56.08
dcobranza@trnetwork.com.mx
EOT;
            // Multicell test
            $pdf->MultiCell(65, 5, $txt, 1, 'L', 0, 0, 15, 40, true);

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->writeHTMLCell(30, 0, 125, '', 'Fecha', 0, 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(30, 0, 125, '', 'Pago #', 0, 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(30, 0, 125, '', 'Folio Recibo', 0, 1, 0, true, 'R', true);
            $pdf->SetY($pdf->GetY()-20);

            $pdf->SetFont('helvetica', '', 10);
            $fecha = $this->convertirFecha(date('d-m-Y'));
            $pdf->writeHTMLCell(35, 0, 160, '', $fecha, 'LRTB', 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(35, 0, 160, '', $numPago, 'LRTB', 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(35, 0, 160, '', '<p style="color: red">'.$folio.'</p>', 'LRTB', 1, 0, true, 'R', true);

            $pdf->SetFont('helvetica', 'B', 10);
            $html = "<p>Cliente:&nbsp;&nbsp;&nbsp;&nbsp; ".$cliente."</p><p>Activo</p>";
            $pdf->SetFillColor(69,77,85);

            $pdf->writeHTMLCell(180, 0, 15, $pdf->GetY()+5, '<p style="color: white">Recibo pago a favor de:</p>', 'LRT', 1, 1, true, 'L', true);
            $pdf->writeHTMLCell(180, 20, 15, '', $html, 'LRB', 1, 0, true, 'C', true);

            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, 15);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetY($pdf->GetY()+5);
            $pdf->SetX(15);

            $tbody = "";
            $cont = 1;
            foreach ($pagos as $pago){
                $descuento_y_porcentaje = ($pago['descuento_porcentaje'] != 0) ? $pago['descuento_porcentaje']."%   ".money_format('%n',$pago['descuento_cantidad']) : "N/A";
                $celda = ($cont % 2 === 0) ? "par" : "inpar";
                $cantidad = 0;
                /*if ($pago['descuento_cantidad'] != "0"){
                    $cantidad = floatval(str_replace(',','', $pago['cantidad']));
                    $cantidad += floatval($pago['descuento_cantidad']);
                    $cantidad = money_format('%n',$cantidad);
                    $cantidad = substr($cantidad,1);
                }else{
                    $cantidad = $pago['cantidad'];
                }*/
                $tbody .= '<tr class="'.$celda.'">
                            <td>
                                '.$pago['no_pago'].'
                            </td>
                            <td>
                                '.$pago['fecha_pago'].'
                            </td>
                            <td>
                                '.$pago['forma_pago'].'
                            </td>
                            <td>
                                $ '.$pago['cantidad'].'
                            </td>                
                            <td class="'.$pago['status'].'">
                                '.$pago['status'].'
                            </td>
                            <td>
                                '.$descuento_y_porcentaje.'
                            </td>
                            </tr>';
                $cont ++;
            }
            $tbody .= '<tr><td><strong>Total</strong></td><td></td><td></td><td><strong>$ '.$infoCliente['monto_pagar'].'</strong></td><td></td><td></td></tr>';

            $html = '<style>.acreditado{background-color: #aee1a8; color: #FFFFFF} .pendiente{background-color: orange; color: #FFFFFF}.aplazado{background-color: red; color: #ffffff}.parcial{background-color: lightgray; color: #FFFFFF}
                    td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}
.par{ background-color: #d0d0d0}
</style>
                     <table cellpadding="4">
                    <tr style="background-color: #454d55; color: #FFFFFF;">
                        <th>Cantidad</th>
                        <th>Fecha de pago</th>
                        <th>Descripción</th>
                        <th>Cantidad Pagada</th>
                        <th>Status</th>
                        <th>Decuento aplicado</th>
                    </tr>' .$tbody.'
                    </table>';

            $pdf->writeHTML($html, true, false, true, false, '');
            if (intval($totalPagos['num_pagos']) > 13){
                $pdf->AddPage();
                $y = 40;
            }else
                $y = '';
            $txt = <<<EOD
La reproducción apocrifa de este comprobante
constituye un delito en  los térmnos de las
disposiciones fiscales. Si tiene alguna duda sobre
este recibo de pago, póngase en contacto con Lic.
Erike Valverde a, dcobranza@trnetwork.com.mx los
intereses moratorios sonn del 8% sobre la deuda
actual más la penalización de $9,000.00 (Nueve mil
pesos 00/100 M.N.) por atrazo en pagos.
Gracias por su pago
EOD;
            $pdf->SetFillColor(246,196,164);
            $pdf->SetFont('helvetica', '', 7);
            // Multicell test
            $pdf->MultiCell(60, 5, $txt, 1, 'L', 1, 0, 15, $y, true);

            //Imagén del codigo QR
            $pdf->Image(ROOT.'views/img/cw-qr.png', $pdf->GetX()+1, $pdf->GetY()-5, 40, 40, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);

            $pdf->SetFont('helvetica', 'B', 8);

            $pdf->writeHTMLCell(40, 0, 105, '', 'Pagos a Crédito', 0, 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(40, 0, 105, '', 'Número de pago', 0, 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(40, 0, 105, '', 'Saldo pendiente', 0, 1, 0, true, 'R', true);

            $pdf->SetFont('helvetica', '', 8);

            $pdf->writeHTMLCell(20, 0, 145, $pdf->GetY()-17, $pagosRestantes, 'LTR', 1, 1, true, 'R', true);
            $pdf->writeHTMLCell(20, 0, 145, $pdf->GetY(), $numPago, 'LR', 1, 1, true, 'R', true);
            $pdf->SetFillColor(144,198,224);
            $pdf->writeHTMLCell(20, 0, 145, $pdf->GetY(), '<strong>$ '.$infoCliente['monto_pendiente'].'</strong>', 'LBR', 1, 1, true, 'R', true);

            $txt = <<<EOD
Este documento solo 
respalda los pagos de 
status Acreditados no
le justifica de adeudos
posteriores. 
EOD;
            $pdf->SetFont('helvetica', '', 6);
            $pdf->SetFillColor(234,229,147);

            // Multicell test
            $pdf->MultiCell(25, 5, $txt, 1, 'L', 1, 0, 170, $pdf->GetY()-15, true);

            // Limpiamos la salida del búfer y lo desactivamos
            ob_end_clean();

            //Close and output PDF document
            //$pdf->Output('Recibo Pago Parcial '.$numPago.'.pdf', 'I');
            //$rutaReciboParcial = ROOT.'files/usuario_'.$infoCliente['id_usuario'].'/Recibo Pago Parcial'.$numPago.'.pdf';
            //Close and save PDF document
            //$pdf->Output($rutaReciboParcial, 'F');
            return $pdf;
        }

    }

    public  function enviarReciboParcial(){
        $this->getLibrary("PHPMailer/Mailer");
        $infoCliente = $this->_admin->getInfoPagoRecibo($_GET['id_pago']);
        $numPago = substr($infoCliente['no_pago'], 0,2);
        $numPago =  intval($numPago);
        $id_usuario = $infoCliente['id_usuario'];
        $rutaReciboParcial = ROOT.'files/usuario_'.$id_usuario.'/Recibo Pago Parcial'.$_GET['id_pago'].'.pdf';
        if (file_exists($rutaReciboParcial)) {
            #Se envia por correo
            $info = [
                "email_usuario" => $infoCliente['email_usuario'],
                "nombre_usuario" => $infoCliente['nombre'],
                "ruta_recibo" => $rutaReciboParcial,
                "nombre_recibo" => "Recibo Pago Parcial".$numPago
            ];
            $mail = new Mailer();
            $respuestaMail = $mail->enviar_recibo_parcial($info);
            if ($respuestaMail)
                echo "Recibo enviado satisfactoreamente";
            else
                echo "Error: No se pudo enviar el recibo!!";
        }else
            echo "Error: ¡No ha generado el Recibo!";
    }

    public function money_format($format, $number)
    {
        $regex  = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?'.
            '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
        if (setlocale(LC_MONETARY, 0) == 'C') {
            setlocale(LC_MONETARY, '');
        }
        $locale = localeconv();
        preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
        foreach ($matches as $fmatch) {
            $value = floatval($number);
            $flags = array(
                'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ?
                    $match[1] : ' ',
                'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
                'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
                    $match[0] : '+',
                'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
                'isleft'    => preg_match('/\-/', $fmatch[1]) > 0
            );
            $width      = trim($fmatch[2]) ? (int)$fmatch[2] : 0;
            $left       = trim($fmatch[3]) ? (int)$fmatch[3] : 0;
            $right      = trim($fmatch[4]) ? (int)$fmatch[4] : $locale['int_frac_digits'];
            $conversion = $fmatch[5];

            $positive = true;
            if ($value < 0) {
                $positive = false;
                $value  *= -1;
            }
            $letter = $positive ? 'p' : 'n';

            $prefix = $suffix = $cprefix = $csuffix = $signal = '';

            $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
            switch (true) {
                case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
                    $prefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
                    $suffix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
                    $cprefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
                    $csuffix = $signal;
                    break;
                case $flags['usesignal'] == '(':
                case $locale["{$letter}_sign_posn"] == 0:
                    $prefix = '(';
                    $suffix = ')';
                    break;
            }
            if (!$flags['nosimbol']) {
                $currency = $cprefix .
                    ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
                    $csuffix;
            } else {
                $currency = '';
            }
            $space  = $locale["{$letter}_sep_by_space"] ? ' ' : '';

            $value = number_format($value, $right, $locale['mon_decimal_point'],
                $flags['nogroup'] ? '' : $locale['mon_thousands_sep']);
            $value = @explode($locale['mon_decimal_point'], $value);

            $n = strlen($prefix) + strlen($currency) + strlen($value[0]);
            if ($left > 0 && $left > $n) {
                $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
            }
            $value = implode($locale['mon_decimal_point'], $value);
            if ($locale["{$letter}_cs_precedes"]) {
                $value = $prefix . $currency . $space . $value . $suffix;
            } else {
                $value = $prefix . $value . $space . $currency . $suffix;
            }
            if ($width > 0) {
                $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
                    STR_PAD_RIGHT : STR_PAD_LEFT);
            }

            $format = str_replace($fmatch[0], $value, $format);
        }
        return $format;
    }

    public  function verReporteCorrida(){
        $id_usuario = $this->_admin->retornaInfoPago($_GET['id_info']);
        $id_usuario = $id_usuario['id_usuario'];
        $nombre_fichero = ROOT.'files/usuario_'.$id_usuario.'/Reporte Corrida de Pagos'.$_GET['id_info'].'.pdf';
        if (!file_exists($nombre_fichero)) {
            $pdf = $this->generarPdfCorrida();
            $pdf->Output($nombre_fichero, 'F');
        }
        $respuesta = BASE_URL."files/usuario_".$id_usuario."/Reporte Corrida de Pagos".$_GET['id_info'].'.pdf';
        print_r($respuesta);
    }

    public function enviarReporteCorrida(){
        $pagos = $this->_admin->getInformacionCorrida($_GET['id_info']);
        $infoCliente = $this->_admin->getInfoPagoRecibo($pagos[0]['id_pago']);
        $this->getLibrary("tcpdf/tcpdf");
        $this->getLibrary("PHPMailer/Mailer");
        #$pdf = $this->generarPdfCorrida();
        //Close and output PDF document
        $rutaReporteCorrida = ROOT.'files/usuario_'.$infoCliente['id_usuario'].'/Reporte Corrida de Pagos'.$_GET['id_info'].'.pdf';
        $rutaPresentacion = ROOT.'files/usuario_'.$infoCliente['id_usuario'].'/Presentacion.pdf';
        $pdf = $this->generarPresentacion($infoCliente);
        $pdf->Output($rutaPresentacion, 'F');
        if (file_exists($rutaReporteCorrida) && file_exists($rutaPresentacion)){
            #Se envia por correo
            $info = [
                "email_usuario" => $infoCliente['email_usuario'],
                "nombre_usuario" => $infoCliente['nombre'],
                "ruta_presentacion" => $rutaPresentacion,
                "ruta_reporte" => $rutaReporteCorrida
            ];
            $mail = new Mailer();
            $respuestaMail = $mail->enviar_reporte_corrida($info);
            if ($respuestaMail)
                echo "Reporte enviado satisfactoreamente";
            else
                echo "Error: No se pudo enviar el reporte!!";
        }else{
            echo "Error: ¡No ha generado el Recibo!";
        }
    }

    public function generarPresentacion($data){
        $this->getLibrary("tcpdf/tcpdf");
        $this->getLibrary("tcpdf/pdf_carta");
        // create new PDF document
        $pdf = new pdf_carta(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator('TR network');
        $pdf->SetAuthor('TR network');
        $pdf->SetTitle('Presentacion');
        $pdf->SetSubject('Sistema de cobranza');
        $pdf->SetKeywords('cobranza, PDF, presentacion, juridico');
        $pdf->setPrintHeader(false);
        //$pdf->setPrintFooter(false);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // set font
        $pdf->SetFont('times', '', 12);
        // add a page
        $pdf->AddPage();
        $pdf->Ln(25);

        $logo = ROOT."views/img/valverde.jpg";
        $pdf->Image($logo,30,20,40);

        $logo = ROOT."views/img/trnetwork.png";
        $pdf->Image($logo,140,25,40);

        setlocale(LC_TIME, 'es_ES.UTF-8');
        date_default_timezone_set ('Europe/Madrid');
        $date = strftime("%d DE %B DE %Y", strtotime(date("d-m-Y")));

        $fecha = 'PUEBLA, PUEBLA '.strtoupper($date);

        $pdf->Text(100,50,$fecha);

        $pdf->SetFont('times', 'I', 13);

        $nombre = $data['nombre'];

        $html = '<p><b>'.$nombre.'</b></p>
                 <p>Presente</p>
                 <p>Apreciado</p>
                 <p align="justify"><b>Valverde Jurídico</b> le envía un afectuoso saludo,
                  así mismo le hacemos de su conocimiento que a partir
                   de ahora nuestro despacho jurídico en conjunto con 
                   <b>TR Network</b> le acompañará y dará seguimiento en su 
                   temporalidad de retribución a TR network, mismo que 
                   confió en usted, en su capacidad intelectual y honorabilidad,
                    otorgándole una beca, un crédito de capacitación, con éxito
                     laboral para usted que enorgullece a esta institución.</p>
                     <p></p>
                 <p align="justify">El curso de capacitación que TR network le otorgó a usted fue diseñado 
                 por expertos y aplicado por profesionales, todo lo que tiene un costo económico
                  importante. Además de estos costos operativos, el financiamiento que se le otorgo
                   tiene un costo que absorbe al cien por ciento TR network, solicitando créditos a
                    instituciones crediticias y programas similares; siendo esta la forma que tiene
                     para poder operar.</p>
                 <p></p>    
                 <p align="justify">Por todo ello es que le invitamos a que cubra puntualmente con los pagos que le han sido
                  programados conforme a lo convenido, ya que así, además de cumplir legalmente con lo contratado,
                   usted estará contribuyendo a que este programa de capacitación continúe y beneficie como a usted,
                    a otras personas.</p>
                 <p></p>
                 <p>Creemos que hacer el bien, produce un bien social y colectivo.</p>

<p align="justify">Sin más por el momento nos despedimos de usted reiterándole que en lo que en nuestras manos este como despacho jurídico 
estaremos prestos al buen desempeño de sus retribuciones, así mismo le hacemos saber que TR network esta muy orgulloso de que
 usted forme parte de esta gran familia de profesionales que es TR network.</p>

<p>Anexo a esta carta recibirá su corrida de pagos a detalle.</p>';

        $pdf->writeHTMLCell(160, 0, 30, 60, $html, '', 1, 0, true, 'L', true);

        // Limpiamos la salida del búfer y lo desactivamos
        ob_end_clean();

        //Close and output PDF document
        #$pdf->Output('carta.pdf', 'I');
        return $pdf;
    }

    public function generarPdfCorrida(){
        $pagos = $this->_admin->getInformacionCorrida($_GET['id_info']);
        $infoCliente = $this->_admin->getInfoPagoRecibo($pagos[0]['id_pago']);
        $this->getLibrary("tcpdf/tcpdf");
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TR network');
        $pdf->SetTitle('Reporte de corridas de pago');
        $pdf->SetSubject('Sistema de cobranza');
        $pdf->SetKeywords('reporte, corrida, pago, informacion, guide');
        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // set margins
        $pdf->SetMargins(30, PDF_MARGIN_TOP, 10);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 5);
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
        // ---------------------------------------------------------
        $pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');
        // set font
        $pdf->SetFont('times', '', 10);
        //$pdf->setCellPaddings(50,0,0,0);
        $pdf->AddPage('L', 'A4');
        $html = '<p style="font-size: .8em;">Apreciable <strong>'.$infoCliente['nombre'].'</strong> por  este medio  nos dirigimos a usted, dando continuidad  a la carta anexa a este correo felicitándole<br>por el proceso
que acaba  de iniciar, asi mismo, le enviamos los plazos y formas de pagos a relizar a partir de la fecha de su primer pago por sus <br>servicios prestados.</p>';
        $pdf->SetY(10);
        $pdf->writeHTML($html, '', '','','','L');
        $pdf->SetY($pdf->GetY()+5);
        $html = '<p style="font-size: .8em;">Según los datos recabados anteriormente en correos e información compartida queda de la siguiente manera:<br></p>';
        $pdf->writeHTML($html,'','','','','');
        $pdf->SetFont('times', '', 8);
        $newDate = $this->convertirFecha($infoCliente['fecha_ingreso']);
        $html = '<table  cellpadding="1" border="1" style="text-align:center; width: 600px;">
                    <tr>
                        <td><strong style="color: #003eff">Consultora</strong></td>
                        <td>'.$infoCliente['consultora'].'</td>
                        <td><strong style="color: #003eff">Cliente:</strong></td>
                        <td>'.$infoCliente['cliente'].'</td>
                        <td><strong style="color: #003eff">Fecha de ingreso</strong></td>
                        <td>'.$newDate.'</td>
                    </tr>
                    <tr>
                        <td><strong style="color: #003eff">Periodo de pago</strong></td>
                        <td>'.$infoCliente['periodo_pago'].'</td>
                        <td><strong style="color: #003eff">Esquema</strong></td>
                        <td>'.$infoCliente['esquema'].'</td>
                        <td><strong style="color: #003eff">Monto a percibir</strong></td>
                        <td><strong>$ '.$infoCliente['monto_percibir'].'</strong></td>
                    </tr>
                    <tr>
                        <td><strong style="color: #003eff">Monto a Pagar:</strong></td>
                        <td><strong>$ '.$infoCliente['monto_pagar'].'</strong></td>
                        <td><strong style="color: #003eff">Período  de pago hacia TR:</strong></td>
                        <td>Quincenal</td>
                        <td><strong style="color: #003eff">Monto Pendiente:</strong></td>
                        <td><strong>$'.$infoCliente['monto_pendiente'].'</strong></td>
                    </tr>   
                </table>';

        $pdf->writeHTML($html, true,false,true,false,'');

        $subtable = '<table style="text-align: center">
                        <tr><td></td><td></td></tr>
                        <tr><td></td><td></td></tr>
                        <tr><td width="50">Nombre:</td><td>TR network SA de CV</td></tr>
                        <tr><td>Banco:</td><td>Scotiabank Inverlat</td></tr>
                        <tr><td>Cuenta</td><td>03603463931</td></tr>
                        <tr><td>Clabe</td><td>044650036034639319</td></tr>
</table>';

        $subtable2 = '<style>table[id="tabla2"] {border: 1px solid #000000; text-align: center;}</style>
                      <table id="tabla2">
                        <tr><th width="150"><b>Forma de pago Deuda total R.</b></th><th width="60"><b>Temporalid</b></th><th width="64"><b>Descuento</b></th></tr>                     
                        <tr><td>Pago en una exhibición</td><td>1er. Mes</td><td>15%</td></tr>
                        <tr><td></td><td></td><td></td></tr>
                        <tr><td>Pago en una exhibición</td><td>2do. Mes</td><td>12%</td></tr>
                        <tr><td></td><td></td><td></td></tr>
                        <tr><td>Pago en una exhibición</td><td>3er. Mes</td><td>10%</td></tr>
                        <tr><td></td><td></td><td></td></tr>
                        <tr><td>Pago en una exhibición</td><td>4to. Mes</td><td>8%</td></tr>
                        <tr><td></td><td></td><td></td></tr>
                        <tr><td>Pago en una exhibición</td><td>5to. Mes</td><td>6%</td></tr>
                        <tr><td></td><td></td><td></td></tr>
                        <tr><td>Pago en una exhibición</td><td>6to. Mes</td><td>5%</td></tr>
                      </table>';

        $cont = 0;
        foreach ($pagos as $pago){
            $cantidad = 0;
            /*if ($pago['descuento_cantidad'] != "0"){
                $cantidad = floatval(str_replace(',','', $pago['cantidad']));
                $cantidad += floatval($pago['descuento_cantidad']);
                $cantidad = money_format('%!n',$cantidad);
            }else{
                $cantidad = $pago['cantidad'];
            }*/
            $cantidad = $pago['cantidad'];
            if ($cont === 0)
                $tbody .= '<tr>
                            <td>
                                '.$pago['no_pago']. '
                            </td>
                            <td style="background-color: #3ee611">
                                ' .$pago['fecha_pago']. '
                            </td>                   
                            <td style="background-color: #41a5da">
                                $ ' .$cantidad. '
                            </td>                
                            <td class="status_'.$pago['status'].'">
                                ' .$pago['status'].'
                            </td>
                            <td rowspan="12">
                                '.$subtable.'
                            </td>
                            <td rowspan="12">
                                '.$subtable2.'
                            </td>                         
                            </tr>';
            else
                $tbody .= '<tr>
                            <td>
                                '.$pago['no_pago']. '
                            </td>
                            <td style="background-color: #3ee611">
                                ' .$pago['fecha_pago'].'
                            </td>                   
                            <td style="background-color: #41a5da">
                                $ '.$cantidad. '
                            </td>                
                            <td class="status_'.$pago['status'].'">
                                ' .$pago['status'].'
                            </td>                                                 
                            </tr>';
            $cont++;
        }

        $tbody .= '<tr><td></td><td><strong>Total</strong></td><td><strong>$ '.$infoCliente['monto_pagar'].'</strong></td><td></td><td></td><td></td></tr>';

        $html = '<style>.status_pendiente{background-color: #f6c4a4}.status_acreditado{background-color: #aee1a8; color: white}.status_aplazado{background-color: red; color: white}.status_parcial{background-color: lightgray; color: white}</style>
                    <table>
                    <tr style="background-color: #454d55; color: #FFFFFF;">
                        <th width="50" style="text-align: center;">Pago</th>
                        <th width="70"></th>
                        <th width="80"></th>
                        <th width="60" style="text-align: center;">Status</th>
                        <th width="190" style="text-align: center;">Cuentas autorizadas por TR network para realización de pagos</th>
                        <th width="280" style="text-align: center;">Tipos de descuentos aplicables en apgo  contado presencial de deuda total restante</th>
                    </tr>' .$tbody.'
                    </table>';

        // output the HTML content
        $pdf->SetFont('times', '', 8);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->SetY(120);
        $html = '<table border="1" cellpadding="2">
                    <tr>
                    <td width="70" height="50"><b>Nota 1:</b></td>
                    <td width="640" height="50" style="text-align: center">Cuando este realizandoo el deposito o transferencia  interbancaria favor de mandar un correo  a esta dirección dcobranza@trnetwork.com.mx, mencionando los siguientes datos <span style="color: #3d3fbc; font-style: italic;"><b>BANCO</b></span> donde se realizo el pago, <span style="color: #3d3fbc; font-style: italic;"><b>FECHA</b></span> en qque se  realizo el pago, <span style="color: #3d3fbc; font-style: italic;"><b>CANTIDAD</b></span> pagada, así posteriormente se le mandara un recibo de pago digital. También puede hacer sus pagos presencialmente en las oficinas de TR network donde se le entregara un contra recibo de forma fisica.</td>
                    </tr>
                </table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->SetFont('times', 'B', 8);
        $pdf->MultiCell(20, 8, 'Nota 2: ', 1, 'L', 0, 0, '',$pdf->GetY(), true);
        $pdf->SetFont('times', '', 8);
        $txt = "Después de la fecha en  que usted recibe su pago (1ra o 2da quincena de mes), tiene como prorroga hasta 3 días naturales para realizar su pago exento de recargos y penalizaciones.";
        $pdf->MultiCell(180, 8, $txt, 1, 'C', 0, 1, '', '', true);

        $pdf->SetFont('times', 'B', 8);
        $pdf->MultiCell(20, 12, 'Nota 3: ', 1, 'L', 0, 0, '',$pdf->GetY()+5, true);
        $pdf->SetFont('times', '', 8);
        $txt = "En caso de requerir factura favor de indicarlo al momento de recibir este  correo, asi como también enviandonos sus datos fiscales personales para la elaboración de dicha factura. (Este punto solo aplica para esquemas de Honorarios y/o Factura). El pago debe ser exclusivamente a la cuenta con nombre TR network SA de CV";
        $pdf->MultiCell(180, 12, $txt, 1, 'C', 0, 1, '', '', true);

        $pdf->SetY($pdf->GetY()+5);
        $html = '<table border="1" cellpadding="2">
                    <tr>
                    <td width="70" rowspan="2"><b>Nota 4:</b></td>
                    <td width="640" style="text-align: center">A partir de la recepción de documentos cuenta con 2 días naturales para aclarar cualquier situación sobre su corriida de pagos, a partir del día de la recepción se considera  como  notificado y a partir de los dos días naturales si no esxiste notificación de corrección de su parte se considera como una <b>confirmación de aceptación.</b></td>
                    </tr>
                    <tr style="text-align: center"><td>Esperamos su confirmación de recepción, Gracias.</td></tr>
                </table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->SetY($pdf->GetY()-3);
        $html = "<p><strong>Valverde Jurídico</strong><br>Departamento de Cobranza de TR network<br><a href=»dcobranza@trnetwork.com.mx»>dcobranza@trnetwork.com.mx</a></p>";
        $pdf->writeHTML($html, true, false, true, false, '');
        // Limpiamos la salida del búfer y lo desactivamos
        ob_end_clean();
        //$nombre_fichero = ROOT.'files/usuario_'.$infoCliente['id_usuario'].'/Reporte Corrida de Pagos'.$_GET['id_info'].'.pdf';
        //$pdf->Output($nombre_fichero, 'I');
        //$pdf->Output('example_003.pdf', 'I');
        return $pdf;
    }

    public function mostrarNotificaciones(){
        if ($this->is_ajax()){
            $notificaciones = $this->_admin->getNotificaciones();
            if (!is_null($notificaciones)){
                foreach ($notificaciones as $notificacion ){
                    $id_notificacion = $notificacion['id_notificacion_pago'];
                    $this->_admin->desactivarNotificacion($id_notificacion);
                }
                print_r(json_encode($notificaciones));
            }else
                echo 0;
        }
    }

    public function busqueda(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $this->_view->setCssPublic(['bootstrap-datetimepicker.min']);
            $this->_view->setJsPublic(['bootstrap-datetimepicker.min', 'jquery.mask.min', 'jspdf.min']);
            $this->_view->setCss(['corrida', 'busqueda']);
            $this->_view->setJs(['busqueda', 'index']);
            $this->_view->rendering('busqueda',true);
        }else header('Location: /cobranza');
    }

    public function obtenerUsuario(){
        if ($this->is_ajax()){
            $result = $this->_admin->obtenerUsuario($_POST);
            if ($result != 0){
                print_r(json_encode($result));
            }else{
                $this->notificacion['tipo_mensaje'] = 'warning';
                $this->notificacion['mensaje'] = "¡No se encontraron resultados!";
                print_r(json_encode($this->notificacion));
            }
        }
    }

    public function verTodas(){
        if ($this->is_ajax()){
            $this->_admin->verTodas();
            $this->notificacion['tipo_mensaje'] = 'success';
            $this->notificacion['mensaje'] = "¡Notificaciones actualizadas!";
            print_r(json_encode($this->notificacion));
        }
    }

    public function sanitizarCantidad($cantidad){
        $cantidad = floatval(str_replace(",", "", $cantidad));
        return $cantidad;
    }

    public function aumentarPagoProximo(){
        if ($this->is_ajax()){
            #obtenemos la cantidad del pago
            $result = 0;
            $pago = $this->_admin->getPago($_POST['id_pago']);
            if (!is_null($pago)){
                $cantidad = $this->sanitizarCantidad($pago['cantidad']);
                $cantidad += floatval($_POST['cantidad']);
                $_POST['cantidad'] = money_format('%!n',$cantidad);
                $result = $this->_admin->actualizarPagoAuto($_POST);
            }

            if ($result == 1){
                $this->notificacion['tipo_mensaje'] = 'success';
                $this->notificacion['mensaje'] = "Cambio realizado exitosamente";
                print_r(json_encode($this->notificacion));
            }else{
                $this->notificacion['tipo_mensaje'] = 'warning';
                $this->notificacion['mensaje'] = "Ocurrio un problema al actualizar el pago";
                print_r(json_encode($this->notificacion));
            }
        }
    }

    public function actualizarCantidadPagos(){
        if ($this->is_ajax()){
            $datosPago = json_decode($_POST['datosPago']);
            $ids_pagos = $datosPago[0];
            $cantidades_pagos = $datosPago[1];
            for ($i=0;$i<count($ids_pagos); $i++){
                $this->_admin->actualizarPagoAuto(['id_pago' => $ids_pagos[$i], 'cantidad' => $cantidades_pagos[$i]]);
            }
            $this->notificacion['tipo_mensaje'] = "success";
            $this->notificacion['mensaje'] = "Datos actualizados correctamente!!";
            print_r(json_encode($this->notificacion));
        }
    }

    public function actualizarUsuario(){
        if ($this->is_ajax()){
            $datos_pago = json_decode($_POST['datos_usuario']);
            $datos_pago = get_object_vars($datos_pago);
            $result = $this->_admin->actualizarUsuario($datos_pago);
            if ($result == 1){
                $this->notificacion['tipo_mensaje'] = "success";
                $this->notificacion['mensaje'] = "Datos actualizados correctamente!!";
                print_r(json_encode($this->notificacion));
            }else{
                $this->notificacion['tipo_mensaje'] = "warning";
                $this->notificacion['mensaje'] = "Ocurrio un error al actualizar los datos";
                print_r(json_encode($this->notificacion));
            }
        }
    }

    public function convertirFecha($fecha){
        setlocale(LC_TIME, 'es_ES.UTF-8');
        date_default_timezone_set ('Europe/Madrid');
        return strftime("%d-%b-%Y", strtotime($fecha));
    }

    public function restablecerFecha($fecha){
        setlocale(LC_TIME, 'es_ES.UTF-8');
        date_default_timezone_set ('Europe/Madrid');
        return strftime("%Y-%m-%d", strtotime($fecha));
    }

    public function status(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $result = $this->_admin->getStatusUsuarios();
            if ($result != 0){
                $this->_view->usuarios = $result;
            }
            $this->_view->setCssPublic(['bootstrap-datetimepicker.min']);
            $this->_view->setJsPublic(['bootstrap-datetimepicker.min']);
            $this->_view->setCss(['status']);
            $this->_view->setJs(['status']);
            $this->_view->rendering('status',true);
        }else header('Location: /cobranza');
    }

    public function obtenerPagos(){
        if ($this->is_ajax()){
            $result = $this->_admin->corridas_existentes($_POST['id_usuario']);
            print_r(json_encode($result));
        }
    }

    public function actualizarStatusUsuario(){
        if ($this->is_ajax()){
            $result = $this->_admin->updateStatusUsuario($_POST);
            if ($result){
                $this->notificacion['tipo_mensaje'] = 'success';
                $this->notificacion['mensaje'] = "¡Status de usuario actualizado!";
                print_r(json_encode($this->notificacion));
            }else{
                $this->notificacion['tipo_mensaje'] = 'warning';
                $this->notificacion['mensaje'] = "¡No se pudo actualizar es status!";
                print_r(json_encode($this->notificacion));
            }
        }
    }

    public function actualizarStatusPagos(){
        if ($this->is_ajax()){
            $ides_pagos = json_decode($_POST['ides_pagos']);
            foreach ($ides_pagos as $id){
                $this->_admin->actualizarStatusPago($id);
            }
            $this->notificacion['tipo_mensaje'] = 'success';
            $this->notificacion['mensaje'] = "¡Cambio realizado correctamente!";
            print_r(json_encode($this->notificacion));
        }
    }

    public function verReciboProyecto(){
        $id_usuario = $this->_admin->retornaInfoPago($_GET['id_info']);
        $id_usuario = $id_usuario['id_usuario'];
        $nombre_fichero = ROOT.'files/usuario_'.$id_usuario.'/Recibo Pendiente Proyecto'.$_GET['id_pago'].'.pdf';
        if (!file_exists($nombre_fichero)) {
            $pdf = $this->generarReciboProyecto();
            $pdf->Output($nombre_fichero, 'F');
        }
        $respuesta = BASE_URL."files/usuario_".$id_usuario."/Recibo Pendiente Proyecto".$_GET['id_pago'].'.pdf';
        print_r($respuesta);
    }

    public function generarReciboProyecto(){
        $pagos = $this->_admin->getInformacionCorrida($_GET['id_info']);
        $infoCliente = $this->_admin->getInfoPagoRecibo($_GET['id_pago']);
        $numPago = substr($infoCliente['no_pago'], 0,2);
        $numPago =  intval($numPago);
        $totalPagos = $this->_admin->contarPagos($_GET['id_info']);
        $pagosRestantes = intval($totalPagos['num_pagos']) - intval($numPago);
        $cliente = $infoCliente['nombre'];
        $arrayNombre = explode(" ", $cliente);
        $iniciales = "";
        for ($i=0; $i< count($arrayNombre); $i++){
            $iniciales .= substr($arrayNombre[$i],0,1);
        }
        $folio = "TR".date('y').date('m').date('d')."-".$iniciales;
        if (!is_null($pagos)){
            $this->getLibrary("tcpdf/tcpdf");
            $this->getLibrary("tcpdf/MYPDF");
            $this->getLibrary("PHPMailer/Mailer");

            // create new PDF document
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set document information
            $pdf->SetCreator('TR network');
            $pdf->SetAuthor('TR network');
            $pdf->SetTitle('Recibo Pago Parcial');
            $pdf->SetSubject('Sistema de cobranza');
            $pdf->SetKeywords('cobranza, PDF, recibo, parcial');

            $pdf->setPrintFooter(false);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // set font
            $pdf->SetFont('times', 'BI', 12);


            // add a page
            $pdf->AddPage();

            $pdf->Ln(25);

            // set cell padding
            $pdf->setCellPaddings(1, 1, 1, 1);

            // set cell margins
            //$pdf->setCellMargins(1, 1, 1, 1);

            $pdf->SetFont('helvetica', '', 10);

            // set some text for example
            $txt = <<<EOT
Blvd. San Felipe 224
Puebla, Puebla 72040
Teléfono: (01) 222.8.89.56.08
dcobranza@trnetwork.com.mx
EOT;

            // Multicell test
            $pdf->MultiCell(65, 5, $txt, 1, 'L', 0, 0, 15, 40, true);


            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->writeHTMLCell(30, 0, 125, '', 'Fecha', 0, 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(30, 0, 125, '', 'Pago #', 0, 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(30, 0, 125, '', 'Folio Recibo', 0, 1, 0, true, 'R', true);
            $pdf->SetY($pdf->GetY()-20);

            $pdf->SetFont('helvetica', '', 10);
            $fecha = date('d-m-Y');
            $pdf->writeHTMLCell(35, 0, 160, '', $this->convertirFecha($fecha), 'LRTB', 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(35, 0, 160, '', $numPago, 'LRTB', 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(35, 0, 160, '', '<p style="color: red">'.$folio.'</p>', 'LRTB', 1, 0, true, 'R', true);

            $pdf->SetFont('helvetica', 'B', 10);
            $html = "<p>Cliente:&nbsp;&nbsp;&nbsp;&nbsp; ".$cliente."</p><p>Activo</p>";
            $pdf->SetFillColor(69,77,85);

            $pdf->writeHTMLCell(180, 0, 15, $pdf->GetY()+5, '<p style="color: white">Recibo pago a favor de:</p>', 'LRT', 1, 1, true, 'L', true);
            $pdf->writeHTMLCell(180, 20, 15, '', $html, 'LRB', 1, 0, true, 'C', true);

            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, 15);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetY($pdf->GetY()+5);
            $pdf->SetX(15);

            $tbody = "";
            $cont = 1;
            foreach ($pagos as $pago){
                $descuento_y_porcentaje = ($pago['descuento_porcentaje'] != 0) ? $pago['descuento_porcentaje']."%   ".money_format('%n',$pago['descuento_cantidad']) : "N/A";
                $celda = ($cont % 2 === 0) ? "par" : "inpar";
                $cantidad = 0;
                if ($pago['descuento_cantidad'] != "0"){
                    $cantidad = floatval(str_replace(',','', $pago['cantidad']));
                    $cantidad += floatval($pago['descuento_cantidad']);
                    $cantidad = money_format('%n',$cantidad);
                    $cantidad = substr($cantidad,1);
                }else{
                    $cantidad = $pago['cantidad'];
                }
                $status = $pago['status'];
                if ($status == 'proyecto'){
                    $status = 'pendiente proyecto';
                    $cantidad = 0;
                }
                $tbody .= '<tr class="'.$celda.'">
                            <td>
                                '.$pago['no_pago'].'
                            </td>
                            <td>
                                '.$pago['fecha_pago'].'
                            </td>
                            <td>
                                '.$pago['forma_pago'].'
                            </td>
                            <td>
                                $ '.$cantidad.'
                            </td>                
                            <td class="'.$pago['status'].'">
                                '.$status.'
                            </td>
                            <td>
                                '.$descuento_y_porcentaje.'
                            </td>
                            </tr>';
                $cont ++;
            }
            $tbody .= '<tr><td><strong>Total</strong></td><td></td><td></td><td><strong>$ '.$infoCliente['monto_pagar'].'</strong></td><td></td><td></td></tr>';

            $html = '<style>.acreditado{background-color: #aee1a8; color: #FFFFFF} .pendiente{background-color: orange; color: #FFFFFF}.aplazado{background-color: red; color: #ffffff}.parcial{background-color: lightgray; color: #FFFFFF}.proyecto{background-color: #DAA520;color: white;}
                    td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}
.par{ background-color: #d0d0d0}
</style>
                     <table cellpadding="4">
                    <tr style="background-color: #454d55; color: #FFFFFF;">
                        <th>Cantidad</th>
                        <th>Fecha de pago</th>
                        <th>Descripción</th>
                        <th>Cantidad Pagada</th>
                        <th>Status</th>
                        <th>Decuento aplicado</th>
                    </tr>' .$tbody.'
                    </table>';

            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            if (intval($totalPagos['num_pagos']) > 13){
                $pdf->AddPage();
                $y = 40;
            }else
                $y = '';

            $txt = <<<EOD
La reproducción apocrifa de este comprobante
constituye un delito en  los térmnos de las
disposiciones fiscales. Si tiene alguna duda sobre
este recibo de pago, póngase en contacto con Lic.
Erike Valverde a, dcobranza@trnetwork.com.mx los
intereses moratorios sonn del 8% sobre la deuda
actual más la penalización de $9,000.00 (Nueve mil
pesos 00/100 M.N.) por atrazo en pagos.
Gracias por su pago
EOD;

            $pdf->SetFillColor(246,196,164);

            $pdf->SetFont('helvetica', '', 7);

            // Multicell test
            $pdf->MultiCell(60, 5, $txt, 1, 'L', 1, 0, 15, $y, true);

            // Image example with resizing
            $pdf->Image(ROOT.'views/img/cw-qr.png', $pdf->GetX()+1, $pdf->GetY()-5, 40, 40, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);

            $pdf->SetFont('helvetica', 'B', 8);

            $pdf->writeHTMLCell(40, 0, 105, '', 'Pagos a Crédito', 0, 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(40, 0, 105, '', 'Número de pago', 0, 1, 0, true, 'R', true);
            $pdf->writeHTMLCell(40, 0, 105, '', 'Saldo pendiente', 0, 1, 0, true, 'R', true);

            $pdf->SetFont('helvetica', '', 8);

            $pdf->writeHTMLCell(20, 0, 145, $pdf->GetY()-17, $pagosRestantes, 'LTR', 1, 1, true, 'R', true);
            $pdf->writeHTMLCell(20, 0, 145, $pdf->GetY(), $numPago, 'LR', 1, 1, true, 'R', true);
            $pdf->SetFillColor(144,198,224);
            $pdf->writeHTMLCell(20, 0, 145, $pdf->GetY(), '<strong>$ '.$infoCliente['monto_pendiente'].'</strong>', 'LBR', 1, 1, true, 'R', true);

            $txt = <<<EOD
Este documento solo 
respalda los pagos de 
status Acreditados no
le justifica de adeudos
posteriores. 
EOD;
            $pdf->SetFont('helvetica', '', 6);
            $pdf->SetFillColor(234,229,147);

            // Multicell test
            $pdf->MultiCell(25, 5, $txt, 1, 'L', 1, 0, 170, $pdf->GetY()-15, true);

            /* Limpiamos la salida del búfer y lo desactivamos */
            ob_end_clean();

            //Close and output PDF document
            //$pdf->Output('Recibo Pago Parcial '.$numPago.'.pdf', 'I');
            return $pdf;
        }

    }

    public  function enviarReciboProyecto(){
        $this->getLibrary("PHPMailer/Mailer");
        $infoCliente = $this->_admin->getInfoPagoRecibo($_GET['id_pago']);
        $numPago = substr($infoCliente['no_pago'], 0,2);
        $numPago =  intval($numPago);
        $id_usuario = $infoCliente['id_usuario'];
        $rutaReciboParcial = ROOT.'files/usuario_'.$id_usuario.'/Recibo Pendiente Proyecto'.$_GET['id_pago'].'.pdf';
        if (file_exists($rutaReciboParcial)) {
            #Se envia por correo
            $info = [
                "email_usuario" => $infoCliente['email_usuario'],
                "nombre_usuario" => $infoCliente['nombre'],
                "ruta_recibo" => $rutaReciboParcial,
                "nombre_recibo" => "Recibo Pago Parcial".$numPago
            ];
            $mail = new Mailer();
            $respuestaMail = $mail->enviar_recibo_parcial($info);
            if ($respuestaMail)
                echo "Recibo enviado satisfactoreamente";
            else
                echo "Error: No se pudo enviar el recibo!!";
        }else
            echo "Error: ¡No ha generado el Recibo!";
    }

    public function actualizaStatusUsuario($id_info_pago){
        $monto_pendiente = $this->_admin->getMontoPendiente($id_info_pago);
        $id_usuario = $this->_admin->retornaInfoPago($id_info_pago);
        $id_usuario = $id_usuario['id_usuario'];
        $monto_pendiente = round( $this->sanitizarCantidad($monto_pendiente), 1, PHP_ROUND_HALF_DOWN);
        if ($monto_pendiente == 0){
            $this->_admin->setStatusUsuario(['id_usuario' => $id_usuario, 'status_usuario' => 'completado']);
        }
    }

    public function regestionar(){
        if ($this->is_ajax()){
            $id_info_pago = $_POST['id_info_pago'];
            $fecha_nueva = $_POST['fecha_nueva'];
            $monto_percibir = $_POST['monto_percibir'];
            $monto_total = $_POST['monto_total'];
            $cantidad = $_POST['cantidad'];
            $num_pagos = $_POST['num_pagos'];
            $ides_pagos = json_decode($_POST['ides_pagos']);
            $monto_pendiente = count($ides_pagos) * $this->sanitizarCantidad($cantidad);
            //$semanas = ($num_pagos >= 12)? 12 : 6;
            $semanas = $num_pagos;
            #Calculamos la nueva corrida de acuerddo a la fecha nueva
            $fechas = $this->dias_trabajados($fecha_nueva);
            $fechas = $this->calcular_fechas($fechas,$semanas);
            $fechas_retorno = [];
            $cont = 0;
            #Se actualiza el monto a percibir
            $result = $this->_admin->actualizarMontoPercibir(['id_informacion_pago' => $id_info_pago, 'monto_nuevo' => $monto_percibir]);
            #Se actualiza el monto a pagar
            $result1 = $this->_admin->actualizarMontoPagar(['id_informacion_pago' => $id_info_pago, 'total_pago' => money_format('%!n',$monto_total)]);
            #Se actualiza el monto pendiente
            $result2 = $this->_admin->actualizarMonto(['id_informacion_pago' => $id_info_pago, 'total' => money_format('%!n', $monto_pendiente)]);
            if ($result1 && $result2){
                foreach ($fechas as $fecha){
                    $fechas[$cont] = $this->convertirFecha($fecha);
                    $cont++;
                }
                for ($i=0; $i < count($ides_pagos); $i++){
                    $this->_admin->actualizarPagoRegestion([
                        'id_pago' => $ides_pagos[$i],
                        'fecha' => $fechas[$i],
                        'cantidad' => money_format('%!n',$cantidad)]);
                    array_push($fechas_retorno, $fechas[$i]);
                }
                print_r(json_encode($fechas_retorno));
            }else{
                $this->notificacion['tipo_mensaje'] = 'error';
                $this->notificacion['mensaje'] = "¡Ocurrio un error al actualizar la corrida de pagos!";
                print_r(json_encode($this->notificacion));
            }
        }
    }

    public  function verReporteCorridaRegestion(){
        $id_usuario = $this->_admin->retornaInfoPago($_GET['id_info']);
        $id_usuario = $id_usuario['id_usuario'];
        $nombre_fichero = ROOT.'files/usuario_'.$id_usuario.'/Reporte Corrida de Pagos(regestion)'.$_GET['id_info'].'.pdf';
        if (!file_exists($nombre_fichero)) {
            $pdf = $this->generarPdfCorrida();
            $pdf->Output($nombre_fichero, 'F');
        }
        $respuesta = BASE_URL."files/usuario_".$id_usuario."/Reporte Corrida de Pagos(regestion)".$_GET['id_info'].'.pdf';
        print_r($respuesta);
    }

    public function enviarReporteCorridaRegestion(){
        $pagos = $this->_admin->getInformacionCorrida($_GET['id_info']);
        $infoCliente = $this->_admin->getInfoPagoRecibo($pagos[0]['id_pago']);
        $this->getLibrary("tcpdf/tcpdf");
        $this->getLibrary("PHPMailer/Mailer");
        $pdf = $this->generarPdfCorrida();
        //Close and output PDF document
        $rutaReporteCorrida = ROOT.'files/usuario_'.$infoCliente['id_usuario'].'/Reporte Corrida de Pagos(regestion)'.$_GET['id_info'].'.pdf';
        $pdf->Output($rutaReporteCorrida, 'F');
        #Se envia por correo
        $info = [
            "email_usuario" => $infoCliente['email_usuario'],
            "nombre_usuario" => $infoCliente['nombre'],
            "ruta_reporte" => $rutaReporteCorrida
        ];
        $mail = new Mailer();
        $respuestaMail = $mail->enviar_reporte_corrida($info);
        if ($respuestaMail)
            echo "<h1>Reporte enviado satisfactoreamente</h1>";
        else
            echo "<h1>Error: No se pudo enviar el reporte!!</h1>";

    }

    public function settings(){
        if (isset($_SESSION['validate']) && $_SESSION['rol'] == 1) {
            $this->_view->setCss(['settings']);
            $this->_view->setJs(['settings']);
            $this->_view->rendering('settings',true);
        }else header('Location: /cobranza');
    }

    public function cambiarContrasenia(){
        if ($this->is_ajax()){
            $this->getLibrary("Util/util");
            $util = new Util();
            $_POST['pass'] = isset($_POST['pass']) ? $util->encrypt($_POST['pass']) : '';
            $result = $this->_admin->setContrasenia($_POST);
            if ($result){
                $this->notificacion['tipo_mensaje'] = 'success';
                $this->notificacion['mensaje'] = "¡Contraseña actualizada correctamente!";
                print_r(json_encode($this->notificacion));
            }else{
                $this->notificacion['tipo_mensaje'] = 'error';
                $this->notificacion['mensaje'] = "¡Error, no se pudo cambiar la contraseña!";
                print_r(json_encode($this->notificacion));
            }
        }
    }

    public function generarReporteStatusPago(){
        $fecha = $this->convertirFecha($_GET['fechaListado']);
        $usuarios = $this->_admin->buscarPagosPorFecha($fecha);
        if ($usuarios != 0){
            $this->getLibrary("tcpdf/tcpdf");
            // create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('TR network');
            $pdf->SetTitle('Reporte de pagos');
            $pdf->SetSubject('Sistema de cobranza');
            $pdf->SetKeywords('reporte, corrida, pago, informacion, guide');
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            // set margins
            $pdf->SetMargins(30, PDF_MARGIN_TOP, 10);
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 5);
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            // set some language-dependent strings (optional)
            if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $pdf->setLanguageArray($l);
            }
            // ---------------------------------------------------------
            $pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');
            // set font
            $pdf->SetFont('times', '', 10);
            //$pdf->setCellPaddings(50,0,0,0);
            $pdf->AddPage('L', 'A4');
            $html = '<h2>Listado de usuarios que estan pagando</h2>';
            $pdf->SetY(10);
            $pdf->writeHTML($html, '', '','','','L');
            // Image example with resizing
            $pdf->Image(ROOT.'views/img/trnetwork.png', 250, 5, 35, 15, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
            $pdf->SetY($pdf->GetY()+10);
            $cont = 1;
            foreach ($usuarios as $usuario){
                //$fecha = substr($usuario['fecha_pago'],0,10);
                //$fecha = $this->convertirFecha($fecha);
                $celda = ($cont % 2 === 0) ? "par" : "inpar";
                $tbody .= '
                <tr class="'.$celda.'">
                <td>
                    '.$usuario['nombre']. '
                </td>
                <td>
                    '.$usuario['email_usuario'].'
                </td>                   
                <td>
                    '.$usuario['fecha_pago'].'
                </td>                
                <td>
                    '.$usuario['generacion'].'
                </td>
                <td>
                    '.$usuario['tecnologia'].'
                </td>
                <td>
                    '.$usuario['status'].'
                </td>                                                
                </tr>';
                $cont ++;
            }

            $html = '    <style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #d0d0d0;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
.par{ background-color: #dddddd}
</style>
                    <table>
                    <tr>
                        <th><b>Nombre</b></th>
                        <th><b>Email</b></th>
                        <th><b>Fecha de pago</b></th>
                        <th><b>Generación</b></th>
                        <th><b>Tecnologia</b></th>
                        <th><b>Status</b></th>
                    </tr>' .$tbody.'
                    </table>';

            // output the HTML content
            $pdf->SetFont('times', '', 10);
            $pdf->writeHTML($html, true, false, true, false, '');
            // Limpiamos la salida del búfer y lo desactivamos
            ob_end_clean();
            //$pdf->Output('example_003.pdf', 'I');
            //return $pdf;
            $pdf->Output(ROOT.'files/Reporte pagos.pdf', 'F');
            $respuesta = BASE_URL."files/Reporte pagos.pdf";
            print_r($respuesta);
        }else{
            echo 'vacio';
        }
    }

    public function backupBD(){
        $db_host = 'localhost'; //Host del Servidor MySQL
        $db_name = 'trnetwork_cobranza'; //Nombre de la Base de datos
        $db_user = 'root'; //Usuario de MySQL
        $db_pass = 'trnetwork'; //Password de Usuario MySQL
        $ruta = "/Users/desarrollo2/'Google Drive'/";

        $fecha = date("Y-m-d"); //Obtenemos la fecha y hora para identificar el respaldo

        // Construimos el nombre de archivo SQL Ejemplo: mibase_20170101-081120.sql
        $salida_sql = $ruta.$db_name.'_'.$fecha.'.sql';

        //Comando para genera respaldo de MySQL, enviamos las variales de conexion y el destino
        $dump = "/usr/local/mysql/bin/mysqldump -u$db_user -p$db_pass --opt $db_name > $salida_sql";
        system($dump, $output); //Ejecutamos el comando para respaldo
    }

    public function generarRegestion(){
        if ($this->is_ajax()){
            #Obtenemos los pagos del usuario
            $result = $this->_admin->corridas_existentes_regestion($_POST['usuario']);
            #Verificamos si hay pagos
            if ($result != 0){
                #Calculamos las nuevas cantidades
                $monto_pagado = 0;
                $corrida = [];
                $band = false;
                $pagos_pendientes = 0;
                $pagos_acreditados = 0;
                foreach ($result as $pago) {
                    if ($pago['status'] == "parcial" || $pago['status'] == "acreditado") {
                        $cantidad_pagada = $this->sanitizarCantidad($pago['cantidad']) - intval($pago['descuento_cantidad']);
                        $cantidad_pagada = number_format($cantidad_pagada,2,'.',',');
                        array_push($corrida,[
                            "pago" => (count($result) >= 12) ? ' qna' : 'mes',
                            "fecha" => $pago['fecha_pago'],
                            "cantidad" => $pago['cantidad'],
                            "status" => $pago['status'],
                            "forma_pago" => "Pago en una sola exhibición",
                            "porcentaje" => $pago['descuento_porcentaje'],
                            "descuento" => $pago['descuento_cantidad'],
                            "cantidad_pagada" => $cantidad_pagada
                        ]);
                        $monto_pagado += $this->sanitizarCantidad($pago['cantidad']);
                        $pagos_acreditados++;
                    }/*else
                        $pagos_pendientes++;*/
                }

                $pagos_pendientes = ($_POST['periodo'] == "Quincenal") ? 12 - $pagos_acreditados : 6 - $pagos_acreditados;
                #Verificamos si la corrida anterior es del mismo periodo que la actál
                if(count($result) >= 12 && $_POST['periodo'] == 'Mensual'){
                    if ($pagos_acreditados > 1){
                        $pagos_acreditados = ($pagos_acreditados % 2 == 0) ? $pagos_acreditados / 2 : ($pagos_acreditados -1)/2;
                    }else
                        $pagos_acreditados = 0;
                    $pagos_pendientes = 6 - $pagos_acreditados;
                }elseif (count($result) < 12 && $_POST['periodo'] == 'Quincenal'){
                    $pagos_acreditados = $pagos_acreditados *2;
                    $pagos_pendientes = 12 - $pagos_acreditados;
                }
                #Restamos la cantidad pagada de la anteior corrida a la cantidad nueva
                $monto_nuevo = $this->sanitizarCantidad($_POST['montoAPagar']) - $monto_pagado;
                $cantidad = round($monto_nuevo / $pagos_pendientes);
                $periodo = ($_POST['periodo'] == 'Quincenal') ? 12 : 6;
                $dias_trabajados = $this->dias_trabajados($_POST['fechaIngreso'],$periodo);
                $semanas = $periodo;
                if ($dias_trabajados < 4 && $_POST['periodo'] == 'Quincenal'){
                    $cantidad_dia = $cantidad / 15;
                    if($dias_trabajados != 0){
                        $cantidad_dt = $dias_trabajados * $cantidad_dia;
                        $cantidad_dt = number_format(round($cantidad_dt),2,'.',',');
                    }else
                        $cantidad_dt = number_format($cantidad,2,'.',',');
                    $semanas = 13;
                    $cantidad_res = (15 - $dias_trabajados)* $cantidad_dia;
                    $cantidad_res = number_format(round($cantidad_res),2,'.',',');
                    $band = true;
                }

                //Aplicación de la regla de los días trabajados para (mensualidades)
                /*if (($dias_trabajados < 19) && $_POST['periodo'] == 'Mensual'){
                    $semanas = 7;
                    $cantidad_dia = $this->dias_mes;
                    $cantidad_dia = $this->sanitizarCantidad($cantidad) / $cantidad_dia;
                    $cantidad_dt = $dias_trabajados * $cantidad_dia;
                    $cantidad_dt = number_format(round($cantidad_dt),2,'.',',');
                    $cantidad_res = ($this->dias_mes - $dias_trabajados)* $cantidad_dia;
                    $cantidad_res = number_format(round($cantidad_res),2,'.',',');
                    $band = true;
                }*/

                $fecha_ingreso = $_POST['fechaIngreso'];
                #Generamos las nuevas fechas de pago
                $fechas = $this->calcular_fechas($fecha_ingreso,$semanas);

                $cont = 0;



                /* Aqui realiza la regla de los 12 dias */
                $fechaEnQueIngreso = date_create($fecha_ingreso);/* fecha de ingreso */
                $fechaSiguienteQuincenaMes = date_create($fechas[0]); /* siguiente quincena/inmediato */
                $diferencia = date_diff($fechaEnQueIngreso,$fechaSiguienteQuincenaMes);
                $diferencia = $diferencia->format('%d');/* formato para obtener solo el numero de dias */


                if(intval($diferencia) < 12 ){
                    /*-- Entra en la siguiente quincena--*/
                    array_shift($fechas);

                    if(sizeof($fechas) == 11 OR sizeof($fechas) == 5){
                        /* Y se anade otra quincena mas, puesto que quedo solo con 11 en la accion anterior */
                        $ultimaFechadeArreglo = $fechas[sizeof($fechas) - 1]; /* se obtiene la ultima fecha del arreglo */
                        $siguienteQuincenaoMes = $this->calcular_fechas($ultimaFechadeArreglo, $semanas);
                        /*Se anade la primera fecha siguiente al array de fechas que ya tenemos */
                        array_push($fechas, $siguienteQuincenaoMes[1]);
                    }
                }




                foreach ($fechas as $fecha){
                    $fechas[$cont] = $this->convertirFecha($fecha);
                    $cont++;
                }
                #Tomamos solo las fechas de los pagos pendientes
                $limite = ($band) ? $pagos_pendientes +1 : $pagos_pendientes;
                $fechas = array_slice($fechas,0,$limite);


                #Obtenemos el numero de pagos pendientes de la primera corrida
                $cont = 0;

                foreach ($fechas as $fecha) {

                    if ($band && $cont == 0){
                        $cantidad_tmp = $cantidad_dt;
                    } elseif ($band && $cont == count($fechas)-1)
                        $cantidad_tmp = $cantidad_res;
                    else
                        $cantidad_tmp = number_format($cantidad, 2, '.', ',');
                    array_push($corrida,[
                        "pago" => ($periodo ==12) ? ' qna' : 'mes',
                        "fecha" => $fecha,
                        "cantidad" => $cantidad_tmp,
                        "status" => 'pendiente',
                        "forma_pago" => "Pago en una sola exhibición",
                        "porcentaje" => 0,
                        "descuento" => 0,
                        "cantidad_pagada" => $cantidad_tmp
                    ]);
                    $cont++;


                }

                if(intval($dias_trabajados) === 0){
                    $corridaFinal = array_slice($corrida,0,12);
                }else{
                    $corridaFinal = $corrida;
                }

                //print_r($corridaFinal);
                //exit();

                print_r(json_encode($corridaFinal));
            }else{
                $this->notificacion['tipo_mensaje'] = 'error';
                $this->notificacion['mensaje'] = "No hay resultados de las corridas existentes";
                print_r(json_encode($this->notificacion));
            }
        }
    }
}
