var cantidad_original = "";
var cantidad_nueva = "";
var tipo_pago = "pendiente";
var num_pago = 0;
var info_corrida_existente = false;

//Función que busca a un usuario por nombre o correo (mediante enter)
$("#usuarioInput").keypress(function(e) {
    var keycode = (e.keyCode ? e.keyCode : e.which);
    if (keycode == '13') {
        var nombre = $(this).val();
        $.ajax({
            url: root+"buscarUsuario",
            type: "POST",
            data: {"termino": nombre, "activo": 'S'},
            success: function (response) {
                //console.log(response);
                if (response != 0){
                    $('#usuarioSelect').show();
                    $("#usuarioInput").hide();
                    respuesta = JSON.parse(response);
                    Object.keys(respuesta).forEach(function(key) {
                        $('#usuarioSelect').append('<option value="'+respuesta[key].id_usuario+'">'+respuesta[key].nombre+'</option>');
                    });
                    //Se manda a verificar si el usuario tiene corridas
                    verificarInfoCorrida(document.getElementById("usuarioSelect").value);
                }else{
                    Swal.fire({
                        type: 'error',
                        title: 'Error de busqueda',
                        text: 'No se encontro ningún registro'
                    })
                }
            }
        });
        return false;
    }
});

//Función que obtine el id de un usuario al ocurrir el cambio en el select
$('#usuarioSelect').on('change', function() {
    var id_usuario = document.getElementById("usuarioSelect").value;
    //Verificamos si el usuario cuenta con corridas
    verificarInfoCorrida(id_usuario);
});

//Función que verifica si el usuario tiene corridas realizadas
function verificarInfoCorrida(id_usuario){
    //Se limpian los campos del formulario
    info_corrida_existente = false;
    $("#consultora").val("");
    $("#cliente").val("");
    $("#datetimepicker1").val("");
    $("#periodo").val("");
    $("#esquema").val("");
    $("#montoAPercibir").val("");
    $("#montoAPagar").val("");
    $.ajax({
        url: root+"verificarInfoCorrida",
        type: "POST",
        data: {"id_usuario": id_usuario},
        success: function (response) {
            //Si la respuesta obtiene información insertamos los datos en el formulario de la corrida
            if (response != 0){
                respuesta = JSON.parse(response);
                $("#consultora").val(respuesta.consultora);
                $("#cliente").val(respuesta.cliente);
                $("#datetimepicker1").val(respuesta.fecha_ingreso);
                $("#periodo").val(respuesta.periodo_pago);
                $("#esquema").val(respuesta.esquema);
                $("#montoAPercibir").val(respuesta.monto_percibir);
                $("#montoAPagar").val(respuesta.monto_pagar);
                info_corrida_existente = true;
            }
        }
    });
}

//Envio de formulario "Generar corrida"
$("#formCorrida").submit(function () {
    if ($('#usuarioSelect').is(':visible')) {
        $.ajax({
            url: root + "generarCorrida",
            type: "POST",
            data: $("#formCorrida").serialize(),
            success: function (response) {
                //console.log(response);
                var respuesta = JSON.parse(response);
                if (respuesta.corrida_existente){
                    $('#alerta_corrida_existente').show();
                    Swal.fire({
                        title: '¿Qieres generar una corrida nueva?',
                        text: "¡El usuario seleccionado ya cuenta con corridas!",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si, generar corrida!'
                    }).then((result) => {
                        if (result.value) {
                            Swal.fire({
                                title: '¿Es regestión?',
                                type: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Si',
                                cancelButtonText: 'No'
                            }).then((result) => {
                                if (result.value) {
                                    $.ajax({
                                        url: root+"generarRegestion",
                                        type: "POST",
                                        data: $("#formCorrida").serialize(),
                                        success: function (response) {
                                            respuesta = JSON.parse(response);
                                            if (respuesta.mensaje){
                                                Swal.fire({
                                                    type: respuesta.tipo_mensaje,
                                                    title: '¡Aviso!',
                                                    text: respuesta.mensaje
                                                });
                                            }else{
                                                //console.log(response);
                                                guardarDatosCorrida();
                                                generarCorridaRegestion(response);
                                            }
                                        }
                                    });
                                }else{
                                    guardarDatosCorrida();
                                    generarCorrida(respuesta);
                                }
                            })
                        }else
                            Swal.fire({
                                title: "¡Aviso!",
                                text: "Operación cancelada",
                                type: "warning",
                                showConfirmButton: false,
                                timer: 1500
                            });
                    })
                }else if(info_corrida_existente){
                    generarCorrida(respuesta);
                }else{
                    guardarDatosCorrida();
                    generarCorrida(respuesta);
                }
            }
        });
    }else{
        Swal.fire({
            title: 'Error',
            text: '¡Selecione un usuario!',
            type: 'warning'
        })
    }
    return false;
});

