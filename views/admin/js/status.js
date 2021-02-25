//$('#pagos_tabla').DataTable();
$('#buscar').click(function () {
    if ($('#nombre').val() != '') {
        $('#tablaStatus').empty();
        $('#usuarios').hide();
        var nombre = $('#nombre').val();
        $.ajax({
            url: root + "obtenerUsuario",
            type: "POST",
            data: {'nombre': nombre},
            success: function (response) {
                //console.log(response);
                var respuesta = JSON.parse(response);
                var cont = 1;
                if (typeof respuesta.tipo_mensaje === 'undefined'){
                    $('#usuarios').show();
                    Object.keys(respuesta).forEach(function(key) {
                        var html = '<tr>'+
                            '<td>'+respuesta[key].nombre+'</td>'+
                            '<td>'+respuesta[key].email_usuario+'</td>'+
                            '<td>'+respuesta[key].generacion+'</td>'+
                            '<td>'+respuesta[key].tecnologia+'</td>'+
                            '<td id="status_'+cont+'" class="status_'+respuesta[key].status+'">'+
                            '<span class="text">'+respuesta[key].status+'</span>'+
                            '<div class="editbox" style="display: none; max-width: 90px;">'+
                            '<div class="form-check form-check-inline">'+
                            '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_pagando'+cont+'" value="pagando" checked>'+
                            '<label class="form-check-label" for="status_pagando'+cont+'">Pagando</label>'+
                            '</div>'+
                            '<div class="form-check form-check-inline">'+
                            '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_completado'+cont+'" value="completado">'+
                            '<label class="form-check-label" for="status_completado'+cont+'">Completado</label>'+
                            '</div>'+
                            '<div class="form-check form-check-inline">'+
                            '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_cancelado'+cont+'" value="cancelado">'+
                            '<label class="form-check-label" for="status_cancelado'+cont+'">Cancelado</label>'+
                            '</div>'+
                            '</div>'+
                            '</td>'+
                            '<td>' +
                            '<div class="btn-group btn-group-toggle" data-toggle="buttons">'+
                            '<button class="btn btn-primary verMas" id_usuario="'+respuesta[key].id_usuario+'"><i class="fa fa-eye"></i></button>'+
                            '<button class="btn btn-success btnEditar" id="editar_'+cont+'" num="'+cont+'"><i class="far fa-edit"></i></button>' +
                            '<button class="btn btn-success btnSave" id="save_'+cont+'"  style="display: none" id_usuario="'+respuesta[key].id_usuario+'" num="'+cont+'"><i class="fas fa-check"></i></button>' +
                            '<button class="btn btn-danger btnCancel" id="cancel_'+cont+'" style="display: none" num="'+cont+'"><i class="fas fa-times"></i></button>' +
                            '</div>'+
                            '</td>'+
                            '</tr>';
                        $('#tablaStatus').append(html);
                        cont++;
                    })
                }else
                    swal({
                        type: respuesta.tipo_mensaje,
                        title: 'Alerta',
                        text: respuesta.mensaje
                    });
            }
        })
    }else
        swal({
            type: 'error',
            title: '¡Error!',
            text: 'El campo de busqueda esta vacio'
        });
});

