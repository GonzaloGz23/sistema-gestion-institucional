
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener("contextmenu", function (e) {
        e.preventDefault(); // Bloquea el menú contextual de clic derecho
    }, false);

    function textLoadingBlur() {
        const textarea = document.querySelectorAll('textarea.tarea-texto');
        textarea.forEach(text => {
            text.addEventListener("blur", function () {
                //console.log('Mouse salió después de hacer clic');

                const id_tarea = this.getAttribute("data-id-list");
                const tarea = this.value; // ← acá estaba el error
                const check = document.querySelector(`input[type="checkbox"][data-id-list="${id_tarea}"]`);

                //console.log(id_tarea, tarea, "mirar la tarea");

                if (parseInt(id_tarea) > 0) {
                    fecthElement({
                        "id_lista_tarea": id_tarea,
                        "tarea": tarea,
                        "url": '../../backend/controller/usuario/notas/get_editar_tarea.php',
                        "chekeado": check && check.checked ? 1 : 0
                    });
                }

                // Remover el listener para evitar múltiples activaciones

            });
        });
        //eliminar tareas
        const btnEliminar = document.querySelectorAll('button.btn-eliminar-tarea');
        btnEliminar.forEach(btn => {
            btn.addEventListener("click", function () {
                let id_tarea = this.getAttribute("data-id-list")
                if (parseInt(id_tarea) > 0) {
                    fecthElement({
                        "id_lista_tarea": id_tarea,
                        "url": '../../backend/controller/usuario/notas/get_lista_eliminar.php',
                    })
                    btn.parentElement.remove();
                }
            })
        })
    }

    document.addEventListener("touchstart", function (e) {
        if (e.touches.length === 1) {
            this.longPressTimer = setTimeout(function () {
                e.preventDefault(); // Bloquea el menú contextual de presión larga
            }, 500); // medio segundo
        }
    }, false);

    document.addEventListener("touchend", function () {
        clearTimeout(this.longPressTimer);
    }, false);

    document.addEventListener("touchmove", function () {
        clearTimeout(this.longPressTimer);
    }, false);
    eliminar();


    function autoResize(el) {
        el.style.height = 'auto';           // Reiniciar
        el.style.height = el.scrollHeight + 'px'; // Ajustar
    }
    // Ajustar tamaño al escribir



    const iconoVista = document.querySelector(".changeModIcon");
    const contenedorNotas = document.getElementById("contenedorNotas");

    iconoVista.addEventListener("click", function () {
        contenedorNotas.classList.toggle("modo-lista");

        // Cambia ícono si querés indicar el estado
        iconoVista.classList.toggle("bi-view-stacked");
        iconoVista.classList.toggle("bi-grid");
    });

    const botones = document.querySelectorAll('[data-bs-target="#modalTexto"], [data-bs-target="#modalLista"]');

    botones.forEach(boton => {
        boton.addEventListener('click', function () {
            const tipo = this.getAttribute('data-tipo'); // "texto" o "lista"


            const contenedorTareas = document.getElementById('conteiner-colaboradores');
            if (contenedorTareas) {
                contenedorTareas.innerHTML = '';  // elimina todo el HTML interno
            }


            // 2. Vaciar el textarea de nueva tarea
            const inputTarea = document.getElementById('titulo_edit');
            if (inputTarea) {
                inputTarea.value = '';            // deja el valor en vacío
            }
            const modal = new bootstrap.Modal(document.getElementById('modalEditarNota'));
            modal.show();
            const abrirModalBtn = document.getElementById('abrirModalBtn');
            if (abrirModalBtn) {
                abrirModalBtn.setAttribute('data-id-nota', 'null');  // Lo pone en "null" como texto
                // o para eliminar el atributo:
                // abrirModalBtn.removeAttribute('data-id-nota');
                console.log(abrirModalBtn.setAttribute('data-id-nota', 'null'));
            }


            fetch('./ui/notas/cont_modal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ tipo })
            })
                .then(response => response.text())
                .then(html => {
                    document.querySelector('.tipo-contenido-edit').innerHTML = html;

                    textTareaFuntion(tipo)
                    guardarnota(tipo);
                    //limpiarColaborador()
                })
                .catch(error => {
                    console.error('Error al cargar el contenido:', error);
                    document.querySelector('.tipo-contenido').innerHTML = '<div class="text-danger">Error al cargar contenido.</div>';
                });
        });
    });

    function textTareaFuntion(tipo, idNota = null) {
        if (idNota == null) {
            idNota = ""
        }
        //console.log("estoy recibiendo el id de nota", idNota)
        if (tipo === 'texto') {
            const textarea = document.getElementById('nota');
            if (textarea) {
                // Aplicar autoajuste inmediatamente
                if (typeof autosizeTextarea === 'function') {
                    autosizeTextarea(textarea);
                } else {
                    // Fallback al método original si la función global no está disponible
                    textarea.style.height = 'auto';
                    textarea.style.height = (textarea.scrollHeight) + 'px';
                    textarea.addEventListener('input', function () {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });
                }
            }

        } else if (tipo === 'lista') {
            const inputNombre = document.getElementById('inp_listar');
            const btnAgregar = document.getElementById('btnAgregarItem');
            const contenedorCheckbox = document.getElementById('checkboxDinamicos');
            //console.log("mostrar lo cargado", inputNombre)
            // Aplicar autoajuste inmediatamente al textarea de lista
            if (inputNombre && typeof autosizeTextarea === 'function') {
                autosizeTextarea(inputNombre);
            }

            // Asegurar que existen los elementos
            if (inputNombre && btnAgregar && contenedorCheckbox) {

                /* // Ajustar altura automáticamente
                inputNombre.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                }); */

                // Agregar al presionar ENTER
                inputNombre.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault(); // Prevenir salto de línea
                        btnAgregar.click(); // Simular click
                    }
                });

                // Click en botón para agregar tarea

                btnAgregar.addEventListener('click', function () {
                    const valor = inputNombre.value.trim();
                    if (valor !== '') {
                        const idUnico = Date.now(); // ID único para identificar

                        // Crear nuevo ítem
                        const nuevoItem = document.createElement('div');
                        nuevoItem.className = 'input-group mb-2 align-items-center tarea-item';
                        const check = document.querySelector("#check_init")
                        nuevoItem.innerHTML = `
                            <div class="input-group-text bg-transparent border-0">
                                <input type="checkbox" data-id-nota="${idNota}" class="form-check-input mt-0 tarea-checkbox" data-id-list=""  id="chk_${idUnico}" data-id="${idUnico}" ${(check.checked ? 'checked' : '')}>
                            </div>
                            
                            <textarea data-id-list="" class="${(check.checked ? 'text-decoration-line-through' : '')} form-control border-0 flex-grow-1 px-2 input-fondo-transparente tarea-texto"
                                data-id="${idUnico}" id="txtarea_${idUnico}" rows="1" placeholder="Editar tarea"
                                style="overflow:hidden; resize:none;">${valor}</textarea>
                            <button class="btn btn-icon btn-eliminar-tarea" data-id-list="" id="btn_${idUnico}" data-id="${idUnico}">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        `;

                        contenedorCheckbox.appendChild(nuevoItem);

                        if (parseInt(idNota) > 0) {
                            const data = fecthElement({
                                "type": "add_new_list",
                                "tarea": valor,
                                "id_nota": idNota,
                                "idUnico": idUnico,
                                "isChecked": check.checked ? 1 : 0,
                                "url": '../../backend/controller/usuario/notas/get_editar.php',
                                "ids": [
                                    "btn_" + idUnico,
                                    "txtarea_" + idUnico,
                                    "chk_" + idUnico

                                ]
                            })
                            //console.log("recibi estos datos", data)
                            check.checked = false
                            textLoadingBlur()
                        } else {
                            //console.log("añadiendo un nuevo elemento para anie", idUnico, idNota)
                        }

                        // Ajustar y enfocar el nuevo textarea correctamente
                        const nuevoTextarea = nuevoItem.querySelector('.tarea-texto');
                        if (nuevoTextarea) {
                            // Usar la función global de autoajuste si está disponible
                            if (typeof autosizeTextarea === 'function') {
                                autosizeTextarea(nuevoTextarea);
                            } else {
                                // Fallback al método original
                                nuevoTextarea.style.height = 'auto';
                                nuevoTextarea.style.height = nuevoTextarea.scrollHeight + 'px';

                                nuevoTextarea.addEventListener('input', function () {
                                    this.style.height = 'auto';
                                    this.style.height = this.scrollHeight + 'px';
                                });
                            }
                        }


                        // Resetear el campo principal
                        inputNombre.value = '';
                        inputNombre.style.height = 'auto';
                    }
                });




                // Tachar texto si está checkeado
                contenedorCheckbox.addEventListener('change', function (e) {
                    if (e.target.classList.contains('tarea-checkbox')) {
                        const input = e.target.closest('.tarea-item').querySelector('.tarea-texto');
                        input.classList.toggle('text-decoration-line-through', e.target.checked);
                    }
                });

                // Eliminar tarea
                //contenedorCheckbox.addEventListener('click', function (e) {
                //    if (e.target.closest('.btn-eliminar-tarea')) {
                //        e.target.closest('.tarea-item').remove();
                //    }
                //});
            } else {
                console.warn('Faltan elementos necesarios para listas');
            }
        }
    }

    function fecthElement(obj) {
        try {
            fetch(obj.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ ...obj })
            })
                .then(response => response.json())
                .then(html => {
                    // document.querySelectorAll(`[data-id="${obj.idUnico}"]`).forEach(el => {
                    //     el.setAttribute('data-id-list', html.id_lista_tarea);
                    // });
                    const ele = document.querySelector("input[type=checkbox][data-id='" + obj.idUnico + "']")
                    if (ele != null) {
                        //console.log(ele, "mirar el elemento de fetcdata")
                        //console.log(html, "mirar aqui")
                        ele.setAttribute("data-id-list", html.id_lista_tarea)
                        //elemento de area
                        document.querySelector("textarea[data-id='" + obj.idUnico + "']").setAttribute("data-id-list", html.id_lista_tarea)
                        //elemento de button
                        document.querySelector("button[data-id='" + obj.idUnico + "']").setAttribute("data-id-list", html.id_lista_tarea)
                    }
                })
                .catch(error => {
                    console.error('Error al cargar el contenido:', error);
                    //document.querySelector('.tipo-contenido-edit').innerHTML = '<div class="text-danger">Error al cargar contenido.</div>';
                });
        } catch (error) {

        }
    }



    //abrir modal para editar

    document.querySelectorAll(".masonry-item.este-es").forEach(card => {
        card.addEventListener("click", function () {
            // Aquí abrís el modal y cargás los datos si hace falta
            // Por ejemplo:
            // cargarNotaEnModal(idNota);
            const idNota = this.dataset.idNota;
            //console.log("Abrir modal para nota ID:", idNota);
            console.log(idNota);
            if (parseInt(idNota) > 0) {

                const modal = new bootstrap.Modal(document.getElementById('modalEditarNota'));
                fetch('./ui/notas/cont_modal.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ "tipo": "edit_nota", "id_nota": idNota })
                })
                    .then(response => response.json())
                    .then(html => {
                        const data = Object.values(html.data)[0]
                        console.log(html.data);

                        //document.querySelector('.tipo-contenido-edit').innerHTML = JSON.stringify(html);


                        // Ajustar tamaño al cargar la página
                        document.getElementById("titulo_edit").value = data.titulo
                        const isRender = render_edit_elements(data.tiponota, data)
                        const inputTitulo = document.getElementById("titulo_edit");

                        console.log(data.tiponota);
                        const iconoPin = document.getElementById("abrirModalBtn");
                        textTareaFuntion(data.tiponota, idNota)
                        if (iconoPin) {
                            iconoPin.setAttribute("data-id-nota", data.id_nota);
                        }


                        const pin = document.getElementById("pinToggle");

                        // Limpiar listeners previos clonando el nodo
                        if (pin) {
                            const nuevoPin = pin.cloneNode(true);
                            pin.parentNode.replaceChild(nuevoPin, pin);

                            // Estado inicial
                            if (data.pineada == '1') {
                                nuevoPin.classList.remove('bi-pin');
                                nuevoPin.classList.add('bi-pin-fill');
                            } else {
                                nuevoPin.classList.remove('bi-pin-fill');
                                nuevoPin.classList.add('bi-pin');
                            }

                            // Evento de click
                            nuevoPin.addEventListener("click", function () {
                                const estaPineada = this.classList.contains("bi-pin-fill");
                                const nuevaPineada = estaPineada ? 0 : 1;

                                // Cambiar ícono visualmente
                                this.classList.toggle("bi-pin");
                                this.classList.toggle("bi-pin-fill");
                                const formData = new FormData();
                                formData.append("id_nota", idNota);
                                formData.append("pineada", nuevaPineada);



                                // Guardar en BD
                                fetch('../../backend/controller/usuario/notas/get_cambiarpin.php', {
                                    method: "POST",
                                    body: formData
                                })
                                    .then(data => data.json())
                                    .then(data => {
                                        if (data.success) {
                                            

                                            const card_pin = document.querySelector(`.masonry-item[data-id-nota="${idNota}"]`);
                                            const contenedor_editar = document.getElementById("contenedorNotas");

                                            if (card_pin) {
                                                const icono_pin = card_pin.querySelector(".btnCambiarPin i");

                                                if (icono_pin) {
                                                    if (parseInt(nuevaPineada) === 1) {
                                                        icono_pin.classList.remove("bi-pin-angle");
                                                        icono_pin.classList.add("bi-pin-angle-fill");
                                                    
                                                                contenedor_editar.prepend(card_pin); // Mueve la nota al principio
                                                        
                                                            
                                                            
                                                    } else {
                                                        icono_pin.classList.remove("bi-pin-angle-fill");
                                                        icono_pin.classList.add("bi-pin-angle");
                                                        contenedor_editar.appendChild(card_pin); // Mueve la nota al final
                                                    }
                                                }
                                            }
                
                                        }
                                        if (!data.success) {
                                            console.error('❌ Error al cambiar pin:', data.message);
                                            // Revertir el cambio si falla
                                            this.classList.toggle("bi-pin");
                                            this.classList.toggle("bi-pin-fill");
                                        }
                                    })
                                    .catch(err => {
                                        console.error('❌ Error de red al cambiar pin:', err);
                                        // Revertir el cambio si falla
                                        this.classList.toggle("bi-pin");
                                        this.classList.toggle("bi-pin-fill");
                                    });
                            });
                        }


                     



                        if (inputTitulo) {
                            // Clonar el nodo para eliminar listeners previos
                            const nuevoInput = inputTitulo.cloneNode(true);
                            inputTitulo.parentNode.replaceChild(nuevoInput, inputTitulo);

                            nuevoInput.value = data.titulo;

                            nuevoInput.addEventListener('blur', function () {
                                const nuevoTitulo = this.value.trim();
                                if (nuevoTitulo !== '') {
                                    fetch('../../backend/controller/usuario/notas/get_titulo.php', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({
                                            id_nota: data.id_nota,
                                            titulo: nuevoTitulo
                                        })
                                    })
                                        .then(res => res.json())
                                        .then(res => {
                                            if (res.success) {

                                                const card = document.querySelector(`.masonry-item[data-id-nota="${data.id_nota}"] p[data-id-titulo]`);
                                                if (card) {
                                                    card.textContent = nuevoTitulo;
                                                }
                                            }
                                            if (!res.success) {
                                                console.error('❌ Error al guardar título:', res.message);
                                            }
                                        })
                                        .catch(err => console.error('❌ Error de red al guardar título:', err));
                                }
                            });
                        }
                        /* 
                                                if (inputTitulo) {
                                                    inputTitulo.value = data.titulo;
                        
                        
                                                    // Usar la función global de autoajuste si está disponible
                                                    if (typeof autosizeTextarea === 'function') {
                                                        autosizeTextarea(inputTitulo);
                                                        inputTitulo.addEventListener('input', function () {
                                                            autosizeTextarea(this);
                                                        });
                                                    } else {
                                                        // Fallback al método original
                                                        inputTitulo.addEventListener('input', () => autoResize(inputTitulo));
                                                        // Si ya tiene contenido cargado
                                                        autoResize(inputTitulo);
                                                    }
                        
                                                    // Guardar título al perder foco
                                                    inputTitulo.addEventListener('blur', function () {
                                                        const nuevoTitulo = this.value.trim();
                                                        if (nuevoTitulo !== '') {
                                                            fetch('../../backend/controller/usuario/notas/get_titulo.php', {
                                                                method: 'POST',
                                                                headers: { 'Content-Type': 'application/json' },
                                                                body: JSON.stringify({
                                                                    id_nota: data.id_nota,
                                                                    titulo: nuevoTitulo
                                                                })
                                                            })
                                                                .then(res => res.json())
                                                                .then(res => {
                                                                    if (res.success) {
                                                                        //console.log('✅ Título guardado correctamente');
                                                                    } else {
                                                                        console.error('❌ Error al guardar título:', res.message);
                                                                    }
                                                                })
                                                                .catch(err => console.error('❌ Error de red al guardar título:', err));
                                                        }
                                                    });
                                                } */

                        // Guardado automático para la nota tipo "nota"
                        if (data.tiponota === 'nota') {
                            const textareaNota = document.querySelector('#tipo-contenido-edit textarea.tarea-texto');
                            if (textareaNota) {
                                // Usar la función global de autoajuste si está disponible
                                if (typeof autosizeTextarea === 'function') {
                                    autosizeTextarea(textareaNota);
                                    textareaNota.addEventListener('input', function () {
                                        autosizeTextarea(this);
                                    });
                                } else {
                                    // Fallback al método original
                                    textareaNota.style.height = 'auto';
                                    textareaNota.style.height = (textareaNota.scrollHeight) + 'px';
                                    textareaNota.addEventListener('input', function () {
                                        this.style.height = 'auto';
                                        this.style.height = (this.scrollHeight) + 'px';
                                    });
                                }
                                //aqui va lo de notas
                                textareaNota.addEventListener('blur', function () {
                                    const nuevoTexto = this.value.trim();
                                    const formData = new FormData();
                                    formData.append('id_nota', data.id_nota);
                                    formData.append('nota', nuevoTexto);
                                    if (nuevoTexto !== '') {
                                        fetch('../../backend/controller/usuario/notas/get_editar_textnota.php', {
                                            method: 'POST',
                                            body: formData
                                        })
                                            .then(res => res.json())
                                            .then(res => {
                                                if (res.success) {
                                                     
                                                    const cardLimiteTexto = document.querySelector(`.masonry-item[data-id-nota="${data.id_nota}"] .limite-texto p`);
                                                        if (cardLimiteTexto) {
                                                            cardLimiteTexto.textContent = nuevoTexto; // o el texto que quieras mostrar
                                                        }
                                                } else {
                                                    console.error('❌ Error al guardar nota:', res.message);
                                                }
                                            })
                                            .catch(err => console.error('❌ Error de red al guardar nota:', err));
                                    }
                                });
                            }
                        }
                        modal.show();
                        if (isRender) {
                            textTareaFuntion("lista", idNota)
                        }
                        //funciones para check box 
                        const checkbox = document.querySelectorAll('input[type="checkbox"].tarea-checkbox');
                        checkbox.forEach(check => {
                            check.addEventListener("change", function () {
                                let id_tarea = this.getAttribute("data-id-list")
                                const textarea = document.querySelector(`textarea[data-id-list="${id_tarea}"]`);
                                let tarea = ""
                                if (textarea) {
                                    tarea = textarea.value
                                }
                                //console.log(id_tarea, "mirar la tarea")
                                if (parseInt(id_tarea) > 0) {
                                    fecthElement({
                                        "id_lista_tarea": id_tarea,
                                        "tarea": tarea,
                                        "url": '../../backend/controller/usuario/notas/get_editar_tarea.php',
                                        "chekeado": (check.checked ? 1 : 0)
                                    })
                                }
                            })
                        })
                        //funciones para text area //rick
                        textLoadingBlur()

                        //eliminar los elementos

                    })
                    .catch(error => {
                        console.error('Error al cargar el contenido:', error);
                        document.querySelector('.tipo-contenido-edit').innerHTML = '<div class="text-danger">Error al cargar contenido.</div>';
                    });
            }

        });
    });
    //Mostrar colaboradores


    document.getElementById("abrirModalBtn").addEventListener("click", function () {


        const idNota = this.getAttribute("data-id-nota");
        //console.log("Se hizo clic en el ícono. ID nota:", idNota);

        // Guardar el ID en un botón invisible o contenedor auxiliar
        document.getElementById('abrirModalColaboradores')?.setAttribute("data-id-nota", idNota);

        // Mostrar el modal de colaboradores
        const modalCol = new bootstrap.Modal(document.getElementById('modalcolaborador'));
        modalCol.show();
        //document.querySelector("#modalcolaborador .modal-body").innerHTML = '';
        // Cargar contenido HTML del modal
        fetch(`./ui/notas/cont_colaborador.php?id=${idNota}`)
            .then(response => response.text())
            .then(html => {
                console.log(idNota);

                document.querySelector("#modalcolaborador .modal-body").innerHTML = html;
                agregarEventosColaboradores(); // función para eventos de agregar/eliminar
                inicializarBuscadorColaboradores();
            })
            .catch(err => {
                console.error("❌ Error al cargar colaboradores:", err);
            });
    });
    function inicializarBuscadorColaboradores() {
 console.log("ingreso aqui");
 
    const inputCol = document.getElementById("inputColaborador");
    const sugerenciasDiv = document.getElementById("sugerenciasColaborador");

    if (!inputCol) return;

    inputCol.addEventListener("input", function() {
        const valor = this.value.toLowerCase();
        sugerenciasDiv.innerHTML = ""; // limpiar sugerencias

        if (valor === "") return;

        const filtrados = colaboradoresDisponibles.filter(c =>
            c.nombre.toLowerCase().includes(valor)
        );

        filtrados.forEach(c => {
            const item = document.createElement("div");
            item.textContent = c.nombre;
            item.style.padding = "5px 10px";
            item.style.cursor = "pointer";

            item.addEventListener("click", () => {
                inputCol.value = c.nombre; // mostrar seleccionado en el input
                sugerenciasDiv.innerHTML = "";

                // Agregar al listado de colaboradores asignados
                const lista = document.getElementById("lista-colaboradores");

                // Evitar duplicados
                if (!lista.querySelector(`.list-group-item[data-id='${c.id}']`)) {
                    const siglas = c.nombre.split(" ").map(n => n[0].toUpperCase()).join("").slice(0,2);
                    const div = document.createElement("div");
                    div.className = "list-group-item d-flex justify-content-between align-items-center";
                    div.dataset.id = c.id;

                    div.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-sm rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                    ${siglas}
                                </span>
                            </div>
                            <div>
                                <small>${c.nombre}</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar-colaborador" data-id="${c.id}" data-nota="<?= htmlspecialchars($idNota) ?>">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    `;

                    lista.appendChild(div);
                }
            });

            sugerenciasDiv.appendChild(item);
        });
    });
}
    document.getElementById("modalcolaborador").addEventListener("hidden.bs.modal", () => {
        const idNota = document.getElementById('abrirModalColaboradores')?.getAttribute("data-id-nota");

        if (idNota && parseInt(idNota) > 0) {
            fetch('./ui/notas/cont_modal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    tipo: "edit_nota",
                    id_nota: idNota
                })
            })
                .then(res => res.json())
                .then(html => {
                    const data = Object.values(html.data)[0];

                    document.getElementById("titulo_edit").value = data.titulo;
                    render_edit_elements(data.tiponota, data);

                    // Restaurar autosize u otros eventos si usás autosizeTextarea o autoResize
                    const inputTitulo = document.getElementById("titulo_edit");
                    if (typeof autosizeTextarea === 'function') {
                        autosizeTextarea(inputTitulo);
                    } else {
                        autoResize(inputTitulo);
                    }

                    // Mostrar nuevamente el modal de edición
                    const modalEditar = new bootstrap.Modal(document.getElementById("modalEditarNota"));
                    modalEditar.show();
                })
                .catch(err => {
                    console.error("❌ Error al recargar modal de edición:", err);
                });
        }
    });
    let estaPineado = 0; // por defecto no pineado

    document.getElementById('pinToggle').addEventListener('click', function () {
        const icon = this;

        // Alternar clase
        if (icon.classList.contains('bi-pin')) {
            icon.classList.remove('bi-pin');
            icon.classList.add('bi-pin-fill');
            estaPineado = 1;
        } else {
            icon.classList.remove('bi-pin-fill');
            icon.classList.add('bi-pin');
            estaPineado = 0;
        }

        //console.log("📌 Estado pineado:", estaPineado);
        // Podés guardar esto en FormData para enviarlo más tarde
    });
    function guardarnota(tipo) {
        const tipo_modal = tipo;
        const modal = document.getElementById('modalEditarNota');

        modal.addEventListener('hidden.bs.modal', function () {
            const titulo = document.getElementById('titulo_edit')?.value.trim() || '';
            const iconoPin = document.getElementById('pinToggle');
            const estaPineado = iconoPin.classList.contains('bi-pin-fill') ? 1 : 0;
            const texto = document.getElementById('nota')?.value.trim() || ''; // solo si no es tipo lista

            const colaboradores = [];
            document.querySelectorAll('#conteiner-colaboradores [data-id-usuario]').forEach(el => {
                const id = el.getAttribute('data-id-usuario');
                if (id) colaboradores.push(id);
            });

            const idNota = modal.getAttribute('data-id-nota'); // Asegurate de setear esto cuando abrís el modal

            const formData = new FormData();
            formData.append('titulo', titulo);
            formData.append('tipo', tipo_modal);
            formData.append('pineado', estaPineado);
            if (idNota) formData.append('id_nota', idNota);

            if (tipo_modal === 'lista') {
                const tareas = [];
                if (document.querySelector(".tarea-item") != null) {

                    document.querySelectorAll('#checkboxDinamicos .tarea-item').forEach(item => {
                        //console.log(item)
                        const checkbox = item.querySelector('.tarea-checkbox');
                        const textarea = item.querySelector('.tarea-texto');
                        tareas.push({
                            id: checkbox?.dataset.id || null,
                            texto: textarea?.value.trim() || '',
                            check: checkbox?.checked ? 1 : 0
                        });
                    });
                    formData.append('tareas', JSON.stringify(tareas));
                }
            } else {
                formData.append('texto', texto); // texto plano solo si no es lista
            }

            colaboradores.forEach((id, i) => formData.append(`colaboradores[${i}]`, id));

            fetch('../../backend/controller/usuario/notas/get_notas.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success === true) {
                        setTimeout(function () {
                            location.reload();
                        }, 3000)
                        //console.log('✅ Nota guardada automáticamente al cerrar el modal');
                    } else {
                        console.warn('❌ Error al guardar:', data.mensaje || data);
                    }
                })
                .catch(err => {
                    console.error('❌ Error inesperado:', err);
                });
        }, { once: true }); // importante: para que no se duplique si se llama varias veces
    }

    /*  document.getElementById('buscador').addEventListener('input', function () {
         const filtro = this.value.toLowerCase().trim();
 
         const notas = document.querySelectorAll('.masonry-item');
 
         notas.forEach(nota => {
             // Buscamos el título dentro de la nota
             const tituloElem = nota.querySelector('.tarea-texto');
             if (!tituloElem) return;
 
             const titulo = tituloElem.textContent.toLowerCase();
 
             if (titulo.includes(filtro)) {
                 nota.style.display = '';  // mostrar
             } else {
                 nota.style.display = 'none';  // ocultar
             }
         });
     }); */
    document.getElementById('buscador').addEventListener('input', function () {
        const filtro = this.value.toLowerCase().trim();

        const notas = document.querySelectorAll('.masonry-item');

        notas.forEach(nota => {
            const tituloElem = nota.querySelector('.tarea-texto');
            const colaboradoresElems = nota.querySelectorAll('.lista-colaboradores-nota [title]');

            let coincideTitulo = false;
            let coincideColaborador = false;

            // Buscar en título
            if (tituloElem) {
                const titulo = tituloElem.textContent.toLowerCase();
                if (titulo.includes(filtro)) {
                    coincideTitulo = true;
                }
            }

            // Buscar en nombres de colaboradores
            colaboradoresElems.forEach(col => {
                const nombreCompleto = col.getAttribute('title').toLowerCase();
                if (nombreCompleto.includes(filtro)) {
                    coincideColaborador = true;
                }
            });

            // Mostrar si coincide en cualquiera
            if (coincideTitulo || coincideColaborador) {
                nota.style.display = '';
            } else {
                nota.style.display = 'none';
            }
        });
    });



});
document.getElementById('actualizarDiv').addEventListener('click', cargarContenidoNotas);






