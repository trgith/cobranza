var appscheme = 'tmprintassistant://';           //scheme name                                                                           Input condition:Required
var host = 'tmprintassistant.epson.com/';        //host name                                                                             Input condition:Required
var action       = 'print?';                     //action name                                                                           Input condition:Required
var query_success = 'success';                   //URL to be displayed when TM Print Assistant has terminated normally.                  Input condition:Can be omitted
var query_ver = 'ver';                           //Version number                                                                        Input condition:Required
var query_datatype = 'data-type';                //Type of print data                                                                    Input condition:Required
var query_data = 'data';                         //Print data                                                                            Input condition:Required
var query_reselect = 'reselect';                 //Reconfiguration of printer upon communication error                                   Input condition:Can be omitted (Default : no)
var query_cut = 'cut';                           //Cutter setting                                                                        Input condition:Can be omitted (Default : no)
var query_fittowidth = 'fit-to-width';           //Scaling up or down print data in accordance with paper width Supported data-type: pdf Input condition:Can be omitted (Default : no)
var query_paperwidth = 'paper-width';            //Specifying paper width (mm)                                                           Input condition:Can be omitted (Default : 80)
var pdfData = '';
var cantidad_original = "";
var cantidad_nueva = "";
var id_pago_global = 0;
var num_pago = 0;
var tipo_pago = "pendiente";

function abrirInputCambioNombreGeneracion(idGeneracion, idTecnologia){
    $('#botonCambioGeneracion').remove();
    $('#botonGuardarCambioGeneracion').remove();
    $('#titleCard').empty('');

    if(idTecnologia == 1){
        /* Esto es Java */
        $('#titleCard').append("<input id='inputCambioNombreGeneracionJava' class='col-xs-3' type='text' value='" + $( "#selectGenJava option:selected" ).text() + "'>");
        $('#CambiarNombreGeneracionContenedor').append("<button class='btn btn-success' id='botonGuardarCambioGeneracion' onclick='cambiarNombreGeneracion(" + idGeneracion + ", 1)'>Guardar Nombre</button>");
    }
    else
    {
        /* Esto es Net */

    }
}

function cambiarNombreGeneracion(idGeneracion, idTecnologia){

    var Gen = new FormData();
    Gen.append('idGeneracion', idGeneracion);

    /* Selecciona si es Java o NET */
    if(idTecnologia == 1){
        /* Java */
        Gen.append('nombreGeneracion', $('#inputCambioNombreGeneracionJava').val());
    }
    else
    {
        /* NET */

    }
    $.ajax({
        url: root + "cambiarNombreGeneracion",
        type: "POST",
        data: Gen,
        contentType: false,
        success: function (response) {
            respuesta = JSON.parse(response);
            if(respuesta.result == 1){
                Swal.fire({
                    type: 'success',
                    title: 'Registro correcto',
                    text: respuesta.mensaje,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    location.reload();
                })
            }
            else
            {
                Swal.fire({
                    type: 'error',
                    title: 'Algo salío mal.',
                    text: respuesta.mensaje,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    location.reload();
                })
            }

        },
        cache: false,
        processData: false
    });

}

$(function () {
    $("#formGeneracion").submit(function () {
        $.ajax({
            url: root + "registrarGeneracion",
            type: "POST",
            data: $("#formGeneracion").serialize(),
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
                    }).then(function () {
                        location.reload();
                    })
                } else if (respuesta.tipo_mensaje == "warning") {
                    swal({
                        type: 'warning',
                        title: 'Alerta de acceso',
                        text: respuesta.mensaje
                    }).then(function () {
                        location.reload();
                    })
                } else if (respuesta.tipo_mensaje == "danger") {
                    Swal.fire({
                        type: 'error',
                        title: 'Error de acceso',
                        text: respuesta.mensaje
                    }).then(function () {
                        location.reload();
                    })
                }
            }
        });
        return false;
    });



    $('#selectGenJava').change(function () {
        $('tbody').empty();
        $('#usuariosContent').hide();
        $('#infoConsultora').hide();
        $('#infoCorrida').hide();
        var id_gen = $(this).children(":selected").val();
        var gen = $(this).children(":selected").text();
        var tecnologia = $(this).children(":selected").attr('tecnologia');
        var formData = new FormData();
        formData.append('idGeneracion', id_gen);
        $.ajax({
            url: root + "mostrarUsuariosGeneracion",
            type: "POST",
            data: formData,
            contentType: false,
            success: function (response) {

                if (response != 0){
                    $('#alerta').hide();
                    $('#usuariosContent').show();
                    var elmnt = document.getElementById("usuariosContent");
                    elmnt.scrollIntoView();
                    $('#titleCard').text(gen);

                    /*-- Se añaden parametros al boton #CambiarNombreGeneracion --*/
                    $('#botonCambioGeneracion').remove();
                    var pruebas = gen;
                    $('#CambiarNombreGeneracionContenedor').append("<button class='btn btn-warning' id='botonCambioGeneracion' onclick='abrirInputCambioNombreGeneracion(" + id_gen + ", 1)'>Cambiar Nombre</button>");

                    var usuarios = JSON.parse(response);
                    for (var i=0; i < usuarios.length ;i++){
                        if (usuarios[i].activo === 'S'){
                            $('#tablaUsuario').append('<tr id="user_'+(i+1)+'">' +
                                '<td id="nombreUser_'+(i+1)+'">' +
                                '<span class="text">'+usuarios[i].nombre+'</span>' +
                                '<input type="text" name="nombreUsuario" class="editbox form-control" value="'+usuarios[i].nombre+'" style="display: none;">' +
                                '</td>'+
                                '<td id="emailUser_'+(i+1)+'">' +
                                '<span class="text">'+usuarios[i].email_usuario+'</span>'+
                                '<input type="text" name="emailUsuario" class="editbox form-control" value="'+usuarios[i].email_usuario+'" style="display: none;">' +
                                '</td>'+
                                '<td>'+
                                '<div class="btn-group btn-group-toggle" data-toggle="buttons">'+
                                '<button class="btn btn-primary btnAccionUsuario" id="btnUser_'+(i+1)+'_accion" num="'+(i+1)+'"><i class="far fa-edit"></i></button>'+
                                '<button class="btn btn-primary btnVer" id="btnUser_'+(i+1)+'_ver" id_usuario="'+usuarios[i].id_usuario+'" style="display: none"><i class="far fa-eye"></i></button>' +
                                '<button class="btn btn-success btnEditarUser" id="btnUser_'+(i+1)+'_editar" num="'+(i+1)+'" id_usuario="'+usuarios[i].id_usuario+'" style="display: none" ><i class="fas fa-check"></i></button>'+
                                '<button class="btn btn-warning btnCancelarUser" id="btnUser_'+(i+1)+'_cancelar" num="'+(i+1)+'" style="display: none"><i class="fas fa-times"></i></button>' +
                                '</div>'+
                                '</td>'+
                                '</tr>');
                        }
                    }
                }else{
                    $('#usuariosContent').hide();
                    $('tbody').empty();
                    $('#alerta').show();
                }
            },
            cache: false,
            processData: false
        });
        return false;
    });

    $('#selectGenNet').change(function () {
        $('tbody').empty();
        $('#usuariosContent').hide();
        $('#infoConsultora').hide();
        $('#infoCorrida').hide();
        var id_gen = $(this).children(":selected").val();
        var gen = $(this).children(":selected").text();
        var tecnologia = $(this).children(":selected").attr('tecnologia');
        var formData = new FormData();
        formData.append('idGeneracion', id_gen);
        $.ajax({
            url: root + "mostrarUsuariosGeneracion",
            type: "POST",
            data: formData,
            contentType: false,
            success: function (response) {
                //console.log(response);
                if (response != 0){
                    $('#alerta').hide();
                    $('#usuariosContent').show();
                    var elmnt = document.getElementById("usuariosContent");
                    elmnt.scrollIntoView();
                    $('#titleCard').text(gen);

                    /*-- Se añaden parametros al boton #CambiarNombreGeneracion --*/
                    $('#botonCambioGeneracion').remove();
                    $('#CambiarNombreGeneracionContenedor').append("<button class='btn btn-warning' id='botonCambioGeneracion' onclick='abrirInputCambioNombreGeneracion(" + id_gen + ")'>Cambiar Nombre</button>");


                    var usuarios = JSON.parse(response);
                    for (var i=0; i < usuarios.length ;i++){
                        if (usuarios[i].activo === 'S'){
                            $('#tablaUsuario').append('<tr id="user_'+(i+1)+'">' +
                                '<td id="nombreUser_'+(i+1)+'">' +
                                '<span class="text">'+usuarios[i].nombre+'</span>' +
                                '<input type="text" name="nombreUsuario" class="editbox form-control" value="'+usuarios[i].nombre+'" style="display: none;">' +
                                '</td>'+
                                '<td id="emailUser_'+(i+1)+'">' +
                                '<span class="text">'+usuarios[i].email_usuario+'</span>'+
                                '<input type="text" name="emailUsuario" class="editbox form-control" value="'+usuarios[i].email_usuario+'" style="display: none;">' +
                                '</td>'+
                                '<td>'+
                                '<div class="btn-group btn-group-toggle" data-toggle="buttons">'+
                                '<button class="btn btn-primary btnAccionUsuario" id="btnUser_'+(i+1)+'_accion" num="'+(i+1)+'"><i class="far fa-edit"></i></button>'+
                                '<button class="btn btn-primary btnVer" id="btnUser_'+(i+1)+'_ver" id_usuario="'+usuarios[i].id_usuario+'" style="display: none"><i class="far fa-eye"></i></button>' +
                                '<button class="btn btn-success btnEditarUser" id="btnUser_'+(i+1)+'_editar" num="'+(i+1)+'" id_usuario="'+usuarios[i].id_usuario+'" style="display: none" ><i class="fas fa-check"></i></button>'+
                                '<button class="btn btn-warning btnCancelarUser" id="btnUser_'+(i+1)+'_cancelar" num="'+(i+1)+'" style="display: none"><i class="fas fa-times"></i></button>' +
                                '</div>'+
                                '</td>'+
                                '</tr>');
                        }
                    }
                }else{
                    $('#usuariosContent').hide();
                    $('tbody').empty();
                    $('#alerta').show();
                }
            },
            cache: false,
            processData: false
        });
        return false;
    });

    $('#btnGuardarPago').click(function () {
        var solicitud = $('#btnGuardarPago').attr('solicitud');
        var numPagos = $('#tablaCorrida > tr').length;
        numPagos--;
        var datos= {};
        datos['solicitud'] = solicitud;
        //datos['consultora'] = $("#consultora_"+numPagos+" input").val();
        datos['consultora'] = $("#consultora_"+numPagos).val();
        datos['no_pago'] = $("#no_pago_"+numPagos+" input").val();
        datos['fecha_pago'] = $("#fecha_"+numPagos+" input").val();
        cantidad = $("#cantidad_"+numPagos+" input").val();
        datos['cantidad'] = $("#cantidad_"+numPagos+" input").val();
        datos['status'] = $("#status_"+numPagos+" input").val();
        datos['forma_pago'] = $("#forma_pago_"+numPagos+" input").val();
        datos['descuento_porcentaje'] = $("#descuento_porcentaje_"+numPagos+" input").val();
        datos['descuento_cantidad'] = $("#descuento_cantidad_"+numPagos+" input").val();
        datos['id_informacion_pago'] = $('#btnAgregarPago').attr('id_info_pago');
        var id_info_pago = $('#btnAgregarPago').attr('id_info_pago');
        total = $('#monto_pendiente_'+id_info_pago).text();
        if (total.indexOf(',') != -1){
            total = total.replace(',','');
        }
        var formData = new FormData();
        formData.append('datos_pago', JSON.stringify(datos));
        //console.log(datos);
        $.ajax({
            url: root + "guardarPago",
            type: "POST",
            data: formData,
            contentType: false,
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
                    }).then(function () {
                        location.reload();
                    })
                } else if (respuesta.tipo_mensaje == "warning") {
                    swal({
                        type: 'warning',
                        title: 'Alerta',
                        text: respuesta.mensaje
                    });
                }
            },
            cache: false,
            processData: false
        });
        return false;
    });

    $('#btnGuardarCambio').click(function () {
        var total = 0;
        var registroPago = {};
        var registrosPagos = [];
        var band = $('#tablaCorrida > tr').length-1;
        for (var i=1;i<=band;i++){
            status = $('input:radio[name=status'+i+']:checked').val();
            if (status ==='pendiente'){
                registroPago['id_pago'] = $('#accion_'+i+' .btnSave').attr('id_pago');
                registroPago['cantidad'] = $('#cantidad_'+i+' .text').text();
                cantidad = $('#cantidad_'+i+' .text').text().replace(',','');
                total +=  parseFloat(cantidad);
                registrosPagos.push(registroPago);
                registroPago = {};
            }
        }
        registroPago['total'] = numeral(total).format('00,000.00');
        registroPago['id_informacion_pago'] = $('#btnAgregarPago').attr('id_info_pago');
        registroPago['monto_nuevo'] = $('#monto_nuevo').val();
        registroPago['total_pago'] = $('#valor_final').text();
        registrosPagos.push(registroPago);
        var formData = new FormData();
        formData.append('datosPago', JSON.stringify(registrosPagos));
        $.ajax({
            url: root + "actualizarPagosAuto",
            type: "POST",
            data: formData,
            contentType: false,
            success: function (response) {
                //console.log(response);
                respuesta = JSON.parse(response);
                if (respuesta.tipo_mensaje == "success") {
                    Swal.fire({
                        //position: 'top',
                        type: 'success',
                        title: 'Registro correcto',
                        text: respuesta.mensaje,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function () {
                        $('#btnModificarAuto').show();
                        location.reload();
                    })
                } else if (respuesta.tipo_mensaje == "warning") {
                    swal({
                        type: 'warning',
                        title: 'Alerta',
                        text: respuesta.mensaje
                    });
                }
            },
            cache: false,
            processData: false
        });
        return false;
    });
});

