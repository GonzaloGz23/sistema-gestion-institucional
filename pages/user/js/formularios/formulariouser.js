
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
        const form = this;
        const formData = new FormData();
        let hasError = false;
        let respuestaIndex = 0;

        // Obtener el id del formulario (desde el hidden)
        const idFormulario = document.querySelector('#resp_form .id_form')?.value;
        if (!idFormulario) {
            Swal.fire("Error", "No se pudo identificar el formulario actual.", "error");
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
        resp_form


        const submitButton = form.querySelector('button[type="submit"]');

        // Agregas el atributo disabled

        submitButton.disabled = true;
        if (hasError) {
            Swal.fire("Campos obligatorios", "Por favor, completa todos los campos obligatorios antes de continuar.", "warning");
            return;
        }

        fetch("../../backend/controller/admin/formularios/respuesta/form_respuestas.php", {
            method: "POST",
            body: formData
        })
            .then(async res => {
                const text = await res.text();
                console.log("Respuesta cruda del servidor:", text);

                try {
                    const json = JSON.parse(text);
                    console.log("Respuesta JSON parseada:", json);

                    if (json.success) {
                        Swal.fire('Éxito', json.message, 'success').then(() => {
                            submitButton.disabled = true;
                            location.reload();
                        });
                    } else {
                        Swal.fire("Error", json.message, "error");
                        submitButton.disabled = true;
                    }
                } catch (err) {
                    console.error("Error al parsear JSON:", err.message);
                    console.error("Contenido que causó el error:", text);
                    Swal.fire("Error", "Error al procesar la respuesta del servidor", "error");
                }
            })
            .catch(err => {
                console.error("Error en la petición:", err);
                Swal.fire("Error", "Error al enviar la solicitud", "error");
            });
    });
}


