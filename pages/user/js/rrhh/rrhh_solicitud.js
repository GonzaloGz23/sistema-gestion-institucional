
document.addEventListener('DOMContentLoaded', function () {
    // Selecciona todos los botones
    const botones = document.querySelectorAll('.button_form_respuestas');

    botones.forEach(boton => {
        boton.addEventListener('click', function () {
            const idFormulario = this.parentElement.querySelector('.id_form').value;

            const formData = new FormData();
            formData.append("id_form", idFormulario);

            fetch('./ui/rrhh/mostrar_solicitud.php', { // Cambiá la ruta si es distinta
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('contenedorFormulario').innerHTML = data;
                    window.scrollTo({ top: document.getElementById('contenedorFormulario').offsetTop, behavior: 'smooth' });
                    activarCalificacion(document.getElementById('contenedorFormulario'))
                    llamarform();
                })
                .catch(error => {
                    console.error('Error al cargar el formulario:', error);
                    document.getElementById('contenedorFormulario').innerHTML = '<p class="text-danger">Hubo un error al cargar el formulario.</p>';
                });
        });
    });
});
function activarCalificacion(contenedor) {
    const calContainers = contenedor.querySelectorAll('.calificacion');
    calContainers.forEach(calContainer => {
        const labels = calContainer.querySelectorAll('label');
        let currentRating = 0;

        labels.forEach(label => {
            const value = parseInt(label.getAttribute('data-value'));

            label.addEventListener('mouseover', () => updateStars(value));
            label.addEventListener('click', () => {
                currentRating = value;
                updateStars(currentRating);
                const input = calContainer.querySelector(`input[value="${value}"]`);
                if (input) input.checked = true;
            });
            label.addEventListener('mouseout', () => updateStars(currentRating));
        });

        function updateStars(value) {
            labels.forEach(label => {
                const icon = label.querySelector('i');
                const starValue = parseInt(label.getAttribute('data-value'));
                if (icon) {
                    icon.classList.toggle('bi-star-fill', starValue <= value);
                    icon.classList.toggle('bi-star', starValue > value);
                }
            });
        }
    });
}