// renderizar las notas cargadas
function render_edit_elements(type, data) {
    // Template para cada colaborador
    const colaboradorTemplate = `
    <span class="avatar avatar-sm avatar-primary-soft" style="cursor:pointer" data-id-unico=":id-unico" data-id-usuario=":id-usuario" title=":empleado">
        <span class="avatar-initials rounded-circle">:iniciales</span>  
    </span>`;

    let auxcolaboradores = "";

    // Generar colaboradores
    if (data.colaboradores && data.colaboradores.length > 0) {
        //console.log('Total colaboradores a renderizar:', data.colaboradores.length);
        data.colaboradores.forEach((item, index) => {
            if (!item || parseInt(item.estadoColaborador) !== 1) return;


            const timestamp = Date.now();
            const random = Math.floor(Math.random() * 1000);
            const idUnico = `${timestamp}_${index}_${random}`;

            let iniciales = '??';
            if (item.nombre && item.apellido) {
                iniciales = `${item.nombre.charAt(0)}${item.apellido.charAt(0)}`.toUpperCase();
            } else if (item.nombre) {
                iniciales = item.nombre.substring(0, 2).toUpperCase();
            } else if (item.apellido) {
                iniciales = item.apellido.substring(0, 2).toUpperCase();
            }

            let colaboradorHtml = colaboradorTemplate
                .replace(/:id-unico/g, idUnico)
                .replace(/:id-usuario/g, item.id_empleado || '')
                .replace(/:iniciales/g, iniciales)
                .replace(/:empleado/g, `${item.apellido || ''}, ${item.nombre || ''}`);

            auxcolaboradores += colaboradorHtml;
        });
    }

    // Render tipo "nota"
    if (type === "nota") {
        const element = `
        <div data-id-nota="${data.id_nota}" class="input-group mb-2 align-items-center tarea-item">
            <textarea class="form-control border-0 flex-grow-1 px-2 input-fondo-transparente tarea-texto"
                rows="1" placeholder="Editar tarea"
                style="overflow: hidden; resize: none; height: 40px;">${data.nota || ''}</textarea>
        </div>`;

        document.getElementById("tipo-contenido-edit").innerHTML = element;


    }

    // Render tipo "lista"
    else if (type === "lista") {
        const baseTimestamp = Date.now();

        const taskTemplate = `
        <div class="input-group mb-2 align-items-center tarea-item">
            <div class="input-group-text bg-transparent border-0">
                <input type="checkbox" data-id-nota=":id-nota" data-id-list=":id-lista" class="form-check-input mt-0 tarea-checkbox" data-id=":id-unico" :checked>
            </div>
            <textarea data-id-list=":id-lista" class="form-control border-0 flex-grow-1 px-2 input-fondo-transparente tarea-texto :is-checked-with-subraye"
                data-id=":id-unico" rows="1" placeholder="Editar tarea"
                style="overflow: hidden; resize: none; height: 40px;">:tarea</textarea>
            <button class="btn btn-icon btn-eliminar-tarea" data-id-list=":id-lista" data-id=":id-unico">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>`;

        let auxReturn = "";

        if (data.lista_tareas && data.lista_tareas.length > 0) {
            data.lista_tareas.forEach((item, index) => {
                const idUnico = `${baseTimestamp}_tarea_${index}_${Math.floor(Math.random() * 1000)}`;
                const tareaHtml = taskTemplate
                    .replace(/:id-unico/g, idUnico)
                    .replace(/:tarea/g, item.tarea || '')
                    .replace(/:id-nota/g, data.id_nota || '')
                    .replace(/:id-lista/g, item.id_lista_tarea || '')
                    .replace(/:checked/g, parseInt(item.list_check) === 0 ? "" : "checked")
                    .replace(/:is-checked-with-subraye/g, parseInt(item.list_check) === 0 ? "" : "text-decoration-line-through");

                auxReturn += tareaHtml;
            });
        }

        const checkboxDin = `
        <div>
            <div id="checkboxDinamicos">${auxReturn}</div>
            <div class="input-group mb-2">
                <div class="input-group-text bg-transparent border-0">
                    <input type="checkbox" id="check_init" data-id-nota="${data.id_nota}" readonly class="form-check-input mt-0 tarea-checkbox">
                </div>
                <textarea class="form-control border-0 flex-grow-1 input-fondo-transparente p-2"
                    id="inp_listar" data-id-nota="${data.id_nota}" name="inp_listar" placeholder="Nuevo Elemento"
                    rows="1" style="overflow:hidden; resize:none;"></textarea>
            </div>
            <div class="text-center mb-3">
                <span type="button" id="btnAgregarItem" class="text-primary" style="cursor:pointer;">
                    <i class="bi bi-plus"></i> Agregar
                </span>
            </div>
        </div>`;

        document.getElementById("tipo-contenido-edit").innerHTML = checkboxDin;
    }

    // Renderizar colaboradores (independientemente del tipo)
    const posiblesContenedores = [
        'conteiner-colaboradores',
        'container-colaboradores',
        'colaboradores-container',
        'colaboradores',
        'conteiner-colaboradores-edit'
    ];

    let colaboradoresContainer = null;
    for (let id of posiblesContenedores) {
        const cont = document.getElementById(id);
        if (cont) {
            colaboradoresContainer = cont;
            //console.log(`Contenedor encontrado: #${id}`);
            break;
        }
    }



    if (colaboradoresContainer) {
        if (auxcolaboradores.trim() !== "") {
            const finalHTML = `
            <div class="avatar-group my-2" data-id-nota="${data.id_nota || ''}">
                ${auxcolaboradores}
            </div>`;
            colaboradoresContainer.innerHTML = finalHTML;

            setTimeout(() => {
                const avatares = colaboradoresContainer.querySelectorAll('.avatar');
                //console.log(`Avatares insertados en el DOM: ${avatares.length}`);
            }, 100);
        } else {
            colaboradoresContainer.innerHTML = `
            <div class="text-muted small my-2">
                <i class="bi bi-person"></i> Sin colaboradores asignados
            </div>`;
        }
    } else {
        console.error('❌ No se encontró ningún contenedor válido para colaboradores.');
    }

    //console.log('✔️ Función render_edit_elements completada');

}



