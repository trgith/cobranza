var count = function(val, max_length) {
    'use strict';
    var len = val.value.length;
    if(len > max_length)
        this.value = val.value.substring(0, max_length);
    else
        $('#count i').text(max_length - len);
};

$(function () {
    $('#formNota').submit(function () {
        $.ajax({
            url: root + "registrarNota",
            type: "POST",
            data: $("#formNota").serialize(),
            success: function (response) {
                var respuesta = JSON.parse(response);
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
                }else if (respuesta.tipo_mensaje == "warning") {
                    swal({
                        type: 'warning',
                        title: 'Alerta de acceso',
                        text: respuesta.mensaje
                    }).then(function () {
                        location.reload();
                    })
                }
            }
        });
        return false;
    });

    $('.btnEdit').click(function () {
        var id = $(this).attr('id');
        $('#edit_'+id+' .text').hide();
        $('#edit_'+id+' .editBox').show();
        $('#edit_'+id+' .btnEdit').hide();
        $('#edit_'+id+' .btnSave').show();
        $('#edit_'+id+' .btnDelet').show();
        $('#edit_'+id+' .btnCancel').show();
    });

    $('.btnCancel').click(function () {
        var id = $(this).attr('id');
        $('#edit_'+id+' .text').show();
        $('#edit_'+id+' .editBox').hide();
        $('#edit_'+id+' .btnEdit').show();
        $('#edit_'+id+' .btnSave').hide();
        $('#edit_'+id+' .btnDelet').hide();
        $('#edit_'+id+' .btnCancel').hide();
    });

    $('.btnSave').click(function () {
        var id = $(this).attr('id');
        var descripcion = $('#descripcion_'+id).val();
        var data = {"idNota" : id, "descripcion" : descripcion};
        var formData = new FormData();
        formData.append('datosNota', JSON.stringify(data));
        $.ajax({
            url: root + "actualizarNota",
            type: "POST",
            data: formData,
            contentType: false,
            success: function (response) {
                //console.log(response);
                var respuesta = JSON.parse(response);
                if (respuesta.tipo_mensaje == "success") {
                    Swal.fire({
                        type: 'success',
                        title: 'OK',
                        text: respuesta.mensaje
                    }).then(function () {
                        location.reload();
                    })
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

    $('.btnDelet').click(function () {
        $id_nota = $(this).attr('id');
        var formData = new FormData();
        formData.append('idNota', $id_nota);
        $.ajax({
            url: root + "eliminarNota",
            type: "POST",
            data: formData,
            contentType: false,
            success: function (response) {
                //console.log(response);
                var respuesta = JSON.parse(response);
                if (respuesta.tipo_mensaje == "success") {
                    Swal.fire({
                        type: 'success',
                        title: 'OK',
                        text: respuesta.mensaje
                    }).then(function () {
                        location.reload();
                    });
                }else if (respuesta.tipo_mensaje == "warning") {
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
});