// ✅ Fecha mínima para la resolución
const inputFecha = document.getElementById('fechaResolucion');
const indicadorNuevo = document.getElementById('indicadorNuevoMensaje');
if (inputFecha) {
    const hoy = new Date().toISOString().split('T')[0];
    inputFecha.min = hoy;
}

// Validar que la fecha de resolución no sea anterior a hoy
const validarFechaResolucion = () => {
    const fechaValor = inputFecha?.value;
    if (!fechaValor) {
        inputFecha.classList.add('is-invalid');
        return false;
    }

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0); // Eliminar hora para comparar solo fecha
    const fechaSeleccionada = new Date(fechaValor);
    if (fechaSeleccionada < hoy) {
        inputFecha.classList.add('is-invalid');
        return false;
    }

    inputFecha.classList.remove('is-invalid');
    return true;
};



document.addEventListener('DOMContentLoaded', function () {
    // Selecciona todos los elementos que tienen el atributo data-bs-toggle="tooltip"
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    
    // Inicializa cada tooltip
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        // Asegúrate de que estás usando la versión correcta de inicialización de Bootstrap (ej. Bootstrap 5)
        // En Bootstrap 5, se usa new bootstrap.Tooltip(element)
        return new bootstrap.Tooltip(tooltipTriggerEl); 
    });
});




// asociar menu contextual
const asociarMenuContextual = (card) => {
    card.addEventListener('contextmenu', e => mostrarMenu(e, card));

    let presionado;
    card.addEventListener('touchstart', e => {
        presionado = setTimeout(() => mostrarMenu(e, card), 600);
    });
    card.addEventListener('touchend', () => clearTimeout(presionado));
};

// listar solicitudes
const renderizarSolicitudes = async () => {
    const contenedor = document.getElementById('bandejaUnificada');
    const spinner = document.getElementById('spinnerCarga');
    const mensajeError = document.getElementById('mensajeErrorSolicitudes');

    // Reset visual
    contenedor.innerHTML = '';
    spinner.classList.remove('d-none');
    mensajeError.classList.add('d-none');
    document.querySelectorAll('[data-mensaje-vacio]').forEach(p => p.classList.add('d-none'));

    try {
        const solicitudes = await obtenerSolicitudes();
        spinner.classList.add('d-none');

        if (solicitudes.length === 0) {
            // Si no hay ninguna solicitud en absoluto, mostrar mensaje de filtro activo
            const filtroActivo = document.querySelector('#tabsFiltros .btn.active')?.dataset.filtro || 'todas';
            const mensajeFiltro = document.querySelector(`[data-mensaje-vacio="${filtroActivo}"]`);
            mensajeFiltro?.classList.remove('d-none');
            return;
        }

        solicitudes.forEach(solicitud => {
            const card = document.createElement('div');
            card.className = 'card mb-3 card-solicitud';

            const estado = solicitud.estado?.toLowerCase() || 'pendiente';
            const tipo = solicitud.tipo || 'sin-tipo';
            const tags = solicitud.tags?.toLowerCase() || '';
            const asunto = solicitud.asunto || 'Sin asunto';
            let destino = 'Sin destino';

            const privado = solicitud.privado == 1;

            if (solicitud.rol_equipo === 'emisor') {
                destino = `A: ${solicitud.equipos_destino || 'Sin destino'}`;
            } else if (solicitud.rol_equipo === 'receptor') {
                const emisor = privado
                    ? `${solicitud.nombre_emisor} ${solicitud.apellido_emisor} <span class="badge bg-primary-soft">Privado</span>`
                    : solicitud.equipo_emisor;
                destino = `De: ${emisor || 'Sin emisor'}`;
            }

            const fecha = solicitud.fecha_creacion || '';
            card.dataset.estado = estado;
            card.dataset.id = solicitud.id_solicitud;
            card.dataset.tipo = tipo;
            card.dataset.tags = tags;
            card.dataset.asunto = asunto;
            card.dataset.destino = destino;
            card.dataset.equipoEmisor = solicitud.id_equipo_emisor;
            card.dataset.rol = solicitud.rol_equipo; // 'emisor' o 'receptor'
            card.innerHTML = `
            <div class="d-flex align-items-start gap-3 p-3 card-body-detalle" style="cursor: pointer;">
                <div class="flex-shrink-0">
                    <img src="../../dist/assets/images/avatar/equipo_avatar.webp" class="rounded-circle" alt="Equipo" width="48" height="48">
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="me-2 text-truncate">
                            <h6 class="mb-1 fw-semibold text-dark text-truncate" style="max-width: 250px;">${destino}</h6>
                            <div class="text-muted small text-truncate" style="max-width: 100%;">${asunto}</div>
                            <div class="mt-1">
                                <span class="badge badge-estado ${getEstadoClass(estado)}">${capitalizar(estado)}</span>
                            </div>
                        </div>
                        <div class="text-end" style="min-width: 80px;">
                            <small class="text-muted d-block small" style="font-size: 0.75rem;">${fecha.split(' ')[0]}</small>
                            ${solicitud.cantidad_mensajes_nuevos > 0
                    ? `<span class="bg-primary text-white fw-semibold d-inline-flex align-items-center justify-content-center mt-1"
                                    style="width: 20px; height: 20px; font-size: 0.75rem; border-radius: 50%;">
                                    ${solicitud.cantidad_mensajes_nuevos}
                                </span>`
                    : ''}
                        </div>
                    </div>
                </div>
            </div>`;
            contenedor.appendChild(card);
            card.querySelector('.card-body-detalle')?.addEventListener('click', () => mostrarDetalle(card));
            asociarMenuContextual(card);
        });

        // Aplicar filtro activo
        const filtroActivo = document.querySelector('#tabsFiltros .btn.active')?.dataset.filtro;
        if (filtroActivo) {
            const tarjetas = document.querySelectorAll('#bandejaUnificada .card-solicitud');
            tarjetas.forEach(card => {
                const tipo = card.dataset.tipo;
                const estado = card.dataset.estado;

                let coincide = false;
                if (filtroActivo === 'todas') {
                    coincide = true;
                } else if (['recibidas', 'enviadas'].includes(filtroActivo)) {
                    coincide = tipo === filtroActivo;
                } else {
                    coincide = estado === filtroActivo;
                }

                card.style.display = coincide ? 'block' : 'none';
            });

            // Mostrar mensaje vacío si no hay coincidencias visibles
            const visibles = [...tarjetas].filter(card => card.style.display !== 'none');
            const mensajeFiltro = document.querySelector(`[data-mensaje-vacio="${filtroActivo}"]`);
            if (visibles.length === 0) {
                mensajeFiltro?.classList.remove('d-none');
            }
        }

    } catch (err) {
        console.error('Error al renderizar solicitudes:', err);
        spinner.classList.add('d-none');
        mensajeError.classList.remove('d-none');
    }
};

