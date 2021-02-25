$(function () {
    $("#loginForm").submit(function () {
        $email = $("#userEmail").val();
        $emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
        if ($emailRegex.test($email)) {
            $.ajax({
                url: root+"acces",
                type: "POST",
                data: $("#loginForm").serialize(),
                success: function (response) {
                    //console.log(response);
                    if (response == 1 ){
                        swal(
                            'Ingreso exitoso!',
                            '',
                            'success',
                        ).then(function () {
                            location.href= root;
                        })
                    }else{
                        $("#estado").html("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">\n" +
                            "  <strong>Datos incorrectos!</strong> Usuario o contraseña incorrecto.\n" +
                            "  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
                            "    <span aria-hidden=\"true\">&times;</span>\n" +
                            "  </button>\n" +
                            "</div>");
                    }
                }

            });
        } else {
            /*valido.innerText = "incorrecto";*/
            $("#estado").html("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">\n" +
                "  <strong>Correo invalido!</strong> Por favor verifique su correo.\n" +
                "  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
                "    <span aria-hidden=\"true\">&times;</span>\n" +
                "  </button>\n" +
                "</div>");
        }
        /**/

        return false;
    });

    $("#registerForm").submit(function () {
        $accesEmail = false;
        $accesPass = false;
        $email = $("#userEmail").val();
        $emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
        if ($emailRegex.test($email)){
            $accesEmail = true;
        }else{
            $("#estado").html("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">\n" +
                "  <strong>Correo invalido!</strong> Por favor verifique su correo.\n" +
                "  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
                "    <span aria-hidden=\"true\">&times;</span>\n" +
                "  </button>\n" +
                "</div>");
            $accesEmail = false;
        }
        if($("#pass").val() === $("#rePass").val()){
            $accesPass = true;
        }else{
            $("#estado").html("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">\n" +
                "  <strong>Las contraseñas no coinciden!</strong> Por favor verifique que las contraseñas sean identicas.\n" +
                "  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
                "    <span aria-hidden=\"true\">&times;</span>\n" +
                "  </button>\n" +
                "</div>");
            $accesPass = false;
        }
        if ($accesEmail && $accesPass) {
            $.ajax({
                url: "views/ajax/gestorRegistro.php",
                type: "POST",
                data: $("#registerForm").serialize(),
                success: function (response) {
                    console.log(response);
                    if (response == 2){
                        swal(
                            'Registro exitoso!',
                            'Usted a sido resgistrado correctamente!',
                            'success',
                        ).then(function () {
                            location.reload();
                        })
                    }else if(response == 0){
                        $("#estado").html("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">\n" +
                            "  <strong>Error!</strong> Ah ocurrido un error al registrarse.\n" +
                            "  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
                            "    <span aria-hidden=\"true\">&times;</span>\n" +
                            "  </button>\n" +
                            "</div>");
                    }else{
                        $("#estado").html("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">\n" +
                            "  <strong>Correo registrado!</strong> El correo que ingreso, ya se encuentra registrado.\n" +
                            "  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
                            "    <span aria-hidden=\"true\">&times;</span>\n" +
                            "  </button>\n" +
                            "</div>");
                    }
                }

            });
        }

        return false;
    })
})