function agregarEventosColaboradores() {
    const select = document.getElementById("nuevo_colaborador");
    if (select) {
        select.addEventListener("change", function () {
            const idUsuario = this.value;
            const idNota = this.getAttribute("data-nota");

            if (idUsuario) {
                // Crear el spinner dinámicamente
                const spinner = document.createElement("div");
                spinner.id = "spinnerCarga";
                spinner.innerHTML = `
                <div class="spinner-border text-primary" role="status" style="position: fixed; top: 50%; left: 50%; z-index: 9999;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            `;
                document.body.appendChild(spinner); // Mostrar el spinner

                const formData = new FormData();
                formData.append("id_usuario", idUsuario);
                formData.append("id_nota", idNota);

                fetch("../../backend/controller/usuario/notas/getEditarColaborador.php", {
                    method: "POST",
                    body: formData
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "ok") {
                            cargarColaboradoresModal(idNota);
                        } else {
                            console.log("Error al agregar colaborador");
                        }
                    })
                    .catch(error => {
                        console.error("Error en la petición:", error);
                    })
                    .finally(() => {
                        // Eliminar el spinner al terminar
                        const spinnerExistente = document.getElementById("spinnerCarga");
                        if (spinnerExistente) {
                            spinnerExistente.remove();
                        }
                    });
            }
        });
    }

    /*  if (select) {
         select.addEventListener("change", function () {
             const idUsuario = this.value;
             const idNota = this.getAttribute("data-nota");
 
             if (idUsuario) {
                 const formData = new FormData();
                 formData.append("id_usuario", idUsuario);
                 formData.append("id_nota", idNota);
 
                 fetch("../../backend/controller/usuario/notas/getEditarColaborador.php", {
                     method: "POST",
                     body: formData
                 })
                     .then(res => res.json())
                     .then(res => {
                         if (res.status === "ok") {
                             // Recarga la lista y el select del modal completo
                             cargarColaboradoresModal(idNota);
                         } else {
                             console.log("Error al agregar colaborador");
                         }
                     });
             }
         });
     } */

    // Delegación de eventos para eliminar colaborador
    const lista = document.getElementById("lista-colaboradores");
    if (lista) {
        lista.addEventListener("click", function (e) {
            if (e.target.closest('.btn-eliminar-colaborador')) {
                const btn = e.target.closest('.btn-eliminar-colaborador');
                const idUsuario = btn.getAttribute("data-id");
                const idNota = btn.getAttribute("data-nota");
                eliminarColaborador(idUsuario, idNota);
            }
        });
    }
}

