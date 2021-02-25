$(function () {
    $("#formLogin").submit(function () {
        $email = $("#correo").val();
        $emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
        if ($emailRegex.test($email)) {
            $.ajax({
                url: root+"login",
                type: "POST",
                data: $("#formLogin").serialize(),
                success: function (response) {
                    //console.log(response);
                    respuesta = JSON.parse(response);
                    if (respuesta.tipo_mensaje == "success") {
                        Swal.fire({
                            //position: 'top',
                            type: 'success',
                            title: 'Acceso correcto',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function(){
                            window.location.href = root+"access";
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
        }else{
            Swal.fire({
                type: 'warning',
                title: 'Alerta de acceso',
                text: "El correo ingresado no es correcto"
            }).then(function(){
                location.reload();
            })
            return false;
        }
    });
    $('#cero').click(function () {
        $('#login').show();
        $('#error-island').hide();
        console.log(rootImage+"fondo.png");
        document.body.style.backgroundImage = "url(" + rootImage + "fondo.png)";
    });
});

var parallax = function(e) {
        var windowWidth = $(window).width();
        if (windowWidth < 768) return;
        var halfFieldWidth = $(".parallax").width() / 2,
            halfFieldHeight = $(".parallax").height() / 2,
            fieldPos = $(".parallax").offset(),
            x = e.pageX,
            y = e.pageY - fieldPos.top,
            newX = (x - halfFieldWidth) / 30,
            newY = (y - halfFieldHeight) / 30;
        $('.parallax [class*="wave"]').each(function(index) {
            $(this).css({
                transition: "",
                transform:
                    "translate3d(" + index * newX + "px," + index * newY + "px,0px)"
            });
        });
    },
    stopParallax = function() {
        $('.parallax [class*="wave"]').css({
            transform: "translate(0px,0px)",
            transition: "all .7s"
        });
        $timeout(function() {
            $('.parallax [class*="wave"]').css("transition", "");
        }, 700);
    };
$(document).ready(function() {
    $(".not-found").on("mousemove", parallax);
    $(".not-found").on("mouseleave", stopParallax);
});