$(document).on('click','.btnVer',function () {
    //se limpia el contenido de la tabla
    $('#tablaConsultora').empty();
    var id_usuario = $(this).attr('id_usuario');
    var formData = new FormData();
    formData.append('idUsuario', id_usuario);
    $.ajax({
        url: root + "mostrarDatosUsuario",
        type: "POST",
        data: formData,
        contentType: false,
        success: function (response) {
            //console.log(response);
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
                        '<span class="cantidad">'+info[key].monto_pagar+'</span>'+
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
    //Ocultamos el voton de vista
    $(".btnVer").prop('disabled', false);
    $(this).prop('disabled', true);
    return false;
});

$(document).on('click','.btnAccionUsuario',function () {
    var num = $(this).attr('num');
    $(this).hide();
    $('#nombreUser_'+num+' .text').hide();
    $('#nombreUser_'+num+' .editbox').show();
    $('#emailUser_'+num+' .text').hide();
    $('#emailUser_'+num+' .editbox').show();
    $('#btnUser_'+num+'_ver').show();
    $('#btnUser_'+num+'_editar').show();
    $('#btnUser_'+num+'_cancelar').show();
});

$(document).on('click','.btnCancelarUser',function () {
    var num = $(this).attr('num');
    $(this).hide();
    $('#nombreUser_'+num+' .text').show();
    $('#nombreUser_'+num+' .editbox').hide();
    $('#emailUser_'+num+' .text').show();
    $('#emailUser_'+num+' .editbox').hide();
    $('#btnUser_'+num+'_ver').hide();
    $('#btnUser_'+num+'_editar').hide();
    $('#btnUser_'+num+'_accion').show();
});

$(document).on('click','.btnEditarUser',function () {
    var id_usuario = $(this).attr('id_usuario');
    var cont = $(this).attr('num');
    var nombre_usuario =  $('#nombreUser_'+cont+' input').val();
    var email_usuario = $('#emailUser_'+cont+' input').val();
    var datos_usuario = {};
    datos_usuario['id_usuario'] = id_usuario;
    datos_usuario['nombre_usuario'] = nombre_usuario;
    datos_usuario['email_usuario'] = email_usuario;
    $.ajax({
        url: root + "actualizarUsuario",
        type: "POST",
        data: {'datos_usuario': JSON.stringify(datos_usuario)},
        success: function (response) {
            console.log(response);
            respuesta = JSON.parse(response);
            if (respuesta.tipo_mensaje == "success") {
                Swal.fire({
                    type: 'success',
                    title: 'Registro correcto',
                    text: respuesta.mensaje,
                    showConfirmButton: false,
                    timer: 1500
                });
                $('#nombreUser_'+cont+' .text').text(nombre_usuario);
                $('#emailUser_'+cont+' .text').text(email_usuario);
            } else if (respuesta.tipo_mensaje == "warning") {
                swal({
                    type: 'warning',
                    title: 'Alerta',
                    text: respuesta.mensaje
                });
            }
        }
    });
});

$(document).on('click','.btnAccion',function () {
    var num = $(this).attr('num');
    $(this).hide();
    $('#btn_'+num+'_verCorrida').show();
    $('#btn_'+num+'_verReporte').show();
    $('#btn_'+num+'_cancelar').show();
    $('.cantidad').mask('00,000.00', {reverse: true});
});

$(document).on('click','.btnCancelar',function () {
    var num = $(this).attr('num');
    $(this).hide();
    $('#btn_'+num+'_verCorrida').hide();
    $('#btn_'+num+'_verReporte').hide();
    $('#btn_'+num+"_accion").show();
    $('.cantidad').mask('00,000.00', {reverse: true});
});

$(document).on('click','.btnEditar',function () {
    var id = $(this).attr('id');
    $('#edit_'+id+' .text').hide();
    $('#edit_'+id+' .editbox').show();
    $('#edit_'+id+' .btnEditar').hide();
    $('#edit_'+id+' .btnSave').show();
    if ($('input:radio[name=status'+id+']:checked').val() === "acreditado" ||
        $('input:radio[name=status'+id+']:checked').val() === "parcial"){
        //$('#edit_'+id+' .btnPrint').show();
        $('#edit_'+id+' .btnSend').show();
    }
    $('#edit_'+id+' .btnPrint').show();
    $('#edit_'+id+' .btnDelete').show();
    $('.btnSave').css("margin-right", "10px");
    $('#edit_'+id+' .btnCancel').show();
    $('.cantidad').mask('00,000.00', {reverse: true});
});

$(document).on('click','.btnCancel',function () {
    var id = $(this).attr('id');
    $('#edit_'+id+' .text').show();
    $('#edit_'+id+' .editbox').hide();
    $('#edit_'+id+' .btnEditar').show();
    $('#edit_'+id+' .btnSave').hide();
    $('#edit_'+id+' .btnPrint').hide();
    $('#edit_'+id+' .btnSend').hide();
    $('#edit_'+id+' .btnCancel').hide();
    $('#edit_'+id+' .btnDelete').hide();
    /* Limpiar campo de descuento */
    $('#descuento_porcentaje_'+id+' input').val($('#descuento_porcentaje_'+id+' .text').text());
    $('#descuento_cantidad_'+id+' input').val($('#descuento_cantidad_'+id+' .text').text());
    $('#cantidad_'+id+' input').val($('#cantidad_'+id+' .text').text());
});

$(document).on('click','.btnSave',function () {
    var id = $(this).attr('id');
    var id_pago = $(this).attr('id_pago');
    var datos = [];
    var temp = {};
    var count = 0;
    var limite = (id_pago == '0')? 13 : 12;
    var total = 0;
    var band = true;
    if ($('input:radio[name=status'+id+']:checked').val() == 'parcial'){
        cantidad_original = $('#cantidad_'+id+' .text').text();
        cantidad_nueva = $('#cantidad_'+id+' input').val();
        if (cantidad_original == cantidad_nueva){
            Swal.fire({
                type: 'warning',
                title: '¡Alerta!',
                text: 'No ah realizado cambios en la cantidad'
            })
            band = false;
        }else {
            $("#parcial").modal("show");
            id_pago_global = parseInt(id_pago);
            num_pago = parseInt(id);
            tipo_pago = 'parcial';
            //Se actualiza el campo de cantidad pagada
            $('#cantidad_pagada_'+id+' input').val(cantidad_nueva);
            $('#cantidad_pagada_'+id+' .text').text(cantidad_nueva);
        }
    }
    if ($('input:radio[name=status'+id+']:checked').val() == 'aplazado'){
        cantidad_original = $('#cantidad_'+id+' .text').text();
        $("#aplazado").modal("show");
        id_pago_global = parseInt(id_pago);
        num_pago = parseInt(id);
        tipo_pago = 'aplazado';
    }
    if ($('input:radio[name=status'+id+']:checked').val() == 'proyecto'){
        id_info_pago = $(this).attr('id_info_pago');
        generaPendienteProyecto(id_info_pago,id_pago);
    }
    if (band){
        if ($('#cantidad_'+id+' input').val() != 0){
            temp['cantidad'] = $('#cantidad_'+id+' input').val();
            $('#edit_'+id).find('input').each(function () {
                var name = $(this).attr('name');
                if (name === "descuento_porcentaje"){
                    if ($(this).val() == 0){
                        $('#descuento_cantidad_'+id+' .text').text(0);
                        $('#descuento_cantidad_'+id+' input').val(0);
                        $('#cantidad_pagada_'+id+' input').val(temp['cantidad']);
                        $('#cantidad_pagada_'+id+' .text').text(temp['cantidad']);
                    }else{
                        var porcentaje = $(this).val() / 100;
                        var cantidad = $('#cantidad_'+id+' input').val();
                        cantidad = cantidad.replace(',', '');
                        cantidad = parseFloat(cantidad);
                        var descuento = cantidad * porcentaje;
                        descuento = descuento.toFixed(2);
                        //Se redondea el descuento
                        descuento = Math.round(descuento);
                        $('#descuento_cantidad_'+id+' .text').text(descuento);
                        $('#descuento_cantidad_'+id+' input').val(descuento);
                        var cantidad_con_descuento = cantidad-descuento;
                        cantidad_con_descuento = numeral(cantidad_con_descuento).format('00,000.00'); //formato de moneda en javascript
                        //no se actualiza en el el campo del formulario
                        $('#cantidad_pagada_'+id+' input').val(cantidad_con_descuento);
                        $('#cantidad_pagada_'+id+' .text').text(cantidad_con_descuento);
                        temp['descuento'] = descuento;
                    }
                }
                //if (name == 'consultora')
                    //  $('#consultora_'+id).text($(this).val()); //cambio
                if (name == 'status'+id){
                    temp['status'] = $('input:radio[name=status'+id+']:checked').val();
                } else temp[name] = $(this).val();
                count++;
                if (count == limite){
                    datos.push(temp);
                    count = 0;
                    temp = {};
                }
                $('#'+name+'_'+id+' .text').text($(this).val());
            });
            if (temp['status'] == 'aplazado'){
                $('#cantidad_'+id+' .text').text(0);
                $('#cantidad_'+id+' input').val(0);
                //Se actualiza en cero el campo cantidad pagada
                $('#cantidad_pagada_'+id+' .text').text(0);
                $('#cantidad_pagada_'+id+' input').val(0);
            }
            count = 1;
            temp['id_pago'] = id_pago;
            if (temp['status'] == 'parcial')
                temp['cantidad'] = cantidad_nueva;
            else
                temp['cantidad'] = $('#cantidad_'+id+' input').val();
            var info_id_pago = $('#btnAgregarPago').attr('id_info_pago');
            temp['id_informacion_pago'] = info_id_pago;
            datos.push(temp);
            $('#edit_'+id+' .text').show();
            $('#edit_'+id+' .editbox').hide();
            $('#edit_'+id+' .btnEditar').show();
            $('#edit_'+id+' .btnSave').hide();
            $('#edit_'+id+' .btnCancel').hide();
            $('#edit_'+id+' .btnDelete').hide();
            $('#edit_'+id+' .btnPrint').hide();
            $('#edit_'+id+' .btnSend').hide();
            var formData = new FormData();
            formData.append('datosPago', JSON.stringify(datos));
            $.ajax({
                url: root + "actualizarPagoUsuario",
                type: "POST",
                data: formData,
                contentType: false,
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
                        })
                    } else if (respuesta.tipo_mensaje == "warning") {
                        swal({
                            type: 'warning',
                            title: 'Alerta',
                            text: respuesta.mensaje
                        });
                    }
                },
                cache: false,
                processData: false
            });
            $('#status_'+id).removeClass();
            $('#status_'+id).addClass('estado_'+temp['status']);
            $('#status_'+id+' .text').text(temp['status']);
            //Modificamos los totales
            modificar_valores(calcularTotalConDescuentos());
            $('#restante').text(calcularRestante());
        }else
            Swal.fire({
                type: 'warning',
                title: '¡Alerta!',
                text: 'No se puede acreditar una cantidad en 0'
            })
    }
    return false;
});