// Helpers opcionales
const capitalizar = str => {
    if (typeof str !== 'string') return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

const getEstadoClass = estado => {
    const est = estado?.toLowerCase();
    if (est === 'resuelta') return 'bg-success';
    if (est === 'rechazada') return 'bg-danger';
    return 'bg-warning text-dark'; // pendiente u otro
};

// ✅ Mostrar detalle y volver
document.getElementById('btnVolver')?.addEventListener('click', async () => {
    const idSolicitud = document.getElementById('detalleSolicitud')?.dataset.idSolicitud;

    if (idSolicitud) {
        try {
            await marcarSolicitudComoLeida(idSolicitud);
        } catch (err) {
            console.warn('Error al marcar como leída:', err);
        }
    }

    indicadorNuevo.style.display = 'none'; // Ocultar el indicador al volver

    // Ocultar sección de detalle y volver a la bandeja
    document.getElementById('detalleSolicitud').style.display = 'none';
    document.getElementById('contenedorSolicitudes').style.display = 'block';
    document.getElementById('btnNuevaSolicitud')?.classList.remove('d-none');

    detenerPollingChat();
    iniciarPollingBandeja();
    ultimoIdMensaje = 0;

    // Si hay card activa, actualizar estado visual en la tarjeta
    if (window.cardActiva) {
        const estadoActualizado = document.getElementById('estadoSolicitud').value;
        window.cardActiva.dataset.estado = estadoActualizado;

        const badge = window.cardActiva.querySelector('.badge-estado');
        badge.textContent = capitalizar(estadoActualizado);
        badge.className = 'badge badge-estado ' + getEstadoClass(estadoActualizado);

        // Ocultar el contador de mensajes no leídos si existía
        const contador = window.cardActiva.querySelector('.bg-primary');
        if (contador) contador.style.display = 'none';
    }

    window.cardActiva = null; // limpiar referencia

    // Limpiar campos del detalle (opcional pero recomendado)
    document.getElementById('detalleAsunto').textContent = '';
    document.getElementById('detalleDestino').textContent = '';
    document.getElementById('estadoSolicitud').value = 'pendiente';
    document.getElementById('badgeEstado').textContent = 'Pendiente';
    document.getElementById('badgeEstado').className = 'badge badge-estado bg-warning text-dark';

    const badgeEtiqueta = document.querySelector('#detalleSolicitud .badge.bg-info-soft');
    badgeEtiqueta.textContent = '';
    badgeEtiqueta.style.display = 'none';

    const chat = document.getElementById('contenedorChat');
    chat.innerHTML = '';
    document.getElementById('contenedorChat').style.display = 'none';

    // Recargar solicitudes para reflejar los cambios de lectura
    await renderizarSolicitudes();
});

// ✅Mostrar los detalles.
const mostrarDetalle = async (card) => {
    const idSolicitud = card.dataset.id;
    if (!idSolicitud) return;


    detenerPollingBandeja();
    iniciarPollingChat(idSolicitud);

    document.getElementById('detalleSolicitud').dataset.idSolicitud = idSolicitud;
    window.cardActiva = card;

    try {
        const data = await obtenerDetalleSolicitud(idSolicitud);
        //console.log('📦 Detalle recibido:', data); // <--- agregá esto
        if (!data.success) throw new Error(data.error || 'No se pudo obtener la solicitud.');

        // ⏩ FASE 1: Mostrar secciones principales
        document.getElementById('detalleSolicitud').style.display = 'block';
        document.getElementById('contenedorSolicitudes').style.display = 'none';
        document.getElementById('btnNuevaSolicitud')?.classList.add('d-none');

        document.getElementById('detalleDestino').textContent = data.destino || 'Sin equipo';
        document.getElementById('detalleAsunto').textContent = data.asunto || 'Sin asunto';


        const estado = data.estado?.toLowerCase() || 'pendiente';
        const selectEstado = document.getElementById('estadoSolicitud');
        const badgeEstado = document.getElementById('badgeEstado');
        const contenedorSelect = document.getElementById('contenedorSelectEstado');
        const contenedorBadge = document.getElementById('contenedorBadgeEstado');

        if (data.tipo === 'recibidas') {
            contenedorSelect.classList.remove('d-none');
            contenedorBadge.classList.add('d-none');
            selectEstado.disabled = false;
        } else {
            contenedorSelect.classList.add('d-none');
            contenedorBadge.classList.remove('d-none');
            badgeEstado.textContent = capitalizar(estado);
            badgeEstado.className = 'badge badge-estado ' + getEstadoClass(estado);
        }
        selectEstado.value = estado;

        // ✅ Renderizar todo el chat, incluido el primer mensaje
        const chat = document.getElementById('contenedorChat');
        chat.innerHTML = '';

        if (Array.isArray(data.chat) && data.chat.length > 0) {
            chat.style.display = 'block';

            data.chat.forEach(m => {
                const div = document.createElement('div');
                div.className = `d-flex mb-3 ${m.es_mio ? 'justify-content-end' : ''}`;
                div.dataset.idMensaje = m.id_mensaje;

                const burbuja = document.createElement('div');
                burbuja.className = `${m.es_mio ? 'bg-primary text-white' : 'bg-white'} rounded p-2 px-3`;

                const meta = document.createElement('div');
                meta.className = `d-flex justify-content-between small ${m.es_mio ? 'text-white-50' : 'text-muted'}`;

                const nombreSpan = document.createElement('span');
                nombreSpan.className = 'flex-grow-1 me-2';
                nombreSpan.textContent = m.autor;

                const fechaObj = new Date(m.fecha);
                const fechaTexto = `${String(fechaObj.getDate()).padStart(2, '0')}/${String(fechaObj.getMonth() + 1).padStart(2, '0')} ${fechaObj.getHours().toString().padStart(2, '0')}:${fechaObj.getMinutes().toString().padStart(2, '0')}`;

                const fechaSpan = document.createElement('span');
                fechaSpan.className = 'text-end';
                fechaSpan.textContent = fechaTexto;

                meta.appendChild(nombreSpan);
                meta.appendChild(fechaSpan);
                burbuja.appendChild(meta);

                const contenido = document.createElement('div');
                contenido.textContent = m.mensaje;
                burbuja.appendChild(contenido);

                if (Array.isArray(m.archivos) && m.archivos.length > 0) {
                    m.archivos.forEach(archivo => {
                        const link = document.createElement('a');
                        link.href = `/sistemaInstitucional/uploads/${archivo.ruta}`;
                        link.textContent = `📎 ${archivo.nombre}`;
                        link.target = '_blank';
                        link.className = `small text-decoration-underline d-block mt-1 ${m.es_mio ? 'text-white' : 'text-dark'}`;
                        burbuja.appendChild(link);
                    });
                }

                div.appendChild(burbuja);
                if (m.es_mio) {
                    div.addEventListener('contextmenu', (e) => mostrarMenuMensaje(e, div));
                }

                chat.appendChild(div);
            });
        } else {
            chat.style.display = 'none';
        }

        // ✅ Marcar como leída
        await marcarSolicitudComoLeida(idSolicitud);

    } catch (err) {
        console.error('🔴 Error en mostrarDetalle:', err); // ⬅️ agregá esto
        mostrarAlerta('error', 'No se pudo mostrar el detalle de la solicitud.', 'Error');
    }
};


// ✅ Actualizar estado visual
document.getElementById('estadoSolicitud')?.addEventListener('change', async () => {
    const nuevoEstado = document.getElementById('estadoSolicitud').value;
    const badge = document.getElementById('badgeEstado');
    const spinner = document.getElementById('spinnerEstado');
    const idSolicitud = document.getElementById('detalleSolicitud')?.dataset.idSolicitud;

    if (!idSolicitud) return;

    // Evitar actualizar si no cambió el estado
    const estadoActual = badge.textContent.toLowerCase();
    if (estadoActual === nuevoEstado) return;

    // Mostrar spinner
    spinner.style.display = 'inline-block';

    const resultado = await actualizarEstadoSolicitud(idSolicitud, nuevoEstado);
    spinner.style.display = 'none';

    if (resultado.success) {
        badge.textContent = capitalizar(nuevoEstado);
        badge.className = 'badge badge-estado ' + getEstadoClass(nuevoEstado);
    } else {
        mostrarAlerta('error', resultado.error || 'No se pudo actualizar el estado.', 'Error');
        // Revertir el select al valor anterior si hubo error
        document.getElementById('estadoSolicitud').value = estadoActual;
    }
});

// ✅ Chat: enviar mensaje
let archivosAdjuntos = [];

const inputAdjuntos = document.getElementById('inputAdjuntosChat');
const listaArchivos = document.getElementById('archivosSeleccionados');

inputAdjuntos?.addEventListener('change', () => {
    const nuevosArchivos = Array.from(inputAdjuntos.files);

    nuevosArchivos.forEach(file => {
        // Evitar duplicados por nombre y tamaño
        if (!archivosAdjuntos.some(f => f.name === file.name && f.size === file.size)) {
            archivosAdjuntos.push(file);
        }
    });

    renderizarArchivosAdjuntos();
    inputAdjuntos.value = ''; // permite volver a cargar el mismo archivo si se elimina
});

const renderizarArchivosAdjuntos = () => {
    listaArchivos.innerHTML = '';

    archivosAdjuntos.forEach((archivo, index) => {
        const badge = document.createElement('span');
        badge.className = 'badge bg-secondary d-flex align-items-center';
        badge.style.gap = '0.5rem';
        badge.textContent = archivo.name;

        const btnCerrar = document.createElement('button');
        btnCerrar.type = 'button';
        btnCerrar.className = 'btn-close btn-close-white btn-sm ms-2';
        btnCerrar.onclick = () => {
            archivosAdjuntos.splice(index, 1);
            renderizarArchivosAdjuntos();
        };

        badge.appendChild(btnCerrar);
        listaArchivos.appendChild(badge);
    });
};

// Renderizar mensaje en el chat
const renderizarMensaje = (msg) => {
    const chat = document.getElementById('contenedorChat');

    // Verificar si ya existe el mensaje
    const existente = chat.querySelector(`[data-id-mensaje="${msg.id_solicitudes_mensaje}"]`);
    if (existente) return;

    const div = document.createElement('div');
    div.className = `d-flex mb-3 ${msg.es_mio ? 'justify-content-end' : ''}`;
    div.dataset.idMensaje = msg.id_solicitudes_mensaje;

    const burbuja = document.createElement('div');
    burbuja.className = `${msg.es_mio ? 'bg-primary text-white' : 'bg-white'} rounded p-2 px-3`;

    const meta = document.createElement('div');
    meta.className = `d-flex justify-content-between small ${msg.es_mio ? 'text-white-50' : 'text-muted'}`;

    const nombreSpan = document.createElement('span');
    nombreSpan.className = 'flex-grow-1 me-2';
    nombreSpan.textContent = msg.autor;

    const fecha = new Date(msg.creado_en);
    const fechaTexto = `${String(fecha.getDate()).padStart(2, '0')}/${String(fecha.getMonth() + 1).padStart(2, '0')} ${String(fecha.getHours()).padStart(2, '0')}:${String(fecha.getMinutes()).padStart(2, '0')}`;

    const fechaSpan = document.createElement('span');
    fechaSpan.className = 'text-end';
    fechaSpan.textContent = fechaTexto;

    meta.appendChild(nombreSpan);
    meta.appendChild(fechaSpan);
    burbuja.appendChild(meta);

    // Mensaje de texto
    const contenido = document.createElement('div');
    contenido.textContent = msg.mensaje;
    burbuja.appendChild(contenido);

    // Archivos adjuntos (si existen)
    if (Array.isArray(msg.archivos) && msg.archivos.length > 0) {
        msg.archivos.forEach(archivo => {
            const link = document.createElement('a');
            link.href = `/sistemaInstitucional/uploads/${archivo.ruta_archivo}`;
            link.textContent = `📎 ${archivo.nombre_original}`;
            link.target = '_blank';
            link.className = `small text-decoration-underline d-block mt-1 ${msg.es_mio ? 'text-white' : 'text-dark'}`;
            burbuja.appendChild(link);
        });
    }

    div.appendChild(burbuja);
    chat.appendChild(div);

    // Verificar si el usuario está viendo los últimos mensajes
    const estaEnUltimoMensaje = chat.scrollHeight - chat.scrollTop <= chat.clientHeight + 10;

    if (!estaEnUltimoMensaje) {
        // Mostrar el indicador "Nuevo"
        indicadorNuevo.style.display = 'inline-block';
    } else {
        // Si está en el último mensaje, scrollear automáticamente
        chat.scrollTop = chat.scrollHeight;
        indicadorNuevo.style.display = 'none';
    }
};

// Iniciar polling al cargar la página
window.addEventListener('DOMContentLoaded', () => {
    iniciarPollingBandeja();
});


// 🚦 DEBOUNCE SIMPLE: Solo 1 variable para evitar envíos duplicados
let enviandoMensaje = false;

document.getElementById('formRespuesta')?.addEventListener('submit', async e => {
    e.preventDefault(); // EVITA que el formulario se envíe de forma tradicional

    // 🛡️ PROTECCIÓN ANTI-DUPLICADOS: Si ya está enviando, ignora el clic
    if (enviandoMensaje) return;
    enviandoMensaje = true; // MARCA que está enviando

    // CAPTURA los datos del formulario antes de procesarlos
    const textarea = e.target.querySelector('textarea');
    const mensaje = textarea.value.trim();
    const archivos = archivosAdjuntos;

    // VALIDA que tengamos una solicitud válida y contenido para enviar
    const idSolicitud = document.getElementById('detalleSolicitud')?.dataset.idSolicitud;
    if (!idSolicitud || (!mensaje && archivos.length === 0)) {
        enviandoMensaje = false; // LIBERA el bloqueo si no hay nada que enviar
        return;
    }

    // 🚀 OPTIMISTIC UPDATE: AQUÍ EMPIEZA LA MAGIA - Mostrar mensaje inmediatamente
    // ESTO HACE que el usuario vea su mensaje al instante, sin esperar al servidor
    const chat = document.getElementById('contenedorChat');
    chat.style.display = 'block'; // ASEGURA que el chat esté visible

    // CREA un ID temporal único para identificar este mensaje mientras se envía
    const tempId = 'temp_' + Date.now();
    const div = document.createElement('div');
    div.className = 'd-flex mb-3 justify-content-end mensaje-contenedor'; // POSICIONA el mensaje a la derecha (mensaje propio)
    div.dataset.idMensaje = tempId; // GUARDA el ID temporal
    div.dataset.enviando = 'true'; // MARCA que este mensaje está siendo enviado

    // CREA la burbuja del mensaje con estilo visual RESPONSIVO
    const burbuja = document.createElement('div');
    burbuja.className = 'bg-primary text-white rounded p-2 px-3 position-relative mensaje-burbuja'; // ESTILO azul para mensajes propios

    // GENERA la fecha y hora actual para mostrar en el mensaje
    const ahora = new Date();
    const fechaTexto = `${String(ahora.getDate()).padStart(2, '0')}/${String(ahora.getMonth() + 1).padStart(2, '0')} ${ahora.getHours().toString().padStart(2, '0')}:${ahora.getMinutes().toString().padStart(2, '0')}`;

    // CREA la sección de metadatos (nombre y fecha)
    const meta = document.createElement('div');
    meta.className = 'd-flex justify-content-between small text-white-50';

    // MUESTRA "Vos" como autor del mensaje
    const nombreSpan = document.createElement('span');
    nombreSpan.className = 'flex-grow-1 me-2 text-break';
    nombreSpan.textContent = 'Vos';

    // MUESTRA la fecha y hora del mensaje
    const fechaSpan = document.createElement('span');
    fechaSpan.className = 'text-end';
    fechaSpan.textContent = fechaTexto;

    // ENSAMBLA los metadatos en la burbuja
    meta.appendChild(nombreSpan);
    meta.appendChild(fechaSpan);
    burbuja.appendChild(meta);

    // AGREGA el contenido del mensaje con MANEJO DE TEXTO LARGO
    const contenido = document.createElement('div');
    contenido.className = 'mensaje-texto'; // CLASE para estilos responsivos
    contenido.textContent = mensaje;
    burbuja.appendChild(contenido);

    // CREA el indicador visual de "enviando" - un spinner amarillo pequeño
    // ESTO LE DICE al usuario que el mensaje se está procesando
    const indicadorEnviando = document.createElement('div');
    indicadorEnviando.className = 'position-absolute top-0 end-0 translate-middle';
    indicadorEnviando.innerHTML = '<span class="spinner-border spinner-border-sm text-secondary" style="width: 12px; height: 12px;"></span>';
    burbuja.appendChild(indicadorEnviando);

    // MUESTRA archivos adjuntos temporalmente si los hay
    // ESTO PERMITE al usuario ver qué archivos está enviando
    if (archivos.length > 0) {
        archivos.forEach(archivo => {
            const archivoDiv = document.createElement('div');
            archivoDiv.className = 'small text-white-50 mt-1';
            archivoDiv.textContent = `📎 ${archivo.name} (enviando...)`; // INDICA que el archivo se está subiendo
            burbuja.appendChild(archivoDiv);
        });
    }

    // ENSAMBLA todo y lo agrega al chat
    div.appendChild(burbuja);
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight; // HACE scroll automático hacia abajo

    // LIMPIA el formulario inmediatamente - ESTO ES CLAVE para la experiencia fluida
    // PERMITE al usuario escribir otro mensaje mientras este se envía
    textarea.value = ''; // BORRA el texto del textarea
    const archivosParaEnviar = [...archivosAdjuntos]; // HACE una copia de los archivos para el envío
    archivosAdjuntos = []; // LIMPIA la lista global de archivos
    document.getElementById('inputAdjuntosChat').value = ''; // RESETEA el input de archivos
    renderizarArchivosAdjuntos(); // ACTUALIZA la vista de archivos adjuntos

    // FEEDBACK sutil en el botón - NO LO BLOQUEA como antes
    // ESTO MUESTRA confirmación visual sin impedir seguir usando la interfaz
    const boton = e.target.querySelector('button');
    const iconoOriginal = boton.innerHTML;
    boton.innerHTML = `<i class="bi bi-check-circle text-success me-1"></i>Enviado`; // MUESTRA "✅ Enviado"

    // RESTAURA el botón a su estado original después de 1 segundo
    setTimeout(() => {
        boton.innerHTML = iconoOriginal;
    }, 1000);

    // 📤 ENVÍO REAL EN SEGUNDO PLANO - AQUÍ SE HACE la petición real al servidor
    // ESTO SUCEDE mientras el usuario ya ve su mensaje y puede seguir interactuando
    try {
        const respuesta = await enviarMensajeSolicitud(idSolicitud, mensaje, archivosParaEnviar);

        if (respuesta.success) {
            // ✅ ÉXITO: ACTUALIZA el mensaje temporal con datos reales del servidor
            // ESTO CONVIERTE el mensaje temporal en un mensaje real
            div.dataset.idMensaje = respuesta.id_mensaje || ''; // ACTUALIZA con el ID real del servidor
            div.removeAttribute('data-enviando'); // QUITA la marca de "enviando"
            indicadorEnviando.remove(); // ELIMINA el spinner de "enviando"

            // ACTUALIZA archivos con enlaces reales del servidor
            if (respuesta.archivos && Array.isArray(respuesta.archivos)) {
                // ELIMINA los archivos temporales que decían "(enviando...)"
                burbuja.querySelectorAll('.small.text-white-50').forEach(el => {
                    if (el.textContent.includes('(enviando...)')) {
                        el.remove();
                    }
                });

                // AGREGA los archivos reales con enlaces funcionales
                respuesta.archivos.forEach(archivo => {
                    const link = document.createElement('a');
                    link.href = `/sistemaInstitucional/uploads/${archivo.ruta}`; // ENLACE real al archivo
                    link.textContent = `📎 ${archivo.nombre}`;
                    link.target = '_blank'; // ABRE en nueva pestaña
                    link.className = 'small text-decoration-underline d-block mt-1 text-white';
                    burbuja.appendChild(link);
                });
            }

            // HABILITA el menú contextual para el mensaje (clic derecho)
            div.addEventListener('contextmenu', (e) => mostrarMenuMensaje(e, div));

            // OCULTA el indicador "Nuevo" si se envía un mensaje
            indicadorNuevo.style.display = 'none';

        } else {
            // ❌ ERROR DEL SERVIDOR: MANEJA cuando el servidor rechaza el mensaje
            // ESTO LIMPIA el mensaje temporal y restaura el formulario
            div.remove(); // ELIMINA el mensaje temporal del chat
            mostrarAlerta('error', respuesta.error || 'No se pudo enviar el mensaje.', 'Error');

            // RESTAURA el contenido del formulario para que el usuario pueda reintentarlo
            textarea.value = mensaje; // DEVUELVE el texto al textarea
            archivosAdjuntos = archivosParaEnviar; // RESTAURA los archivos
            renderizarArchivosAdjuntos(); // ACTUALIZA la vista de archivos
        }
    } catch (error) {
        // ❌ ERROR DE RED: MANEJA problemas de conexión
        // ESTO SUCEDE cuando no hay internet o el servidor no responde
        div.remove(); // ELIMINA el mensaje temporal
        mostrarAlerta('error', 'Error de conexión. Intenta nuevamente.', 'Error');

        // RESTAURA el contenido del formulario
        textarea.value = mensaje;
        archivosAdjuntos = archivosParaEnviar;
        renderizarArchivosAdjuntos();
    } finally {
        // 🔓 LIBERA el bloqueo para permitir nuevos envíos
        enviandoMensaje = false;
    }
});

// ✅ Filtros por estado / tipo
document.getElementById('tabsFiltros')?.addEventListener('click', e => {
    if (!e.target.matches('button[data-filtro]')) return;

    document.querySelectorAll('#tabsFiltros .btn').forEach(b => b.classList.remove('active'));
    e.target.classList.add('active');

    // ✅ Limpiar el campo de búsqueda
    document.getElementById('buscadorSolicitudes').value = '';
    document.getElementById('mensajeBusquedaSinResultados')?.classList.add('d-none');

    const filtro = e.target.dataset.filtro;
    const tarjetas = document.querySelectorAll('#bandejaUnificada .card-solicitud');

    tarjetas.forEach(card => {
        const tipo = card.dataset.tipo;
        const estado = card.dataset.estado;
        const tags = (card.dataset.tags || '').split(',');
        const coincide = filtro === 'todas' || filtro === tipo || filtro === estado;
        card.style.display = coincide ? 'block' : 'none';
    });

    // ✅ Mostrar mensaje por pestaña si no hay tarjetas visibles
    const visibles = [...tarjetas].some(card => card.style.display !== 'none');
    document.querySelectorAll('[data-mensaje-vacio]').forEach(p => p.classList.add('d-none'));
    if (!visibles) {
        document.querySelector(`[data-mensaje-vacio="${filtro}"]`)?.classList.remove('d-none');
    }
});

// 🔍 Filtro por texto
document.getElementById('buscadorSolicitudes')?.addEventListener('input', e => {
    const valor = e.target.value.toLowerCase();
    const tarjetas = document.querySelectorAll('#bandejaUnificada .card-solicitud');
    const mensajeBusqueda = document.getElementById('mensajeBusquedaSinResultados');

    // Ocultar todos los mensajes de filtro por pestaña
    document.querySelectorAll('[data-mensaje-vacio]').forEach(p => p.classList.add('d-none'));

    let hayCoincidencias = false;

    tarjetas.forEach(card => {
        const texto = card.textContent.toLowerCase();
        const coincide = texto.includes(valor);
        card.style.display = coincide ? 'block' : 'none';
        if (coincide) hayCoincidencias = true;
    });

    mensajeBusqueda.classList.toggle('d-none', hayCoincidencias);

    // ✅ Restaurar mensaje por pestaña si se borra el texto y no hay resultados visibles
    if (!valor) {
        const filtroActivo = document.querySelector('#tabsFiltros .btn.active')?.dataset.filtro || 'todas';
        const mensajeFiltro = document.querySelector(`[data-mensaje-vacio="${filtroActivo}"]`);

        const visibles = [...tarjetas].some(card => card.style.display !== 'none');
        if (!visibles) mensajeFiltro?.classList.remove('d-none');
    }
});

let listaEquipos = [];
const checkboxTodos = document.getElementById('enviarATodos');

// Enviar solicitud
document.getElementById('formNuevaSolicitud')?.addEventListener('submit', async e => {
    e.preventDefault();

    const btnEnviar = e.target.querySelector('button[type="submit"]');
    const iconoOriginal = btnEnviar.innerHTML;

    // 🔁 Mostrar spinner y bloquear
    btnEnviar.disabled = true;
    btnEnviar.innerHTML = `
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        <span class="visually-hidden">Enviando...</span>
    `;

    await enviarSolicitud(); // ya contiene validaciones y feedback

    // Restaurar botón
    btnEnviar.disabled = false;
    btnEnviar.innerHTML = iconoOriginal;
});

// Mostrar menú contextual en clic derecho o touch
const menu = document.getElementById('menuContextual');
let solicitudActiva = null;

const mostrarMenu = (evento, card) => {
    evento.preventDefault?.();
    solicitudActiva = card;

    const estado = card.dataset.estado;
    const equipoEmisor = card.dataset.equipoEmisor;
    const equipoUsuario = document.querySelector('main')?.dataset.team;
    const rol = card.dataset.rol; // 'emisor' o 'receptor'

    const contenedorMenu = document.getElementById('menuContextual');

    // Limpiar cualquier botón de eliminación previo
    contenedorMenu.querySelectorAll('.item-eliminar')?.forEach(e => e.remove());

    const puedeEliminar = (estado === 'resuelta' || estado === 'rechazada');
    const tooltip = rol === 'emisor'
        ? 'Eliminar para el equipo que envió la solicitud'
        : 'Eliminar para el equipo receptor (no la verás más)';

    const btnEliminar = document.createElement('button');
    btnEliminar.className = `dropdown-item d-flex align-items-center gap-2 ${puedeEliminar ? 'text-danger' : 'text-muted'} item-eliminar`;
    btnEliminar.id = 'opcionEliminar';
    btnEliminar.disabled = !puedeEliminar;
    //
    btnEliminar.innerHTML = `Eliminar`;

    contenedorMenu.appendChild(btnEliminar);

    const x = evento.pageX ?? evento.touches?.[0]?.pageX;
    const y = evento.pageY ?? evento.touches?.[0]?.pageY;

    if (x && y) {
        menu.style.display = 'block';
        menu.style.left = `${x}px`;
        menu.style.top = `${y}px`;
    }
};

document.addEventListener('click', () => menu.style.display = 'none');
document.addEventListener('scroll', () => menu.style.display = 'none');

document.querySelectorAll('.card-solicitud').forEach(card => {
    card.addEventListener('contextmenu', e => mostrarMenu(e, card));
    let presionado;
    card.addEventListener('touchstart', e => {
        presionado = setTimeout(() => mostrarMenu(e, card), 600);
    });
    card.addEventListener('touchend', () => clearTimeout(presionado));
});

document.addEventListener('click', async e => {
    if (e.target.id === 'opcionEliminar') {
        if (!solicitudActiva) return;
        const id = solicitudActiva.dataset.id;
        const estado = solicitudActiva.dataset.estado;

        confirmarAccion('¿Eliminar esta solicitud?', async () => {
            const resp = await eliminarSolicitud(id);
            if (resp.success) {
                solicitudActiva.remove();
                mostrarAlerta('success', 'La solicitud fue eliminada.', 'Eliminada');
            } else {
                mostrarAlerta('error', resp.error || 'No se pudo eliminar.', 'Error');
            }
        });
        menu.style.display = 'none';
    }
});

// Inicializar select2 y cargar opciones
const inicializarSelectEquipos = async (equipos = null) => {
    if (!equipos) {
        equipos = await cargarEquiposDisponibles();
    }
    const select = document.getElementById('selectEquipos');

    select.innerHTML = ''; // Limpiar por si ya tenía algo

    equipos.forEach(e => {
        const option = document.createElement('option');
        option.value = e.id_equipo;
        option.textContent = e.alias;
        select.appendChild(option);
    });

    // Inicializar select2
    $('#selectEquipos').select2({
        dropdownParent: $('#modalNuevaSolicitud'),
        placeholder: 'Seleccionar equipos',
        width: '100%'
    });
};

// Habilitar o deshabilitar el select2 si se marca "Enviar a todos"
document.getElementById('enviarATodos')?.addEventListener('change', e => {
    const desactivado = e.target.checked;
    $('#selectEquipos').prop('disabled', desactivado).trigger('change.select2');
});

// Control de polling
let ultimoIdMensaje = 0;

// Iniciar polling en la bandeja
const iniciarPollingBandeja = () => {
    //console.log('⏳ Iniciando polling para la bandeja');
    clearInterval(temporizadorPolling);
    verificarNuevasSolicitudes();
    temporizadorPolling = setInterval(verificarNuevasSolicitudes, intervaloPolling);
};

// Detener polling en la bandeja
const detenerPollingBandeja = () => {
    //console.log('🛑 Deteniendo polling de la bandeja');
    clearInterval(temporizadorPolling);
    temporizadorPolling = null; // Aseguramos que no haya referencias residuales
};

// Iniciar polling en el chat de detalle
let intervaloChat = 20000;
let temporizadorChat = null;
const iniciarPollingChat = (idSolicitud) => {
    //console.log('⏳ Iniciando polling para el chat de solicitud:', idSolicitud);
    solicitudActiva = idSolicitud;

    const chat = document.getElementById('contenedorChat');
    const mensajes = chat.querySelectorAll('.d-flex[data-idmensaje]');
    ultimoIdMensaje = mensajes.length ? mensajes[mensajes.length - 1].dataset.idmensaje : 0;

    const pollingChat = async () => {
        const nuevosMensajes = await verificarNuevosMensajes(solicitudActiva, ultimoIdMensaje);
        if (nuevosMensajes.length > 0) {
            nuevosMensajes.forEach(msg => {
                renderizarMensaje(msg);
                ultimoIdMensaje = msg.id_mensaje;
            });
            intervaloChat = 5000;
        } else {
            intervaloChat = Math.min(30000, intervaloChat + 2000);
        }

        clearInterval(temporizadorChat);
        temporizadorChat = setInterval(pollingChat, intervaloChat);
    };

    clearInterval(temporizadorChat);
    pollingChat();
};

// Detener polling en el chat
const detenerPollingChat = () => {
    //console.log('🛑 Deteniendo polling del chat');
    solicitudActiva = null;
    clearInterval(temporizadorChat);
    temporizadorChat = null; // Aseguramos que no haya referencias residuales
};
window.addEventListener('DOMContentLoaded', async () => {
    await cargarConfigArchivos();

    const checkboxPrivada = document.getElementById('checkPrivada');
    const idEquipoUsuario = document.querySelector('main')?.dataset.team;
    if (!idEquipoUsuario && checkboxPrivada) {
        checkboxPrivada.checked = true;
        checkboxPrivada.disabled = true;
        checkboxPrivada.closest('.form-check')?.classList.add('text-muted');
        const avisoPrivada = document.getElementById('avisoPrivada');
        if (avisoPrivada) avisoPrivada.style.display = 'block';
    }

    listaEquipos = await cargarEquiposDisponibles(); // ✅ OK
    await inicializarSelectEquipos(listaEquipos); // ⬅️ agregar esta línea
    await renderizarSolicitudes(); // ✅ ya actualizado con nuevas cards
    // Enviar con Enter (sin Shift)
    const textareaRespuesta = document.querySelector('#formRespuesta textarea');
    textareaRespuesta?.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('formRespuesta').requestSubmit();
        }
    });

    // 🔄 Ajuste dinámico del botón flotante de solicitudes
    const ajustarBotonFlotanteSolicitud = () => {
        const barraInferior = document.getElementById('barraInferior');
        const boton = document.getElementById('btnNuevaSolicitud');

        if (!barraInferior || !boton) return;

        const visible = window.getComputedStyle(barraInferior).display !== 'none';
        boton.style.bottom = visible ? '72px' : '1rem'; // subirlo si hay barra
    };

    ajustarBotonFlotanteSolicitud();
    window.addEventListener('resize', ajustarBotonFlotanteSolicitud);

    // Ocultar botón si se abre un modal
    const botonFlotante = document.getElementById('btnNuevaSolicitud');

    if (botonFlotante) {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('show.bs.modal', () => {
                botonFlotante.style.display = 'none';
            });

            modal.addEventListener('hidden.bs.modal', () => {
                ajustarBotonFlotanteSolicitud(); // mostrar y reubicar
                botonFlotante.style.display = 'block';
            });
        });
    }

});