function llamarform() {
    document.getElementById('resp_form').addEventListener('submit', function (e) {
        e.preventDefault();

        // Protección contra doble clic
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            if (submitButton.disabled) {
                return; // Ya se está procesando
            }
            submitButton.disabled = true;
            submitButton.textContent = 'Enviando...';
        }

        const formData = new FormData();
        let hasError = false;
        let respuestaIndex = 0;

        // Obtener el id del formulario (desde el hidden)
        const idFormulario = document.querySelector('#resp_form .id_form')?.value;
        if (!idFormulario) {
            /*   Swal.fire("Error", "No se pudo identificar el formulario actual.", "error"); */
            mostrarAlerta("error", "No se pudo identificar el formulario actual.", "Error");
            return;
        }
        formData.append("id_formulario", idFormulario);

        const preguntas = document.querySelectorAll(".respuestas");

        preguntas.forEach((item) => {
            const id_pregunta = item.querySelector('input[name="id_pregunta"]').value;
            const obligatorio = item.querySelector('input[name="obligatorio"]').value;

            let tieneRespuesta = false;

            // --- SELECT ---
            const select = item.querySelector("select");
            if (select) {
                const valor = select.value;

                if (valor) {
                    formData.append(`respuestas[${respuestaIndex}][id_preguntas]`, id_pregunta);
                    formData.append(`respuestas[${respuestaIndex}][respuesta]`, valor);
                    formData.append(`respuestas[${respuestaIndex}][id_opcion_preguntas]`, valor); // value = id_opcion_preguntas
                    respuestaIndex++;
                    tieneRespuesta = true;
                }
            }

            // --- RADIO BUTTONS ---
            const radios = item.querySelectorAll('input[type="radio"]');
            const checkedRadio = Array.from(radios).find(r => r.checked);
            if (checkedRadio) {
                formData.append(`respuestas[${respuestaIndex}][id_preguntas]`, id_pregunta);
                formData.append(`respuestas[${respuestaIndex}][respuesta]`, checkedRadio.value);
                formData.append(`respuestas[${respuestaIndex}][id_opcion_preguntas]`, checkedRadio.value);
                respuestaIndex++;
                tieneRespuesta = true;
            }

            // --- CHECKBOXES ---
            const checks = item.querySelectorAll('input[type="checkbox"]:checked');
            checks.forEach(check => {
                formData.append(`respuestas[${respuestaIndex}][id_preguntas]`, id_pregunta);
                formData.append(`respuestas[${respuestaIndex}][respuesta]`, check.value);
                formData.append(`respuestas[${respuestaIndex}][id_opcion_preguntas]`, check.value);
                respuestaIndex++;
                tieneRespuesta = true;
            });

            // --- INPUTS tipo texto, número, textarea ---
            const inputs = item.querySelectorAll('input[type="text"], input[type="number"], textarea');
            inputs.forEach(input => {
                if (input.value.trim()) {
                    formData.append(`respuestas[${respuestaIndex}][id_preguntas]`, id_pregunta);
                    formData.append(`respuestas[${respuestaIndex}][respuesta]`, input.value);
                    formData.append(`respuestas[${respuestaIndex}][id_opcion_preguntas]`, '');
                    respuestaIndex++;
                    tieneRespuesta = true;
                }
            });

            // --- FECHA ---
            const fecha = item.querySelector('input[type="date"]');
            if (fecha && fecha.value) {
                formData.append(`respuestas[${respuestaIndex}][id_preguntas]`, id_pregunta);
                formData.append(`respuestas[${respuestaIndex}][respuesta]`, fecha.value);
                formData.append(`respuestas[${respuestaIndex}][id_opcion_preguntas]`, '');
                respuestaIndex++;
                tieneRespuesta = true;
            }

            // --- HORA ---
            const hora = item.querySelector('input[type="time"]');
            if (hora && hora.value) {
                formData.append(`respuestas[${respuestaIndex}][id_preguntas]`, id_pregunta);
                formData.append(`respuestas[${respuestaIndex}][respuesta]`, hora.value);
                formData.append(`respuestas[${respuestaIndex}][id_opcion_preguntas]`, '');
                respuestaIndex++;
                tieneRespuesta = true;
            }

            // --- ARCHIVO ---
            const fileInput = item.querySelector('input[type="file"]');
            if (fileInput) {
                const file = fileInput.files[0];
                if (file) {
                    formData.append(`respuestas[${respuestaIndex}][archivo]`, file);
                    formData.append(`respuestas[${respuestaIndex}][id_preguntas]`, id_pregunta);
                    formData.append(`respuestas[${respuestaIndex}][respuesta]`, file.name);
                    formData.append(`respuestas[${respuestaIndex}][id_opcion_preguntas]`, '');
                    respuestaIndex++;
                    tieneRespuesta = true;
                } else if (obligatorio == 1) {
                    item.classList.add("border", "border-danger");
                    hasError = true;
                    return;
                }
            }

            // --- VALIDACIÓN ---
            if (obligatorio == 1 && !tieneRespuesta) {
                item.classList.add("border", "border-danger");
                hasError = true;
            } else {
                item.classList.remove("border", "border-danger");
            }
        });

        if (hasError) {
            // Rehabilitar el botón en caso de error de validación
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Enviar';
            }
            /*   Swal.fire("Campos obligatorios", "Por favor, completa todos los campos obligatorios antes de continuar.", "warning"); */
            mostrarAlerta("warning", "Por favor, completa todos los campos obligatorios antes de continuar.", "Campos obligatorios");
            return;
        }

        fetch("../../backend/controller/admin/formularios/respuesta/form_respuestas.php", {
            method: "POST",
            body: formData
        })
            .then(async res => {
                const text = await res.text();
                //console.log("Respuesta cruda del servidor:", text);

                try {
                    const json = JSON.parse(text);
                    //console.log("Respuesta JSON parseada:", json);

                    if (json.success) {
                        //Swal.fire("Éxito", json.message, "success");
                        mostrarAlerta("success", json.message, "Éxito");
                        // Vaciar el formulario
                        document.getElementById('contenedorFormulario').innerHTML = '';
                        // Actualizar la lista de solicitudes sin recargar la página
                        actualizarSolicitudes();
                    } else {
                        // Rehabilitar el botón en caso de error del servidor
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = 'Enviar';
                        }
                        //Swal.fire("Error", json.message, "error");
                        mostrarAlerta("error", json.message, "Error");
                    }
                } catch (err) {
                    // Rehabilitar el botón en caso de error de parsing
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = 'Enviar';
                    }
                    console.error("Error al parsear JSON:", err.message);
                    console.error("Contenido que causó el error:", text);
                    //Swal.fire("Error", "Error al procesar la respuesta del servidor", "error");
                    mostrarAlerta("error", "Error al procesar la respuesta del servidor", "Error");
                }
            })
            .catch(err => {
                // Rehabilitar el botón en caso de error de red
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Enviar';
                }
                console.error("Error en la petición:", err);
                //Swal.fire("Error", "Error al enviar la solicitud", "error");
                mostrarAlerta("error", "Error al enviar la solicitud", "Error");
            });
    });
}