//Función que nera la corrida
function generarCorrida(respuesta){
    $('#corridaGenerada').show();
    var fechas = respuesta.fechas;
    for (var i=0; i < fechas.length ;i++){
        $('tbody').append('<tr id="edit_'+(i+1)+'">' +
            '<td>'+
            '<span>'+(i+1)+respuesta.pago+'</span>' +
            '<input type="text" name="no_pago" class="form-control" value="'+(i+1)+respuesta.pago+'" style="display: none; max-width: 90px;">' +
            '</td>' +
            '<td id="fecha_pago_'+(i+1)+'">'+
            '<span class="text">'+fechas[i]+'</span>' +
            '<input type="text" name="fecha_pago" class="editbox form-control" value="'+fechas[i]+'" style="display: none; max-width: 120px;">' +
            '</td>' +
            '<td id="cantidad_'+(i+1)+'">' +
            '<span class="text cantidad">'+respuesta.cantidad+'</span>' +
            '<input type="text" name="cantidad" class="editbox form-control cantidad" value="'+respuesta.cantidad+'" style="display: none; max-width: 90px;">' +
            '</td>' +
            '<td id="status_'+(i+1)+'" class="estado_pendiente">' +
            '<span class="text">pendiente</span>' +
            '<div class="editbox" style="display: none; max-width: 90px;">'+
            '<div class="form-check form-check-inline">'+
            '<input class="form-check-input" type="radio" name="status'+(i+1)+'" id="status_pendiente'+(i+1)+'" value="pendiente" checked="checked">'+
            '<label class="form-check-label" for="status_pendiente'+(i+1)+'">Pendiente</label>'+
            '</div>'+
            '<div class="form-check form-check-inline">'+
            '<input class="form-check-input" type="radio" name="status'+(i+1)+'" id="status_acreditado'+(i+1)+'" value="acreditado">'+
            '<label class="form-check-label" for="status_acreditado'+(i+1)+'">Acreditado</label>'+
            '</div>'+
            '<div class="form-check form-check-inline">'+
            '<input class="form-check-input" type="radio" name="status'+(i+1)+'" id="status_aplazado'+(i+1)+'" value="aplazado">'+
            '<label class="form-check-label" for="status_aplazado'+(i+1)+'">Aplazado</label>'+
            '</div>'+
            '<div class="form-check form-check-inline">'+
            '<input class="form-check-input" type="radio" name="status'+(i+1)+'" id="status_parcial'+(i+1)+'" value="parcial">'+
            '<label class="form-check-label" for="status_parcial'+(i+1)+'">Parcial</label>'+
            '</div>'+
            '</div>'+
            '</td>' +
            '<td id="forma_pago_'+(i+1)+'">' +
            '<span class="text">Pago en una sola exhibición</span>' +
            '<input type="text" name="forma_pago" class="editbox form-control" value="Pago en una sola exhibición" style="display: none; max-width: 222px;">' +
            '</td>' +
            '<td id="descuento_porcentaje_'+(i+1)+'">' +
            '<span class="text">0</span>' +
            '<input type="text" name="descuento_porcentaje" class="editbox form-control" value="0" style="display: none; max-width: 80px;" >' +
            '</td>' +
            '<td id="descuento_cantidad_'+(i+1)+'">' +
            '<span class="text">0</span>' +
            '<input type="text" name="descuento_cantidad" class="editbox form-control" value="0" style="display: none; max-width: 80px;" disabled>' +
            '</td>' +
            '<td id="cantidad_pagada_'+(i+1)+'">' +
            '<span class="text">'+respuesta.cantidad+'</span>' +
            '<input type="text" name="cantidad_pagada" class="editbox form-control" value="'+respuesta.cantidad+'" style="display: none; max-width: 80px;" disabled>' +
            '</td>' +
            '<td>' +
            '<button class="btn btn-primary btnEditar" id="'+(i+1)+'"><i class="far fa-edit"></i></button>' +
            '<button class="btn btn-success btnSave" id="'+(i+1)+'" style="display: none" style="margin-right: 10px;"><i class="fas fa-check"></i></button>' +
            '<button class="btn btn-danger btnCancel" id="'+(i+1)+'" style="display: none"><i class="fas fa-times"></i></button>' +
            '</td>' +
            '</tr>');
    }
    //Si existen dias trabajados se modificacan los valores del primer pago
    if (respuesta.cantidad_dt){
        $('#cantidad_1 .text').text(respuesta.cantidad_dt);
        $('#cantidad_1 input').val(respuesta.cantidad_dt);
        $('#cantidad_pagada_1 .text').text(respuesta.cantidad_dt);
        $('#cantidad_pagada_1 input').val(respuesta.cantidad_dt);

        $('#cantidad_'+fechas.length+' .text').text(respuesta.cantidad_res);
        $('#cantidad_'+fechas.length+' input').val(respuesta.cantidad_res);
        $('#cantidad_pagada_'+fechas.length+' .text').text(respuesta.cantidad_res);
        $('#cantidad_pagada_'+fechas.length+' input').val(respuesta.cantidad_res);
    }
    var total = $('#montoAPagar').val();
    //Modificamos la primera cantidad de pago para anivelar
    var cantidad_sobrada = parseFloat(total.replace(',', '')) - parseFloat(calcularTotal().replace(',',''));
    cantidad_sobrada = parseFloat(cantidad_sobrada.toFixed(2));
    var cantidadPago1 = parseFloat($('#cantidad_1 .text').text().replace(',', ''));
    if (cantidad_sobrada != 0){
        cantidadPago1 = cantidadPago1 + cantidad_sobrada;
        cantidadPago1 = numeral(cantidadPago1.toFixed(2)).format('00,000.00');
        $('#cantidad_1 .text').text(cantidadPago1);
        $('#cantidad_1 input').val(cantidadPago1);
        $('#cantidad_pagada_1 .text').text(cantidadPago1);
        $('#cantidad_pagada_1 input').val(cantidadPago1);
    }
    $('tbody').append('<tr id="final"><td></td><td><strong>Total</strong></td><td id="valor_final" class="cantidad">'+total+'</td><td></td><td></td><td></td><td><strong>Total con descuento</strong></td><td id="total_descuento" class="cantidad"><span>'+calcularTotalConDescuentos()+'</span><input type="text" name="input_total_descuento" id="input_total_descuento" value="'+total+'" style="display: none;"></td><td><strong>Restante</strong><br><span id="restante">'+calcularRestante()+'</span></td></tr>');
    //Deshabilitamos el boton Generar
    $('#btnGenerar').attr('disabled','disabled');
    //Hacemos scroll para ubicarnos en la tabla
    var elmnt = document.getElementById("btnEnviar");
    elmnt.scrollIntoView();
}