$('#generarParcial').click(function () {
    //Se actuliza la propiedad solicitud para indentificar que es parcial
    $('#btnGuardarPago').attr('solicitud', 'parcial');
    num_pago += 1;
    cantidad_original = cantidad_original.replace(',','');
    cantidad_original = parseFloat(cantidad_original);
    cantidad_nueva = cantidad_nueva.replace(',','');
    cantidad_nueva = parseFloat(cantidad_nueva);
    var diferencia = cantidad_original - cantidad_nueva;
    var opcion = $('input:radio[name=valorParcial]:checked').val();
    if (opcion == "siguiente"){
        var id_pago_siguiente = id_pago_global + 1;
        var num_pagos = $('#tablaCorrida tr').length;
        if (num_pagos == num_pago){
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: 'Este es el ultimo pago'
            })
        }else {
            $.ajax({
                url: root + "aumentarPagoProximo",
                type: "POST",
                data: {'id_pago':id_pago_siguiente,'cantidad':diferencia},
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
                        })
                    } else if (respuesta.tipo_mensaje == "warning") {
                        swal({
                            type: 'warning',
                            title: 'Alerta',
                            text: respuesta.mensaje
                        });
                    }
                }
            });
            cantidad_anterior = $('#cantidad_'+num_pago+' .text').text();
            cantidad_anterior = cantidad_anterior.replace(',','');
            cantidad_nueva = numeral(parseFloat(cantidad_anterior) + diferencia).format('00,000.00');
            $('#cantidad_'+num_pago+' .text').text(cantidad_nueva);
            $('#cantidad_'+num_pago+' input').val(cantidad_nueva);
            $('#cantidad_pagada_'+num_pago+' .text').text(cantidad_nueva);
            $('#cantidad_pagada_'+num_pago+' input').val(cantidad_nueva);
        }
    }
    if (opcion == "distribuido"){
        count = 1;
        datosPago = [];
        temp = [];
        numPagosPendientes = 0;
        $('#tablaCorrida tr').each(function () {
            status = $('input:radio[name=status'+count+']:checked').val();
            if (status == "pendiente") {
                numPagosPendientes++;
                temp.push($('#cantidad_'+count).attr('id_pago'));
                count++;
            }else count++;
        });
        datosPago.push(temp);
        temp = [];
        if (numPagosPendientes != 0){
            cantidad_dividida = diferencia / numPagosPendientes;
            count = 1;
            //Verificamos si no hay un desface de cantidades
            var total = $('#monto_total').text();
            $('#tablaCorrida tr').each(function () {
                cantidad = $("#cantidad_"+count+" input").val();
                status = $('input:radio[name=status'+count+']:checked').val();
                if (typeof cantidad !== 'undefined' && status == "pendiente") {
                    if (cantidad.indexOf(',') != -1){
                        cantidad = cantidad.replace(',','');
                    }
                    cantidad = parseFloat(cantidad);
                    cantidad  += cantidad_dividida;
                    cantidad = Math.round(cantidad);
                    cantidad = numeral(cantidad).format('00,000.00');
                    $('#cantidad_'+count+' .text').text(cantidad);
                    $('#cantidad_'+count+' input').val(cantidad);
                    $('#cantidad_pagada_'+count+' .text').text(cantidad);
                    $('#cantidad_pagada_'+count+' input').val(cantidad);
                    temp.push(cantidad);
                    count++;
                }else count++;
            });
            var cantidad_sobrada = parseFloat(total.replace(',', '')) - parseFloat(calcularTotal());
            cantidad_sobrada = parseFloat(cantidad_sobrada.toFixed(2));
            var cantidadPagoSigiente = parseFloat($('#cantidad_'+num_pago+' .text').text().replace(',', ''));
            if (cantidad_sobrada != 0){
                cantidadPagoSigiente = cantidadPagoSigiente + cantidad_sobrada;
                cantidadPagoSigiente = numeral(cantidadPagoSigiente.toFixed(2)).format('00,000.00');
                $('#cantidad_'+num_pago+' .text').text(cantidadPagoSigiente);
                $('#cantidad_'+num_pago+' input').val(cantidadPagoSigiente);
                $('#cantidad_pagada_'+num_pago+' .text').text(cantidadPagoSigiente);
                $('#cantidad_pagada_'+num_pago+' input').val(cantidadPagoSigiente);
                temp[0] = cantidadPagoSigiente;
                $('#total_descuento').text(calcularTotalConDescuentos());
            }
            datosPago.push(temp);
            $.ajax({
                url: root + "actualizarCantidadPagos",
                type: "POST",
                data: {'datosPago':JSON.stringify(datosPago)},
                success: function (response) {
                    //console.log(response);
                    respuesta = JSON.parse(response);
                    if (respuesta.tipo_mensaje == "success") {
                        Swal.fire({
                            //position: 'top',
                            type: 'success',
                            title: 'Registro correcto',
                            text: respuesta.mensaje,
                            showConfirmButton: false,
                            timer: 1500
                        })
                    } else if (respuesta.tipo_mensaje == "warning") {
                        swal({
                            type: 'warning',
                            title: 'Alerta',
                            text: respuesta.mensaje
                        });
                    }
                }
            });
        }else
            swal({
                type: 'warning',
                title: 'Alerta',
                text: "¡No hay pagos posteriores!"
            });
    } 
    if (opcion == "nuevo"){
        $("#cantidad").val(numeral(diferencia).format('00,000.00'));
        $("#agregarPago").modal("show");
    }
    $('#parcial').modal('hide');
    //Modificamos los totales
    modificar_valores(calcularTotalConDescuentos());
    $('#restante').text(calcularRestante());
});

