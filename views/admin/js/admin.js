$(function () {
    $("#formRegistro").submit(function () {
        $email = $("#email").val();
        $emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
        if ($emailRegex.test($email)) {
            $.ajax({
                url: root+"registrarUsuario",
                type: "POST",
                data: $("#formRegistro").serialize(),
                success: function (response) {
                    //console.log(response);
                    respuesta = JSON.parse(response);
                    if (respuesta.tipo_mensaje == "success") {
                        Swal.fire({
                            type: 'success',
                            title: 'Registro correcto',
                            text: respuesta.mensaje,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function(){
                            location.reload();
                        })
                    }else if (respuesta.tipo_mensaje == "warning"){
                        swal({
                            type: 'warning',
                            title: '¡Alerta!',
                            text: respuesta.mensaje
                        })
                    }else if (respuesta.tipo_mensaje == "danger") {
                        Swal.fire({
                            type: 'error',
                            title: 'Error de acceso',
                            text: respuesta.mensaje
                        })
                    }
                }
            });
            return false;
        }else{
            Swal.fire({
                type: 'warning',
                title: 'Alerta de acceso',
                text: "El correo ingresado no es correcto"
            }).then(function(){
                location.reload();
            })
            return false;
        }    
    });
});