//Funcion que calcula el total mostrado en la tabla
function calcularTotal(){
    var total = 0;
    $('#formCorridaPagos').find('input').each(function () {
        var name = $(this).attr('name');
        if (name==="cantidad") {
            var cantidad = $(this).val();
            total += parseFloat(cantidad.replace(',', ''));
        }
    });
    return numeral(total).format('00,000.00');
}

//Funcion que calcula el total con descuentos aplicados
function calcularTotalConDescuentos(){
    var totalConDescuento = 0;
    $('#formCorridaPagos').find('input').each(function () {
        var name = $(this).attr('name');
        if (name==="cantidad_pagada") {
            var cantidad = $(this).val();
            totalConDescuento += parseFloat(cantidad.replace(',', ''));
        }
    });
    return numeral(totalConDescuento).format('00,000.00');
}

//evento para ver el reporte  de corrida
$('#verReporte').click(function () {
    var req = new XMLHttpRequest();
    req.open('GET', root+"verReporteCorrida&id_info="+$(this).attr('id_info'), false);
    req.send(null);
    //console.log(req.responseText);
    if (req.status == 200)
        window.open(req.responseText, '_blank', 'fullscreen=yes');
});

//evento que genera el pdf de una corrida de pago
function generarCorridaPagoPdf(idCorridaPago){
    var req = new XMLHttpRequest();
    req.open('GET', root+"verReporteCorrida&id_info="+idCorridaPago, false);
    req.send(null);
    if (req.status == 200)
        console.log("Reporte de corrida generado exitosamente");
}

