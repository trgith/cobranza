<?php
class backendModel extends Model{
    public function __construct(){
        parent::__construct();
    }

    public function getGeneraciones(){
        $fields = "*";
        $table = "generacion";
        $where = ['1' => '1'];
        $result = $this->_db->select($table,$fields,$where);
        if (!empty($result)){
            return $result;
        }else return null;
    }

    public function verificarUsuario($data){
        $bind = [":email" => $data['email']];
        $fields = "*";
        $table = "usuario";
        $where = ['email_usuario' => ':email'];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result[0];
        }else return null;
    }

    public function registrarUsuario($data,$pass,$fecha){
        $fields = ["email_usuario" => $data['email'], "nombre" =>$data['nombre'], "contrasenia" => $pass, "fecha_registro" => $fecha,"telefono_antiguo" => $data['telefono_antiguo'], "telefono_nuevo" => $data['telefono_nuevo'],"nota" => $data['nota'], "id_generacion" => $data['generacion']];
        $table = "usuario";
        $where = ["1" => "1"];
        $result = $this->_db->insert($table,$fields,$where);
        return $result;
    }

    public function getUsuarios(){
        $fields = "*";
        $table = "usuario";
        $where = ['1' => '1'];
        $result = $this->_db->select($table,$fields,$where);
        if (!empty($result)){
            return $result;
        }else return null;
    }

    public function cambiarNombreGeneracion($data){
        $bind = [":id_generacion" => $data['idGeneracion']];
        $table = "generacion";
        $info = ["generacion" => $data['nombreGeneracion']];
        $where = ["id_generacion" => ":id_generacion"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function registrarGeneracion($data){
        $fields = ["generacion" => $data['generacion'], "tecnologia" => $data['tecnologia']];
        $table = "generacion";
        $where = ["1" => "1"];
        $result = $this->_db->insert($table,$fields,$where);
        return $result;
    }

    public function registrarCorrida($data){
        $fields = [
            "consultora" => $data['consultora'],
            "cliente" => $data['cliente'],
            "fecha_ingreso" => $data['fechaIngreso'],
            "periodo_pago" => $data['periodo'],
            "esquema" => $data['esquema'],
            "monto_percibir" => $data["montoAPercibir"],
            "monto_pagar" => $data['montoAPagar'],
            "monto_pendiente" => $data['total_pagar'],
            "id_usuario" => $data['usuario']
        ];
        $table = "informacion_pago";
        $where = ["1" => "1"];
        $result = $this->_db->insert($table,$fields,$where);
        return $result;
    }

    public function registrarPagos($data){
        $fields = [
            "no_pago" => $data['no_pago'],
            "fecha_pago" => $data['fecha_pago'],
            "cantidad" => $data['cantidad'],
            "consultora" => $data['consultora'],
            "status" => $data['status'],
            "forma_pago" => $data['forma_pago'],
            "descuento_porcentaje" => $data['descuento_porcentaje'],
            "descuento_cantidad" => $data['descuento_cantidad'],
            "id_informacion_pago" => $data['id_informacion_pago']
        ];
        $table = "pago";
        $where = ["1" => "1"];
        $result = $this->_db->insert($table,$fields,$where);
        return $result;
    }

    public function getId(){
        $mysqli = new mysqli(HOST, USER, PSWD, DATABASE);
        if ($result = $mysqli->query("SELECT MAX(id_informacion_pago) as id FROM informacion_pago")) {

            $row = $result->fetch_object();

            return $row->id;
        }
    }

    public function getCuentasBancarias(){
        $fields = "*";
        $table = "cuenta_pago";
        $where = ['1' => '1'];
        $result = $this->_db->select($table,$fields,$where);
        if (!empty($result)){
            return $result;
        }else return null;
    }

    public function registrarCuentaBancaria($data){
        $fields = ['nombre' => $data['nombre'], "banco" => $data['banco'], "cuenta" => $data['cuenta'], "clabe" => $data['clave']];
        $table = "cuenta_pago";
        $where = ["1" => "1"];
        $result = $this->_db->insert($table,$fields,$where);
        return $result;
    }

    public function actualizarCuenta($data){
        $bind = [":id_cuenta" => $data['id_cuenta_pago']];
        $table = "cuenta_pago";
        $info = ["nombre" => $data['nombre'], "banco" => $data['banco'], "cuenta" => $data['cuenta'], "clabe" => $data['clabe']];
        $where = ["id_cuenta_pago" => ":id_cuenta"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function eliminarCuentaBancaria($idCuenta){
        $bind = [":idCuenta" => $idCuenta];
        $table = "cuenta_pago";
        $where = ['id_cuenta_pago' => ':idCuenta'];
        $result = $this->_db->delete($table, $where, $bind);
    }

    public function getNotas(){
        $fields = "*";
        $table = "nota";
        $where = ['1' => '1'];
        $result = $this->_db->select($table,$fields,$where);
        if (!empty($result)){
            return $result;
        }else return null;
    }

    public function registrarNota($nota){
        $fields = ['descripcion' => $nota];
        $table = 'nota';
        $where = ['1' => '1'];
        $result = $this->_db->insert($table,$fields,$where);
        return $result;
    }

    public function actualizarNota($data){
        $bind = [":id_nota" => $data['idNota']];
        $table = "nota";
        $info = ["descripcion" => $data['descripcion']];
        $where = ["id_nota" => ":id_nota"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function eliminarNota($idNota){
        $bind = [":idNota" => $idNota];
        $table = "nota";
        $where = ['id_nota' => ':idNota'];
        $result = $this->_db->delete($table, $where, $bind);
        return $result;
    }

    public function usuariosGeneracion($idGeneracion){
        $bind = [":id_generacion" => $idGeneracion];
        $fields = ["id_usuario","email_usuario", "nombre", "fecha_registro", "activo"];
        $table = "usuario";
        $where = ['id_generacion' => ':id_generacion'];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result;
        }else return null;
    }

    public function getInformacionPago($idUsuario){
        $bind = [":id_usuario" => $idUsuario];
        $fields = '*';
        $table = "informacion_pago";
        $where = ['id_usuario' => ':id_usuario'];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result;
        }else return null;
    }

    public function getInformacionCorrida($idInfoPago){
        $bind = [":id_informacion_pago" => $idInfoPago];
        $fields = '*';
        $table = "pago";
        $where = ['id_informacion_pago' => ':id_informacion_pago'];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result;
        }else return null;
    }

    public function actualizarPago($data){
        $bind = [":id_pago" => $data['id_pago']];
        $table = "pago";
        $info = [
            "consultora" => $data['consultora'],
            "cantidad" => $data['cantidad'],
            "status" => $data['status'],
            "forma_pago" => $data['forma_pago'],
            "fecha_pago" => $data['fecha_pago'],
            "descuento_porcentaje" => $data['descuento_porcentaje'],
            "descuento_cantidad" => $data['descuento_cantidad'],
        ];
        $where = ["id_pago" => ":id_pago"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function buscarUsuario($data){
        $query = "SELECT u.nombre, u.email_usuario, u.id_usuario, g.generacion, g.id_generacion
                  FROM usuario u 
                  INNER JOIN generacion g ON u.id_generacion = g.id_generacion 
                  WHERE (u.email_usuario LIKE '%".$data['termino']."%' OR u.nombre LIKE '%".$data['termino']."%') AND u.activo = '".$data['activo']."' AND u.id_rol_usuario = 2";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function getUsuariosInactivos(){
        $query = "SELECT u.nombre, u.email_usuario, u.id_usuario, g.generacion, g.id_generacion
                  FROM usuario u 
                  INNER JOIN generacion g ON u.id_generacion = g.id_generacion 
                  WHERE u.activo = 'N'";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function activarUsuario($idUsuario){
        $bind = [":id_usuario" => $idUsuario];
        $table = "usuario";
        $info = ["activo" => 'S'];
        $where = ["id_usuario" => ":id_usuario"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function getUsuariosActivos(){
        $query = "SELECT u.nombre, u.email_usuario, u.id_usuario, g.generacion, g.id_generacion
                  FROM usuario u 
                  INNER JOIN generacion g ON u.id_generacion = g.id_generacion 
                  WHERE u.activo = 'S' AND u.id_rol_usuario != 1";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function desactivarUsuario($idUsuario){
        $bind = [":id_usuario" => $idUsuario];
        $table = "usuario";
        $info = ["activo" => 'N'];
        $where = ["id_usuario" => ":id_usuario"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function getAccesos(){
        $query = "SELECT ha.*, u.nombre
                  FROM historial_acceso ha 
                  INNER JOIN usuario u ON ha.id_usuario = u.id_usuario";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function getFechas(){
        $fields = '*';
        $table = "pago";
        $where = ['1' => '1'];
        $result = $this->_db->select($table,$fields,$where);
        if (!empty($result)){
            return $result;
        }else return null;
    }

    public function insertarNotificacion($data){
        $fields = ['descripcion' => $data['descripcion'], "fecha_notificacion" => $data['fecha_notificacion'], "activo" => 1, "id_usuario" => $data['id_usuario']];
        $table = "notificacion_pago";
        $where = ["1" => "1"];
        $result = $this->_db->insert($table,$fields,$where);
        return $result;
    }

    public function getInfoPago($id_info_pago, $fecha_pago){
        $query = "SELECT p.no_pago, p.cantidad, p.fecha_pago, p.status, u.nombre, u.email_usuario, u.telefono_antiguo
                  FROM pago p 
                  INNER JOIN informacion_pago ip ON p.id_informacion_pago = ip.id_informacion_pago
                  INNER JOIN usuario u ON ip.id_usuario = u.id_usuario
                  WHERE p.id_informacion_pago = ".$id_info_pago." AND p.fecha_pago = '".$fecha_pago."';";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result[0];
        }else return 0;
    }

    public function getNotificaciones(){
        $bind = [":visto" => 'No'];
        $fields = '*';
        $table = "notificacion_pago";
        $where = ['visto' => ':visto'];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result;
        }else return null;
    }

    public function actualizarVistoNotificacion($id_notificacion){
        $bind = [":id_notificacion_pago" => $id_notificacion];
        $table = "notificacion_pago";
        $info = ["visto" => 'Si'];
        $where = ["id_notificacion_pago" => ":id_notificacion_pago"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function getInfoPagoRecibo($id_pago){
        $query = "SELECT p.no_pago, p.cantidad, p.fecha_pago, p.status, p.descuento_porcentaje, p.descuento_cantidad,u.id_usuario, u.nombre, u.email_usuario, u.telefono_antiguo, u.telefono_nuevo, 
ip.monto_pagar, ip.monto_pendiente, ip.consultora, ip.cliente, ip.fecha_ingreso, ip.periodo_pago, ip.esquema, ip.monto_percibir, ip.id_usuario
                  FROM pago p 
                  INNER JOIN informacion_pago ip ON p.id_informacion_pago = ip.id_informacion_pago
                  INNER JOIN usuario u ON ip.id_usuario = u.id_usuario
                  WHERE p.id_pago = ".$id_pago.";";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result[0];
        }else return 0;
    }

    public function corridas_existentes($id_usuario){
        $query = "SELECT p.*
                  FROM pago p 
                  INNER JOIN informacion_pago ip ON p.id_informacion_pago = ip.id_informacion_pago
                  INNER JOIN usuario u ON ip.id_usuario = u.id_usuario
                  WHERE u.id_usuario = ".$id_usuario.";";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function corridas_existentes_regestion($id_usuario){
        $query = "SELECT p.*
                  FROM pago p 
                  INNER JOIN informacion_pago ip ON p.id_informacion_pago = ip.id_informacion_pago
                  WHERE p.id_informacion_pago = (SELECT ip.id_informacion_pago
                  FROM usuario u
                  INNER JOIN informacion_pago ip ON u.id_usuario = ip.id_usuario
                  WHERE u.id_usuario = ". $id_usuario ." ORDER BY ip.id_informacion_pago DESC LIMIT 1);";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function actualizarMonto($data){
        $bind = [":id_info_pago" => $data['id_informacion_pago']];
        $table = "informacion_pago";
        $info = ["monto_pendiente" => $data['total']];
        $where = ["id_informacion_pago" => ":id_info_pago"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function actualizarPagoAuto($data){
        $bind = [":id_pago" => $data['id_pago']];
        $table = "pago";
        $info = ["cantidad" => $data['cantidad']];
        $where = ["id_pago" => ":id_pago"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function eliminarPago($id_pago){
        $bind = [":id_pago" => $id_pago];
        $table = "pago";
        $where = ['id_pago' => ':id_pago'];
        $result = $this->_db->delete($table, $where, $bind);
        return $result;
    }

    public function getPago($id_pago){
        $bind = [":id_pago" => $id_pago];
        $fields = '*';
        $table = "pago";
        $where = ['id_pago' => ":id_pago"];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result[0];
        }else return null;
    }

    public function contarPagos($id_info_pago){
        $query = "SELECT COUNT(id_informacion_pago) AS num_pagos FROM pago WHERE id_informacion_pago = ".$id_info_pago.";";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result[0];
        }else return 0;
    }

    public function getIdUsuario(){
        $mysqli = new mysqli(HOST, USER, PSWD, DATABASE);
        if ($result = $mysqli->query("SELECT MAX(id_usuario) as id FROM usuario")) {

            $row = $result->fetch_object();

            return $row->id;
        }
    }

    public function desactivarNotificacion($id_notificacion){
        $bind = [":id_notificacion_pago" => $id_notificacion];
        $table = "notificacion_pago";
        $info = ["activo" => 0];
        $where = ["id_notificacion_pago" => ":id_notificacion_pago"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function actualizarMontoPagar($data){
        $bind = [":id_info_pago" => $data['id_informacion_pago']];
        $table = "informacion_pago";
        $info = ["monto_pagar" => $data['total_pago']];
        $where = ["id_informacion_pago" => ":id_info_pago"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function actualizarMontoPercibir($data){
        $bind = [":id_info_pago" => $data['id_informacion_pago']];
        $table = "informacion_pago";
        $info = ["monto_percibir" => $data['monto_nuevo']];
        $where = ["id_informacion_pago" => ":id_info_pago"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function obtenerUsuario($data){
        $query = "SELECT id_usuario, email_usuario, nombre, nota, status, generacion, tecnologia
                  FROM usuario u
                  INNER JOIN generacion g ON g.id_generacion = u.id_generacion  
                  WHERE (u.email_usuario LIKE '%".$data['nombre']."%' OR u.nombre LIKE '%".$data['nombre']."%') AND u.activo = 'S' AND u.id_generacion != 1";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function verTodas(){
        $query = "SET SQL_SAFE_UPDATES = 0;
                    UPDATE notificacion_pago	
                    SET visto = 'Si'
                    WHERE visto = 'No'";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function getMontoPagar($id_info){
        $bind = [":id_info" => $id_info];
        $fields = 'monto_pagar';
        $table = "informacion_pago";
        $where = ['id_informacion_pago' => ':id_info'];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result[0]['monto_pagar'];
        }else return null;
    }

    public function getMontoPendiente($id_info){
        $bind = [":id_info" => $id_info];
        $fields = 'monto_pendiente';
        $table = "informacion_pago";
        $where = ['id_informacion_pago' => ':id_info'];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result[0]['monto_pendiente'];
        }else return null;
    }

    public function actualizarUsuario($data){
        $bind = [":id_user" => $data['id_usuario']];
        $table = "usuario";
        $info = ["nombre" => $data['nombre_usuario'], "email_usuario" => $data['email_usuario']];
        $where = ["id_usuario" => ":id_user"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function retornaInfoPago($id_info_pago){
        $bind = [":id_info_pago" => $id_info_pago];
        $fields = '*';
        $table = "informacion_pago";
        $where = ['id_informacion_pago' => ":id_info_pago"];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result[0];
        }else return null;
    }

    public function updateStatusUsuario($data){
        $bind = [":id_user" => $data['id_usuario']];
        $table = "usuario";
        $info = ["status" => $data['status']];
        $where = ["id_usuario" => ":id_user"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function actualizarStatusPago($id_pago){
        $bind = [":idPago" => $id_pago];
        $table = "pago";
        $info = ["status" => "proyecto"];
        $where = ["id_pago" => ":idPago"];
        $result = $this->_db->update($table, $info, $where, $bind);
        return $result;
    }

    public function setStatusUsuario($data){
        $bind = [":id_user" => $data['id_usuario']];
        $table = "usuario";
        $info = ["status" => $data['status_usuario']];
        $where = ["id_usuario" => ":id_user"];
        $result = $this->_db->update($table, $info, $where, $bind);
        return $result;
    }

    public function actualizarPagoRegestion($data){
        $bind = [":id_pago" => $data['id_pago']];
        $table = "pago";
        $info = ["fecha_pago" => $data['fecha'],"cantidad" => $data['cantidad']];
        $where = ["id_pago" => ":id_pago"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function setContrasenia($data){
        $bind = [":id_user" => $data['id_usuario']];
        $table = "usuario";
        $info = ["contrasenia" => $data['pass']];
        $where = ["id_usuario" => ":id_user"];
        $result = $this->_db->update($table, $info, $where,$bind);
        return $result;
    }

    public function getStatusUsuarios(){
        $query = "SELECT DISTINCT u.nombre, u.email_usuario, p.fecha_pago, g.generacion, g.tecnologia, p.status
                  FROM usuario u
                  INNER JOIN generacion g ON g.id_generacion = u.id_generacion 
                  INNER JOIN informacion_pago ip ON ip.id_usuario = u.id_usuario
                  INNER JOIN pago p ON p.id_informacion_pago = ip.id_informacion_pago
                  WHERE u.status = 'pagando' AND u.id_rol_usuario = 2";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function buscarPagosPorFecha($fecha){
        $query = "SELECT u.nombre, u.email_usuario, u.id_usuario, p.fecha_pago, g.generacion, g.tecnologia, p.status
                  FROM usuario u 
                  INNER JOIN generacion g ON u.id_generacion = g.id_generacion
                  INNER JOIN informacion_pago ip ON ip.id_usuario = u.id_usuario
                  INNER JOIN pago p ON p.id_informacion_pago = ip.id_informacion_pago 
                  WHERE p.fecha_pago LIKE '%".$fecha."%' AND u.status = 'pagando' AND u.id_rol_usuario = 2";
        $result = $this->_db->run($query);
        if (!empty($result)){
            return $result;
        }else return 0;
    }

    public function registrarReporte($data){
        $fields = ["nombre" => $data['nombre'], "fecha" =>$data['fecha'], "id_info_pago" => $data['id_info_pago']];
        $table = "reportes_corrida";
        $where = ["1" => "1"];
        $result = $this->_db->insert($table,$fields,$where);
        return $result;
    }

    public function buscarReportesCorrida($data){
        $bind = [":id_info" => $data];
        $fields = "*";
        $table = "reportes_corrida";
        $where = ['id_info_pago' => ':id_info'];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if (!empty($result)){
            return $result;
        }else return null;
    }
}