const menuMensaje = document.getElementById('menuContextualMensaje');
let mensajeActivo = null;

// Mostrar menú contextual en mensaje
const mostrarMenuMensaje = (e, div) => {
    e.preventDefault();
    mensajeActivo = div;

    const x = e.pageX ?? e.touches?.[0]?.pageX;
    const y = e.pageY ?? e.touches?.[0]?.pageY;

    if (x && y) {
        menuMensaje.style.display = 'block';
        menuMensaje.style.left = `${x}px`;
        menuMensaje.style.top = `${y}px`;
    }
};

document.addEventListener('click', () => {
    menuMensaje.style.display = 'none';
    mensajeActivo = null;
});

document.getElementById('opcionEliminarMensaje')?.addEventListener('click', async () => {
    if (!mensajeActivo) return;

    const idMensaje = mensajeActivo.dataset.idMensaje;
    const idSolicitud = document.getElementById('detalleSolicitud')?.dataset.idSolicitud;

    if (!idMensaje || !idSolicitud) return;

    confirmarAccion('¿Eliminar este mensaje?', async () => {
        const resultado = await eliminarMensajeSolicitud(idMensaje);
        if (resultado.success) {
            await mostrarDetalle({ dataset: { id: idSolicitud } });
        } else {
            mostrarAlerta('error', resultado.error || 'No se pudo eliminar el mensaje.', 'Error');
        }
    });

    menuMensaje.style.display = 'none';
    mensajeActivo = null;
});

window.addEventListener('beforeunload', () => {
    detenerPollingBandeja();
    detenerPollingChat();
});



// Al hacer clic en el indicador "Nuevo"
indicadorNuevo.addEventListener('click', () => {
    const chat = document.getElementById('contenedorChat');
    chat.scrollTop = chat.scrollHeight;
    indicadorNuevo.style.display = 'none';
});