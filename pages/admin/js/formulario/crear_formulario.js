

const contenedor = document.getElementById('contenedor-preguntas');
const tipo_formulario_comnt = document.getElementById('tipo_form');



const select = document.getElementById('tipo-enfoque');
const divAsignacion = document.getElementById('opcion-asignacion');

select.addEventListener('change', function () {
    const valor = this.value;

    if (valor === 'general') {
        // Oculta el div si es "General"
        divAsignacion.classList.add('d-none');
        divAsignacion.innerHTML = '';

    } else {
        let formData = new FormData();
        formData.append("descrip", valor);
        // Muestra el div y carga contenido PHP vía AJAX
        fetch('./ui/formulario/cont_dinamic_enfoque.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                divAsignacion.innerHTML = data;
                divAsignacion.classList.remove('d-none');
                select_enf();
            })
            .catch(error => {
                divAsignacion.innerHTML = '<p>Error al cargar el contenido.</p>';
                console.error(error);
            });
    }
});

function select_enf() {
    let selec_tipo_enfoque = null;
    let verif = "";
    const tagContainer = document.getElementById('tag-container');

    if (document.getElementById('sel_equipos')) {
        selec_tipo_enfoque = document.getElementById('sel_equipos');
        verif = "equipo";
    }

    if (document.getElementById('sel_empleado')) {
        selec_tipo_enfoque = document.getElementById('sel_empleado');
        verif = "empleado";
    }

    if (selec_tipo_enfoque) {
        selec_tipo_enfoque.addEventListener('change', function () {
            const selectedOptions = Array.from(this.selectedOptions);

            selectedOptions.forEach(option => {
                const selectedText = option.text;
                const selectedValue = option.value;
                const tagId = selectedValue;

                // Evitar duplicados
                if (document.getElementById(tagId)) return;

                // Crear contenedor para el tag y el input
                const wrapper = document.createElement('div');
                wrapper.className = 'd-inline-block me-2 mb-3';
                wrapper.id = tagId;

                // Badge visual
                const tag = document.createElement('span');
                tag.className = 'badge bg-primary-soft';
                tag.textContent = selectedText;

                // Botón de cerrar
                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'btn-close btn-close-black btn-sm ms-2';
                closeBtn.onclick = () => wrapper.remove();

                // Input hidden
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.className = 'input-selected';

                hiddenInput.name = 'seleccionados[]';
                hiddenInput.value = selectedValue;

                // Armar todo
                tag.appendChild(closeBtn);
                wrapper.appendChild(tag);
                wrapper.appendChild(hiddenInput);

                tagContainer.appendChild(wrapper);
            });
        });
    }
}

//cambiar el campo de acuerdo a lo que selecciona
document.addEventListener('change', function (e) {
    if (e.target && e.target.matches('.tipoCampos')) {
        const select = e.target;
        const valor = select.value;

        const card = select.closest('.card');
        const div_cont_campo = card.querySelector('.cont-campos');

        let formData = new FormData();
        formData.append("descrip", valor);

        fetch('./ui/formulario/cont_dinamic_campos.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                div_cont_campo.innerHTML = data;
                activarCalificacion(div_cont_campo)

            })
            .catch(error => {
                div_cont_campo.innerHTML = '<p class="text-danger">Error al cargar el contenido.</p>';
                console.error('Error en fetch:', error);
            });
    }
});

function agregarCampo(boton) {
    const grupo = boton.closest('.input-group');
    const container = grupo.parentElement;

    const nuevoCampo = document.createElement('div');
    nuevoCampo.className = 'input-group mb-2';
    nuevoCampo.innerHTML = `
        <input type="text" name="campo[]" id="campo" class="form-control" placeholder="Opción">
        <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">-</button>
    `;

    container.appendChild(nuevoCampo);
}


