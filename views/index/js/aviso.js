$("#formAviso").submit(function () {
    if ($('input[name=type]:checked', '#formAviso').val() == 1){
        window.location.href = "http://trnetwork.com.mx/quienestrnetwork/";
    }else {
        swal(
            'Debe aceptar el aviso de privacidad!',
            '',
            'error'
        )
    }
    //$("#radio_1").prop("checked", true);
    //window.location.href = "http://trnetwork.com.mx/quienestrnetwork/";
    return false;
})