function actualizarSolicitudes() {
    const mainContent = document.querySelector('.db-content');
    const idUsuario = mainContent?.dataset.user;

    //console.log("Elemento mainContent encontrado:", mainContent);
    //console.log("Valor de data-user:", idUsuario);

    if (!idUsuario) {
        console.warn("ID de usuario no encontrado en el dataset.");
        return;
    }

    fetch("./ui/rrhh/listar_solicitudes_usuario.php", {
        method: "POST",
        body: new URLSearchParams({ id_empleado: idUsuario })
    })
        .then(res => res.text())
        .then(html => {
            //console.log("Respuesta HTML recibida:", html);

            let contenedorSolicitudes = document.querySelector('.solicitudes-container');

            if (!contenedorSolicitudes) {
                console.warn("Contenedor de solicitudes no encontrado. Creando uno nuevo...");

                // Crear el contenedor si no existe
                contenedorSolicitudes = document.createElement('div');
                contenedorSolicitudes.className = 'row solicitudes-container';
                const parent = document.querySelector('.db-content .container');

                // Insertar el contenedor antes del `hr` o al final
                const hrElement = document.querySelector('.db-content hr.my-4');
                if (hrElement) {
                    parent.insertBefore(contenedorSolicitudes, hrElement.nextSibling);
                } else {
                    parent.appendChild(contenedorSolicitudes);
                }
            }

            contenedorSolicitudes.innerHTML = html;
        })
        .catch(err => {
            console.error("Error al actualizar las solicitudes del usuario:", err);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('button_ver_solicitud_usuario')) {

            //deshabilitar el boton
            const botonVerMas = e.target;
            botonVerMas.disabled = true;

            const card = e.target.closest('.card');
            const idSolicitudRH = card.querySelector('.id_solicitud_rh').value;
            const idFormulario = card.querySelector('.id_form').value;
            const fecha = card.querySelector('.fecha').value;
            const idEmpleado = card.querySelector('.id_empleado').value;

            const formData = new URLSearchParams();
            formData.append('id_solicitud_rh', idSolicitudRH);
            formData.append('id_form', idFormulario);
            formData.append('fecha', fecha);
            formData.append('id_empleado', idEmpleado);

            fetch('./ui/rrhh/detalle_solicitud.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    //console.log("Respuesta JSON:", data); // <-- Verificar la estructura recibida

                    const modalBody = document.getElementById('modalContenidoSolicitud');

                    // Verificar que data.html exista antes de asignarlo
                    if (data.html) {
                        modalBody.innerHTML = data.html;
                    } else {
                        console.warn("La clave 'html' no está definida en la respuesta del servidor.");
                        modalBody.innerHTML = '<p class="text-danger">Error al cargar la solicitud.</p>';
                    }

                    const respuestaRRHH = data.respuesta_rrhh ?? 'Sin respuesta';
                    modalBody.innerHTML += `
                        <div class="mt-4">
                            <strong>Respuesta de RRHH:</strong>
                            <p class="text-secondary">${respuestaRRHH}</p>
                        </div>
                    `;

                    const modal = new bootstrap.Modal(document.getElementById('modalSolicitudUsuario'));
                    modal.show();
                })
                .catch(err => {
                    console.error('Error al cargar el detalle de la solicitud:', err);
                    // Swal.fire('Error', 'No se pudo cargar el detalle de la solicitud.', 'error');
                    mostrarAlerta('error', 'No se pudo cargar el detalle de la solicitud.', 'Error');
                })
                .finally(() => {
                    // Re-habilitar el botón después de la petición
                    setTimeout(() => {
                        botonVerMas.disabled = false;
                    }, 500);
                });
        }
    });
});