$('#generarAplazado').click(function () {
    //De actuliza la propiedad solicitud para indentificar que es aplazado
    $('#btnGuardarPago').attr('solicitud', 'aplazado');
    num_pago += 1;
    cantidad_original = cantidad_original.replace(',','');
    cantidad_original = parseFloat(cantidad_original);
    var opcion = $('input:radio[name=valorAplazado]:checked').val();
    if (opcion == "siguiente"){
        var id_pago_siguiente = id_pago_global + 1;
        var num_pagos = $('#tablaCorrida tr').length;
        if (num_pagos == num_pago){
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: 'Este es el ultimo pago'
            })
        }else {
            $.ajax({
                url: root + "aumentarPagoProximo",
                type: "POST",
                data: {'id_pago':id_pago_siguiente,'cantidad':cantidad_original},
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
                        })
                    } else if (respuesta.tipo_mensaje == "warning") {
                        swal({
                            type: 'warning',
                            title: 'Alerta',
                            text: respuesta.mensaje
                        });
                    }
                }
            });
            cantidad_anterior = $('#cantidad_'+num_pago+' .text').text();
            cantidad_anterior = cantidad_anterior.replace(',','');
            cantidad_nueva = numeral(parseFloat(cantidad_anterior) + cantidad_original).format('00,000.00');
            $('#cantidad_'+num_pago+' .text').text(cantidad_nueva);
            $('#cantidad_'+num_pago+' input').val(cantidad_nueva);
            //Se actualiza la cantidad pagada
            $('#cantidad_pagada_'+num_pago+' .text').text(cantidad_nueva);
            $('#cantidad_pagada_'+num_pago+' input').val(cantidad_nueva);
        }
    }
    if (opcion == "distribuido"){
        count = 1;
        datosPago = [];
        temp = [];
        numPagosPendientes = 0;
        $('#tablaCorrida tr').each(function () {
            status = $('input:radio[name=status'+count+']:checked').val();
            if (status == "pendiente") {
                numPagosPendientes++;
                temp.push($('#cantidad_'+count).attr('id_pago'));
                count++;
            }else count++;
        });
        datosPago.push(temp);
        temp = [];
        if (numPagosPendientes != 0){
            cantidad_dividida = cantidad_original / numPagosPendientes;
            count = 1;
            $('#tablaCorrida tr').each(function () {
                cantidad = $("#cantidad_"+count+" input").val();
                status = $('input:radio[name=status'+count+']:checked').val();
                if (typeof cantidad !== 'undefined' && status == "pendiente") {
                    if (cantidad.indexOf(',') != -1){
                        cantidad = cantidad.replace(',','');
                    }
                    cantidad = parseFloat(cantidad);
                    cantidad  += cantidad_dividida;
                    cantidad = Math.round(cantidad);
                    cantidad = numeral(cantidad).format('00,000.00');
                    $('#cantidad_'+count+' .text').text(cantidad);
                    $('#cantidad_'+count+' input').val(cantidad);
                    //Se actualiza la cantidad pagada
                    $('#cantidad_pagada_'+count+' .text').text(cantidad);
                    $('#cantidad_pagada_'+count+' input').val(cantidad);
                    temp.push(cantidad);
                    count++;
                }else count++;
            });
            //Verificamos si no hay un desface de cantidades
            var total = $('#monto_total').text();
            var cantidad_sobrada = parseFloat(total.replace(',', '')) - parseFloat(calcularTotal());
            cantidad_sobrada = parseFloat(cantidad_sobrada.toFixed(2));
            var cantidadPagoSigiente = parseFloat($('#cantidad_'+num_pago+' .text').text().replace(',', ''));
            if (cantidad_sobrada != 0){
                cantidadPagoSigiente = cantidadPagoSigiente + cantidad_sobrada;
                cantidadPagoSigiente = numeral(cantidadPagoSigiente.toFixed(2)).format('00,000.00');
                $('#cantidad_'+num_pago+' .text').text(cantidadPagoSigiente);
                $('#cantidad_'+num_pago+' input').val(cantidadPagoSigiente);
                $('#cantidad_pagada_'+num_pago+' .text').text(cantidadPagoSigiente);
                $('#cantidad_pagada_'+num_pago+' input').val(cantidadPagoSigiente);
                temp[0] = cantidadPagoSigiente;
                $('#total_descuento').text(calcularTotalConDescuentos());
            }
            datosPago.push(temp);
            $.ajax({
                url: root + "actualizarCantidadPagos",
                type: "POST",
                data: {'datosPago':JSON.stringify(datosPago)},
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
                        })
                    } else if (respuesta.tipo_mensaje == "warning") {
                        swal({
                            type: 'warning',
                            title: 'Alerta',
                            text: respuesta.mensaje
                        });
                    }
                }
            });
        }else
            swal({
                type: 'warning',
                title: 'Alerta',
                text: "¡No hay pagos posteriores!"
            });
    }
    if (opcion == "nuevo"){
        $("#cantidad").val(numeral(cantidad_original).format('00,000.00'));
        $("#agregarPago").modal("show");
    }
    $('#aplazado').modal('hide');
    //Modificamos los totales
    modificar_valores(calcularTotalConDescuentos());
    $('#restante').text(calcularRestante());
});

