// Admin Solicitudes UI - Versión solo lectura sin interacciones

let equipoSeleccionado = null;

// Inicializar selector de equipos
const inicializarSelectorEquipos = async () => {
    const selectEquipo = document.getElementById('selectEquipoAdmin');
    if (!selectEquipo) {
        console.error('❌ No se encontró el elemento selectEquipoAdmin');
        return;
    }

    try {
        console.log('🔄 Cargando equipos...');
        const equipos = await cargarEquiposAdmin();
        
        console.log('📋 Equipos recibidos:', equipos);
        
        // Limpiar y agregar opción por defecto
        selectEquipo.innerHTML = '<option value="">Seleccionar equipo...</option>';
        
        if (equipos && equipos.length > 0) {
            equipos.forEach(equipo => {
                const option = document.createElement('option');
                option.value = equipo.id_equipo;
                option.textContent = equipo.alias;
                selectEquipo.appendChild(option);
            });
            console.log('✅ Se agregaron', equipos.length, 'equipos al selector');
        } else {
            selectEquipo.innerHTML = '<option value="">No hay equipos disponibles</option>';
            console.warn('⚠️ No se encontraron equipos');
        }

        // Event listener para cambio de equipo
        selectEquipo.addEventListener('change', async (e) => {
            equipoSeleccionado = e.target.value;
            const buscador = document.getElementById('buscadorSolicitudes');
            
            if (equipoSeleccionado) {
                // Habilitar buscador cuando se selecciona un equipo
                buscador.disabled = false;
                buscador.placeholder = 'Buscar por asunto, equipo o nombre del agente...';
                await renderizarSolicitudesAdmin();
            } else {
                // Deshabilitar buscador cuando no hay equipo seleccionado
                buscador.disabled = true;
                buscador.placeholder = 'Primero selecciona un equipo para habilitar la búsqueda...';
                buscador.value = ''; // Limpiar búsqueda
                // Limpiar bandeja si no hay equipo seleccionado
                document.getElementById('bandejaUnificada').innerHTML = '';
                mostrarMensajeSeleccionEquipo();
            }
        });

    } catch (err) {
        console.error('❌ Error al inicializar selector de equipos:', err);
        selectEquipo.innerHTML = '<option value="">Error al cargar equipos</option>';
        mostrarAlerta('error', 'Error al cargar la lista de equipos: ' + err.message, 'Error');
    }
};

// Mostrar mensaje inicial para seleccionar equipo
const mostrarMensajeSeleccionEquipo = () => {
    const contenedor = document.getElementById('bandejaUnificada');
    contenedor.innerHTML = `
        <div class="text-center text-muted mt-5">
            <i class="bi bi-arrow-up fs-2 text-primary"></i>
            <h5 class="mt-2">Selecciona un equipo</h5>
            <p>Elige un equipo del selector de arriba para ver sus solicitudes.</p>
        </div>
    `;
};