function eliminarColaborador(idUsuario, idNota) {
    const formData = new FormData();
    formData.append("id_usuario", idUsuario);
    formData.append("id_nota", idNota);

    fetch("../../backend/controller/usuario/notas/getEliminarColaborador.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(res => {
            if (res.status === "ok") {
                // Recarga el contenido del modal para actualizar lista y select
                cargarColaboradoresModal(idNota);
                mostrarCol(idNota)
            } else {
                console.error("Error al eliminar colaborador");
            }
        });
}

function cargarColaboradoresModal(idNota) {
    //console.log("Cargando colaboradores para nota:", idNota);
    fetch(`./ui/notas/cont_colaborador.php?id=${idNota}`)
        .then(res => res.text())
        .then(html => {
            const contenedor = document.getElementById("modal-content");
            if (contenedor) {
                contenedor.innerHTML = html;
               agregarEventosColaboradores();
                mostrarCol(idNota) 
                

            }
            
        });
}






function cargarContenidoNotas() {
    // ejemplo básico
    fetch('./ui/notas/cargarnota.php')
        .then(res => res.text())
        .then(html => {
            document.getElementById('contenedorNotas').innerHTML = html;
        });
}

function mostrarCol(idNota) {
    const formData = new FormData();
    formData.append("id_nota", idNota);

    fetch("../../backend/controller/usuario/notas/getmostrarColaborador.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("conteiner-colaboradores");
            const card = document.querySelector(`.masonry-item.este-es[data-id-nota="${idNota}"]`);

            if (!container) return;

            if (Array.isArray(data) && data.length > 0) {
                let html = "";

                data.forEach(usuario => {
                    const siglas = usuario.siglas;
                    const idUsuario = usuario.id;
                    const nombreCompleto = `${usuario.nombre} ${usuario.apellido}`;
                    const timestamp = Date.now();
                    const idUnico = `${timestamp}_${Math.floor(Math.random() * 1000)}`;

                    const colaboradorHtml = `
                    <span class="avatar avatar-sm avatar-primary-soft" style="cursor:pointer" 
                          data-id-unico="${idUnico}" data-id-usuario="${idUsuario}" 
                          title="${nombreCompleto}">
                        <span class="avatar-initials rounded-circle">${siglas}</span>  
                    </span>
                `;

                    html += colaboradorHtml;

                });

                container.innerHTML = html;

                if (card) {
                    // Busca dentro de la tarjeta el contenedor de colaboradores
                    const elementos = card.querySelector('.lista-colaboradores-nota');


                    elementos.innerHTML = html;
                }



            } else {
                container.innerHTML = `
                <div class="text-muted small my-2">
                    <i class="bi bi-person"></i> Sin colaboradores asignados
                </div>`;
                if (card) {
                    // Busca dentro de la tarjeta el contenedor de colaboradores
                    const elementos = card.querySelector('.lista-colaboradores-nota');


                    elementos.innerHTML = `
                <div class="text-muted small my-2">
                    <i class="bi bi-person"></i> Sin colaboradores asignados
                </div>`;
                }
            }
        })
        .catch(err => {
            console.error("Error al cargar colaboradores:", err);
        });
}



