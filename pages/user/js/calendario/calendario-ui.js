document.addEventListener('DOMContentLoaded', () => {
    $('#editEmpleadosSeleccionados').select2({
        placeholder: 'Seleccionar empleados',
        width: '100%',
        dropdownParent: $('#ModalEdit')
    });

    $('#editEquiposSeleccionados').select2({
        placeholder: 'Seleccionar equipos',
        width: '100%',
        dropdownParent: $('#ModalEdit')
    });
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        timeZone: 'local',
        initialView: 'dayGridMonth',
        contentHeight: 600, // controla el tamaño total del calendario
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        events: obtenerEventos(),
        displayEventTime: false, // Oculta la hora
        eventDisplay: 'block',
        // Renderizar eventos como <div>, no <a>
        eventContent: (arg) => {
            const inner = document.createElement('div');
            inner.className = 'fc-custom-event';
            inner.innerHTML = `<span>${arg.event.title}</span>`;
            return { domNodes: [inner] };
        },
        // Tooltip
        eventDidMount: (info) => {
            if (info.event.extendedProps.descripcion || info.event.extendedProps.tipo_evento) {
                const tooltip = `
                <strong>${info.event.title}</strong><br>
                <em>${info.event.extendedProps.tipo_evento}</em><br>
                ${info.event.extendedProps.descripcion}
              `;
                tippy(info.el, {
                    content: tooltip,
                    allowHTML: true,
                    theme: 'light-border',
                });
            }
        },
        // Clic en día → modal o contenedor, según pantalla
        dateClick: (info) => {
            const fechaUTC = new Date(info.date);
            const fechaLocal = new Date(fechaUTC.getTime() + (fechaUTC.getTimezoneOffset() * 60000));
            const fechaStr = fechaLocal.toISOString().split('T')[0];

            if (window.innerWidth >= 992) {
                cargarEventosPorFecha(fechaStr, 'listaModalEventosDia', true);
            } else {
                cargarEventosPorFecha(fechaStr, 'listaEventosDia');
            }
        },
    });

    calendar.render();

    // Colores
    const colores = [
        "#ea4335", "#e34f2d", "#e91e63", "#f29900",
        "#fbbc04", "#34a853", "#00bcd4", "#4285f4",
        "#3f51b5", "#9c27b0", "#9e9e9e"
    ];

    const colorActualBtn = document.getElementById('colorActual');
    const inputColor = document.getElementById('color');
    const paletaColores = document.getElementById('paletaColores');

    if (colorActualBtn && inputColor && paletaColores) {
        // Mostrar/ocultar paleta al hacer clic
        colorActualBtn.addEventListener('click', () => {
            paletaColores.style.display = paletaColores.style.display === 'none' ? 'grid' : 'none';
        });

        // Generar botones
        colores.forEach(color => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.style.backgroundColor = color;
            btn.dataset.color = color;

            if (color === inputColor.value) {
                btn.classList.add('selected');
            }

            btn.addEventListener('click', () => {
                inputColor.value = color;
                colorActualBtn.style.backgroundColor = color;

                document.querySelectorAll('#paletaColores button').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');

                paletaColores.style.display = 'none';
            });

            paletaColores.appendChild(btn);
        });

        // Ocultar al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!document.querySelector('.color-selector').contains(e.target)) {
                paletaColores.style.display = 'none';
            }
        });
    }

    const editColorActualBtn = document.getElementById('editColorActual');
    const editInputColor = document.getElementById('editColor');
    const editPaletaColores = document.getElementById('editPaletaColores');

    if (editColorActualBtn && editInputColor && editPaletaColores) {
        // Mostrar/ocultar paleta al hacer clic
        editColorActualBtn.addEventListener('click', () => {
            editPaletaColores.style.display = editPaletaColores.style.display === 'none' ? 'grid' : 'none';
        });

        // Generar botones
        colores.forEach(color => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.style.backgroundColor = color;
            btn.dataset.color = color;

            btn.addEventListener('click', () => {
                editInputColor.value = color;
                editColorActualBtn.style.backgroundColor = color;

                document.querySelectorAll('#editPaletaColores button').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');

                editPaletaColores.style.display = 'none';
            });

            editPaletaColores.appendChild(btn);
        });

        // Ocultar si clickea afuera
        document.addEventListener('click', (e) => {
            if (!document.querySelector('.edit-color-selector')?.contains(e.target)) {
                editPaletaColores.style.display = 'none';
            }
        });
    }

    // Checkbox "Todo el día"
    const checkTodoDia = document.getElementById('checkTodoDia');
    const inputDesde = document.getElementById('fechaHoraInicio');
    const inputHasta = document.getElementById('fechaHoraFin');

    if (checkTodoDia && inputDesde && inputHasta) {
        checkTodoDia.addEventListener('change', () => {
            if (checkTodoDia.checked) {
                const fechaSeleccionada = inputDesde.value || new Date().toISOString().slice(0, 10);
                const fecha = fechaSeleccionada.split('T')[0]; // Solo la parte de fecha

                inputDesde.value = `${fecha}T00:00`;
                inputHasta.value = `${fecha}T23:59`;

                inputDesde.disabled = true;
                inputHasta.disabled = true;
            } else {
                inputDesde.disabled = false;
                inputHasta.disabled = false;
            }
        });

        // Si el checkbox estaba marcado al abrir el modal, aplicar el comportamiento por defecto
        if (checkTodoDia.checked) {
            checkTodoDia.dispatchEvent(new Event('change'));
        }
    }

    // Checkbox "Todo el día" en Modal de Edición
    const editCheckTodoDia = document.getElementById('editCheckTodoDia');
    const editInputDesde = document.getElementById('editFechaHoraInicio');
    const editInputHasta = document.getElementById('editFechaHoraFin');

    if (editCheckTodoDia && editInputDesde && editInputHasta) {
        editCheckTodoDia.addEventListener('change', () => {
            if (editCheckTodoDia.checked) {
                const fechaSeleccionada = editInputDesde.value ? editInputDesde.value.split('T')[0] : new Date().toISOString().slice(0, 10);
                editInputDesde.value = `${fechaSeleccionada}T00:00`;
                editInputHasta.value = `${fechaSeleccionada}T23:59`;

                editInputDesde.disabled = true;
                editInputHasta.disabled = true;
            } else {
                editInputDesde.disabled = false;
                editInputHasta.disabled = false;
            }
        });

        if (editCheckTodoDia.checked) {
            editCheckTodoDia.dispatchEvent(new Event('change'));
        }
    }


    // Validación al enviar formulario de nuevo evento
    const formNuevo = document.querySelector('#ModalAdd form');
    const tipoEventoSelect = document.getElementById('tipo-evento');
    const opcionesPersonalizado = document.getElementById('personalizadoOpciones');
    const selectEmpleados = document.getElementById('selectEmpleados');
    const selectEquipos = document.getElementById('selectEquipos');

    tipoEventoSelect?.addEventListener('change', () => {
        if (tipoEventoSelect.value === 'Personalizado') {
            opcionesPersonalizado.classList.remove('d-none');
        } else {
            opcionesPersonalizado.classList.add('d-none');
            selectEmpleados.classList.add('d-none');
            selectEquipos.classList.add('d-none');
        }
    });

    // Manejar selección empleados/equipos
    document.getElementById('personalizadoEmpleados')?.addEventListener('change', async () => {
        selectEmpleados.classList.remove('d-none');
        selectEquipos.classList.add('d-none');

        const empleados = await cargarEmpleados();
        const select = document.getElementById('empleadosSeleccionados');

        select.innerHTML = ''; // Limpiar antes de cargar

        if (empleados.length) {
            empleados.forEach(empleado => {
                const option = document.createElement('option');
                option.value = empleado.id_empleado;
                option.textContent = empleado.nombre;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option disabled>No se encontraron empleados.</option>';
        }
    });

    document.getElementById('personalizadoEquipos')?.addEventListener('change', async () => {
        selectEquipos.classList.remove('d-none');
        selectEmpleados.classList.add('d-none');

        const equipos = await cargarEquipos();
        const select = document.getElementById('equiposSeleccionados');

        select.innerHTML = ''; // Limpiar antes de cargar

        if (equipos.length) {
            equipos.forEach(equipo => {
                const option = document.createElement('option');
                option.value = equipo.id_equipo;
                option.textContent = equipo.alias;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option disabled>No se encontraron equipos.</option>';
        }
    });

    if (formNuevo) {
        formNuevo.addEventListener('submit', (e) => {
            // 🔵 Bloquear botón primero
            const botonGuardar = formNuevo.querySelector('button[type="submit"]');
            const textoOriginal = botonGuardar ? botonGuardar.innerHTML : '';
            
            if (botonGuardar) {
                botonGuardar.disabled = true;
                botonGuardar.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Guardando...
                `;
            }

            if (!validarFechas()) {
                e.preventDefault();
                
                // 🔴 Restaurar si falla
                if (botonGuardar) {
                    botonGuardar.disabled = false;
                    botonGuardar.innerHTML = textoOriginal || 'Guardar';
                }
                return;
            }

            // Si llega aquí, se envía el formulario con el botón bloqueado
        });
    }


    // Edición de evento
    document.getElementById("editHorarioInicio")?.addEventListener("input", () => {
        const fechaInicio = document.getElementById("editFechaInicio").value;
        const hora = document.getElementById("editHorarioInicio").value;
        const seleccionada = new Date(`${fechaInicio}T${hora}`);
        const ahora = new Date();
        ahora.setMinutes(ahora.getMinutes() + 5);

        if (fechaInicio === ahora.toISOString().split('T')[0] && seleccionada < ahora) {
            //Swal.fire({ icon: 'error', title: 'Hora inválida', text: 'No podés elegir una hora anterior a la actual.' });
            mostrarAlerta('error', 'No podés elegir una hora anterior a la actual.', 'Hora inválida');
            document.getElementById("editHorarioInicio").value = "";
        }
    });

    document.getElementById("editDatetimeFin")?.addEventListener("input", () => {
        const inicioFecha = document.getElementById("editFechaInicio").value;
        const inicioHora = document.getElementById("editHorarioInicio").value;
        const inicio = new Date(`${inicioFecha}T${inicioHora}`);
        const fin = new Date(document.getElementById("editDatetimeFin").value);
        const ahora = new Date();

        if (fin <= inicio || fin < ahora) {
            /*  Swal.fire({
                 icon: 'error',
                 title: 'Fecha/Hora inválida',
                 text: 'La fecha y hora de fin no puede ser menor a la hora de inicio ni a la actual.'
             }); */
            mostrarAlerta('error', 'La fecha y hora de fin no puede ser menor a la hora de inicio ni a la actual.', 'Fecha/Hora inválida');
            document.getElementById("editDatetimeFin").value = "";
        }
    });

    document.getElementById("formEditar")?.addEventListener("submit", (e) => {
        const form = document.getElementById("formEditar");

        const fechaInicio = document.getElementById("editFechaHoraInicio").value;
        const fechaFin = document.getElementById("editFechaHoraFin").value;
        const tipoEvento = document.getElementById("editTipoEventoHidden").value; // ⚡ usamos el hidden ahora

        if (!fechaInicio || !fechaFin) {
            e.preventDefault();
            //Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Completá la fecha de inicio y de fin.' });
            mostrarAlerta('warning', 'Completá la fecha de inicio y de fin.', 'Campos incompletos');
            return;
        }

        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);

        if (fin <= inicio) {
            e.preventDefault();
            //Swal.fire({ icon: 'error', title: 'Rango inválido', text: 'La fecha de fin debe ser posterior a la de inicio.' });
            mostrarAlerta('error', 'La fecha de fin debe ser posterior a la de inicio.', 'Rango inválido');
            return;
        }

        // 🔵 Si es Personalizado, preparar datos
        if (tipoEvento === "Personalizado") {
            const empleadosSeleccionados = $('#editEmpleadosSeleccionados').val();
            const equiposSeleccionados = $('#editEquiposSeleccionados').val();

            if ((!empleadosSeleccionados || empleadosSeleccionados.length === 0) &&
                (!equiposSeleccionados || equiposSeleccionados.length === 0)) {
                e.preventDefault();
                /*    Swal.fire({
                       icon: 'warning',
                       title: 'Asignaciones requeridas',
                       text: 'Seleccioná al menos un empleado o un equipo.'
                   }); */
                mostrarAlerta('warning', 'Seleccioná al menos un empleado o un equipo.', 'Asignaciones requeridas');
                return;
            }

            if ((empleadosSeleccionados && empleadosSeleccionados.length > 0) &&
                (equiposSeleccionados && equiposSeleccionados.length > 0)) {
                e.preventDefault();
                /*  Swal.fire({
                     icon: 'warning',
                     title: 'Error en asignación',
                     text: 'No podés asignar empleados y equipos al mismo tiempo.'
                 }); */
                mostrarAlerta('warning', 'No podés asignar empleados y equipos al mismo tiempo.', 'Error en asignación');
                return;
            }

            // Limpiar inputs viejos
            form.querySelectorAll('.input-personalizado-temp').forEach(input => input.remove());

            if (empleadosSeleccionados?.length > 0) {
                empleadosSeleccionados.forEach(id => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'empleadosSeleccionados[]';
                    hidden.value = id;
                    hidden.classList.add('input-personalizado-temp');
                    form.appendChild(hidden);
                });
            }

            if (equiposSeleccionados?.length > 0) {
                equiposSeleccionados.forEach(id => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'equiposSeleccionados[]';
                    hidden.value = id;
                    hidden.classList.add('input-personalizado-temp');
                    form.appendChild(hidden);
                });
            }
        }

        // ✅ Si pasa las validaciones, antes de enviar:
        const btnGuardarEditar = form.querySelector('button[type="submit"]');
        const btnCerrarEditar = form.querySelector('button[data-bs-dismiss="modal"]');

        if (btnGuardarEditar) {
            btnGuardarEditar.disabled = true;
            btnGuardarEditar.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Guardando...
            `;
        }

        if (btnCerrarEditar) {
            btnCerrarEditar.disabled = true;
        }

        // Guardar fechas en inputs ocultos
        document.getElementById("editStart").value = fechaInicio + ":00";
        document.getElementById("editEnd").value = fechaFin + ":00";

        // ⚡ No hacemos preventDefault aquí, dejamos que envíe normalmente
    });


    document.getElementById("btnEliminarEvento")?.addEventListener("click", () => {
        /* Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará el evento.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarEvento(); // ya existe esta función
            }
        }); */
        confirmarAccion("Esta acción eliminará el evento.", eliminarEvento);
    });

    const modalAddElement = document.getElementById('ModalAdd');

    modalAddElement?.addEventListener('shown.bs.modal', () => {

        const idCreadorInput = document.getElementById('idCreador');
        const idEquipoCreadorInput = document.getElementById('idEquipoCreador');
        const mainContent = document.querySelector('main.db-content');

        if (idCreadorInput && mainContent?.dataset.user) {
            idCreadorInput.value = mainContent.dataset.user;
        }
        if (idEquipoCreadorInput && mainContent?.dataset.team) {
            idEquipoCreadorInput.value = mainContent.dataset.team || ''; // por si no tiene equipo
        }

        // Reiniciar el checkbox "Todo el día"
        const checkTodoDia = document.getElementById('checkTodoDia');
        const inputDesde = document.getElementById('fechaHoraInicio');
        const inputHasta = document.getElementById('fechaHoraFin');

        if (checkTodoDia) {
            checkTodoDia.checked = false;
            inputDesde.disabled = false;
            inputHasta.disabled = false;
        }

        document.getElementById('title').value = '';
        document.getElementById('description').value = '';
        document.getElementById('tipo-evento').value = '';

        // Inicializar Select2 cada vez que se abre el modal
        $('#empleadosSeleccionados').select2({
            placeholder: 'Seleccionar empleados',
            width: '100%',
            dropdownParent: $('#ModalAdd') // Importante para que se dibuje dentro del modal
        });

        $('#equiposSeleccionados').select2({
            placeholder: 'Seleccionar equipos',
            width: '100%',
            dropdownParent: $('#ModalAdd') // Importante para que se dibuje dentro del modal
        });
    });

    const modalEditElement = document.getElementById('ModalEdit');

    modalEditElement?.addEventListener('shown.bs.modal', () => {
        $('#editEmpleadosSeleccionados').select2({
            placeholder: 'Seleccionar empleados',
            width: '100%',
            dropdownParent: $('#ModalEdit')
        });

        $('#editEquiposSeleccionados').select2({
            placeholder: 'Seleccionar equipos',
            width: '100%',
            dropdownParent: $('#ModalEdit')
        });
    });

    const hoy = new Date().toISOString().split('T')[0];
    if (window.innerWidth < 992) {
        cargarEventosPorFecha(hoy, 'listaEventosDia');
    }

    // ubicación dinámica del botón flotante.
    const ajustarBotonFlotante = () => {
        const barraInferior = document.getElementById('barraInferior');
        const boton = document.getElementById('btnNuevoEvento');

        if (!barraInferior || !boton) return;

        const visible = window.getComputedStyle(barraInferior).display !== 'none';

        boton.style.bottom = visible ? '72px' : '1rem'; // subirlo si se ve la barra
    };

    // Ejecutar al cargar
    ajustarBotonFlotante();

    // También al redimensionar ventana
    window.addEventListener('resize', ajustarBotonFlotante);
    const botonFlotante = document.getElementById('btnNuevoEvento');

    if (botonFlotante) {
        // Escuchar todos los modales abiertos
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('show.bs.modal', () => {
                botonFlotante.style.display = 'none';
            });

            modal.addEventListener('hidden.bs.modal', () => {
                botonFlotante.style.display = 'block';
            });
        });
    }
});

const validarFechas = () => {
    const titulo = document.getElementById("title").value.trim();
    const descripcion = document.getElementById("description").value.trim();
    const tipoEvento = document.getElementById("tipo-evento").value;
    const fechaInicio = document.getElementById("fechaHoraInicio").value;
    const fechaFin = document.getElementById("fechaHoraFin").value;

    if (!titulo || !descripcion || !tipoEvento || !fechaInicio || !fechaFin) {
        /*  Swal.fire({
             icon: 'warning',
             title: 'Campos obligatorios',
             text: 'Completá el título, descripción, tipo de evento y fechas.'
         }); */
        mostrarAlerta('warning', 'Completá el título, descripción, tipo de evento y fechas.', 'Campos obligatorios');
        return false;
    }

    const inicio = new Date(fechaInicio);
    const fin = new Date(fechaFin);

    if (fin <= inicio) {
        /*  Swal.fire({
             icon: 'error',
             title: 'Rango inválido',
             text: 'La fecha de fin debe ser posterior a la de inicio.'
         }); */
        mostrarAlerta('error', 'La fecha de fin debe ser posterior a la de inicio.', 'Rango inválido');
        return false;
    }

    // ✨ Validación para eventos Personalizados
    if (tipoEvento === 'Personalizado') {
        const personalizadoTipo = document.querySelector('input[name="personalizadoTipo"]:checked')?.value;

        if (!personalizadoTipo) {
            /* Swal.fire({
                icon: 'warning',
                title: 'Asignación requerida',
                text: 'Seleccioná si el evento es para empleados o para equipos.'
            }); */
            mostrarAlerta('warning', 'Seleccioná si el evento es para empleados o para equipos.', 'Asignación requerida');
            return false;
        }

        const form = document.querySelector('#ModalAdd form');

        // Primero eliminamos cualquier input hidden anterior (por si cambia selección)
        form.querySelectorAll('.input-personalizado-temp').forEach(input => input.remove());

        if (personalizadoTipo === 'empleados') {
            const empleadosSeleccionados = $('#empleadosSeleccionados').val(); // Usamos Select2
            if (!empleadosSeleccionados || empleadosSeleccionados.length === 0) {
                /*   Swal.fire({
                      icon: 'warning',
                      title: 'Empleados no seleccionados',
                      text: 'Seleccioná al menos un empleado.'
                  }); */
                mostrarAlerta('warning', 'Seleccioná al menos un empleado.', 'Empleados no seleccionados');
                return false;
            }

            empleadosSeleccionados.forEach(id => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'empleadosSeleccionados[]';
                hidden.value = id;
                hidden.classList.add('input-personalizado-temp');
                form.appendChild(hidden);
            });
        }

        if (personalizadoTipo === 'equipos') {
            const equiposSeleccionados = $('#equiposSeleccionados').val(); // Usamos Select2
            if (!equiposSeleccionados || equiposSeleccionados.length === 0) {
                /*   Swal.fire({
                      icon: 'warning',
                      title: 'Equipos no seleccionados',
                      text: 'Seleccioná al menos un equipo.'
                  }); */
                mostrarAlerta('warning', 'Seleccioná al menos un equipo.', 'Equipos no seleccionados');
                return false;
            }

            equiposSeleccionados.forEach(id => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'equiposSeleccionados[]';
                hidden.value = id;
                hidden.classList.add('input-personalizado-temp');
                form.appendChild(hidden);
            });
        }
    }

    // Guardar los campos ocultos
    document.getElementById("start").value = fechaInicio + ":00";
    document.getElementById("end").value = fechaFin + ":00";

    return true;
};