$(document).on('click', '.btnDelete', function () {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡El registro se eliminará!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, eliminar!'
    }).then((result) => {
        if (result.value) {
            var pago = {};
            pago['id_pago'] = $(this).attr('id_pago');
            id = $(this).attr('id');
            cantidad = $("#cantidad_"+id+" input").val();
            if (cantidad.indexOf(',') != -1){
                cantidad = cantidad.replace(',','');
            }
            cantidad = parseFloat(cantidad);
            total = $('#monto_total').text();
            if (total.indexOf(',') != -1){
                total = total.replace(',','');
            }
            total = parseFloat(total);
            total = total - cantidad;
            pago['total'] = total;
            id_info_pago = $('#btnAgregarPago').attr('id_info_pago');
            pago['id_informacion_pago'] = id_info_pago;
            pago['status'] = $('#status_'+id+' .text').text();
            pago['cantidad'] = cantidad;
            pago['descuento'] = $('#descuento_cantidad_'+id+' .text').text();
            monto_pendiente = $('#monto_pendiente_'+id_info_pago).text();
            if (monto_pendiente.indexOf(',') != -1){
                monto_pendiente = monto_pendiente.replace(',','');
            }
            pago['monto_pendiente'] = monto_pendiente;
            $.ajax({
                url: root + "eliminarPago",
                type: "POST",
                data: pago,
                success: function (response) {
                    //console.log(response);
                    respuesta = JSON.parse(response);
                    if (respuesta.tipo_mensaje == "success") {
                        Swal.fire({
                            //position: 'top',
                            type: 'success',
                            title: '¡Eliminado!',
                            text: respuesta.mensaje,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function () {
                            location.reload();
                        })
                    }
                }
            });
            return false;
        }
    })
});

$(document).on('click','#agregarModalBtn',function () {
    var total_descuento = parseFloat(($("#total_descuento").text()).replace(',',''));
    var monto_total = parseFloat(($('#monto_total').text()).replace(',',''));
    var mensaje = $("#mensaje-modal");
    var fecha = moment($('#fecha').val()).format('DD-MMM-YYYY');
    var cantidad = $('#cantidad').val();
    var numPagos = $('#tablaCorrida > tr').length;
    var idInfoPago = $('#btnAgregarPago').attr('id_info_pago');
    var periodoPago = (numPagos >= 12) ? ' qin.' : ' mes.';
    var html = '<tr id="edit_'+numPagos+'">' +
        '<td id="no_pago_'+numPagos+'">'+
        '<span>'+numPagos+periodoPago+'</span>' +
        '<input type="text" name="no_pago" class="form-control" value="'+numPagos+periodoPago+'" style="display: none; max-width: 60px;">' +
        '<input type="text" id="consultora_'+numPagos+'" name="consultora" class="form-control editbox" value="'+$('#consultora_'+(numPagos-1)).val()+'" style="display: none; max-width: 60px;">' +
        '</td>' +
        '<td style="min-width: 110px;" id="fecha_'+numPagos+'">' +
        '<span>'+fecha+'</span>' +
        '<input type="text" name="fecha_pago" class="form-control" value="'+fecha+'" style="display: none; max-width: 60px;">' +
        '</td>' +
        '<td id="cantidad_'+numPagos+'">' +
        '<span class="text cantidad">'+cantidad+'</span>' +
        '<input type="text" name="cantidad" class="editbox form-control cantidad" value="'+cantidad+'" style="display: none; max-width: 100px;">' +
        '</td>' +
        '<td id="status_'+numPagos+'" class="estado_pendiente">' +
        '<span class="text">Pendiente</span>' +
        '<div class="editbox" style="display: none; max-width: 90px;">'+
        '<div class="form-check form-check-inline">'+
        '<input class="form-check-input" type="radio" name="status'+numPagos+'" id="status_pendiente'+numPagos+'" value="pendiente" checked>'+
        '<label class="form-check-label" for="status">Pendiente</label>'+
        '</div>'+
        '<div class="form-check form-check-inline">'+
        '<input class="form-check-input" type="radio" name="status'+numPagos+'" id="status_acreditado'+numPagos+'" value="acreditado">'+
        '<label class="form-check-label" for="status2">Acreditado</label>'+
        '</div>'+
        '<div class="form-check form-check-inline">'+
        '<input class="form-check-input" type="radio" name="status'+numPagos+'" id="status_aplazado'+numPagos+'" value="aplazado">'+
        '<label class="form-check-label" for="status3">Aplazado</label>'+
        '</div>'+
        '<div class="form-check form-check-inline">'+
        '<input class="form-check-input" type="radio" name="status'+numPagos+'" id="status_parcial'+numPagos+'" value="parcial">'+
        '<label class="form-check-label" for="status4">Parcial</label>'+
        '</div>'+
        '<div class="form-check form-check-inline">'+
        '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_proyecto'+cont+'" value="proyecto">'+
        '<label class="form-check-label" for="status_proyecto'+cont+'">Proyecto</label>'+
        '</div>'+
        '</div>'+
        '</td>' +
        '<td id="forma_pago_'+numPagos+'">' +
        '<span class="text">Pago en una sola exhibición</span>' +
        '<input type="text" name="forma_pago" class="editbox form-control" value="pago en una sola exhibición" style="display: none; max-width: 200px;">' +
        '</td>' +
        '<td id="descuento_porcentaje_'+numPagos+'">' +
        '<span class="text">0</span>' +
        '<input type="text" name="descuento_porcentaje" class="editbox form-control porcentaje_decuento" num="'+numPagos+'" value="0" style="display: none; max-width: 80px;" >' +
        '</td>' +
        '<td id="descuento_cantidad_'+numPagos+'">' +
        '<span class="text">0</span>' +
        '<input type="text" name="descuento_cantidad" class="editbox form-control descuento" value="0" style="display: none; max-width: 80px;" disabled>' +
        '</td>' +
        '<td id="cantidad_pagada_'+numPagos+'">' +
        '<span class="text cantidad_pagada">'+cantidad+'</span>' +
        '<input type="text" name="cantidad_pagada" class="editbox form-control cantidad_pagada" value="'+cantidad+'" style="display: none; max-width: 100px;" disabled>' +
        '</td>' +
        '<td style="min-width: 120px;">'+
        '<button class="btn btn-primary btnEditar" id="'+numPagos+'" disabled><i class="far fa-edit"></i></button>' +
        '<button class="btn btn-success btnSave" id="'+numPagos+'"  style="display: none" id_pago=0 style="margin-right: 10px;"><i class="fas fa-check"></i></button>' +
        '<button class="btn btn-danger btnCancel" id="'+numPagos+'" style="display: none"><i class="fas fa-times"></i></button>' +
        '</td>' +
        '</tr>';
    $('#btnAgregarPago').hide();
    $(html).insertBefore($('#final'));
    cantidad = parseFloat(cantidad.replace(',',''));
    if (tipo_pago == 'pendiente'){
        monto_total += cantidad;
        total_descuento += cantidad;
        var diferencia = monto_total - total_descuento;
        diferencia = numeral(diferencia).format('00,000.00');
        monto_total = numeral(monto_total).format('00,000.00');
        total_descuento = numeral(total_descuento).format('00,000.00');
        $("#monto_total").text(monto_total);
        $('#montoAPagar').val(monto_total);
        $('#total_descuento').text(total_descuento);
        $('#diferencia').text(diferencia);
    }else
        modificar_valores(calcularTotalConDescuentos());
    $('#agregarPago').modal('hide');
    $('#btnGuardarPago').show();
    $('#btnModificarAuto').hide();
    $('#restante').text(calcularRestante());
    $('.cantidad').mask('00,000.00', {reverse: true});
});

