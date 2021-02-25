$('#btnChangePass').click(function () {
    //$(this).style.visibility = "hidden";
    document.getElementById("btnChangePass").style.visibility = "hidden";
    document.getElementById("formPass").style.visibility = "visible";
});

$('#formPass').submit(function() {
    $.ajax({
        url: root + "cambiarContrasenia",
        type: "POST",
        data: $('#formPass').serialize(),
        success: function (response) {
            console.log(response);
            var result = JSON.parse(response);
            Swal.fire({
                type: result.tipo_mensaje,
                text: result.mensaje
            })
        }
    });
    return false;
});

//Metodo que realiza el respaldo de la BD
$('#bacupBD').click(function () {
    var req = new XMLHttpRequest();
    req.open('GET', root+"backupBD", false);
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
                title: 'Respaldo realizado!',
                showConfirmButton: false,
                timer: 1500
            });
});