$('#nombre').keypress(function(e) {
    var keycode = (e.keyCode ? e.keyCode : e.which);
    if (keycode == '13') {
        if ($('#nombre').val() != '') {
            $('#tablaStatus').empty();
            $('#usuarios').hide();
            var nombre = $('#nombre').val();
            $.ajax({
                url: root + "obtenerUsuario",
                type: "POST",
                data: {'nombre': nombre},
                success: function (response) {
                    //console.log(response);
                    var respuesta = JSON.parse(response);
                    var cont = 1;
                    if (typeof respuesta.tipo_mensaje === 'undefined'){
                        $('#usuarios').show();
                        Object.keys(respuesta).forEach(function(key) {
                            var html = '<tr>'+
                                '<td>'+respuesta[key].nombre+'</td>'+
                                '<td>'+respuesta[key].email_usuario+'</td>'+
                                '<td>'+respuesta[key].generacion+'</td>'+
                                '<td>'+respuesta[key].tecnologia+'</td>'+
                                '<td id="status_'+cont+'" class="status_'+respuesta[key].status+'">'+
                                '<span class="text">'+respuesta[key].status+'</span>'+
                                '<div class="editbox" style="display: none; max-width: 90px;">'+
                                '<div class="form-check form-check-inline">'+
                                '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_pagando'+cont+'" value="pagando" checked>'+
                                '<label class="form-check-label" for="status_pagando'+cont+'">Pagando</label>'+
                                '</div>'+
                                '<div class="form-check form-check-inline">'+
                                '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_completado'+cont+'" value="completado">'+
                                '<label class="form-check-label" for="status_completado'+cont+'">Completado</label>'+
                                '</div>'+
                                '<div class="form-check form-check-inline">'+
                                '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_cancelado'+cont+'" value="cancelado">'+
                                '<label class="form-check-label" for="status_cancelado'+cont+'">Cancelado</label>'+
                                '</div>'+
                                '</div>'+
                                '</td>'+
                                '<td>' +
                                '<div class="btn-group btn-group-toggle" data-toggle="buttons">'+
                                '<button class="btn btn-primary verMas" id_usuario="'+respuesta[key].id_usuario+'"><i class="fa fa-eye"></i></button>'+
                                '<button class="btn btn-success btnEditar" id="editar_'+cont+'" num="'+cont+'"><i class="far fa-edit"></i></button>' +
                                '<button class="btn btn-success btnSave" id="save_'+cont+'"  style="display: none" id_usuario="'+respuesta[key].id_usuario+'" num="'+cont+'"><i class="fas fa-check"></i></button>' +
                                '<button class="btn btn-danger btnCancel" id="cancel_'+cont+'" style="display: none" num="'+cont+'"><i class="fas fa-times"></i></button>' +
                                '</div>'+
                                '</td>'+
                                '</tr>';
                            $('#tablaStatus').append(html);
                            cont++;
                            $('#usuariosTable').DataTable();
                        })
                    }else
                        swal({
                            type: respuesta.tipo_mensaje,
                            title: 'Alerta',
                            text: respuesta.mensaje
                        });
                }
            })
        }else
            swal({
                type: 'error',
                title: '¡Error!',
                text: 'El campo de busqueda esta vacio'
            });
    }
});

$(document).on('click','.verMas', function () {
    $('#pagos').hide();
    $('#tablaPagos').empty();
    var id_usuario = $(this).attr('id_usuario');
    $.ajax({
        url: root + "obtenerPagos",
        type: "POST",
        data: {'id_usuario': id_usuario},
        success: function (response) {
            //console.log(response);
            var respuesta = JSON.parse(response);
            if (respuesta){
                $('#pagos').show();
                var cont = 1;
                Object.keys(respuesta).forEach(function(key) {
                    var html = '<tr>'+
                        '<td>'+cont+'</td>'+
                        '<td>'+respuesta[key].fecha_pago+'</td>'+
                        '<td>'+respuesta[key].cantidad+'</td>'+
                        '<td class="status_'+respuesta[key].status+'">'+respuesta[key].status+'</td>'+
                        '</tr>';
                    $('#tablaPagos').append(html);
                    cont++;
                })
            }else
                Swal.fire({
                    type: 'warning',
                    title: '¡Alerta!',
                    text: 'No hay pagos para este usuario'
                })

        }
    })
});

$(document).on('click', '.btnEditar', function () {
    var num = $(this).attr('num');
    $('#status_'+num+' span').hide();
    $('#status_'+num+' .editbox').show();
    $('#editar_'+num).hide();
    $('#save_'+num).show();
    $('#cancel_'+num).show();
});

$(document).on('click', '.btnCancel', function () {
    var num = $(this).attr('num');
    $('#status_'+num+' span').show();
    $('#status_'+num+' .editbox').hide();
    $('#editar_'+num).show();
    $('#save_'+num).hide();
    $('#cancel_'+num).hide();
});

$(document).on('click', '.btnSave', function () {
    var num = $(this).attr('num');
    var id_usuario = $(this).attr('id_usuario');
    var status = $('input:radio[name=status'+num+']:checked').val();
    $.ajax({
        url: root + "actualizarStatusUsuario",
        type: "POST",
        data: {'id_usuario': id_usuario,'status': status},
        success: function (response) {
            //console.log(response);
            var respuesta = JSON.parse(response);
            Swal.fire({
                type: respuesta.tipo_mensaje,
                text: respuesta.mensaje
            })
        }
    })
    $('#status_'+num+' span').text(status);
    $('#status_'+num).removeClass();
    $('#status_'+num).addClass('status_'+status);
});

//Evento que genera el reporte de usuarios en estado PAGANDO
$('#listadoForm').submit(function () {
    var fecha = $('#fechaListado').val();
    var req = new XMLHttpRequest();
    req.open('GET', root+"generarReporteStatusPago&fechaListado="+fecha, false);
    req.send(null);
    //console.log(req.responseText);
    if (req.status == 200 && req.responseText != 'vacio')
        window.open(req.responseText, '_blank', 'fullscreen=yes');
    else
        Swal.fire({
            type: 'warning',
            text: '¡No se encontraron pagos!'
        });
    return false;
});

$(document).ready(function() {
    $('#usuariosTable').DataTable();
} );