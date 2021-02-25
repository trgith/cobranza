$(function () {
   $('#formBuscar').submit(function () {
        $.ajax({
            url: root + "buscarUsuario",
            type: "POST",
            data: $("#formBuscar").serialize(),
            success: function (response) {
                $('#respuestaBusqueda').hide();
                $('#tablaResultado').hide();
                $('#tbodyUsuario').empty();
                if (response != 0){
                    var respuesta = JSON.parse(response);
                    $('#tablaResultado').show();
                    $('#etiquetaUsuario').text(respuesta.nombre);
                    $('#tbodyUsuario').append(
                        "<tr>" +
                        "<td>" + respuesta.nombre + "</td>" +
                        "<td>" + respuesta.email_usuario + "</td>" +
                        "<td>" + respuesta.generacion + "</td>" +
                        "<td>" +
                        '<button class="btn btn-success btnActivar" id="' +respuesta.id_usuario + '"><i class="fas fa-check"></i></button>' +
                        "</td>" +
                        "</tr>"
                    );
                }else{
                    $('#respuestaBusqueda').show();
                }

            }
        });
        return false;
   });
});

$(document).on('click','.btnActivar',function () {
    var id_usuario = $(this).attr('id');
    var formData = new FormData();
    formData.append('id_usuario', id_usuario);
    $.ajax({
        url: root + "activarUsuario",
        type: "POST",
        data: formData,
        contentType: false,
        success: function (response) {
            console.log(response);
            var respuesta = JSON.parse(response);
            if (respuesta.tipo_mensaje == "success") {
                Swal.fire({
                    //position: 'top',
                    type: 'success',
                    title: 'OK',
                    text: respuesta.mensaje
                }).then(function () {
                    location.reload();
                });
            }else if (respuesta.tipo_mensaje == "warning"){
                swal({
                    type: 'warning',
                    title: 'Alerta de acceso',
                    text: respuesta.mensaje
                });
            }
        },
        cache: false,
        processData: false
    });
    return false;
});