//Evento que envia el reporte de la corrida por correo
$('#enviarReporte').click(function () {
    var req = new XMLHttpRequest();
    req.open('GET', root+'enviarReporteCorrida&id_info='+$(this).attr('id_info'), false);
    req.send(null);
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

//Evento que calcula el monto a pagar
$('#montoAPercibir').keypress(function(e){
    var keycode = (e.keyCode ? e.keyCode : e.which);
    if (keycode == '13') {
        if ($('#montoAPercibir').val() != "") {
            var monto = $('#montoAPercibir').val();
            monto = monto.replace(',', '');
            var montoConFormato = monto * 2.5;
            montoConFormato = new Intl.NumberFormat("en-IN").format(montoConFormato)+".00";
            $('#montoAPagar').val(montoConFormato);
        }
        e.preventDefault();
        return false;
    }
});

//Evento que habilita los campos de un pago para su edición
$(document).on('click','.btnEditar',function () {
    var id = $(this).attr('id');
    $('#edit_'+id+' .text').hide();
    $('#edit_'+id+' .editbox').show();
    $('#edit_'+id+' .btnEditar').hide();
    $('#edit_'+id+' .btnSave').show();
    $('.btnSave').css("margin-right", "10px");
    $('#edit_'+id+' .btnCancel').show();
    $('.cantidad').mask('00,000.00', {reverse: true});
    return false;
});

//Evento que cancela la edición de los campos de un pago
$(document).on('click','.btnCancel',function () {
    var id = $(this).attr('id');
    $('#edit_'+id+' .text').show();
    $('#edit_'+id+' .editbox').hide();
    $('#edit_'+id+' .btnEditar').show();
    $('#edit_'+id+' .btnSave').hide();
    $('#edit_'+id+' .btnCancel').hide();
    return false;
});

//Evento que guada los cambios de un pago
$(document).on('click','.btnSave',function () {
    var id = $(this).attr('id');
    var diferencia = 0;
    var band = true;
    if ($('input:radio[name=status'+id+']:checked').val() == 'parcial'){
        cantidad_original = $('#cantidad_'+id+' .text').text();
        cantidad_nueva = $('#cantidad_'+id+' input').val();
        if (cantidad_original == cantidad_nueva){
            Swal.fire({
                type: 'warning',
                title: '¡Alerta!',
                text: 'No ha realizado cambios en la cantidad'
            });
            band = false;
        }else {
            $("#parcial").modal("show");
            tipo_pago = 'parcial';
            num_pago = parseInt(id);
            //se actualiza el monto en el campo de cantidad pagada
            $('#cantidad_pagada_'+id+' .editbox').val(cantidad_nueva);
            $('#cantidad_pagada_'+id+' .text').text(cantidad_nueva);
        }
    }
    if ($('input:radio[name=status'+id+']:checked').val() == 'aplazado'){
        cantidad_original = $('#cantidad_'+id+' .text').text();
        $("#aplazado").modal("show");
        tipo_pago = 'aplazado';
        num_pago = parseInt(id);
    }
    if (band){
        $('#edit_'+id).find('input, textarea').each(function () {
            var name = $(this).attr('name');
            if (name === "descuento_porcentaje" && $(this).val()!=0){
                var porcentaje = $(this).val() / 100;
                var  cantidad = $('#cantidad_'+id+' .editbox').val();
                cantidad = cantidad.replace(',', '');
                var descuento = cantidad * porcentaje;
                descuento = descuento.toFixed(2);
                //Se aplica redondeo al descuento
                descuento = Math.round(descuento);
                var nueva_cantidad = parseFloat(cantidad - descuento);
                nueva_cantidad = numeral(nueva_cantidad).format("00,000.00");
                $('#descuento_cantidad_'+id+' .editbox').val(descuento);
                $('#cantidad_pagada_'+id+' .editbox').val(nueva_cantidad);
                $('#cantidad_pagada_'+id+' .text').text(nueva_cantidad);
                modificar_valores(calcularTotalConDescuentos());
            }
            if (name === "descuento_porcentaje" && $(this).val()==0){
                $('#descuento_cantidad_'+id+' .editbox').val(0);
                modificar_valores(calcularTotalConDescuentos());
            }
            if (name == 'status'+id){
                $('#status_'+id+' .text').text($('input:radio[name=status'+id+']:checked').val());
                $('#status_'+id).removeClass();
                $('#status_'+id).addClass('estado_'+$('input:radio[name=status'+id+']:checked').val());
            }
            else
                $('#'+name+'_'+id+' .text').text($(this).val());
        });
        $('#edit_'+id+' .text').show();
        $('#edit_'+id+' .editbox').hide();
        $('#edit_'+id+' .btnEditar').show();
        $('#edit_'+id+' .btnSave').hide();
        $('#edit_'+id+' .btnCancel').hide();
        $('.cantidad').mask('00,000.00', {reverse: true});
        $('#restante').text(calcularRestante());
    }
    return false;
});

//Función que modifica el total de descuento y regresa la diferencia entre el valor total
function modificar_valores(valor_descuento){
    $('#total_descuento span').text(valor_descuento);
    valor_final = $('#valor_final').text();
    valor_final = valor_final.replace(',','');
    total_descuento = $('#total_descuento').text();
    total_descuento = total_descuento.replace(',','');
    diferencia = valor_final - total_descuento;
    if (diferencia < 0)
        diferencia *= -1;
    diferencia = numeral(diferencia).format("00,000.00");
    //$('#diferencia').text(diferencia);
}

//Evento que agrega un pago nuevo a la corrida
$(document).on('click','#agregarModalBtn',function () {
    var mensaje = $("#mensaje-modal");
    var fecha = moment($('#fecha').val()).format('DD-MMM-YYYY');
    var cantidad = $('#cantidad');
    var numPagos = $('tbody > tr').length;
    numPagos = numPagos;
    var periodoPago = (numPagos == 13) ? ' qin.' : ' men.';
    var html = '<tr id="edit_'+numPagos+'"><td>'+
        '<span>'+numPagos+periodoPago+'</span>' +
        '<input type="text" name="no_pago" class="form-control" value="'+numPagos+periodoPago+'" style="display: none; max-width: 90px;">' +
        '</td>' +
        '<td id="fecha_pago_'+numPagos+'">'+
        '<span>'+fecha+'</span>' +
        '<input type="text" name="fecha_pago" class="form-control" value="'+fecha+'" style="display: none; max-width: 90px;">' +
        '</td>' +
        '<td id="cantidad_'+numPagos+'">' +
        '<span class="text cantidad">'+cantidad.val()+'</span>' +
        '<input type="text" name="cantidad" class="editbox form-control cantidad" value="'+cantidad.val()+'" style="display: none; max-width: 90px;">' +
        '</td>' +
        '<td id="status_'+numPagos+'" class="estado_pendiente">' +
        '<span class="text">pendiente</span>' +
        '<div class="editbox" style="display: none; max-width: 90px;">'+
        '<div class="form-check form-check-inline">'+
        '<input class="form-check-input" type="radio" name="status'+numPagos+'" id="status_pendiente'+numPagos+'" value="pendiente" checked>'+
        '<label class="form-check-label" for="status'+numPagos+'">Pendiente</label>'+
        '</div>'+
        '<div class="form-check form-check-inline">'+
        '<input class="form-check-input" type="radio" name="status'+numPagos+'" id="status_acreditado'+numPagos+'" value="acreditado">'+
        '<label class="form-check-label" for="status'+numPagos+'">Acreditado</label>'+
        '</div>'+
        '<div class="form-check form-check-inline">'+
        '<input class="form-check-input" type="radio" name="status'+numPagos+'" id="status_aplazado'+numPagos+'" value="aplazado">'+
        '<label class="form-check-label" for="status'+numPagos+'">Aplazado</label>'+
        '</div>'+
        '<div class="form-check form-check-inline">'+
        '<input class="form-check-input" type="radio" name="status'+numPagos+'" id="status_parcial'+numPagos+'" value="parcial">'+
        '<label class="form-check-label" for="status'+numPagos+'">Parcial</label>'+
        '</div>'+
        '</div>'+
        '</td>' +
        '<td id="forma_pago_'+numPagos+'">' +
        '<span class="text">Pago en una sola exhibición</span>' +
        '<input type="text" name="forma_pago" class="editbox form-control" value="Pago en una sola exhibición" style="display: none; max-width: 222px;">' +
        '</td>' +
        '<td id="descuento_porcentaje_'+numPagos+'">' +
        '<span class="text">0</span>' +
        '<input type="text" name="descuento_porcentaje" class="editbox form-control" value="0" style="display: none; max-width: 80px;" >' +
        '</td>' +
        '<td id="descuento_cantidad_'+numPagos+'">' +
        '<span class="text">0</span>' +
        '<input type="text" name="descuento_cantidad" class="editbox form-control" value="0" style="display: none; max-width: 80px;">' +
        '</td>' +
        '<td id="cantidad_pagada_'+numPagos+'">' +
        '<span class="text cantidad_pagada">'+cantidad.val()+'</span>' +
        '<input type="text" name="cantidad_pagada" class="editbox form-control cantidad_pagada" value="'+cantidad.val()+'" style="display: none; max-width: 90px;">' +
        '</td>' +
        '<td>' +
        '<button class="btn btn-primary btnEditar" id="'+numPagos+'"><i class="far fa-edit"></i></button>' +
        '<button class="btn btn-success btnSave" id="'+numPagos+'" style="display: none" style="margin-right: 10px"><i class="fas fa-check"></i></button>' +
        '<button class="btn btn-danger btnCancel" id="'+numPagos+'" style="display: none"><i class="fas fa-times"></i></button>' +
        '</td></tr>';
    $('#btnAgregarPago').hide();
    $(html).insertBefore($('#final'));
    $('#agregarPago').modal('hide');
    var total = $('#valor_final').text();
    total = parseFloat(total.replace(',', ''));
    var cantidad = cantidad.val();
    cantidad = parseFloat(cantidad.replace(',',''));
    total = total+cantidad;
    total = numeral(total).format("00,000.00");
    if (tipo_pago == 'pendiente'){
        $("#valor_final").text(total);
        $('#montoAPagar').val(total);
        modificar_valores(total);
    }else
        modificar_valores(calcularTotalConDescuentos());
    $('.cantidad').mask('00,000.00', {reverse: true});
    $('#restante').text(calcularRestante());
});

//Función que guarda los datos de la corrida pero no los pagos
function guardarDatosCorrida(){
    var registros = {};
    $('#formCorrida').find('input, select').each(function () {
        var name = $(this).attr('name');
        registros[name] = {};
        registros[name] = $(this).val();
    });
    var formData = new FormData();
    formData.append('informacionPago', JSON.stringify(registros));
    $.ajax({
        url: root + "registrarDatosCorrida",
        type: "POST",
        data: formData,
        contentType: false,
        success: function (response) {
            //console.log(response);
            respuesta = JSON.parse(response);
            Swal.fire({
                type: respuesta.tipo_mensaje,
                title: '¡Aviso!',
                text: respuesta.mensaje
            });

        },
        cache: false,
        processData: false
    });
}

//Evento que guarda la corrida de pagos
$(document).on('click','.btnEnviar',function () {
    var registroPago = {};
    var registrosPagos = [];
    var count = 0;
    $('#formCorridaPagos').find('input').each(function () {
        var name = $(this).attr('name');
        if (name.indexOf('status') != -1){
            registroPago['status'] = $('input:radio[name='+name+']:checked').val();
        } else {
            registroPago[name] = {};
            registroPago[name] = $(this).val();
        }
        count++;
        if (count == 11){
            registrosPagos.push(registroPago);
            count = 0;
            registroPago = {};
        }
    });

    var formData = new FormData();

    formData.append('pago', JSON.stringify(registrosPagos));
    formData.append('id_usuario', document.getElementById("usuarioSelect").value);
    $.ajax({
        url: root + "registrarCorrida",
        type: "POST",
        data: formData,
        contentType: false,
        success: function (response) {
            console.log(response);
            var respuesta = JSON.parse(response);
            //se  pasa  el  id de la tabla de información de corrida al boton del reporte
            $('#verReporte').attr('id_info', respuesta.id);
            $('#enviarReporte').attr('id_info', respuesta.id);

            if (respuesta.tipo_mensaje == "success") {

                //Generamos el reporte después de guardar la corrida
                generarCorridaPagoPdf(respuesta.id);

                Swal.fire({
                    //position: 'top',
                    type: 'success',
                    title: 'OK',
                    text: respuesta.mensaje
                });
                $('#btnEnviar').hide();
                $('#btnAgregarPago').hide();
            }else if (respuesta.tipo_mensaje == "warning"){
                swal({
                    type: 'warning',
                    title: 'Alerta de acceso',
                    text: respuesta.mensaje
                });
            }else if (respuesta.tipo_mensaje == "danger") {
                Swal.fire({
                    type: 'error',
                    title: 'Error de acceso',
                    text: respuesta.mensaje
                });
            }
        },
        cache: false,
        processData: false
    });
    return false;
});

//Evento que modifica el valor de los pagos pendientes de una corrida
$(document).on('click','#modificarCorridas',function () {
    var monto_nuevo = $("#monto_nuevo").val();
    monto_nuevo = monto_nuevo.replace(',', '');
    var montoConFormato = monto_nuevo * 2.5;
    var numPagos = $('tbody > tr').length;
    var monto = 0;
    if (numPagos ==12){
        monto = montoConFormato / 12;
    }else{
        monto = montoConFormato / 6;
    }
    monto = monto.toFixed(2);
    for (var i=1; i <= numPagos; i++) {
        if ($('#status_'+i+' .text').text() === "pendiente"){
            $('#cantidad_'+i+' .text').text(monto);
            $('#cantidad_'+i+' .editbox').val(monto);
        }
    }
});

//Evento que genera un pago de tipo parcial
$('#generarParcial').click(function () {
    //Se actuliza la propiedad solicitud para indentificar que es parcial
    //$('#btnGuardarPago').attr('solicitud', 'parcial');
    num_pago += 1;
    cantidad_original = cantidad_original.replace(',','');
    cantidad_original = parseFloat(cantidad_original);
    cantidad_nueva = cantidad_nueva.replace(',','');
    cantidad_nueva = parseFloat(cantidad_nueva);
    var diferencia = cantidad_original - cantidad_nueva;
    var opcion = $('input:radio[name=valorParcial]:checked').val();
    if (opcion == "siguiente"){
        var num_pagos = $('#tablaCorrida tr').length;
        if (num_pagos == num_pago){
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: 'Este es el ultimo pago'
            })
        }else {
            cantidad_anterior = $('#cantidad_'+num_pago+' .text').text();
            cantidad_anterior = parseFloat(cantidad_anterior.replace(',',''));
            cantidad_nueva = numeral(cantidad_anterior + diferencia).format('00,000.00');
            $('#cantidad_'+num_pago+' .text').text(cantidad_nueva);
            $('#cantidad_'+num_pago+' input').val(cantidad_nueva);
            //se copia la cantidad del nuevo pago en la cantidad pagada
            $('#cantidad_pagada_'+num_pago+' .text').text(cantidad_nueva);
            $('#cantidad_pagada_'+num_pago+' .editbox').val(cantidad_nueva);
            modificar_valores(calcularTotalConDescuentos());
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
            $('#tablaCorrida tr').each(function () {
                cantidad = $("#cantidad_"+count+" input").val();
                status = $('input:radio[name=status'+count+']:checked').val();
                //console.log(cantidad);
                if (typeof cantidad !== 'undefined' && status == "pendiente") {
                    cantidad = parseFloat(cantidad.replace(',',''));
                    cantidad  += cantidad_dividida;
                    cantidad = Math.round(cantidad);
                    cantidad = numeral(cantidad).format('00,000.00');
                    $('#cantidad_'+count+' .text').text(cantidad);
                    $('#cantidad_'+count+' input').val(cantidad);
                    //se copia la cantidad del nuevo pago en la cantidad pagada
                    $('#cantidad_pagada_'+count+' .text').text(cantidad);
                    $('#cantidad_pagada_'+count+' input').val(cantidad);
                    temp.push(cantidad);
                    count++;
                }else count++;
            });
            datosPago.push(temp);
            modificar_valores(calcularTotalConDescuentos());
            //Verificamos si no hay un desface de cantidades
            var total = $('#valor_final').text();
            var cantidad_sobrada = parseFloat(total.replace(',', '')) - parseFloat(calcularTotal().replace(',',''));
            cantidad_sobrada = parseFloat(cantidad_sobrada.toFixed(2));
            var cantidadPagoSigiente = parseFloat($('#cantidad_'+num_pago+' .text').text().replace(',', ''));
            if (cantidad_sobrada != 0){
                cantidadPagoSigiente = cantidadPagoSigiente + cantidad_sobrada;
                cantidadPagoSigiente = numeral(cantidadPagoSigiente.toFixed(2)).format('00,000.00');
                $('#cantidad_'+num_pago+' .text').text(cantidadPagoSigiente);
                $('#cantidad_'+num_pago+' input').val(cantidadPagoSigiente);
                $('#cantidad_pagada_'+num_pago+' .text').text(cantidadPagoSigiente);
                $('#cantidad_pagada_'+num_pago+' input').val(cantidadPagoSigiente);
                $('#total_descuento').text(calcularTotalConDescuentos());
            }
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
    $('#restante').text(calcularRestante());
});

//Evento que genera un pago de tipo aplazado
$('#generarAplazado').click(function () {
    //Se actuliza la propiedad solicitud para indentificar que es aplazado
    //$('#btnGuardarPago').attr('solicitud', 'aplazado');
    num_pago += 1;
    cantidad_original = cantidad_original.replace(',','');
    cantidad_original = parseFloat(cantidad_original);
    var opcion = $('input:radio[name=valorAplazado]:checked').val();
    if (opcion == "siguiente"){
        var num_pagos = $('#tablaCorrida tr').length;
        if (num_pagos == num_pago){
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: 'Este es el ultimo pago'
            })
        }else{
            cantidad_anterior = $('#cantidad_'+num_pago+' .text').text();
            cantidad_anterior = cantidad_anterior.replace(',','');
            cantidad_nueva = numeral(parseFloat(cantidad_anterior) + cantidad_original).format('00,000.00');
            $('#cantidad_'+num_pago+' .text').text(cantidad_nueva);
            $('#cantidad_'+num_pago+' input').val(cantidad_nueva);
            //se copia la cantidad del nuevo pago en la cantidad pagada
            $('#cantidad_pagada_'+num_pago+' .text').text(cantidad_nueva);
            $('#cantidad_pagada_'+num_pago+' input').val(cantidad_nueva);
            //se deja en 0 la cantidad del pago
            $('#cantidad_'+(num_pago-1)+' .text').text(0);
            $('#cantidad_'+(num_pago-1)+' input').val(0);
            $('#cantidad_pagada_'+(num_pago-1)+' .text').text(0);
            $('#cantidad_pagada_'+(num_pago-1)+' input').val(0);
        }
    }
    if (opcion == "distribuido"){
        count = 1;
        numPagosPendientes = 0;
        $('#tablaCorrida tr').each(function () {
            status = $('input:radio[name=status'+count+']:checked').val();
            if (status == "pendiente") {
                numPagosPendientes++;
                count++;
            }else count++;
        });
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
                    //se actualiza la columna de cantidad pagada
                    $('#cantidad_pagada_'+(count)+' .text').text(cantidad);
                    $('#cantidad_pagada_'+(count)+' input').val(cantidad);
                    count++;
                }else count++;
            });
            //se deja en 0 la cantidad del pago
            $('#cantidad_'+(num_pago-1)+' .text').text(0);
            $('#cantidad_'+(num_pago-1)+' input').val(0);
            //Se deja en 0 la cantidad pagada
            $('#cantidad_pagada_'+(num_pago-1)+' .text').text(0);
            $('#cantidad_pagada_'+(num_pago-1)+' input').val(0);
            modificar_valores(calcularTotalConDescuentos());
            //Verificamos si no hay un desface de cantidades
            var total = $('#valor_final').text();
            var cantidad_sobrada = parseFloat(total.replace(',', '')) - parseFloat(calcularTotal().replace(',',''));
            cantidad_sobrada = parseFloat(cantidad_sobrada.toFixed(2));
            var cantidadPagoSigiente = parseFloat($('#cantidad_'+num_pago+' .text').text().replace(',', ''));
            if (cantidad_sobrada != 0){
                cantidadPagoSigiente = cantidadPagoSigiente + cantidad_sobrada;
                cantidadPagoSigiente = numeral(cantidadPagoSigiente.toFixed(2)).format('00,000.00');
                $('#cantidad_'+num_pago+' .text').text(cantidadPagoSigiente);
                $('#cantidad_'+num_pago+' input').val(cantidadPagoSigiente);
                $('#cantidad_pagada_'+num_pago+' .text').text(cantidadPagoSigiente);
                $('#cantidad_pagada_'+num_pago+' input').val(cantidadPagoSigiente);
                $('#total_descuento').text(calcularTotalConDescuentos());
            }
        }else
            swal({
                type: 'warning',
                title: 'Alerta',
                text: "¡No hay pagos posteriores!"
            });
    }
    if (opcion == "nuevo"){
        //se deja en 0 la cantidad del pago
        $('#cantidad_'+(num_pago-1)+' .text').text(0);
        $('#cantidad_'+(num_pago-1)+' input').val(0);
        //Se deja en 0 la cantidad pagada
        $('#cantidad_pagada_'+(num_pago-1)+' .text').text(0);
        $('#cantidad_pagada_'+(num_pago-1)+' input').val(0);
        $("#cantidad").val(numeral(cantidad_original).format('00,000.00'));
        $("#agregarPago").modal("show");
    }
    $('#aplazado').modal('hide');
    $('#restante').text(calcularRestante());
});

