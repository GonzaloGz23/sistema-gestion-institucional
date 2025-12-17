
document.addEventListener('DOMContentLoaded', function () {
    // Gestión del foco antes de cerrar el modal
    document.getElementById('modalVerSolicitud').addEventListener('hide.bs.modal', function (event) {
        const modal = event.target;
        const focusedElement = document.activeElement;

        if (modal.contains(focusedElement)) {
            focusedElement.blur();
        }
    });

    // Selecciona todos los botones
    const botones = document.querySelectorAll('.button_form_respuestas');

    botones.forEach(boton => {
        boton.addEventListener('click', function () {
            const idFormulario = this.parentElement.querySelector('.id_form').value;

            const formData = new FormData();
            formData.append("id_form", idFormulario);

            fetch('./ui/resp_formulario/listar_empleados.php', { // Cambiá la ruta si es distinta
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('contenedorFormulario').innerHTML = data;
                    window.scrollTo({ top: document.getElementById('contenedorFormulario').offsetTop, behavior: 'smooth' });
                    llamarSolicitud()
                    //llamarform();
                })
                .catch(error => {
                    console.error('Error al cargar el formulario:', error);
                    document.getElementById('contenedorFormulario').innerHTML = '<p class="text-danger">Hubo un error al cargar el formulario.</p>';
                });
        });
    });
});




function llamarSolicitud() {
    document.addEventListener("click", function (e) {
        if (e.target && e.target.classList.contains("button_ver_solicitud")) {
            const parent = e.target.closest(".d-flex");
            const id_form = parent.querySelector(".id_form")?.value;
            const fecha = parent.querySelector(".fecha")?.value;
            const id_empleado = parent.querySelector(".id_empleado")?.value;

            if (!id_form && !fecha && !id_empleado) {
                alert("Faltan datos para mostrar la solicitud.");
                return;
            }

            const formData = new URLSearchParams();
            formData.append("id_form", id_form);
            formData.append("fecha", fecha);
            formData.append("id_empleado", id_empleado);

            fetch("./ui/resp_formulario/cont_solicitud.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.text())
                .then(html => {
                    document.getElementById("contenidoSolicitud").innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById("modalVerSolicitud"));
                    modal.show();
                })
                .catch(err => {
                    console.error("Error al cargar la solicitud:", err);
                    Swal.fire("Error", "No se pudo cargar la solicitud.", "error");
                });
        }
    });
}