// Renderizar solicitudes (adaptado del módulo original)
const renderizarSolicitudesAdmin = async () => {
    const contenedor = document.getElementById('bandejaUnificada');
    const spinner = document.getElementById('spinnerCarga');
    const mensajeError = document.getElementById('mensajeErrorSolicitudes');

    console.log('🎯 Iniciando renderizado de solicitudes...');
    console.log('🎯 Equipo seleccionado:', equipoSeleccionado);

    if (!equipoSeleccionado) {
        console.log('⚠️ No hay equipo seleccionado');
        mostrarMensajeSeleccionEquipo();
        return;
    }

    // Reset visual
    contenedor.innerHTML = '';
    spinner.classList.remove('d-none');
    mensajeError.classList.add('d-none');
    document.querySelectorAll('[data-mensaje-vacio]').forEach(p => p.classList.add('d-none'));

    try {
        console.log('📞 Llamando a obtenerSolicitudesAdmin...');
        const solicitudes = await obtenerSolicitudesAdmin(equipoSeleccionado);
        console.log('📋 Solicitudes recibidas:', solicitudes);

        spinner.classList.add('d-none');

        if (!solicitudes || solicitudes.length === 0) {
            console.log('📭 No hay solicitudes para mostrar');
            contenedor.innerHTML = `
                <div class="text-center text-muted mt-4">
                    <i class="bi bi-inbox fs-2"></i>
                    <h5 class="mt-2">No hay solicitudes</h5>
                    <p>Este equipo no tiene solicitudes registradas.</p>
                </div>
            `;
            return;
        }

        console.log('🏗️ Construyendo', solicitudes.length, 'tarjetas...');
        solicitudes.forEach((solicitud, index) => {
            console.log(`📄 Procesando solicitud ${index + 1}:`, solicitud.asunto);
            
            const card = document.createElement('div');
            card.className = 'card card-solicitud mb-3 border-0 shadow-sm';
            card.dataset.id = solicitud.id_solicitud;
            card.dataset.estado = solicitud.estado;
            card.dataset.tipo = solicitud.tipo;
            // Agregar datos para búsqueda específica
            card.dataset.asunto = solicitud.asunto.toLowerCase();
            card.dataset.emisor = `${solicitud.nombre_emisor} ${solicitud.apellido_emisor}`.toLowerCase();
            card.dataset.equipoEmisor = solicitud.equipo_emisor.toLowerCase();
            card.dataset.equiposDestino = (solicitud.equipos_destino || '').toLowerCase();

            const estadoClass = getEstadoClass(solicitud.estado);
            const tipoIcon = solicitud.tipo === 'enviadas' ? 'bi-arrow-up' : 'bi-arrow-down';
            const tipoColor = solicitud.tipo === 'enviadas' ? 'text-success' : 'text-primary';

            card.innerHTML = `
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <!-- TÍTULO CON ESTADO Y BOTÓN - Permite salto de línea para asuntos largos -->
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <div class="d-flex align-items-center gap-2 flex-grow-1 min-width-0">
                                    <i class="bi ${tipoIcon} ${tipoColor} flex-shrink-0"></i>
                                    <h6 class="card-title mb-0 flex-grow-1">${solicitud.asunto}</h6>
                                </div>
                                <!-- ESTADO Y BOTÓN AGRUPADOS - Se mantienen juntos en desktop, se van abajo en móvil -->
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <span class="badge ${estadoClass}">${capitalizar(solicitud.estado)}</span>
                                    <button class="btn btn-outline-primary btn-sm" onclick="mostrarDetalleAdmin(this.closest('.card-solicitud'))">
                                        <i class="bi bi-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center text-muted small mb-1">
                                <i class="bi bi-person me-1"></i>
                                <span class="me-3">${solicitud.nombre_emisor} ${solicitud.apellido_emisor} (${solicitud.equipo_emisor})</span>
                                <i class="bi bi-arrow-right me-1"></i>
                                <span>${solicitud.equipos_destino || 'Sin destinatarios'}</span>
                            </div>
                            
                            <div class="d-flex align-items-center text-muted small">
                                <i class="bi bi-calendar me-1"></i>
                                <span class="me-3">${solicitud.fecha_creacion}</span>
                                ${solicitud.total_mensajes > 0 ? `
                                    <i class="bi bi-chat-dots me-1"></i>
                                    <span>${solicitud.total_mensajes} mensaje${solicitud.total_mensajes !== 1 ? 's' : ''}</span>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            contenedor.appendChild(card);
            console.log(`✅ Tarjeta ${index + 1} agregada al DOM`);
        });

        console.log('🎉 Renderizado completo:', solicitudes.length, 'solicitudes mostradas');

    } catch (err) {
        console.error('❌ Error en renderizado:', err);
        spinner.classList.add('d-none');
        mensajeError.classList.remove('d-none');
        mensajeError.textContent = 'Error al cargar solicitudes: ' + err.message;
    }
};