$(document).on('click','#modificarCorridas',function () {
    var monto_nuevo = $("#monto_nuevo").val();
    monto_nuevo = monto_nuevo.replace(',', '');
    monto_nuevo = parseFloat(monto_nuevo);
    var montoConFormato = monto_nuevo * 2.5;
    //console.log(montoConFormato);
    var numPagos = $('#tablaCorrida > tr').length-1;
    var monto = 0;
    //console.log(numPagos);
    monto = montoConFormato / numPagos;
    monto = monto.toFixed(2);
    var band = false;
    for (var i=1; i <= numPagos; i++) {
        if ($('#status_'+i+' .text').text() === "pendiente"){
            $('#cantidad_'+i+' .text').text(numeral(monto).format('00,000.00'));
            $('#cantidad_'+i+' input').val(numeral(monto).format('00,000.00'));
            band = true;
        }
    }
    if (band){
        var total = calcularTotal();
        $('#valor_final').text(numeral(total).format('00,000.00'));
        $('#updateAutomatic').modal('hide');
        $('#btnGuardarCambio').show();
        $('#btnModificarAuto').hide();
        $('.cantidad').mask('00,000.00', {reverse: true});
    }else
        Swal.fire({
            type: 'warning',
            title: '¡Alerta!',
            text: 'No hay pagos pendientes'
        })
});

$(document).on('click','.btnVerCorrida',function () {
    var num = $(this).attr('num');
    var formData = new FormData();
    var id_info_pago = $(this).attr('id_info_pago');
    var monto_total = $(this).attr('monto_pago');
    formData.append('id_informacion_pago', id_info_pago);
    $.ajax({
        url: root + "mostrarCorrida",
        type: "POST",
        data: formData,
        contentType: false,
        success: function (response) {
            //console.log(response);
            var info = JSON.parse(response);
            $('#infoCorrida').show();
            var elmnt = document.getElementById("infoCorrida");
            elmnt.scrollIntoView();
            var total_descuento = 0;
            var cont = 1;
            $('#tablaCorrida').empty();
            Object.keys(info).forEach(function(key) {
                var cantidad = info[key].cantidad;
                if (cantidad.indexOf(',') != -1){
                    cantidad = cantidad.replace(',','');
                }
                cantidad = parseFloat(cantidad);
                var descuento = parseFloat(info[key].descuento_cantidad);
                var cantidad_pagada = cantidad - descuento;
                total_descuento += cantidad_pagada;
                cantidad_pagada = numeral(cantidad_pagada).format('00,000.00');
                $('#tablaCorrida').append('<tr id="edit_'+cont+'">' +
                    '<td>'+
                    '<span>'+info[key].no_pago+'</span>' +
                    '<input type="text" id="consultora_'+cont+'" name="consultora" class="form-control consultora" value="'+info[key].consultora+'" style="display: none">'+
                    '</td>' +
                    '<td id="fecha_'+cont+'" style="min-width: 110px;">' +
                    '<span>'+info[key].fecha_pago+'</span>' +
                    '</td>' +
                    '<td id="cantidad_'+cont+'" id_pago="'+info[key].id_pago+'">' +
                    '<span class="text cantidad">'+info[key].cantidad+'</span>' +
                    '<input type="text" name="cantidad" class="editbox form-control cantidad" value="'+info[key].cantidad+'" style="display: none; max-width: 100px;">' +
                    '</td>' +
                    '<td id="status_'+cont+'" class="estado_'+info[key].status+'">' +
                    '<span class="text">'+info[key].status+'</span>' +
                    '<div class="editbox" style="display: none; max-width: 90px;">'+
                    '<div class="form-check form-check-inline">'+
                    '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_pendiente'+cont+'" value="pendiente">'+
                    '<label class="form-check-label" for="status_pendiente'+cont+'">Pendiente</label>'+
                    '</div>'+
                    '<div class="form-check form-check-inline">'+
                    '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_acreditado'+cont+'" value="acreditado">'+
                    '<label class="form-check-label" for="status_acreditado'+cont+'">Acreditado</label>'+
                    '</div>'+
                    '<div class="form-check form-check-inline">'+
                    '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_aplazado'+cont+'" value="aplazado">'+
                    '<label class="form-check-label" for="status_aplazado'+cont+'">Aplazado</label>'+
                    '</div>'+
                    '<div class="form-check form-check-inline">'+
                    '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_parcial'+cont+'" value="parcial">'+
                    '<label class="form-check-label" for="status_parcial'+cont+'">Parcial</label>'+
                    '</div>'+
                    '<div class="form-check form-check-inline">'+
                    '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_proyecto'+cont+'" value="proyecto">'+
                    '<label class="form-check-label" for="status_proyecto'+cont+'">Proyecto</label>'+
                    '</div>'+
                    '</div>'+
                    '</td>' +
                    '<td id="forma_pago_'+cont+'">' +
                    '<span class="text">'+info[key].forma_pago+'</span>' +
                    '<input type="text" name="forma_pago" class="editbox form-control" value="'+info[key].forma_pago+'" style="display: none; max-width: 200px;">' +
                    '</td>' +
                    '<td id="descuento_porcentaje_'+cont+'">' +
                    '<span class="text">'+info[key].descuento_porcentaje+'</span>' +
                    '<input type="text" name="descuento_porcentaje" class="editbox form-control" value="'+info[key].descuento_porcentaje+'" style="display: none; max-width: 80px;" >' +
                    '</td>' +
                    '<td id="descuento_cantidad_'+cont+'">' +
                    '<span class="text">'+info[key].descuento_cantidad+'</span>' +
                    '<input type="text" name="descuento_cantidad" class="editbox form-control" value="'+info[key].descuento_cantidad+'" style="display: none; max-width: 80px;" disabled>' +
                    '</td>' +
                    '<td id="cantidad_pagada_'+cont+'" id_pago="'+info[key].id_pago+'">' +
                    '<span class="text cantidad_pagada">'+cantidad_pagada+'</span>' +
                    '<input type="text" name="cantidad_pagada" class="editbox form-control cantidad_pagada" value="'+cantidad_pagada+'" style="display: none; max-width: 100px;" disabled>' +
                    '</td>' +
                    '<td style="min-width: 120px;" id="accion_'+cont+'">' +
                    '<div class="btn-group btn-group-toggle" data-toggle="buttons">'+
                    '<button class="btn btn-primary btnEditar" id="'+cont+'"><i class="far fa-edit"></i></button>' +
                    '<button class="btn btn-success btnSave" id="'+cont+'" id_pago="'+info[key].id_pago+'" id_info_pago="'+info[key].id_informacion_pago+'" style="display: none" style="margin-right: 10px;"><i class="fas fa-check"></i></button>' +
                    '<button class="btn btn-danger btnDelete" id="'+cont+'" id_pago="'+info[key].id_pago+'" style="display: none"><i class="fa fa-trash-alt"></i></button>'+
                    '<button class="btn btn-info btnPrint"  style="display: none" id="'+cont+'" id_pago="'+info[key].id_pago+'" id_info_pago="'+info[key].id_informacion_pago+'"><i class="fa fa-print"></i></button>'+
                    '<button class="btn btn-dark btnSend" style="display: none"><i class="fa fa-envelope" onclick="generarReportePago('+info[key].id_pago+','+info[key].id_informacion_pago+')"></i></button>'+
                    '<button class="btn btn-warning btnCancel" id="'+cont+'" style="display: none"><i class="fas fa-times"></i></button>' +
                    '</div>'+
                    '</td>' +
                    '</tr>');
                switch (info[key].status) {
                    case "pendiente":
                        $("#status_pendiente"+cont).attr('checked','checked');
                        break;
                    case "acreditado":
                        $("#status_acreditado"+cont).attr('checked','checked');
                        break;
                    case "aplazado":
                        $("#status_aplazado"+cont).attr('checked','checked');
                        break;
                    case "parcial":
                        $("#status_parcial"+cont).attr('checked','checked');
                        break;
                    default:
                        $("#status_proyecto"+cont).attr('checked','checked');
                }
                cont++;
            })
            total_descuento = total_descuento.toFixed(2);
            if (parseFloat(monto_total.replace(',','')) < total_descuento)
                diferencia = total_descuento - parseFloat(monto_total.replace(',',''));
            else
                diferencia = parseFloat(monto_total.replace(',','')) - total_descuento;
            diferencia = numeral(diferencia).format('00,000.00');
            //Se asigana el total al final de la tabla
            total_descuento = numeral(total_descuento).format('00,000.00');
            $('#tablaCorrida').append('<tr id="final" style="background-color: #d8d0d0;"><td></td><td><strong>Total</strong></td><td id="monto_total">'+monto_total+'</td><td></td><td></td><td></td><td><strong>Total con descuento.</strong></td><td id="total_descuento" class="cantidad"><span>'+total_descuento+'</span><input type="text" name="total_descuento_input" id="total_descuento_input" value="'+total_descuento+'" style="display: none;"></td><td><strong>Restante</strong><br><span id="restante">'+calcularRestante()+'</span></td></tr>');
            //Se asigna el id de la tabla de imformación de pago
            $('#btnAgregarPago').attr('id_info_pago',id_info_pago);
            $('#btnRegestion').attr('id_info_pago',id_info_pago);
            $('.cantidad').mask('00,000.00', {reverse: true});
        },
        cache: false,
        processData: false
    });
    $(".btnVerCorrida").prop('disabled', false);
    $("#btn_"+num+"_verCorrida").prop('disabled', true);
    return false;
});

