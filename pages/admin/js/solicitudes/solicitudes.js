document.addEventListener('DOMContentLoaded', function () {

    const contenedorFormulario = document.getElementById('contenedorFormulario');
    const modal = document.getElementById('modalVerSolicitud');

    // Gestión del foco antes de cerrar el modal
    modal.addEventListener('hide.bs.modal', function () {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    });

    // Limpiar contenido del modal al terminar de cerrarse
    modal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('contenidoSolicitud').innerHTML = '';
        document.getElementById("inputRespuestaRRHH").value = "";
        document.getElementById("btnGuardarRespuesta").disabled = true;
    });

    // Evento para los botones "Ver Solicitudes"
    document.querySelectorAll('.button_form_respuestas').forEach(boton => {
        boton.addEventListener('click', function () {
            const idFormulario = this.parentElement.querySelector('.id_form').value;

            const formData = new FormData();
            formData.append("id_form", idFormulario);

            fetch('./ui/solicitudes/listar_empleados.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    contenedorFormulario.innerHTML = data;
                    window.scrollTo({ top: contenedorFormulario.offsetTop, behavior: 'smooth' });
                })
                .catch(error => {
                    console.error('Error al cargar las solicitudes:', error);
                    contenedorFormulario.innerHTML = '<p class="text-danger">Hubo un error al cargar las solicitudes.</p>';
                });
        });
    });

    // Delegación de eventos para los botones "Ver solicitud"
    contenedorFormulario.addEventListener("click", function (e) {
        if (e.target && e.target.classList.contains("button_ver_solicitud")) {
            const card = e.target.closest(".card-body");
            const id_form = card.querySelector(".id_form")?.value;
            const fecha = card.querySelector(".fecha")?.value;
            const id_empleado = card.querySelector(".id_empleado")?.value;
            const id_solicitud_rh = e.target.getAttribute("data-id-solicitud");

            if (!id_form || !fecha || !id_empleado) {
                //Swal.fire("Error", "Faltan datos para mostrar la solicitud.", "warning");
                mostrarAlerta("warning", "Faltan datos para mostrar la solicitud.", "Error");
                return;
            }

            // Marcar como vista antes de mostrar el modal
            if (id_solicitud_rh) {
                marcarComoVista(id_solicitud_rh, e.target);
            }

            const formData = new URLSearchParams();
            formData.append("id_form", id_form);
            formData.append("fecha", fecha);
            formData.append("id_empleado", id_empleado);

            fetch("./ui/solicitudes/cont_solicitud.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    const modalInstance = new bootstrap.Modal(modal);
                    modalInstance.show();

                    // Renderizar contenido
                    document.getElementById("contenidoSolicitud").innerHTML = data.html;

                    // Asignar el id_solicitud_rh y la respuesta
                    asignarIdSolicitud(data.id_solicitud_rh, data.respuesta);
                })
                .catch(err => {
                    console.error("Error al cargar la solicitud:", err);
                    //Swal.fire("Error", "No se pudo cargar la solicitud.", "error");
                    mostrarAlerta("error", "No se pudo cargar la solicitud.", "Error");
                });
        }
    });
});