function eliminar() {
    const notas = document.querySelectorAll(".masonry-item");

    notas.forEach(item => {
        let longPressTimer;

        // 👉 Clic derecho en escritorio
        item.addEventListener("contextmenu", function (e) {
            e.preventDefault();
            mostrarMenuEliminar(e.pageX, e.pageY, item.dataset.idNota, item);
        });

        // 👉 Presión larga en móvil
        item.addEventListener("touchstart", function (e) {
            longPressTimer = setTimeout(() => {
                const touch = e.touches[0];
                mostrarMenuEliminar(touch.pageX, touch.pageY, item.dataset.idNota, item);
            }, 600); // 600ms = presión larga
        });

        item.addEventListener("touchend", () => clearTimeout(longPressTimer));
        item.addEventListener("touchmove", () => clearTimeout(longPressTimer));
    });

    // Ocultar menú si se hace clic fuera
    document.addEventListener("click", () => {
        const menu = document.getElementById("menu-eliminar");
        if (menu) menu.remove();
    });
}

function mostrarMenuEliminar(x, y, idNota, cardElemento) {
    // Eliminar menú anterior
    const viejo = document.getElementById("menu-eliminar");
    if (viejo) viejo.remove();

    // Crear contenedor de menú flotante
    const menu = document.createElement("div");
    menu.id = "menu-eliminar";
    menu.className = "position-absolute p-2 shadow rounded bg-white border";
    menu.style.top = y + "px";
    menu.style.left = x + "px";
    menu.style.zIndex = "9999";
    menu.style.minWidth = "150px";

    // Botón eliminar
    const btnEliminar = document.createElement("button");
    btnEliminar.className = "btn btn-sm text-danger w-100 mb-1";
    btnEliminar.innerHTML = '<i class="bi bi-trash "></i> Eliminar';
    btnEliminar.onclick = () => {
        eliminarNota(idNota);
        menu.remove();
    };

    // Botón pinear / despinear
    const btnPin = document.createElement("button");
    btnPin.className = "btn btn-sm text-primary w-100";
    const btnIcono = cardElemento.querySelector(".btnCambiarPin i");
    const estaPineada = btnIcono.classList.contains("bi-pin-angle-fill");

    btnPin.innerHTML = `
        <i class="bi ${estaPineada ? 'bi-pin-angle' : 'bi-pin-angle-fill'}"></i> 
        ${estaPineada ? 'Quitar fijación' : 'Fijar'}
    `;
    btnPin.onclick = () => {
        togglePineado(idNota, !estaPineada);
        menu.remove();
    };

    // Agregar botones al menú
    menu.appendChild(btnEliminar);
    menu.appendChild(btnPin);
    document.body.appendChild(menu);
}