function generarReportePago(id_pago,id_info_pago){
    $('#verReporte').attr('id_info', id_info_pago);
    $('#verReporte').attr('id_pago', id_pago);
    $('#enviarReporte').attr('id_info', id_info_pago);
    $('#enviarReporte').attr('id_pago', id_pago);
    $("#modalReporte").modal("show");
}

/****** Impresion desde Mac *****/
$(document).on('click','.btnPrint',function () {
    $("#logo_tr").show();
    var datos = {};
    var num = $(this).attr("id");
    if ($('input:radio[name=status'+num+']:checked').val() == 'pendiente'){
        var cantidad = parseFloat(($('#cantidad_'+num+' input').val()).replace(',',''));
        var descuento = parseFloat($('#descuento_porcentaje_'+num+' input').val());
        descuento = (descuento / 100)*cantidad;
        $('#cantidad_'+num+' input').val(numeral(cantidad - descuento).format('00,000.00'));
        $('#descuento_cantidad_'+num+' input').val(descuento);
    }
    datos['id_pago'] = $(this).attr("id_pago");
    datos['id_info_pago'] = $(this).attr("id_info_pago");
    datos['cantidad'] = $('#cantidad_'+num+' input').val();
    datos['status'] = $('input:radio[name=status'+num+']:checked').val();
    datos['descuento_porcentaje'] = $('#descuento_porcentaje_'+num+' input').val();
    datos['descuento_cantidad'] = $('#descuento_cantidad_'+num+' input').val();
    var formData = new FormData();
    formData.append('datos', JSON.stringify(datos));
    $.ajax({
        url: root + "generarRecibo",
        type: "POST",
        data: formData,
        contentType: false,
        success: function (response) {
            //console.log(response);
            data = JSON.parse(response);
            var success = window.location.href;
            var ver = '1';
            var datatype = 'pdf';
            var reselect = 'yes';
            var cut = 'feed';
            var fittowidth = 'yes';
            var paperwidth = '80';
            var urlData = '';
            var imgData;
            html2canvas($("#logo_tr"), {
                useCORS: true,
                onrendered: function (canvas) {
                    imgData = canvas.toDataURL('image/png');
                    var pdf = new jsPDF('p', 'mm', [230, 360])
                    //pdf.addImage(imgData, 'PNG', 15, 0, 54, 26)
                    pdf.setFontSize(20)
                    pdf.setFont("helvetica");
                    pdf.text('TR network SA de CV', 5, 10)
                    pdf.setFontSize(6)
                    pdf.setFontType("normal")
                    pdf.text('Blvd. San Felipe 224', 3, 20)
                    pdf.text('Col. Valle del Angel, C. P. 72040',3,25)
                    pdf.text('Puebla, Pue.',3,30)
                    pdf.text('Tel. 222 889 5608',3,35)
                    pdf.text('World Trade Center CDMX',45,20)
                    pdf.text('Montecito 38, Nápoles, 03810',45,25)
                    pdf.text('Ciudad de México CDMX', 45,30)
                    pdf.setFontSize(10)
                    pdf.setFontType("bold")
                    pdf.text('Recibo de pago', 25, 45)
                    pdf.setFontSize(7)
                    pdf.setFontType("normal")
                    pdf.setLineWidth(1)
                    pdf.line(0,48,100,48)
                    pdf.text('#Pago: '+data.num_pago+"/ "+data.num_pagos,5,53)
                    pdf.text('Fecha: '+data.fecha_pago,50,53)
                    pdf.text('Recibimos de: ',5,60)
                    pdf.setFontType("bold")
                    pdf.text(data.nombre,25,60)
                    pdf.text('Descripción',5,65)
                    pdf.text('Importe', 45,65)
                    pdf.setFontType("normal")
                    pdf.text('Taller de capacitación',5,70)
                    pdf.text(data.cantidad,45,70)
                    pdf.text(data.porcentaje,5,75)
                    pdf.text(data.descuento,45,75)
                    pdf.text(data.mensaje_descuento,5,80)
                    pdf.text(data.cantidad_con_descuento,45,80)
                    pdf.text('Deuda previa: '+data.deuda_previa,5,85)
                    pdf.text('Deuda restante: '+data.deuda_restante,45,85)
                    pdf.setLineWidth(0.2)
                    pdf.line(0,90,100,90)
                    pdf.setLineWidth(0.2)
                    pdf.line(10,110,35,110)
                    pdf.line(45,110,70,110)
                    pdf.text('Entrego',20,113)
                    pdf.text('Recibió',55,113)
                    pdf.setLineWidth(1)
                    pdf.line(0,115,100,115)
                    pdf.setFontSize(5)
                    pdf.text('La reproducción apocrifa de este comprobante constituye un delito en los términos de las \n' +
                        'disposiciones fiscales. si tiene una duda sobre este recibo de pago, póngase en contacto\n' +
                        'Lic. Erika Valverde a. dcobranza@trnetwork.com.mx Por atrasos en pagos los intereses\n' +
                        'con moratorios son del 8% sobre la deuda actúal más la penalización de $9,000.00 \n'+
                        '(Nueve mil pesos 00/100 M N)',5,118)
                    if (data.sistema =='Windows' || data.sistema == 'Mac')
                        pdf.output('dataurlnewwindow');
                    else{
                        pdfData = pdf.output('datauristring');
                        pdfData = pdfData.substring(pdfData.indexOf(',')+1);
                        urlData = appscheme + host + action +
                        query_success + '=' + encodeURIComponent(success) + '&' +
                        query_ver + '=' + ver + '&' +
                        query_datatype + '=' + datatype + '&' +
                        query_data + '=' + encodeURIComponent(pdfData) + '&' +
                        query_reselect + '=' + reselect + '&' +
                        query_cut + '=' + cut + '&' +
                        query_fittowidth + '=' + fittowidth + '&' +
                        query_paperwidth + '=' + paperwidth;
                        location.href = urlData;
                    }
                }
            });
            $("#logo_tr").hide();
        },
        cache: false,
        processData: false
    });
    return false;
});

function calcularTotal() {
    //Calcular el total
    var count = 1;
    var cantidad;
    var total = 0;
    $('#tablaCorrida tr').each(function () {
        cantidad = $("#cantidad_"+count+" input").val();
        status = $('input:radio[name=status'+count+']:checked').val();
        if (typeof cantidad !== 'undefined') {
            if (cantidad.indexOf(',') != -1){
                cantidad = cantidad.replace(',','');
            }
            cantidad = parseFloat(cantidad);
            total  += cantidad;
            count++;
        }else count++;
    });
    total = total.toFixed(2);
    return total;
}

function enviarMail(id_pago, id_info_pago) {
    var req = new XMLHttpRequest();
    req.open('GET', root+"reciboPagoParcial&id_pago="+id_pago+"&id_informacion_pago="+id_info_pago, false);
    req.send(null);
    if (req.status == 200)
        Swal.fire({
            type: 'success',
            title: req.responseText,
            showConfirmButton: false,
            timer: 1500
        })
}

function verReporteCorrida(id_usuario, id_info_pago) {
    window.open(files_root+"usuario_"+id_usuario+"/Reporte Corrida de Pagos"+id_info_pago+".pdf","_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=200,left=500,width=800,height=600");
}

