$('#buscar').click(function () {
    busqueda();
});

$('#nombre').keypress(function(e) {
    var keycode = (e.keyCode ? e.keyCode : e.which);
    if (keycode == '13') {
        busqueda();
    }
});

function busqueda(){
    if ($('#nombre').val() != ''){
        $('#tablaConsultora').empty();
        $('#tablaCorrida').empty();
        $('#bodyTablaJava').empty();
        $('#bodyTablaNet').empty();
        $('#usuariosContent').hide();
        $('#infoConsultora').hide();
        $('#infoCorrida').hide();
        $('#java').hide();
        $('#net').hide();
        var formData = new FormData();
        formData.append('nombre', $('#nombre').val());
        $.ajax({
            url: root + "obtenerUsuario",
            type: "POST",
            data: formData,
            contentType: false,
            success: function (response) {
                //console.log(response);
                var respuesta = JSON.parse(response);
                if (typeof respuesta.tipo_mensaje === 'undefined'){
                    $('#usuariosContent').show();
                    var i =1;
                    var j =1;
                    Object.keys(respuesta).forEach(function(key) {
                        var bisible = 'style="display: none;"';
                        if (respuesta[key].nota != "" && respuesta[key].nota != null)
                            bisible = 'style=""';
                        if (respuesta[key].tecnologia === 'java'){
                            $('#java').show();
                            $('#bodyTablaJava').append('<tr id="user_'+i+'">' +
                                '<td class="userName" id="'+respuesta[key].id_usuario+'">' +
                                '<span>'+respuesta[key].nombre+'</span>' +
                                '</td>'+
                                '<td>' +
                                '<span>'+respuesta[key].email_usuario+'</span>'+
                                '</td>'+
                                '<td style="width: 160px">' +
                                '<div class="row">' +
                                '<div class="col">' +
                                '<span>'+respuesta[key].generacion+'</span>'+
                                '</div>'+
                                '<div class="col" style="padding-left: 0px">' +
                                '<button type="button" class="btn btn-danger nota" id="'+respuesta[key].id_usuario+'" onclick="verNota(\''+respuesta[key].nota+'\')" '+bisible+'><span><i class="far fa-comment-alt"></i></span></button>'+
                                '</div>'+
                                '</div>'+
                                '</td>'+
                                '</tr>');
                            i++;
                        }else {
                            $('#net').show();
                            $('#bodyTablaNet').append('<tr id="user_'+j+'">' +
                                '<td class="userName" id="'+respuesta[key].id_usuario+'">' +
                                '<span>'+respuesta[key].nombre+'</span>' +
                                '</td>'+
                                '<td>' +
                                '<span>'+respuesta[key].email_usuario+'</span>'+
                                '</td>'+
                                '<td style="width: 160px">' +
                                '<div class="row">' +
                                '<div class="col">' +
                                '<span>'+respuesta[key].generacion+'</span>'+
                                '</div>'+
                                '<div class="col">' +
                                '<button type="button" class="btn btn-danger nota" id="'+respuesta[key].id_usuario+'" onclick="verNota(\''+respuesta[key].nota+'\')" '+bisible+'><span><i class="far fa-comment-alt"></i></span></button>'+
                                '</div>'+
                                '</div>'+
                                '</td>'+
                                '</tr>');
                            j++;
                        }
                    })
                }else{
                    if (respuesta.tipo_mensaje == "success") {
                        Swal.fire({
                            type: 'success',
                            title: 'OK',
                            text: respuesta.mensaje
                        });
                    }else if (respuesta.tipo_mensaje == "warning"){
                        swal({
                            type: 'warning',
                            title: 'Alerta',
                            text: respuesta.mensaje
                        });
                    }else if (respuesta.tipo_mensaje == "danger") {
                        Swal.fire({
                            type: 'error',
                            title: 'Error de acceso',
                            text: respuesta.mensaje
                        });
                    }
                }
            },
            cache: false,
            processData: false
        })
    }else{
        Swal.fire({
            type: 'error',
            title: 'Oops...',
            text: '¡el campo de busqueda esta vacio!'
        })
    }
    return false;
}

$(document).on('click','.userName',function () {
    //se limpia el contenido de la tabla
    $('#tablaConsultora').empty();
    var id_usuario = $(this).attr('id');
    var formData = new FormData();
    formData.append('idUsuario', id_usuario);
    $.ajax({
        url: root + "mostrarDatosUsuario",
        type: "POST",
        data: formData,
        contentType: false,
        success: function (response) {
            var info = JSON.parse(response);
            if (typeof info.tipo_mensaje !== 'undefined') {
                Swal.fire({
                    type: info.tipo_mensaje,
                    title: 'Reultados',
                    text: info.mensaje,
                    showConfirmButton: false,
                    timer: 1500
                })
            }else{
                $('#infoConsultora').show();
                var elmnt = document.getElementById("infoConsultora");
                elmnt.scrollIntoView();
                var cont = 1;
                Object.keys(info).forEach(function(key) {
                    $('#tablaConsultora').append('<tr>'+
                        '<td>' +
                        '<span>'+info[key].consultora+'</span>'+
                        '</td>'+
                        '<td>' +
                        '<span>'+info[key].cliente+'</span>'+
                        '</td>'+
                        '<td>' +
                        '<span>'+info[key].fecha_ingreso+'</span>'+
                        '</td>'+
                        '<td>' +
                        '<span>'+info[key].periodo_pago+'</span>'+
                        '</td>'+
                        '<td>' +
                        '<span>'+info[key].esquema+'</span>'+
                        '</td>'+
                        '<td>' +
                        '<span class="cantidad">'+info[key].monto_percibir+'</span>'+
                        '</td>'+
                        '<td>' +
                        '<span class="cantidad" id="monto_pendiente_'+info[key].id_informacion_pago+'">'+info[key].monto_pendiente+'</span>'+
                        '</td>'+
                        '<td>' +
                        '<span class="cantidad" id="monto_pagar_'+info[key].id_informacion_pago+'">'+info[key].monto_pagar+'</span>'+
                        '</td>'+
                        '<td>' +
                        '<div class="btn-group btn-group-toggle" data-toggle="buttons">'+
                        '<button class="btn btn-primary btnAccion" id="btn_'+cont+'_accion" num="'+cont+'" id_info_pago="'+info[key].id_informacion_pago+'" monto_pago="'+info[key].monto_pagar+'"><i class="far fa-edit"></i></button>'+
                        '<button class="btn btn-primary btnVerCorrida" id="btn_'+cont+'_verCorrida" num="'+cont+'" style="display: none" id_info_pago="'+info[key].id_informacion_pago+'" monto_pago="'+info[key].monto_pagar+'"><i class="fa fa-eye"></i></button>'+
                        '<button class="btn btn-danger btnVerReporte" id="btn_'+cont+'_verReporte" style="display: none" onclick="verReporteCorrida('+info[key].id_usuario+','+info[key].id_informacion_pago+')" ><i class="fas fa-file-alt"></i></button>'+
                        '<button class="btn btn-warning btnCancelar" id="btn_'+cont+'_cancelar" num="'+cont+'" style="display: none"><i class="fas fa-times"></i></button>' +
                        '</div>'+
                        '</td>'
                    );
                    cont++;
                })
            }
        },
        cache: false,
        processData: false
    });
});

function verNota(nota) {
    Swal.fire({
        title: "Comentario",
        html: '<hr><p style="background-color: lightyellow; padding: 80px;border: solid;border-color: chocolate;">'+nota+'</p>'
    })
}