// Función para alternar el pineado
function togglePineado(idNota, nuevoEstado) {
    const formData = new FormData();
    formData.append("id_nota", idNota);
    formData.append("pineada", nuevoEstado ? 1 : 0);
    const nota = document.querySelector(`.masonry-item[data-id-nota="${idNota}"]`);
    const contenedor = document.getElementById("contenedorNotas");

    fetch('../../backend/controller/usuario/notas/get_cambiarpin.php', {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                //console.log("✅ Nota " + (nuevoEstado ? "pineada" : "despineada"));
                if (nuevoEstado == true) {
                    contenedor.prepend(nota); // Mueve la nota al principio
                } else {
                    contenedor.appendChild(nota); // Mueve la nota al final
                }
                // Podés actualizar el ícono en pantalla si querés
                const card = document.querySelector(`.masonry-item[data-id-nota="${idNota}"]`);
                if (card) {
                    const icono = card.querySelector(".btnCambiarPin i");
                    if (icono) {
                        icono.classList.toggle("bi-pin-angle-fill", nuevoEstado);
                        icono.classList.toggle("bi-pin-angle", !nuevoEstado);
                    }
                }
            } else {
                console.error("❌ Error al actualizar pineado:", data.message);
            }
        })
        .catch(err => {
            console.error("❌ Error de red al pinear/despinear:", err);
        });
}


function eliminarNota(idNota) {
    const formData = new FormData();
    formData.append("id_nota", idNota);
    fetch('../../backend/controller/usuario/notas/get_eliminarNota.php', {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === "ok") {
                document.querySelector(`.masonry-item[data-id-nota="${idNota}"]`)?.remove();
                //console.log("✅ Nota eliminada correctamente");
            } else {
                //console.log("❌ Error al eliminar nota");
            }
        })
        .catch(err => {
            console.error("Error eliminando nota:", err);

        });

}


