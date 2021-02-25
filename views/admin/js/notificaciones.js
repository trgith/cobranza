$('.btn-visto').click(function () {
    var formData = new FormData();
    formData.append('id_notificacion', $(this).attr('id'));
    $.ajax({
        url: root + "actualizarVistoNotificacion",
        type: "POST",
        data: formData,
        contentType: false,
        success: function (response) {
            //console.log(response);
            var respuesta = JSON.parse(response);
            if (respuesta.tipo_mensaje == "success") {
                Swal.fire({
                    type: 'success',
                    title: 'Actualización correcta',
                    text: respuesta.mensaje,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function(){
                    location.reload();
                })
            }else if (respuesta.tipo_mensaje == "warning") {
                swal({
                    type: 'warning',
                    title: 'Alerta de actualización',
                    text: respuesta.mensaje
                }).then(function () {
                    location.reload();
                })
            }
        },
        cache: false,
        processData: false
    });
    return false;
});

$('#btnVerTodo').click(function () {
    $.ajax({
        url: root + "verTodas",
        type: "POST",
        data: {},
        contentType: false,
        success: function (response) {
            //console.log(response);
            var respuesta = JSON.parse(response);
            if (respuesta.tipo_mensaje == "success") {
                Swal.fire({
                    type: 'success',
                    title: 'Actualización correcta',
                    text: respuesta.mensaje,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function(){
                    location.reload();
                })
            }else if (respuesta.tipo_mensaje == "warning") {
                swal({
                    type: 'warning',
                    title: 'Alerta de actualización',
                    text: respuesta.mensaje
                }).then(function () {
                    location.reload();
                })
            }
        }
    });
    return false;
});