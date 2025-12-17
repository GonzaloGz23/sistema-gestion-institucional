async function cargarNotas() {
    const resp = await fetch('../../backend/controller/usuario/notasnew/listarCard.php');
    const notas = await resp.json();
    const container = document.getElementById('contenedorNotas');
    container.innerHTML = '';

    // Ordenar inicialmente: pineadas primero
    notas.sort((a, b) => b.pineada - a.pineada || new Date(b.fecha_creacion) - new Date(a.fecha_creacion));

    notas.forEach(nota => {
        let contenidoCard = '';
        if (nota.tiponota === 'lista') {
            const tareasHtml = nota.lista_tareas.map(t =>
                `<div class="d-flex align-items-center mb-2 tarea-item" data-id="${t.id_lista_tarea}">
                <input type="checkbox" class="form-check-input me-2" ${t.list_check ? 'checked' : ''} disabled>
<p class="mb-0 flex-grow-1 input-fondo-transparente tarea-texto ${t.list_check ? 'tachado' : ''}">${t.tarea}</p>
             </div>`).join('');
            contenidoCard = `<div class="limite-lista">${tareasHtml}</div>`;
        } else {
            contenidoCard = `<p class="mb-0 flex-grow-1 input-fondo-transparente tarea-texto">${nota.nota}</p>`;
        }

        const colaboradoresHtml = nota.colaboradores.map(c =>
            `<span class="avatar avatar-sm avatar-primary-soft" title="${c.nombre} ${c.apellido}">
                <span class="avatar-initials rounded-circle">${c.iniciales}</span>
             </span>`).join('');

        const card = document.createElement('div');
        card.className = 'masonry-item este-es';
        card.dataset.idNota = nota.id_nota;
        card.dataset.pineada = nota.pineada;

        card.innerHTML = `
            <div class="card mb-2 border-top border-4 border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="mb-0 text-secondary fs-6"><small>${nota.fecha_creacion}</small></span>
                        <button type="button" class="btn btn-link p-0 ms-3 btnCambiarPin text-secondary"
                            data-id-nota="${nota.id_nota}" data-pineada="${nota.pineada}" title="Pin">
                            <i class="bi ${nota.pineada ? 'bi-pin-angle-fill' : 'bi-pin-angle'} fs-3 icon-pin"></i>
                        </button>
                    </div>
                    <p class="mb-0 flex-grow-1 input-fondo-transparente tarea-texto h4">${nota.titulo}</p>
                    <div class="limite-lista">${contenidoCard}</div>
                    <div class="d-flex align-items-center justify-content-between my-2">
                        <div class="avatar-group lista-colaboradores-nota limite-colaboradores">${colaboradoresHtml}</div>
                        <button type="button" class="btn btn-link p-0 ms-2 btnEliminarNota text-secondary" title="Eliminar">
                            <i class="bi bi-trash fs-5"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(card);

        // 🔹 Evento click del pin
        const btnPin = card.querySelector('.btnCambiarPin');
        btnPin.addEventListener('click', async (e) => {
            e.stopPropagation();
            let pineada = btnPin.dataset.pineada === '1' ? 0 : 1;
            const icon = btnPin.querySelector('i');

            icon.classList.toggle('bi-pin-angle-fill', pineada === 1);
            icon.classList.toggle('bi-pin-angle', pineada === 0);
            btnPin.dataset.pineada = pineada;
            card.dataset.pineada = pineada;

            try {
                const res = await fetch('../../backend/controller/usuario/notasnew/cambiarpin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_nota: nota.id_nota, pineada })
                });
                const data = await res.json();
                if (!data.success) console.error('Error al actualizar pin', data.error);
            } catch (err) {
                console.error('Error al actualizar pin', err);
            }

            reordenarNotas(container);
        });

        // 🔹 Eliminar nota
        const btnEliminar = card.querySelector('.btnEliminarNota');
        btnEliminar.addEventListener('click', async (e) => {
            e.stopPropagation();
            try {
                const res = await fetch('../../backend/controller/usuario/notasnew/eliminarnota.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_nota: nota.id_nota })
                });
                const data = await res.json();
                if (data.success) card.remove();
                else console.error('Error al eliminar', data.error || 'Desconocido');
            } catch (err) {
                console.error('Error al eliminar', err);
            }
        });

        // 🔹 Abrir modal
        card.addEventListener('click', async (e) => {
            if (e.target.closest('.btnCambiarPin') || e.target.closest('.btnEliminarNota')) return;

            const modalTitulo = document.getElementById('modalTitulo');
            const modalContenido = document.getElementById('modalContenido');
            const modalListaTareas = document.getElementById('modalListaTareas');
            const modalColaboradores = document.getElementById('contcolaborador');

            modalTitulo.innerHTML = `<textarea id="inpTituloNota" class="form-control border-0 flex-grow-1 input-fondo-transparente p-2 tarea-texto" rows="1" style="resize:none;">${nota.titulo}</textarea>`;

            inpTituloNota.addEventListener('blur', async () => {
                const nuevoTitulo = inpTituloNota.value.trim();
                if (nuevoTitulo && nuevoTitulo !== nota.titulo) {
                    try {
                        const res = await fetch('../../backend/controller/usuario/notasnew/editarnota.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_nota: nota.id_nota, titulo: nuevoTitulo })
                        });
                        const data = await res.json();
                        if (data.success) {
                            nota.titulo = nuevoTitulo;
                            const card = document.querySelector(`.masonry-item[data-id-nota="${nota.id_nota}"]`);
                            if (card) card.querySelector('.tarea-texto.h4').textContent = nuevoTitulo;
                        } else {
                            console.error('Error al editar título', data.message || 'Desconocido');
                        }
                    } catch (err) {
                        console.error('Error al editar título', err);
                    }
                }
            });

            inpTituloNota.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    inpTituloNota.blur();
                }
            });

            // 🔹 Cargar colaboradores actualizados antes de mostrar el modal
            try {
                const res = await fetch('../../backend/controller/usuario/notasnew/obtenercolaboradores.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_nota: nota.id_nota })
                });
                const data = await res.json();
                if (data.success && Array.isArray(data.colaboradores)) {
                    nota.colaboradores = data.colaboradores;
                } else {
                    console.warn('No se pudieron cargar los colaboradores actualizados:', data.message);
                }
            } catch (err) {
                console.error('Error al actualizar lista de colaboradores', err);
            }

            // 🔸 Renderizar los colaboradores (sin modificar tu estructura original)
            modalColaboradores.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div id="conteiner-colaboradores" class="avatar-group my-2" data-id-nota="${nota.id_nota}">
                ${nota.colaboradores.map((c, index) => `
                    <span class="avatar avatar-sm avatar-primary-soft" style="cursor:pointer" 
                        data-id-unico="${Date.now()}_${index}_${c.id_empleado}" 
                        data-id-usuario="${c.id_empleado}" 
                        title="${c.nombre}, ${c.apellido}">
                        <span class="avatar-initials rounded-circle">${c.iniciales}</span>  
                    </span>
                `).join('')}
            </div>
            <span class="ms-2">
                <i id="btnAgregarColaborador" class="bi bi-person-add col-edit-change fs-3" style="cursor:pointer;" data-id-nota="${nota.id_nota}"></i>
            </span>
        </div>
    `;
            const btnAgregarColaborador = document.getElementById('btnAgregarColaborador');

            if (btnAgregarColaborador) {
                btnAgregarColaborador.addEventListener('click', async () => {
                    const modalColabEl = document.getElementById('modalcolaborador');
                    if (!modalColabEl) return;

                    modalColabEl.style.zIndex = 2000;
                    const modalColab = new bootstrap.Modal(modalColabEl);
                    modalColab.show();

                    const listaColaboradores = document.getElementById('lista-colaboradores');
                    const contenedorSelect = document.getElementById('contenedor-select-colaborador');

                    listaColaboradores.innerHTML = '';
                    contenedorSelect.innerHTML = '';

                    const selectColab = document.createElement('select');
                    selectColab.id = 'nuevo_colaborador';
                    selectColab.className = 'form-select mb-3';
                    selectColab.dataset.nota = nota.id_nota;
                    selectColab.innerHTML = `<option value="">Seleccionar colaborador</option>`;
                    contenedorSelect.appendChild(selectColab);

                    let empleadosDisponibles = [];

                    try {
                        const res = await fetch('../../backend/controller/usuario/notasnew/listarempleados.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_nota: nota.id_nota })
                        });

                        const data = await res.json();

                        if (data.success) {
                            empleadosDisponibles = data.empleados.filter(emp =>
                                !nota.colaboradores.some(c => c.id_empleado == emp.id_empleado)
                            );

                            empleadosDisponibles.forEach(emp => {
                                const opt = document.createElement('option');
                                opt.value = emp.id_empleado;
                                opt.textContent = `${emp.nombre} ${emp.apellido}`;
                                selectColab.appendChild(opt);
                            });
                        } else {
                            console.error('Error al traer empleados:', data);
                        }
                    } catch (err) {
                        console.error('Error al cargar colaboradores disponibles', err);
                    }

                    const renderColaboradores = () => {
                        listaColaboradores.innerHTML = '';
                        nota.colaboradores.forEach(c => {
                            const item = document.createElement('div');
                            item.className = 'list-group-item d-flex justify-content-between align-items-center';
                            item.dataset.id = c.id_empleado;
                            item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="avatar avatar-sm rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                  style="width: 36px; height: 36px;">
                                ${c.iniciales}
                            </span>
                        </div>
                        <div>
                            <small>${c.nombre} ${c.apellido}</small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger btn-eliminar-colaborador" 
                            data-id="${c.id_empleado}" 
                            data-nota="${nota.id_nota}">
                        <i class="bi bi-x-lg"></i>
                    </button>
                `;

                            // 🔸 Evento de eliminar dinámico
                            const btnEliminar = item.querySelector('.btn-eliminar-colaborador');
                            btnEliminar.onclick = async () => {
                                const idColab = btnEliminar.dataset.id;
                                try {
                                    const res = await fetch('../../backend/controller/usuario/notasnew/eliminarcolaborador.php', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ id_nota: nota.id_nota, id_colaborador: idColab })
                                    });

                                    const data = await res.json();
                                    if (data.success) {
                                        // Eliminar del array local
                                        nota.colaboradores = nota.colaboradores.filter(c => c.id_empleado != idColab);
                                        item.remove();

                                        // 🔥 Volver a agregar al select
                                        const empEliminado = empleadosDisponibles.find(e => e.id_empleado == idColab);
                                        if (empEliminado) {
                                            const opt = document.createElement('option');
                                            opt.value = empEliminado.id_empleado;
                                            opt.textContent = `${empEliminado.nombre} ${empEliminado.apellido}`;
                                            selectColab.appendChild(opt);
                                        }

                                        // 🔄 ACTUALIZAR CONTENEDOR DE MODAL PRINCIPAL
                                        const contenedorModalPrincipal = document.getElementById('conteiner-colaboradores');
                                        if (contenedorModalPrincipal) {
                                            contenedorModalPrincipal.innerHTML = nota.colaboradores.map((c, index) => `
                                    <span class="avatar avatar-sm avatar-primary-soft" style="cursor:pointer" 
                                        data-id-unico="${Date.now()}_${index}_${c.id_empleado}" 
                                        data-id-usuario="${c.id_empleado}" 
                                        title="${c.nombre}, ${c.apellido}">
                                        <span class="avatar-initials rounded-circle">${c.iniciales}</span>  
                                    </span>
                                `).join('');
                                        }
                                    }
                                } catch (err) {
                                    console.error('Error al eliminar colaborador', err);
                                }
                            };

                            listaColaboradores.appendChild(item);
                        });
                    };

                    renderColaboradores();

                    // --------------------------
                    // Agregar colaborador
                    // --------------------------
                    selectColab.onchange = async () => {
                        const idColab = selectColab.value;
                        if (!idColab) return;

                        try {
                            const formData = new FormData();
                            formData.append('id_nota', nota.id_nota);
                            formData.append('id_usuario', idColab);

                            const res = await fetch('../../backend/controller/usuario/notasnew/agregarcolaborador.php', {
                                method: 'POST',
                                body: formData
                            });

                            const data = await res.json();

                            if (data.success === 'true') {
                                // Evitar duplicados
                                if (!nota.colaboradores.some(c => c.id_empleado == data.id)) {
                                    nota.colaboradores.unshift({
                                        id_empleado: data.id,
                                        nombre: data.nombre,
                                        apellido: data.apellido,
                                        iniciales: data.siglas
                                    });
                                }

                                // 🔥 Quitar del select el colaborador recién agregado
                                selectColab.querySelector(`option[value="${data.id}"]`)?.remove();

                                renderColaboradores();
                                selectColab.value = '';

                                // 🔄 ACTUALIZAR CONTENEDOR DE MODAL PRINCIPAL
                                const contenedorModalPrincipal = document.getElementById('conteiner-colaboradores');
                                if (contenedorModalPrincipal) {
                                    contenedorModalPrincipal.innerHTML = nota.colaboradores.map((c, index) => `
                            <span class="avatar avatar-sm avatar-primary-soft" style="cursor:pointer" 
                                data-id-unico="${Date.now()}_${index}_${c.id_empleado}" 
                                data-id-usuario="${c.id_empleado}" 
                                title="${c.nombre}, ${c.apellido}">
                                <span class="avatar-initials rounded-circle">${c.iniciales}</span>  
                            </span>
                        `).join('');
                                }
                            } else {
                                console.error(data.message || 'Error al agregar colaborador');
                            }
                        } catch (err) {
                            console.error('Error al agregar colaborador', err);
                        }
                    };
                });
            }




            if (nota.tiponota === 'lista') {
                modalListaTareas.innerHTML = nota.lista_tareas.map((t, index) =>
                    `<div class="input-group mb-2 tarea-item" data-id="${t.id_lista_tarea}">
                        <div class="input-group-text bg-transparent border-0 handle" style="cursor:grab;">
                            <i class="bi bi-grip-vertical fs-3"></i>
                        </div>
                        <div class="input-group-text bg-transparent border-0">
                            <input type="checkbox" class="form-check-input mt-0 tarea-checkbox" ${t.list_check ? 'checked' : ''}>
                        </div>
                        <textarea class="form-control border-0 flex-grow-1 input-fondo-transparente p-2 tarea-texto"
                            rows="1" style="overflow:hidden; resize:none;">${t.tarea}</textarea>
                        <button class="btn btn-icon btn-eliminar-tarea"><i class="bi bi-x-lg"></i></button>
                    </div>`).join('');

                // Input para nueva tarea
                modalListaTareas.insertAdjacentHTML('beforeend', `
                    <div class="input-group mb-2">
                        <div class="input-group-text bg-transparent border-0">
                            <input type="checkbox" id="check_init" readonly class="form-check-input mt-0 tarea-checkbox">
                        </div>
                        <textarea class="form-control border-0 flex-grow-1 input-fondo-transparente p-2"
                            id="inp_listar"
                            name="inp_listar"
                            placeholder="Nuevo Elemento"
                            rows="1"
                            style="overflow:hidden; resize:none;"></textarea>
                    </div>
                    <div class="text-center mb-3">
                        <span type="button" id="btnAgregarItem" class="text-primary" style="cursor:pointer;">
                            <i class="bi bi-plus"></i> Agregar
                        </span>
                    </div>
                `);

                modalContenido.style.display = 'none';
                modalListaTareas.style.display = 'block';

                const actualizarOrden = () => {
                    const items = [...modalListaTareas.querySelectorAll('.tarea-item')];
                    items.forEach((item, i) => {
                        const spanOrden = item.querySelector('.orden-num');
                        if (spanOrden) spanOrden.textContent = i + 1;
                    });
                };

                // Función para agregar tarea
                const agregarTarea = async () => {
                    const inputNueva = document.getElementById('inp_listar');
                    const texto = inputNueva.value.trim();
                    const chequear = (check_init.checked == true) ? 1 : 0;

                    console.log(chequear);


                    const ordenNueva = 1;
                    console.log(actualizarOrden());

                    if (!texto) return;

                    try {
                        const res = await fetch('../../backend/controller/usuario/notasnew/agregarlistas.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_nota: nota.id_nota, tarea: texto, check: chequear, ordenlis: ordenNueva })
                        });
                        const data = await res.json();
                        if (data.success && data.id_lista_tarea) {
                            const idTarea = data.id_lista_tarea;
                            const div = document.createElement('div');
                            div.className = 'input-group mb-2 tarea-item';
                            div.dataset.id = idTarea;

                            div.innerHTML = `
                                <div class="input-group-text bg-transparent border-0 handle" style="cursor:grab;">
                                    <i class="bi bi-grip-vertical fs-3"></i>
                                </div>
                                <div class="input-group-text bg-transparent border-0">
    <input type="checkbox" class="form-check-input mt-0 tarea-checkbox" ${chequear ? 'checked' : ''}>
</div>
<textarea class="form-control border-0 flex-grow-1 input-fondo-transparente p-2 tarea-texto ${chequear ? 'tachado' : ''}"
    rows="1" style="overflow:hidden; resize:none;">${texto}</textarea>
                                <button class="btn btn-icon btn-eliminar-tarea"><i class="bi bi-x-lg"></i></button>
                            `;
                            const contenedorInput = document.getElementById('inp_listar').closest('.input-group');
                            modalListaTareas.insertBefore(div, contenedorInput);
                            inputNueva.value = '';

                            // Eventos de la nueva tarea
                            configurarEventosTarea(div);
                        }
                    } catch (err) {
                        console.error('Error al agregar tarea:', err);
                    }
                };

                document.getElementById('btnAgregarItem').onclick = agregarTarea;
                document.getElementById('inp_listar').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        agregarTarea();
                    }
                });

                // Configurar eventos de las tareas existentes

                modalListaTareas.querySelectorAll('.tarea-item').forEach(div => {
                    configurarEventosTarea(div);
                });

                function configurarEventosTarea(div) {
                    const idTarea = div.dataset.id;
                    const textarea = div.querySelector('.tarea-texto');
                    const checkbox = div.querySelector('.tarea-checkbox');
                    const btnEliminar = div.querySelector('.btn-eliminar-tarea');

                    // ✅ Si la tarea ya está marcada al crearla, aplicar tachado visual
                    if (checkbox.checked) {
                        textarea.classList.add('tachado');
                    }

                    // Editar texto
                    textarea.addEventListener('blur', async () => {
                        const nuevoTexto = textarea.value.trim();
                        try {
                            const res = await fetch('../../backend/controller/usuario/notasnew/editarlista.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_lista_tarea: idTarea, tarea: nuevoTexto })
                            });
                            const data = await res.json();
                            if (data.success) {
                                // Actualizar en card principal
                                const card = document.querySelector(`.masonry-item[data-id-nota="${nota.id_nota}"]`);
                                if (card) {
                                    const tareaCard = card.querySelector(`.tarea-item[data-id="${idTarea}"] .tarea-texto`);
                                    if (tareaCard) tareaCard.textContent = nuevoTexto;
                                }
                            } else {
                                console.error('Error al actualizar tarea', data.error || 'Desconocido');
                            }
                        } catch (err) {
                            console.error(err);
                        }
                    });

                    // Checkbox
                    checkbox.addEventListener('change', async () => {
                        //const checked = checkbox.checked ? 1 : 0;
                        const checked = checkbox.checked ? 1 : 0;
                        console.log(checked);

                        // 🔹 Aplica o quita tachado en el modal
                        if (checked) {
                            textarea.classList.add('tachado');
                        } else {
                            textarea.classList.remove('tachado');
                        }

                        // 🔹 Aplica o quita tachado también en la card principal
                        const card = document.querySelector(`.masonry-item[data-id-nota="${nota.id_nota}"]`);
                        if (card) {
                            const tareaCard = card.querySelector(`.tarea-item[data-id="${idTarea}"] .tarea-texto`);
                            if (tareaCard) {
                                if (checked) {
                                    tareaCard.classList.add('tachado');
                                } else {
                                    tareaCard.classList.remove('tachado');
                                }
                            }
                        }

                        // 🔹 Guarda el cambio en la base de datos
                        //console.log(chequear);

                        try {
                            const res = await fetch('../../backend/controller/usuario/notasnew/editarlista.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    id_lista_tarea: idTarea,
                                    list_check: checked
                                })
                            });
                            const data = await res.json();
                            if (!data.success) console.error('Error al actualizar check', data.error || 'Desconocido');
                        } catch (err) {
                            console.error(err);
                        }
                    });

                    // Eliminar
                    btnEliminar.addEventListener('click', async () => {
                        try {
                            const res = await fetch('../../backend/controller/usuario/notasnew/eliminarlista.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_lista_tarea: idTarea })
                            });
                            const data = await res.json();
                            if (data.success) div.remove();
                        } catch (err) {
                            console.error(err);
                        }
                    });
                }


                // Sortable
                Sortable.create(modalListaTareas, {
                    animation: 150,
                    handle: '.handle',
                    onEnd: function () {
                        actualizarOrden();
                        const ordenTareas = [...modalListaTareas.querySelectorAll('.tarea-item')].map((tarea, index) => ({
                            id: tarea.getAttribute('data-id'),
                            orden: index + 1
                        }));
                        fetch('../../backend/controller/usuario/notasnew/ordenlista.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ tareas: ordenTareas })
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (!data.success) console.error('Error al guardar orden:', data.message || 'Desconocido');
                            })
                            .catch(err => console.error(err));
                    }
                });

                actualizarOrden();

            } else {
                /*  modalContenido.textContent = nota.nota;
                 modalContenido.style.display = 'block';
                 modalListaTareas.style.display = 'none'; */
                modalContenido.innerHTML = `
        <textarea id="inpContenidoNota" class="form-control border-0 flex-grow-1 input-fondo-transparente p-2 tarea-texto"
            rows="5" style="resize:none;">${nota.nota}</textarea>
    `;
                modalContenido.style.display = 'block';
                modalListaTareas.style.display = 'none';


                const inpContenidoNota = document.getElementById('inpContenidoNota');
                if (inpContenidoNota) {
                    inpContenidoNota.addEventListener('blur', async () => {



                        const nuevoContenido = inpContenidoNota.value.trim();
                        if (nuevoContenido && nuevoContenido !== nota.nota) {
                            try {
                                const res = await fetch('../../backend/controller/usuario/notasnew/editarnota.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ id_nota: nota.id_nota, nota: nuevoContenido })
                                });
                                const data = await res.json();
                                if (data.success) {
                                    nota.nota = nuevoContenido;
                                    // Actualiza card principal

                                    const card = document.querySelector(`.masonry-item[data-id-nota="${nota.id_nota}"]`);
                                    if (card) {
                                        // Solo para notas de contenido (no listas)
                                        card.querySelector('.limite-lista').innerHTML = `<p class="mb-0 flex-grow-1 input-fondo-transparente tarea-texto">${nuevoContenido}</p>`;
                                    }
                                }
                            } catch (err) {
                                console.error('Error al actualizar nota:', err);
                            }
                        }
                    });
                }

            }

            new bootstrap.Modal(document.getElementById('modalNota')).show();
        });

        // 🔹 Buscador
        const buscador = document.getElementById('buscador');
        buscador.addEventListener('input', () => {
            const filtro = buscador.value.toLowerCase();
            const notas = document.querySelectorAll('#contenedorNotas .masonry-item');

            notas.forEach(card => {
                const titulo = card.querySelector('.tarea-texto.h4')?.textContent.toLowerCase() || '';
                const tareas = Array.from(card.querySelectorAll('.tarea-item p')).map(t => t.textContent.toLowerCase()).join(' ');
                card.style.display = (titulo.includes(filtro) || tareas.includes(filtro)) ? 'inline-block' : 'none';
            });
        });
    });
  
}

//agregar
const modalNota = document.getElementById('modalNota');
if (modalNota) {
    modalNota.addEventListener('hidden.bs.modal', () => {
        cargarNotas();
    });
}

// 🔄 Reordenar cards
function reordenarNotas(container) {
    const notas = Array.from(container.querySelectorAll('.masonry-item'));
    const pineadas = notas.filter(n => n.dataset.pineada === '1');
    const noPineadas = notas.filter(n => n.dataset.pineada !== '1');

    noPineadas.sort((a, b) => {
        const fechaA = new Date(a.querySelector('.fs-6 small').textContent);
        const fechaB = new Date(b.querySelector('.fs-6 small').textContent);
        return fechaB - fechaA;
    });

    container.innerHTML = '';
    pineadas.forEach(n => container.appendChild(n));
    noPineadas.forEach(n => container.appendChild(n));
}

document.addEventListener('DOMContentLoaded', cargarNotas);


// Abrir modal para crear nueva nota
document.querySelectorAll('.cargar-contenido').forEach(btn => {
    btn.addEventListener('click', () => {
        const tipo = btn.dataset.tipo; // 'texto' o 'lista'

        // Cerrar el modal flotante primero
        const modalFlotante = bootstrap.Modal.getInstance(document.getElementById('modalFlotante'));
        if (modalFlotante) {
            modalFlotante.hide();
        }

        // Esperar a que se cierre antes de abrir el nuevo modal
        setTimeout(() => {
            abrirModalCrearNota(tipo);
        }, 300);
    });
});

function abrirModalCrearNota(tipo) {
    const modal = document.getElementById('modalCrearNota');
    const body = modal.querySelector('.modal-body');
    body.innerHTML = ''; // limpiar modal

    // Título
    const textareaTitulo = document.createElement('textarea');
    textareaTitulo.id = 'nuevoTituloNota';
    textareaTitulo.className = 'form-control border-0 flex-grow-1 input-fondo-transparente p-2 tarea-texto';
    textareaTitulo.rows = 1;
    textareaTitulo.style.height = '38px';
    textareaTitulo.placeholder = 'Título';
    body.appendChild(textareaTitulo);

    if (tipo === 'texto') {
        const textareaContenido = document.createElement('textarea');
        textareaContenido.id = 'nuevoContenidoNota';
        textareaContenido.className = 'form-control border-0 flex-grow-1 input-fondo-transparente p-2 tarea-texto mt-3';
        textareaContenido.rows = 5;
        textareaContenido.style.height = '128px';
        textareaContenido.placeholder = 'Contenido';
        body.appendChild(textareaContenido);
    } else {
        // Lista de tareas
        const listaContainer = document.createElement('div');
        listaContainer.id = 'nuevaListaTareasContainer';
        listaContainer.className = 'mb-3';
        body.appendChild(listaContainer);

        const divInput = document.createElement('div');
        divInput.className = 'input-group mb-2';
        divInput.innerHTML = `
            <div class="input-group-text bg-transparent border-0">
                <input type="checkbox" id="check_init_nueva" class="form-check-input mt-0 tarea-checkbox">
            </div>
            <textarea class="form-control border-0 flex-grow-1 input-fondo-transparente p-2"
                id="inpNuevaTarea" placeholder="Nuevo Elemento" rows="1" style="overflow: hidden; resize: none; height: 38px;"></textarea>`;
        body.appendChild(divInput);

        const agregarSpan = document.createElement('div');
        agregarSpan.className = 'text-center mb-3';
        agregarSpan.innerHTML = `<span type="button" id="btnAgregarNuevaTarea" class="text-primary" style="cursor:pointer;">
            <i class="bi bi-plus"></i> Agregar
        </span>`;
        body.appendChild(agregarSpan);

        // Sortable
        Sortable.create(listaContainer, { animation: 150, handle: '.handle' });

        const agregarTarea = () => {
            const valor = document.getElementById('inpNuevaTarea').value.trim();
            const chequear = document.getElementById('check_init_nueva').checked ? 1 : 0;
            if (!valor) return;

            const div = document.createElement('div');
            div.className = 'input-group mb-2 tarea-item';
            div.dataset.check = chequear;
            div.innerHTML = `
                <div class="input-group-text bg-transparent border-0 handle" style="cursor:grab;">
                    <i class="bi bi-grip-vertical fs-3"></i>
                </div>
                <div class="input-group-text bg-transparent border-0">
                    <input type="checkbox" class="form-check-input mt-0 tarea-checkbox" ${chequear ? 'checked' : ''}>
                </div>
                <textarea class="form-control border-0 flex-grow-1 input-fondo-transparente p-2 tarea-texto"
                    rows="1" style="overflow: hidden; resize: none; height: 48px;">${valor}</textarea>
                <button class="btn btn-icon btn-eliminar-tarea"><i class="bi bi-x-lg"></i></button>`;
            listaContainer.appendChild(div);

            div.querySelector('.btn-eliminar-tarea').onclick = () => div.remove();
            document.getElementById('inpNuevaTarea').value = '';
            document.getElementById('check_init_nueva').checked = false;
        };

        document.getElementById('btnAgregarNuevaTarea').onclick = agregarTarea;
        document.getElementById('inpNuevaTarea').addEventListener('keypress', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                agregarTarea();
            }
        });
    }

    // Guardar nota al cerrar modal
    modal.addEventListener('hidden.bs.modal', async () => {
        const titulo = textareaTitulo.value.trim();
        if (!titulo) return;

        const formData = new FormData();
        formData.append('titulo', titulo);
        formData.append('tipo', tipo);
        formData.append('pineado', 0);

        if (tipo === 'texto') {
            const contenido = document.getElementById('nuevoContenidoNota').value.trim();
            formData.append('texto', contenido);
        } else {
            const tareas = [...document.querySelectorAll('#nuevaListaTareasContainer .tarea-item')].map((t, i) => ({
                texto: t.querySelector('textarea').value,
                check: t.querySelector('input[type=checkbox]').checked ? 1 : 0,
                orden: i + 1
            }));
            formData.append('tareas', JSON.stringify(tareas));
        }

        try {
            const res = await fetch('../../backend/controller/usuario/notasnew/crearnota.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                
    cargarNotas(); // dejar tu función tal cual
            }
        } catch (err) {
            console.error(err);
        }
    }, { once: true });


    // 🔹 Agregado: tachado instantáneo al marcar tareas nuevas o ya checkeadas
    function aplicarTachadoInicial() {
        // Al cargar o agregar tareas nuevas, si alguna está marcada, aplicar tachado
        document.querySelectorAll('#nuevaListaTareasContainer .tarea-item').forEach((item) => {
            const checkbox = item.querySelector('input[type=checkbox]');
            const textarea = item.querySelector('textarea');
            if (checkbox && textarea) {
                if (checkbox.checked) {
                    textarea.classList.add('tachado');
                } else {
                    textarea.classList.remove('tachado');
                }
            }
        });
    }

    // Se ejecuta cuando se cambia el estado de un checkbox
    document.addEventListener('change', (e) => {
        if (e.target.matches('#nuevaListaTareasContainer input[type=checkbox]')) {
            const checkbox = e.target;
            const textarea = checkbox.closest('.tarea-item').querySelector('textarea');

            if (checkbox.checked) {
                textarea.classList.add('tachado');
            } else {
                textarea.classList.remove('tachado');
            }
        }
    });

    // Observa cambios en el contenedor de tareas (por si se agregan dinámicamente)
    const contenedorTareas = document.getElementById('nuevaListaTareasContainer');
    if (contenedorTareas) {
        const observer = new MutationObserver(() => aplicarTachadoInicial());
        observer.observe(contenedorTareas, { childList: true, subtree: true });
        aplicarTachadoInicial(); // aplicar también al inicio
    }




    new bootstrap.Modal(modal).show();
}

