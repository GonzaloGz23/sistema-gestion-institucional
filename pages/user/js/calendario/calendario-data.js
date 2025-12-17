const obtenerEventos = () => {
    const main = document.querySelector('main.db-content');
    if (!main) return '../../backend/controller/usuario/calendario/listar_eventos.php'; 

    const idUsuario = main.dataset.user;
    const idEquipo = main.dataset.team;
    
    // ✅ Agregar timestamp para evitar cache
    const timestamp = new Date().getTime();
    const random = Math.random();

    return `../../backend/controller/usuario/calendario/listar_eventos.php?id_usuario=${idUsuario}&id_equipo=${idEquipo}&t=${timestamp}&r=${random}`;
};

// Cargar empleados habilitados y no borrados
const cargarEmpleados = async () => {
    try {
        const resp = await fetch('../../backend/controller/usuario/calendario/modal_listar_empleados.php');
        const data = await resp.json();
        return Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Error al cargar empleados:', error);
        return [];
    }
};

// Cargar equipos habilitados y no borrados
const cargarEquipos = async () => {
    try {
        const resp = await fetch('../../backend/controller/usuario/calendario/modal_listar_equipos.php');
        const data = await resp.json();
        return Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Error al cargar equipos:', error);
        return [];
    }
};

const eliminarEvento = () => {
    document.getElementById("formEliminar").submit();
};

const agregarEvento = () => {
    const fechaHoraInicio = document.getElementById("fechaHoraInicio").value;
    const fechaHoraFin = document.getElementById("fechaHoraFin").value;

    if (!fechaHoraFin || (!fechaHoraInicio && !document.getElementById("checkTodoDia").checked)) {
        mostrarAlerta('warning', 'Completá las fechas requeridas.', 'Campos incompletos');
        return false;
    }

    const startInput = document.getElementById("start");
    const endInput = document.getElementById("end");

    if (document.getElementById("checkTodoDia").checked) {
        const fecha = fechaHoraInicio.split("T")[0]; // ⚡ CORRECTO: tomamos fechaHoraInicio
        startInput.value = fecha + "T00:00:00";
        endInput.value = fecha + "T23:59:59"; // Seteamos manualmente 23:59:59
    } else {
        startInput.value = fechaHoraInicio + ":00";
        endInput.value = fechaHoraFin + ":00";
    }

    return true;
};

const cargarEventosPorFecha = (fechaStr, idContenedor, abrirModal = false) => {
    const contenedor = document.getElementById(idContenedor);
    if (!contenedor) return;

    const spinner = document.getElementById('spinnerEventos');
    if (idContenedor === 'listaEventosDia' && spinner) {
        spinner.classList.remove('d-none');
        spinner.classList.add('d-flex');
    }

    contenedor.innerHTML = '<div class="text-muted">Cargando eventos...</div>';

    const main = document.querySelector('main.db-content');
    const idUsuario = main?.dataset.user;
    const idEquipo = main?.dataset.team;

    const start = `${fechaStr}T00:00:00`;
    const end = `${fechaStr}T23:59:59`;
    
    // ✅ Forzar bypass de cache
    const timestamp = new Date().getTime();
    const random = Math.random();

    fetch(`../../backend/controller/usuario/calendario/listar_eventos.php?start=${start}&end=${end}&id_usuario=${idUsuario}&id_equipo=${idEquipo}&t=${timestamp}&r=${random}`, {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (idContenedor === 'listaEventosDia' && spinner) {
            spinner.classList.remove('d-flex');
            spinner.classList.add('d-none');
        }
        contenedor.innerHTML = '';

        if (!data.length) {
            contenedor.innerHTML = '<div class="text-muted">No hay eventos para este día.</div>';
            return;
        }

        data.forEach(evento => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';

            const inicio = new Date(evento.start);
            const fin = new Date(evento.end);

            const fechaInicio = inicio.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const fechaFin = fin.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const horaInicio = inicio.toTimeString().slice(0, 5);
            const horaFin = fin.toTimeString().slice(0, 5);

            let rangoFechas;
            if (fechaInicio === fechaFin) {
                rangoFechas = `${fechaInicio} de ${horaInicio} a ${horaFin}`;
            } else {
                rangoFechas = `${fechaInicio} ${horaInicio} ➔ ${fechaFin} ${horaFin}`;
            }

            item.innerHTML = `
                <div class="d-flex">
                    <div class="me-3" style="width: 4px; background-color: ${evento.color}; border-radius: 2px;"></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">${evento.title}</div>
                        <div class="small text-muted">${evento.extendedProps?.descripcion || ''}</div>
                        <div class="small text-muted">${evento.extendedProps?.tipo_evento || ''}</div>
                        <div class="small text-muted">${rangoFechas}</div>
                    </div>
                </div>
            `;

            item.addEventListener('click', (e) => {
                e.stopPropagation();

                const main = document.querySelector('main.db-content');
                const idEquipoLogueado = parseInt(main?.dataset.team) || null;
                const idEquipoCreador = parseInt(evento.extendedProps?.id_equipo_creador) || null;


                console.log('DEBUG CLICK:', {
                    idEquipoLogueado,
                    idEquipoCreador,
                    evento
                });



                // Validación: solo permitir edición si pertenece al mismo equipo
                if (idEquipoLogueado && idEquipoCreador && idEquipoLogueado === idEquipoCreador) {
                    cargarModalEdicion(evento);

                    if (abrirModal) {
                        const modalEventos = bootstrap.Modal.getInstance(document.getElementById('modalEventosDia'));
                        modalEventos?.hide();
                    }
                }
                // Si no cumple, no hace nada (no se abre modal, ni error, ni alerta)
            });

            contenedor.appendChild(item);
        });

        if (abrirModal) {
            new bootstrap.Modal(document.getElementById('modalEventosDia')).show();
        }
    });
};

const obtenerAsignadosEvento = async (idEvento) => {
    try {
        const resp = await fetch(`../../backend/controller/usuario/calendario/obtener_asignados_evento.php?id_evento=${idEvento}`);
        const data = await resp.json();
        return data;
    } catch (error) {
        console.error("Error al obtener asignados del evento:", error);
        return null;
    }
};