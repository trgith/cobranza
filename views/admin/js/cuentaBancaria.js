$(function () {
    $('#formCuentaBancaria').submit(function () {
        $.ajax({
            url: root + "registrarCuentaBancaria",
            type: "POST",
            data: $("#formCuentaBancaria").serialize(),
            success: function (response) {
                var respuesta = JSON.parse(response);
                if (respuesta.tipo_mensaje == "success") {
                    Swal.fire({
                        //position: 'top',
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
        var data = {};
        var datosCuenta = [];
        var count = 0;
        $('#edit_'+id).find('input').each(function () {
            var name = $(this).attr('name');
            data[name] = $(this).val();
            count++;
            if (count == 5){
                datosCuenta.push(data);
                count = 0;
                data = {};
            }
        });
        var formData = new FormData();
        formData.append('datosCuenta', JSON.stringify(datosCuenta));
        $.ajax({
            url: root + "actualizarCuentaBancaria",
            type: "POST",
            data: formData,
            contentType: false,
            success: function (response) {
                //console.log(response);
                var respuesta = JSON.parse(response);
                if (respuesta.tipo_mensaje == "success") {
                    Swal.fire({
                        //position: 'top',
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
        $id_cuenta = $(this).attr('id');
        var formData = new FormData();
        formData.append('idCuenta', $id_cuenta);
        $.ajax({
            url: root + "eliminarCuentaBancaria",
            type: "POST",
            data: formData,
            contentType: false,
            success: function (response) {
                console.log(response);
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