//Función que calcula el total de los pagos pendientes
function calcularRestante(){
    var count = 1;
    var monto_pendiente = 0;
    $('#tablaCorrida tr').each(function () {
        status = $('input:radio[name=status'+count+']:checked').val();
        if (status == "pendiente") {
            monto_pendiente += parseFloat($('#cantidad_'+count+' input').val().replace(',',''));
            count++;
        }else count++;
    });
    return numeral(monto_pendiente).format("00,000.00");
}

//Función que genera la corrida de regestion
function generarCorridaRegestion(data) {
    $('#corridaGenerada').show();
    var respuesta = JSON.parse(data);
    var cont = 1;
    Object.keys(respuesta).forEach(function(key) {
        $('tbody').append('<tr id="edit_'+cont+'">' +
            '<td>'+
            '<span>'+cont+respuesta[key].pago+'</span>' +
            '<input type="text" name="no_pago" class="form-control" value="'+cont+respuesta[key].pago+'" style="display: none; max-width: 90px;">' +
            '</td>' +
            '<td id="fecha_pago_'+cont+'">'+
            '<span class="text">'+respuesta[key].fecha+'</span>' +
            '<input type="text" name="fecha_pago" class="editbox form-control" value="'+respuesta[key].fecha+'" style="display: none; max-width: 90px;">' +
            '</td>' +
            '<td id="cantidad_'+cont+'">' +
            '<span class="text cantidad">'+respuesta[key].cantidad+'</span>' +
            '<input type="text" name="cantidad" class="editbox form-control cantidad" value="'+respuesta[key].cantidad+'" style="display: none; max-width: 90px;">' +
            '</td>' +
            '<td id="status_'+cont+'" class="estado_'+respuesta[key].status+'">' +
            '<span class="text">'+respuesta[key].status+'</span>' +
            '<div class="editbox" style="display: none; max-width: 90px;">'+
            '<div class="form-check form-check-inline">'+
            '<input class="form-check-input" type="radio" name="status'+cont+'" id="status_pendiente'+cont+'" value="pendiente" checked="checked">'+
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
            '</div>'+
            '</td>' +
            '<td id="forma_pago_'+cont+'">' +
            '<span class="text">'+respuesta[key].forma_pago+'</span>' +
            '<input type="text" name="forma_pago" class="editbox form-control" value="'+respuesta[key].forma_pago+'" style="display: none; max-width: 222px;">' +
            '</td>' +
            '<td id="descuento_porcentaje_'+cont+'">' +
            '<span class="text">'+respuesta[key].porcentaje+'</span>' +
            '<input type="text" name="descuento_porcentaje" class="editbox form-control" value="'+respuesta[key].porcentaje+'" style="display: none; max-width: 80px;" >' +
            '</td>' +
            '<td id="descuento_cantidad_'+cont+'">' +
            '<span class="text">'+respuesta[key].descuento+'</span>' +
            '<input type="text" name="descuento_cantidad" class="editbox form-control" value="'+respuesta[key].descuento+'" style="display: none; max-width: 80px;" disabled>' +
            '</td>' +
            '<td id="cantidad_pagada_'+cont+'">' +
            '<span class="text">'+respuesta[key].cantidad_pagada+'</span>' +
            '<input type="text" name="cantidad_pagada" class="editbox form-control" value="'+respuesta[key].cantidad_pagada+'" style="display: none; max-width: 80px;" disabled>' +
            '</td>' +
            '<td>' +
            '<button class="btn btn-primary btnEditar" id="'+cont+'"><i class="far fa-edit"></i></button>' +
            '<button class="btn btn-success btnSave" id="'+cont+'" style="display: none" style="margin-right: 10px;"><i class="fas fa-check"></i></button>' +
            '<button class="btn btn-danger btnCancel" id="'+cont+'" style="display: none"><i class="fas fa-times"></i></button>' +
            '</td>' +
            '</tr>');
        switch (respuesta[key].status) {
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
    });
    cont--;
    var total = $('#montoAPagar').val();
    //Modificamos la primera cantidad de pago para anivelar
    var cantidad_sobrada = parseFloat(total.replace(',', '')) - parseFloat(calcularTotal().replace(',',''));
    cantidad_sobrada = parseFloat(cantidad_sobrada.toFixed(2));
    var cantidadPagoUltimo = parseFloat($('#cantidad_'+cont+' .text').text().replace(',', ''));
    if (cantidad_sobrada != 0){
        cantidadPagoUltimo = cantidadPagoUltimo + cantidad_sobrada;
        cantidadPagoUltimo = numeral(cantidadPagoUltimo.toFixed(2)).format('00,000.00');
        $('#cantidad_'+cont+' .text').text(cantidadPagoUltimo);
        $('#cantidad_'+cont+' input').val(cantidadPagoUltimo);
        $('#cantidad_pagada_'+cont+' .text').text(cantidadPagoUltimo);
        $('#cantidad_pagada_'+cont+' input').val(cantidadPagoUltimo);
    }
    //<td id="diferencia"></td>
    $('tbody').append('<tr id="final"><td></td><td><strong>Total</strong></td><td id="valor_final" class="cantidad">'+total+'</td><td></td><td></td><td></td><td><strong>Total con descuento</strong></td><td id="total_descuento" class="cantidad"><span>'+calcularTotalConDescuentos()+'</span><input type="text" name="input_total_descuento" id="input_total_descuento" value="'+total+'" style="display: none;"></td><td><strong>Restante</strong><br><span id="restante">'+calcularRestante()+'</span></td></tr>');
    //Deshabilitamos el boton Generar
    $('#btnGenerar').attr('disabled','disabled');
    //Hacemos scroll para ubicarnos en la tabla
    var elmnt = document.getElementById("btnEnviar");
    elmnt.scrollIntoView();
}