const cargarModalEdicion = async (evento) => {

    document.getElementById("eliminarId").value = evento.id;
    // Rellenar campos básicos
    document.getElementById("editId").value = evento.id;
    document.getElementById("editTitle").value = evento.title || "";
    document.getElementById("editDescription").value = evento.extendedProps?.descripcion || "";
    document.getElementById("editColor").value = evento.color || "#3788d8";

    // Color visual en paleta
    const editColorActualBtn = document.getElementById('editColorActual');
    const editPaletaColores = document.getElementById('editPaletaColores');

    if (editColorActualBtn && editPaletaColores) {
        editColorActualBtn.style.backgroundColor = evento.color || '#4285f4';

        editPaletaColores.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
        const botonSeleccionado = Array.from(editPaletaColores.querySelectorAll('button'))
            .find(btn => btn.dataset.color === (evento.color || '#4285f4'));
        if (botonSeleccionado) {
            botonSeleccionado.classList.add('selected');
        }
    }

    const tipoEvento = evento.extendedProps?.tipo_evento || "";

    document.getElementById("editTipoEvento").value = tipoEvento;
    document.getElementById("editTipoEventoHidden").value = tipoEvento;


    // ⚡ Usar directamente el string recibido
    document.getElementById("editFechaHoraInicio").value = evento.start.slice(0, 16);
    document.getElementById("editFechaHoraFin").value = evento.end.slice(0, 16);
    document.getElementById("editStart").value = evento.start.slice(0, 19);
    document.getElementById("editEnd").value = evento.end.slice(0, 19);

    // Todo el día
    const inicio = new Date(evento.start);
    const fin = new Date(evento.end);
    const todoElDia = inicio.getHours() === 0 && inicio.getMinutes() === 0 && fin.getHours() === 23 && fin.getMinutes() === 59;
    const check = document.getElementById("editCheckTodoDia");
    if (check) {
        check.checked = todoElDia;
        document.getElementById("editFechaHoraInicio").disabled = todoElDia;
        document.getElementById("editFechaHoraFin").disabled = todoElDia;
    }

    // 👉 Gestión especial si es Personalizado
    const personalizadoOpciones = document.getElementById("editPersonalizadoOpciones");
    const selectEmpleados = document.getElementById("editSelectEmpleados");
    const selectEquipos = document.getElementById("editSelectEquipos");

    if (tipoEvento === "Personalizado") {
        personalizadoOpciones.classList.remove('d-none');

        try {
            const asignados = await obtenerAsignadosEvento(evento.id);

            if (asignados?.tipo === "empleados") {
                document.getElementById("editPersonalizadoEmpleados").checked = true;
                selectEmpleados.classList.remove('d-none');
                selectEquipos.classList.add('d-none');

                const empleadosDisponibles = await cargarEmpleados();
                const idsAsignados = asignados.lista.map(a => a.id_empleado);

                $('#editEmpleadosSeleccionados').select2('destroy').empty();

                empleadosDisponibles.forEach(emp => {
                    const option = new Option(emp.nombre, emp.id_empleado, false, false);
                    $('#editEmpleadosSeleccionados').append(option);
                });

                $('#editEmpleadosSeleccionados').select2({
                    placeholder: 'Seleccionar empleados',
                    width: '100%',
                    dropdownParent: $('#ModalEdit')
                });

                // ✨ Seleccionar los que corresponden
                $('#editEmpleadosSeleccionados').val(idsAsignados).trigger('change');

            } else if (asignados?.tipo === "equipos") {
                document.getElementById("editPersonalizadoEquipos").checked = true;
                selectEquipos.classList.remove('d-none');
                selectEmpleados.classList.add('d-none');

                const equiposDisponibles = await cargarEquipos();
                const idsAsignados = asignados.lista.map(a => a.id_equipo);

                $('#editEquiposSeleccionados').select2('destroy').empty();

                equiposDisponibles.forEach(eq => {
                    const option = new Option(eq.alias, eq.id_equipo, false, false);
                    $('#editEquiposSeleccionados').append(option);
                });

                $('#editEquiposSeleccionados').select2({
                    placeholder: 'Seleccionar equipos',
                    width: '100%',
                    dropdownParent: $('#ModalEdit')
                });

                // ✨ Seleccionar los que corresponden
                $('#editEquiposSeleccionados').val(idsAsignados).trigger('change');
            }
        } catch (error) {
            console.error("Error cargando asignados:", error);
        }
    } else {
        personalizadoOpciones.classList.add('d-none');
        selectEmpleados.classList.add('d-none');
        selectEquipos.classList.add('d-none');
    }

    // Mostrar el modal
    new bootstrap.Modal(document.getElementById('ModalEdit')).show();
};