//evento para ver el reporte  de corrida
$('#verReporte').click(function () {
    var req = new XMLHttpRequest();
    req.open('GET', root+"verReciboParcial&id_pago="+$(this).attr('id_pago')+"&id_info="+$(this).attr('id_info'), false);
    req.send(null);
    //console.log(req.responseText);
    if (req.status == 200)
        window.open(req.responseText, '_blank', 'fullscreen=yes');
});

$('#enviarReporte').click(function () {
    var req = new XMLHttpRequest();
    req.open('GET', root+"enviarReciboParcial&id_pago="+$(this).attr('id_pago')+"&id_info="+$(this).attr('id_info'), false);
    req.send(null);
    //console.log(req.responseText);
    if (req.status == 200)
        if (req.responseText.substr(0,5) == 'Error')
            Swal.fire({
                type: 'error',
                title: req.responseText,
                showConfirmButton: false,
                timer: 1500
            });
        else
            Swal.fire({
                type: 'success',
                title: req.responseText,
                showConfirmButton: false,
                timer: 1500
            });
});

//Funcion que calcula el total con descuentos aplicados
function calcularTotalConDescuentos(){
    var totalConDescuento = 0;
    $('#tablaCorrida').find('input').each(function () {
        var name = $(this).attr('name');
        if (name==="cantidad_pagada") {
            var cantidad = $(this).val();
            totalConDescuento += parseFloat(cantidad.replace(',', ''));
        }
    });
    return numeral(totalConDescuento).format('00,000.00');
}

function modificar_valores(valor_descuento){
    $('#total_descuento span').text(valor_descuento);
    monto_total = $('#monto_total').text();
    monto_total = parseFloat(monto_total.replace(',',''));
    total_descuento = $('#total_descuento').text();
    total_descuento = parseFloat(total_descuento.replace(',',''));
    diferencia = monto_total - total_descuento;
    if (diferencia < 0)
        diferencia *= -1;
    diferencia = numeral(diferencia).format("00,000.00");
    //$('#diferencia').text(diferencia);
}

function generaPendienteProyecto(id_info_pago,id_pago) {
    var cont = 1;
    var idesPagos =[];
    var id_info_pago = id_info_pago;
    var id_pago_actual = id_pago;
    $('#tablaCorrida tr').each(function () {
        status = $('input:radio[name=status'+cont+']:checked').val();
        if (status == "pendiente") {
            $("#status_proyecto"+cont).attr('checked','checked');
            $('#status_'+cont+' span').text('proyecto');
            $('#status_'+cont).removeClass();
            $('#status_'+cont).addClass('estado_proyecto');
            id_pago = $('#cantidad_'+cont).attr('id_pago');
            idesPagos.push(id_pago);
            cont++;
        }else cont++;
    });
    $.ajax({
        url: root + "actualizarStatusPagos",
        type: "POST",
        data: {'ides_pagos': JSON.stringify(idesPagos)},
        success: function (response) {
            var respuesta = JSON.parse(response);
            Swal.fire({
                type: respuesta.tipo_mensaje,
                text: respuesta.mensaje
            })
        }
    })
    $('#btnGenerarReciboProyecto').show();
    $('#btnAgregarPago').hide();
    $('#btnRegestion').hide();
    $('#verReporteProyecto').attr('id_info', id_info_pago);
    $('#verReporteProyecto').attr('id_pago', id_pago_actual);
    $('#enviarReporteProyecto').attr('id_pago', id_pago);
}

$('#verReporteProyecto').click(function () {
    var req = new XMLHttpRequest();
    req.open('GET', root+"verReciboProyecto&id_pago="+$(this).attr('id_pago')+"&id_info="+$(this).attr('id_info'), false);
    req.send(null);
    //console.log(req.responseText);
    if (req.status == 200)
        window.open(req.responseText, '_blank', 'fullscreen=yes');
});

$('#enviarReporteProyecto').click(function () {
    var req = new XMLHttpRequest();
    req.open('GET', root+"enviarReciboProyecto&id_pago="+$(this).attr('id_pago'), false);
    req.send(null);
    //console.log(req.responseText);
    if (req.status == 200)
        if (req.responseText.substr(0,5) == 'Error')
            Swal.fire({
                type: 'error',
                title: req.responseText,
                showConfirmButton: false,
                timer: 1500
            });
        else
            Swal.fire({
                type: 'success',
                title: req.responseText,
                showConfirmButton: false,
                timer: 1500
            });
});

function calcularRestante(){
    var count = 1;
    var monto_pendiente = 0;
    $('#tablaCorrida tr').each(function () {
        status = $('input:radio[name=status'+count+']:checked').val();
        if (status == "pendiente" || status == "proyecto") {
            monto_pendiente += parseFloat($('#cantidad_'+count+' input').val().replace(',',''));
            count++;
        }else count++;
    });
    return numeral(monto_pendiente).format("00,000.00");
}

$('#btnRegestion').click(function () {
    $('#regestionar').attr('id_info_pago', $(this).attr('id_info_pago'));
});

$('#regestionForm').submit(function () {
    var id_info_pago = $('#regestionar').attr('id_info_pago');
    var fecha_nueva = $('#fechaRegestion').val();
    var monto_percibir = $("#cantidad_regestion").val();
    var monto_nuevo = $("#cantidad_regestion").val();
    monto_nuevo = parseFloat(monto_nuevo.replace(',', ''));
    monto_nuevo  *= 2.5;
    var numPagos = $('#tablaCorrida > tr').length-1;
    var monto = monto_nuevo / numPagos;
    monto = monto.toFixed(2);
    var band = false;
    var idesPagos =[];
    var pagosNum = [];
    for (var i=1; i <= numPagos; i++) {
        if ($('#status_'+i+' .text').text() === "pendiente"){
            $('#cantidad_'+i+' .text').text(numeral(monto).format('00,000.00'));
            $('#cantidad_'+i+' input').val(numeral(monto).format('00,000.00'));
            //Se actualiza el monto de la cantidad pagada
            $('#cantidad_pagada_'+i+' .text').text(numeral(monto).format('00,000.00'));
            $('#cantidad_pagada_'+i+' input').val(numeral(monto).format('00,000.00'));
            id_pago = $('#cantidad_'+i).attr('id_pago');
            idesPagos.push(id_pago);
            pagosNum.push(i);
            band = true;
        }
    }
    if (band){
        $.ajax({
            url: root + "regestionar",
            type: "POST",
            data: {
                'id_info_pago' : id_info_pago,
                'fecha_nueva' : fecha_nueva,
                'monto_percibir' : monto_percibir,
                'monto_total' : monto_nuevo,
                'cantidad' : monto,
                'num_pagos' : numPagos,
                'ides_pagos': JSON.stringify(idesPagos)
            },
            success: function (response) {
                console.log(response);
                info = JSON.parse(response);
                if (typeof info.tipo_mensaje !== 'undefined') {
                    Swal.fire({
                        type: info.tipo_mensaje,
                        title: 'Error de actualización',
                        text: info.mensaje
                    })
                }else{
                    //Se actualiza la fecha del pago
                    for (var i=0; i < pagosNum.length; i++) {
                        $('#fecha_'+pagosNum[i]).text(info[i]);
                    }
                    $('#monto_total').text(numeral(monto_nuevo).format('00,000.00'));
                    modificar_valores(calcularTotalConDescuentos());
                    $('#restante').text(calcularRestante());
                    $('#btnRegestion').hide();
                    $('#regestionModal').modal('hide');
                    $('#verCorridaRegestion').attr('id_info',id_info_pago);
                    $('#enviarCorridaRegestion').attr('id_info',id_info_pago);
                    $('#modalReporteRegestion').modal('show');
                }
            }
        });
    }else
        Swal.fire({
            type: 'warning',
            title: '¡Alerta!',
            text: 'No hay pagos pendientes'
        })
    return false;
});

$('#verCorridaRegestion').click(function () {
    var req = new XMLHttpRequest();
    req.open('GET', root+"verReporteCorridaRegestion&id_info="+$(this).attr('id_info'), false);
    req.send(null);
    //console.log(req.responseText);
    if (req.status == 200)
        window.open(req.responseText, '_blank', 'fullscreen=yes');
});

$('#enviarCorridaRegestion').click(function () {
    var req = new XMLHttpRequest();
    req.open('GET', root+"enviarReporteCorridaRegestion&id_info="+$(this).attr('id_info'), false);
    req.send(null);
    //console.log(req.responseText);
    if (req.status == 200)
        if (req.responseText.substr(0,5) == 'Error')
            Swal.fire({
                type: 'error',
                title: req.responseText,
                showConfirmButton: false,
                timer: 1500
            });
        else
            Swal.fire({
                type: 'success',
                title: req.responseText,
                showConfirmButton: false,
                timer: 1500
            });
});