// Mostrar detalle de solicitud (modo solo lectura)
const mostrarDetalleAdmin = async (card) => {
    const idSolicitud = card.dataset.id;
    if (!idSolicitud) return;

    try {
        const respuesta = await obtenerDetalleSolicitudAdmin(idSolicitud);
        
        if (!respuesta.success) {
            mostrarAlerta('error', respuesta.error || 'Error al cargar el detalle.', 'Error');
            return;
        }

        const { solicitud, destino, chat } = respuesta;

        // Actualizar información del detalle
        document.getElementById('detalleAsunto').textContent = solicitud.asunto;
        document.getElementById('detalleDestino').textContent = destino;

        // Estado como badge fijo (no editable en modo admin)
        const badgeEstado = document.getElementById('badgeEstado');
        const estadoClass = getEstadoClass(solicitud.estado);
        badgeEstado.textContent = capitalizar(solicitud.estado);
        badgeEstado.className = `badge badge-estado ${estadoClass}`;

        // Ocultar selector de estado y mostrar badge
        document.getElementById('contenedorSelectEstado').style.display = 'none';
        document.getElementById('contenedorBadgeEstado').classList.remove('d-none');

        // Renderizar chat (solo lectura)
        const contenedorChat = document.getElementById('contenedorChat');
        contenedorChat.innerHTML = '';
        contenedorChat.style.display = 'block';

        if (chat && chat.length > 0) {
            chat.forEach(mensaje => {
                renderizarMensajeAdmin(mensaje);
            });
            contenedorChat.scrollTop = contenedorChat.scrollHeight;
        } else {
            contenedorChat.innerHTML = '<p class="text-muted text-center">No hay mensajes en esta solicitud.</p>';
        }

        // Ocultar contenedor principal y mostrar detalle
        document.getElementById('contenedorSolicitudes').style.display = 'none';
        document.getElementById('detalleSolicitud').style.display = 'block';

    } catch (err) {
        console.error('Error al mostrar detalle:', err);
        mostrarAlerta('error', 'Error al cargar el detalle de la solicitud.', 'Error');
    }
};

// Renderizar mensaje en el chat (versión admin - solo lectura)
const renderizarMensajeAdmin = (msg) => {
    const chat = document.getElementById('contenedorChat');

    // Verificar si ya existe el mensaje
    const existente = chat.querySelector(`[data-id-mensaje="${msg.id_solicitudes_mensaje}"]`);
    if (existente) return;

    const div = document.createElement('div');
    div.className = 'd-flex mb-3';
    div.dataset.idMensaje = msg.id_solicitudes_mensaje;
    
    const burbuja = document.createElement('div');
    burbuja.className = 'bg-light border rounded p-2 px-3 w-100';

    const meta = document.createElement('div');
    meta.className = 'd-flex justify-content-between small text-muted';

    const nombreSpan = document.createElement('span');
    nombreSpan.className = 'flex-grow-1 me-2 fw-semibold';
    nombreSpan.textContent = msg.autor;

    const fecha = new Date(msg.fecha);
    const fechaTexto = `${String(fecha.getDate()).padStart(2, '0')}/${String(fecha.getMonth() + 1).padStart(2, '0')} ${String(fecha.getHours()).padStart(2, '0')}:${String(fecha.getMinutes()).padStart(2, '0')}`;

    const fechaSpan = document.createElement('span');
    fechaSpan.className = 'text-end';
    fechaSpan.textContent = fechaTexto;

    meta.appendChild(nombreSpan);
    meta.appendChild(fechaSpan);
    burbuja.appendChild(meta);

    // Mensaje de texto
    const contenido = document.createElement('div');
    contenido.className = 'mt-1';
    contenido.textContent = msg.mensaje;
    burbuja.appendChild(contenido);

    // Archivos adjuntos (si existen)
    if (Array.isArray(msg.archivos) && msg.archivos.length > 0) {
        const archivosDiv = document.createElement('div');
        archivosDiv.className = 'mt-2';
        
        msg.archivos.forEach(archivo => {
            const enlace = document.createElement('a');
            enlace.href = `../uploads/solicitudes/${archivo.ruta}`;
            enlace.target = '_blank';
            enlace.className = 'badge bg-secondary text-decoration-none me-1';
            enlace.innerHTML = `<i class="bi bi-paperclip"></i> ${archivo.nombre}`;
            archivosDiv.appendChild(enlace);
        });
        
        burbuja.appendChild(archivosDiv);
    }

    div.appendChild(burbuja);
    chat.appendChild(div);
};

