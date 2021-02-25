$(document).ready(function() {
    $('#dataTable').DataTable();

    //Activa opciones de la botonera
    $(".nav-item.active").removeClass("active");
    $("#"+opcion).addClass("active");

    //Activa las opciones del dropdown
    if (typeof item !== 'undefined') {
        $(".dropdown-item.active").removeClass("active");
        $("#"+item).addClass("active");
    }
    // Mostrar notificaciones activas
    cont = 1;
    $.ajax({
        url: root+"mostrarNotificaciones",
        type: "POST",
        data: $("#formRegistro").serialize(),
        success: function (response) {
            respuesta = JSON.parse(response);
            Object.keys(respuesta).forEach(function(key) {
                if(respuesta[key].activo == 1){
                    app.add({title: 'Notificacion', type: 'info', body: respuesta[key].descripcion, timeout: cont+2});
                }
                cont++;
            })
        }
    });
    //setTimeout("window.open('../views/layout/backend/js/destruye_sesion.php','_top');", 600000);
});

const app = new Vue({
    el: '#Toasts',
    methods: {

        add(params) {
            for (let key in this.defaults) {
                if (params[key] === undefined) {
                    params[key] = this.defaults[key];
                }
            }

            params.created = Date.now();
            params.id = Math.random();
            params.expire = setTimeout(() => {this.remove(params.id);}, params.timeout * 1000);

            this.content.unshift(params);
        },

        remove(id) {
            this.content.splice(this.index(id), 1);
        },
        index(id) {
            for (let key in this.content) {
                if (id === this.content[key].id) {
                    return key;
                }
            }
        },

        type(type) {
            switch (type) {
                case 'error':
                    return 'is-danger';
                case 'success':
                    return 'is-success';
                case 'info':
                    return 'is-info';}

        } },


    data() {

        return {
            defaults: {
                title: 'undefined title',
                body: 'undefined body',
                timeout: 5 },

            content: [],
            a: { title: 'title', body: 'body', type: 'info', timeout: 3 } };

    }
});

$('#respaldo').click(function () {
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