function asignarIdSolicitud(idSolicitudRH, respuesta = "") {
    const inputRespuesta = document.getElementById("inputRespuestaRRHH");
    const btnGuardarRespuesta = document.getElementById("btnGuardarRespuesta");
    const spinnerRespuesta = document.getElementById("spinnerRespuestaRRHH");

    // Asignar el ID de la solicitud
    btnGuardarRespuesta.setAttribute("data-id-solicitud", idSolicitudRH);

    // Asignar la respuesta existente
    inputRespuesta.value = respuesta;

    // El botón debe estar habilitado si hay un ID válido
    btnGuardarRespuesta.disabled = !idSolicitudRH;

    // Habilitar el botón cuando se escribe algo
    inputRespuesta.addEventListener("input", function () {
        btnGuardarRespuesta.disabled = inputRespuesta.value.trim() === "" || !idSolicitudRH;
    });

    // Registrar evento click en el botón de guardar respuesta
    btnGuardarRespuesta.onclick = function () {
        const nuevaRespuesta = inputRespuesta.value.trim();
        const solicitudID = btnGuardarRespuesta.getAttribute("data-id-solicitud");

        if (!solicitudID) {
            //Swal.fire("Error", "No se pudo identificar la solicitud.", "warning");
            mostrarAlerta("warning", "No se pudo identificar la solicitud.", "Error");
            return;
        }

        if (!nuevaRespuesta) {
            //Swal.fire("Error", "Debe escribir una respuesta antes de enviarla.", "warning");
            mostrarAlerta("warning", "Debe escribir una respuesta antes de enviarla.", "Error");
            return;
        }

        // Deshabilitar el botón y mostrar spinner
        btnGuardarRespuesta.disabled = true;
        spinnerRespuesta.classList.remove("d-none");

        const formData = new FormData();
        formData.append("id_solicitud_rh", solicitudID);
        formData.append("respuesta", nuevaRespuesta);

        fetch("./ui/solicitudes/guardar_respuesta.php", {
            method: "POST",
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Swal.fire("Éxito", "Respuesta guardada correctamente.", "success");
                    mostrarAlerta("success", "Respuesta guardada correctamente.", "Éxito");
                    inputRespuesta.value = "";
                } else {
                    //Swal.fire("Error", data.message, "error");
                    mostrarAlerta("error", data.message, "Error");
                }
            })
            .catch(error => {
                console.error("Error al guardar la respuesta:", error);
                //Swal.fire("Error", "No se pudo guardar la respuesta.", "error");
                mostrarAlerta("error", data.message, "Error");
            })
            .finally(() => {
                // Rehabilitar el botón y ocultar spinner
                spinnerRespuesta.classList.add("d-none");
                btnGuardarRespuesta.disabled = false;
            });
    };
}

// Función para marcar solicitud como vista y actualizar UI
function marcarComoVista(idSolicitudRH, botonElement) {
    const formData = new FormData();
    formData.append("id_solicitud_rh", idSolicitudRH);

    fetch("./ui/solicitudes/marcar_vista.php", {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remover el punto rojo de la tarjeta (ahora está en la esquina superior derecha)
                const card = botonElement.closest(".card");
                const puntoRojo = card.querySelector(".punto-rojo-notificacion");
                if (puntoRojo) {
                    puntoRojo.remove();
                }

                // Actualizar contador en la página principal si existe
                if (data.id_formulario && data.nuevas_restantes !== undefined) {
                    actualizarContadorFormulario(data.id_formulario, data.nuevas_restantes);
                }
            }
        })
        .catch(error => {
            console.error("Error al marcar como vista:", error);
        });
}

// Función para actualizar el contador de un formulario específico
function actualizarContadorFormulario(idFormulario, nuevasRestantes) {
    // Buscar todas las tarjetas de formularios en la página principal
    const tarjetasFormularios = document.querySelectorAll('.button_form_respuestas');
    
    tarjetasFormularios.forEach(boton => {
        const idFormInput = boton.parentElement.querySelector('.id_form');
        if (idFormInput && idFormInput.value == idFormulario) {
            const card = boton.closest('.card');
            if (card) {
                // Remover contador existente (ahora está en la esquina superior izquierda)
                const contadorExistente = card.querySelector('.contador-formulario-notificacion');
                if (contadorExistente) {
                    contadorExistente.remove();
                }
                
                // Agregar nuevo contador solo si hay solicitudes nuevas
                if (nuevasRestantes > 0) {
                    const nuevoContador = document.createElement('span');
                    nuevoContador.className = 'badge rounded-pill bg-danger contador-formulario-notificacion';
                    nuevoContador.textContent = nuevasRestantes > 99 ? '99+' : nuevasRestantes;
                    card.appendChild(nuevoContador);
                }
            }
        }
    });
}