function activarCalificacion(contenedor) {
    const calContainer = contenedor.querySelector('.calificacion');
    if (!calContainer) return;

    const labels = calContainer.querySelectorAll('label');
    let currentRating = 0;

    labels.forEach(label => {
        const value = parseInt(label.getAttribute('data-value'));

        label.addEventListener('mouseover', () => {
            updateStars(value);
        });

        label.addEventListener('click', () => {
            currentRating = value;
            updateStars(currentRating);

            const input = calContainer.querySelector(`input[value="${value}"]`);
            if (input) input.checked = true;
        });

        label.addEventListener('mouseout', () => {
            updateStars(currentRating);
        });
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
}



window.addEventListener('load', function () {
    // ...todo tu código actual aquí...
    new Sortable(contenedor, {
        animation: 150,
        handle: '.bi-grip-horizontal',
        ghostClass: 'ghost',
        onEnd: actualizarOrden
    });
});
function duplicarPregunta() {
    const original = document.querySelector('.pregunta');

    const clon = original.cloneNode(true);
    limpiarCampos(clon);
    document.getElementById('contenedor-preguntas').appendChild(clon);
    actualizarOrden();
    actualizarNames();
}


function limpiarCampos(pregunta) {
    // Limpia los campos de entrada del clon
    pregunta.querySelectorAll('input, textarea, select').forEach(input => {
        if (input.type === 'checkbox' || input.type === 'radio') {
            input.checked = false;
        } else {
            input.value = '';
        }
    });
}

function actualizarOrden() {
    // Actualiza los valores del input "orden" para cada pregunta
    document.querySelectorAll('.pregunta').forEach((pregunta, index) => {
        const inputOrden = pregunta.querySelector('input[name="orden[]"]');
        if (inputOrden) {
            inputOrden.value = index + 1;
        }
    });
}

function actualizarNames() {
    // Actualiza los name="campo[i][]" según el índice de la pregunta
    const preguntas = document.querySelectorAll('.pregunta');
    preguntas.forEach((pregunta, index) => {
        pregunta.querySelectorAll('[name^="campo"]').forEach(input => {


        });
    });
}

// Guardar el formulario
document.getElementById('form-creacion').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData();
    const preguntas = document.querySelectorAll(".pregunta")
    var objPregunta = {}
    preguntas.forEach((item) => {
        console.log(item.getElementsByTagName("select"))
        const inputs = item.getElementsByTagName("input")
        const select = item.getElementsByTagName("select")[0].value
        // Array.from(selects).forEach(selectAux => {
        //     select = selectAux.value;

        //     console.log("Seleccionado:", select,selectAux.options[selectAux.selectedIndex].text);
        // });
        let orden = Object.values(objPregunta).length + 1
        Array.from(inputs).forEach(input => {
            console.log("input", input, input.id, input.value)
            switch (input.id) {
                case "orden":
                    objPregunta[input.value] = {
                        "pregunta": "",
                        "obligatorio": "",
                        "opciones": [],
                        "tipo_campo": select
                    }
                    break;
                case "obligatorio":

                    objPregunta[orden]["obligatorio"] = input.checked ? 1 : 0;
                    break;
                case "pregunta":
                    console.log(orden, objPregunta)
                    objPregunta[orden]["pregunta"] = input.value;
                    console.log(orden, objPregunta)

                    break
                case "campo":
                    objPregunta[orden]["opciones"].push(input.value);
                    break;
                default:
                    break;
            }
        })
        console.log(objPregunta)
    })
    //seleccionados
    let inputSelected = []
    document.querySelectorAll(".input-selected").forEach((item) => {
        inputSelected.push(item.value)
    })

    const submitButton = form.querySelector('button[type="submit"]');

    // Agregas el atributo disabled

submitButton.disabled = true;
    

    //console.log(formData)
    //return
    formData.set("data", JSON.stringify(objPregunta))
    formData.set("tipoFormulario", document.getElementById("tipo_form").value)
    formData.set("tituloFormulario", document.getElementById("form_titulo").value)
    formData.set("tipoenfoque", document.getElementById("tipo-enfoque").value)
    formData.set("seleccionados", JSON.stringify(inputSelected))
    fetch('../../backend/controller/admin/formularios/crear_formulario/crear_formulario.php', {
        method: 'POST',
        body: formData
    })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);

            } catch (error) {
                console.error("Respuesta del servidor no es JSON válido:", text);
                throw new Error("Respuesta no válida");
            }
        })
        .then(data => {
            if (data.success) {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    
                    submitButton.disabled = true;
                    location.reload();

                });
            } else {
                Swal.fire('Error', data.message, 'error');
                submitButton.disabled = true;
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
        });
});



