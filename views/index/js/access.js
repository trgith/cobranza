$(function () {
	$("#formAccess").submit(function () {
		$.ajax({
			url: root+"validateAccess",
            type: "POST",
            data: $("#formAccess").serialize(),
            success: function (response) {
                console.log(response);
                respuesta = JSON.parse(response);
                if (respuesta.tipo_mensaje == "success") {
                    Swal.fire({
                        //position: 'top',
                        type: 'success',
                        title: 'Acceso correcto',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function(){
                        window.location.href = rootAdmin+"index";
                    })
                }else if (respuesta.tipo_mensaje == "warning"){
                    swal({
                        type: 'warning',
                        title: 'Alerta de acceso',
                        text: respuesta.mensaje
                    }).then(function(){
                        location.reload();
                    })
                }else if (respuesta.tipo_mensaje == "danger") {
                    Swal.fire({
                        type: 'error',
                        title: 'Error de acceso',
                        text: respuesta.mensaje
                    }).then(function(){
                        location.reload();
                    })
                }
            }
        });
        return false;    
	});

});