// Funciones auxiliares (reutilizadas del módulo original)
const capitalizar = str => {
    if (typeof str !== 'string') return str;
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

const getEstadoClass = estado => {
    const est = estado?.toLowerCase();
    if (est === 'resuelta') return 'bg-success text-white';
    if (est === 'rechazada') return 'bg-danger text-white';
    return 'bg-warning text-dark';
};

// Botón volver (adaptado)
document.getElementById('btnVolver')?.addEventListener('click', () => {
    // Ocultar detalle y volver a la bandeja
    document.getElementById('detalleSolicitud').style.display = 'none';
    document.getElementById('contenedorSolicitudes').style.display = 'block';
    
    // Mostrar selector de estado y ocultar badge
    document.getElementById('contenedorSelectEstado').style.display = 'block';
    document.getElementById('contenedorBadgeEstado').classList.add('d-none');
    
    // Limpiar campos del detalle
    document.getElementById('detalleAsunto').textContent = '';
    document.getElementById('detalleDestino').textContent = '';
    document.getElementById('contenedorChat').innerHTML = '';
    document.getElementById('contenedorChat').style.display = 'none';
});

// Filtros por estado/tipo (adaptado para respetar búsqueda activa)
document.getElementById('tabsFiltros')?.addEventListener('click', e => {
    if (!e.target.matches('button[data-filtro]')) return;

    document.querySelectorAll('#tabsFiltros .btn').forEach(b => b.classList.remove('active'));
    e.target.classList.add('active');

    // NO limpiar búsqueda - mantener texto actual
    const buscador = document.getElementById('buscadorSolicitudes');
    const valorBusqueda = buscador ? buscador.value.toLowerCase().trim() : '';
    
    document.getElementById('mensajeBusquedaSinResultados')?.classList.add('d-none');

    const filtro = e.target.dataset.filtro;
    const tarjetas = document.querySelectorAll('#bandejaUnificada .card-solicitud');

    let hayCoincidencias = false;

    tarjetas.forEach(card => {
        // PASO 1: Verificar si coincide con el filtro de pestaña
        const coincideConPestana = coincideConFiltro(card, filtro);
        
        let coincideBusqueda = false;
        
        if (valorBusqueda === '') {
            // Si no hay búsqueda, mostrar según filtro de pestaña
            coincideBusqueda = true;
        } else {
            // PASO 2: Si coincide con pestaña, verificar búsqueda
            if (coincideConPestana) {
                const asunto = card.dataset.asunto || '';
                const emisor = card.dataset.emisor || '';
                const equipoEmisor = card.dataset.equipoEmisor || '';
                const equiposDestino = card.dataset.equiposDestino || '';
                
                const coincideAsunto = asunto.includes(valorBusqueda);
                const coincideEmisor = emisor.includes(valorBusqueda);
                const coincideEquipoEmisor = equipoEmisor.includes(valorBusqueda);
                const coincideEquipoDestino = equiposDestino.includes(valorBusqueda);
                
                coincideBusqueda = coincideAsunto || coincideEmisor || coincideEquipoEmisor || coincideEquipoDestino;
            }
        }
        
        // RESULTADO FINAL: debe coincidir con pestaña Y búsqueda
        const mostrar = coincideConPestana && coincideBusqueda;
        card.style.display = mostrar ? 'block' : 'none';
        
        if (mostrar) hayCoincidencias = true;
    });

    // Mostrar mensajes apropiados
    document.querySelectorAll('[data-mensaje-vacio]').forEach(p => p.classList.add('d-none'));
    
    if (valorBusqueda && !hayCoincidencias && equipoSeleccionado) {
        // Si hay búsqueda pero no hay resultados
        document.getElementById('mensajeBusquedaSinResultados')?.classList.remove('d-none');
    } else if (!valorBusqueda && !hayCoincidencias && equipoSeleccionado) {
        // Si no hay búsqueda pero tampoco hay resultados del filtro
        const mensajeElement = document.querySelector(`[data-mensaje-vacio="${filtro}"]`);
        if (mensajeElement) {
            mensajeElement.classList.remove('d-none');
        }
    }
});

// Función auxiliar para obtener el filtro activo
const obtenerFiltroActivo = () => {
    const botonActivo = document.querySelector('#tabsFiltros .btn.active');
    return botonActivo ? botonActivo.dataset.filtro : 'todas';
};

// Función auxiliar para verificar si una tarjeta coincide con el filtro activo
const coincideConFiltro = (card, filtro) => {
    if (filtro === 'todas') return true;
    
    const tipo = card.dataset.tipo;
    const estado = card.dataset.estado;
    
    return filtro === tipo || filtro === estado;
};

// Filtro por texto con criterios específicos (asunto, equipo, nombre del empleado)
// CONTEXTO: Solo busca dentro de las tarjetas que coinciden con la pestaña activa
document.getElementById('buscadorSolicitudes')?.addEventListener('input', e => {
    const valor = e.target.value.toLowerCase().trim();
    const tarjetas = document.querySelectorAll('#bandejaUnificada .card-solicitud');
    const mensajeBusqueda = document.getElementById('mensajeBusquedaSinResultados');
    const filtroActivo = obtenerFiltroActivo();

    // Ocultar mensajes de filtro por pestaña
    document.querySelectorAll('[data-mensaje-vacio]').forEach(p => p.classList.add('d-none'));

    let hayCoincidencias = false;

    tarjetas.forEach(card => {
        // PASO 1: Verificar si la tarjeta coincide con el filtro activo (pestaña)
        const coincideConPestana = coincideConFiltro(card, filtroActivo);
        
        let coincideBusqueda = false;
        
        if (valor === '') {
            // Si no hay texto de búsqueda, mostrar según filtro de pestaña
            coincideBusqueda = true;
        } else {
            // PASO 2: Si coincide con la pestaña, verificar búsqueda por texto
            if (coincideConPestana) {
                // Buscar en criterios específicos:
                const asunto = card.dataset.asunto || '';
                const emisor = card.dataset.emisor || '';
                const equipoEmisor = card.dataset.equipoEmisor || '';
                const equiposDestino = card.dataset.equiposDestino || '';
                
                // 1. Buscar en asunto
                const coincideAsunto = asunto.includes(valor);
                
                // 2. Buscar en nombre del empleado emisor
                const coincideEmisor = emisor.includes(valor);
                
                // 3. Buscar en equipos (tanto emisor como receptor)
                const coincideEquipoEmisor = equipoEmisor.includes(valor);
                const coincideEquipoDestino = equiposDestino.includes(valor);
                
                coincideBusqueda = coincideAsunto || coincideEmisor || coincideEquipoEmisor || coincideEquipoDestino;
            }
        }
        
        // RESULTADO FINAL: debe coincidir TANTO con la pestaña COMO con la búsqueda
        const mostrar = coincideConPestana && coincideBusqueda;
        card.style.display = mostrar ? 'block' : 'none';
        
        if (mostrar) hayCoincidencias = true;
    });

    // Mostrar mensaje si no hay coincidencias
    if (valor && !hayCoincidencias && equipoSeleccionado) {
        mensajeBusqueda?.classList.remove('d-none');
    } else {
        mensajeBusqueda?.classList.add('d-none');
        
        // Si no hay búsqueda pero tampoco hay coincidencias con el filtro, mostrar mensaje de filtro
        if (!valor && !hayCoincidencias && equipoSeleccionado) {
            const mensajeElement = document.querySelector(`[data-mensaje-vacio="${filtroActivo}"]`);
            if (mensajeElement) {
                mensajeElement.classList.remove('d-none');
            }
        }
    }
});

// Inicializar todo al cargar la página
window.addEventListener('DOMContentLoaded', async () => {
    await inicializarSelectorEquipos();
    mostrarMensajeSeleccionEquipo();
});