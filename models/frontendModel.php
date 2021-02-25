<?php
class frontendModel extends Model{

    public function __construct(){
        parent::__construct();
    }

    public function verificarUsuario($data){
        $bind = [":email" => $data['correo'],":pass" => $data['contrasena'], ":activo" => "S"];
        $fields = ["id_usuario","id_rol_usuario","telefono_antiguo","codigo_acceso","nombre", "email_usuario"];
        $table = "usuario";
        $where = ["AND" => ["email_usuario" => ":email","contrasenia" => ":pass", "activo" => ":activo"]];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if($result){
            return $result[0];
        }else return null;
    }

    public function actualizarClaveAcceso($data){
        $bind = [":correo" => $data['correo']];
        $table = "usuario";
        $info = ["codigo_acceso" => $data['clave']];
        $where = ["email_usuario" => ":correo"];
        $result = $this->_db->update($table, $info, $where, $bind);
        return $result;
    }

    public function verificarClave($data){
        $bind = [":clave" => $data];
        $fields = ["codigo_acceso"];
        $table = "usuario";
        $where = ["codigo_acceso" => ":clave"];
        $result = $this->_db->select($table,$fields,$where,$bind);
        if ($result) {
            return $result[0];
        }else return null;
    }

    public function registrar_acceso($data){
        $fields = ["fecha_hora" => $data['fecha_hora'], "ip" => $data['ip'], "navegador" => $data['navegador'], "sistema_operativo" => $data['sistema_operativo'], "ubicacion" => $data['ubicacion'], "id_usuario" => $data['id_usuario']];
        $table = "historial_acceso";
        $where = ["1" => "1"];
        $result = $this->_db->insert($table,$fields,$where);
        return $result;
    }
}