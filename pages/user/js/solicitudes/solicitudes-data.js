// cargar archivos 
let configArchivos = {
    extensiones_permitidas: [],
    tamano_maximo_bytes: 10 * 1024 * 1024 // fallback por si falla la carga
};

const cargarConfigArchivos = async () => {
    try {
        const resp = await fetch('../../backend/config/config_archivos.json');
        const data = await resp.json();
        configArchivos = data;
    } catch (e) {
        console.warn('No se pudo cargar la configuración de archivos. Se usan valores por defecto.');
    }
};




$(document).on('change', '#privado_checkbox', function () {
    console.log($('#privado_valor_enviado').val());

    if ($(this).is(':checked')) {
        $('#privado_valor_enviado').val('1');
    } else {
        $('#privado_valor_enviado').val('0');
    }
});


const enviarSolicitud = async () => {
    const btn = document.querySelector('#formNuevaSolicitud button[type="submit"]');
    const mensajeError = document.getElementById('mensajeErrorSolicitud');
    const checkboxPrivado = document.getElementById('privado_checkbox'); // Obtener el checkbox
    mensajeError.style.display = 'none';
    mensajeError.textContent = '';

    const asunto = document.getElementById('inputAsunto').value.trim();
    const contenido = document.getElementById('inputMensaje').value.trim();
    const enviarATodos = document.getElementById('enviarATodos').checked;
    const Privado = document.getElementById('privado_valor_enviado').value;
    console.log(Privado);
    const equiposSeleccionados = $('#selectEquipos').val(); // array
    const archivos = document.getElementById('inputArchivo').files;

    if (!asunto || !contenido) {
        mensajeError.textContent = 'Completá el asunto y el mensaje.';
        mensajeError.style.display = 'block';
        return;
    }

    if (!enviarATodos && (!equiposSeleccionados || equiposSeleccionados.length === 0)) {
        mensajeError.textContent = 'Seleccioná al menos un equipo o marcá "Enviar a todos".';
        mensajeError.style.display = 'block';
        return;
    }

    // 🔒 Bloquear botón
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    checkboxPrivado.disabled = true;
    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Enviando...
    `;



    const extensionesPermitidas = configArchivos.extensiones_permitidas;
    const maxTamano = configArchivos.tamano_maximo_bytes;

    const formData = new FormData();
    formData.append('asunto', asunto);
    formData.append('contenido', contenido);
    formData.append('enviar_a_todos', enviarATodos ? '1' : '0');
    formData.append('privado', Privado);

    if (!enviarATodos) {
        equiposSeleccionados.forEach(id => {
            formData.append('equipos[]', id);
        });
    }

    for (let archivo of archivos) {
        const extension = archivo.name.split('.').pop().toLowerCase();
        if (!extensionesPermitidas.includes(extension)) {
            mensajeError.textContent = `Archivo "${archivo.name}" tiene una extensión no permitida.`;
            mensajeError.style.display = 'block';
            return;
        }

        if (archivo.size > maxTamano) {
            mensajeError.textContent = `Archivo "${archivo.name}" supera el tamaño máximo de 10MB.`;
            mensajeError.style.display = 'block';
            return;
        }

        formData.append('archivo[]', archivo);
    }


    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/crear_solicitud.php', {
            method: 'POST',
            body: formData
        });

        const data = await resp.json();
        console.log('📨 Respuesta del servidor:', data);

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalNuevaSolicitud'))?.hide();
            document.getElementById('formNuevaSolicitud').reset();
            $('#selectEquipos').val(null).trigger('change');

            mostrarAlerta('success', 'Tu solicitud fue enviada correctamente.', 'Éxito');
            await renderizarSolicitudes();
        } else {
            mostrarAlerta('error', data.error || 'Error al enviar la solicitud.', 'Error');
        }
    } catch (err) {
        console.error('❌ Error en el envío:', err);
        mostrarAlerta('error', 'Ocurrió un problema inesperado.', 'Error del servidor');
    } finally {
        btn.disabled = false;
        checkboxPrivado.disabled = false;

        btn.innerHTML = textoOriginal;
    }
};

// Cargar equipos activos (excepto el propio)
const cargarEquiposDisponibles = async () => {
    const idEquipoUsuario = document.querySelector('main')?.dataset.team;
    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/listar_equipos_activos.php');
        const data = await resp.json();
        if (data.success && Array.isArray(data.equipos)) {
            return data.equipos.filter(e => e.id_equipo != idEquipoUsuario);
        }
    } catch (err) {
        console.error('Error al obtener equipos disponibles:', err);
    }
    return [];
};

// listar solicitudes en bandeja de entrada
const obtenerSolicitudes = async () => {
    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/listar_solicitudes.php');
        const data = await resp.json();
        if (data.success && Array.isArray(data.solicitudes)) {
            return data.solicitudes;
        } else {
            console.warn(data.error || 'Error al obtener solicitudes');
            return [];
        }
    } catch (err) {
        console.error('Error al obtener solicitudes:', err);
        return [];
    }
};

// obtener deatalles de las solicitudes
const obtenerDetalleSolicitud = async (id_solicitud) => {
    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/detalle_solicitud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id_solicitud })
        });
        const data = await resp.json();
        return data;
    } catch (error) {
        console.error('Error al obtener detalle:', error);
        return { success: false, error: 'Error de conexión con el servidor.' };
    }
};

// Enviar mensaje (con o sin archivos)
const enviarMensajeSolicitud = async (id_solicitud, mensaje, archivos = []) => {
    const formData = new FormData();
    formData.append('id_solicitud', id_solicitud);
    formData.append('mensaje', mensaje);

    for (let archivo of archivos) {
        formData.append('archivo[]', archivo);
    }

    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/enviar_mensajes.php', {
            method: 'POST',
            body: formData
        });

        /* return await resp.json(); */
        const texto = await resp.text(); // 🧪 Agregado temporal
        console.log('📥 Respuesta cruda del servidor:', texto);

        return JSON.parse(texto); // en vez de resp.json() para ver el error exacto
    } catch (err) {
        console.error('Error al enviar mensaje:', err);
        return { success: false, error: 'Error del servidor al enviar el mensaje.' };
    }
};

// Eliminar mensaje
const eliminarMensajeSolicitud = async (id_mensaje) => {
    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/eliminar_mensaje.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id_mensaje })
        });

        return await resp.json();
    } catch (error) {
        console.error('Error al eliminar mensaje:', error);
        return { success: false, error: 'Error de conexión con el servidor.' };
    }
};

const actualizarEstadoSolicitud = async (idSolicitud, nuevoEstado) => {
    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/actualizar_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                id_solicitud: idSolicitud,
                estado: nuevoEstado
            })
        });

        return await resp.json();
    } catch (err) {
        console.error('Error al actualizar estado:', err);
        return { success: false, error: 'Error de red o del servidor.' };
    }
};

const eliminarSolicitud = async (id_solicitud) => {
    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/eliminar_solicitud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id_solicitud })
        });
        return await resp.json();
    } catch (err) {
        console.error('Error al eliminar solicitud:', err);
        return { success: false, error: 'Error del servidor.' };
    }
};

const marcarSolicitudComoLeida = async (idSolicitud) => {
    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/marcar_leido.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id_solicitud: idSolicitud })
        });
        return await resp.json();
    } catch (e) {
        console.warn('No se pudo marcar como leída la solicitud:', e);
        return { success: false };
    }
};

// Polling adaptativo - Intervalo inicial (20s) y máximo (30s)
let intervaloPolling = 20000;
const intervaloMin = 5000;
const intervaloMax = 30000;
let temporizadorPolling;

// Verificar nuevas solicitudes
const verificarNuevasSolicitudes = async () => {
    //console.log("Ejecutando verificarNuevasSolicitudes...");
    try {
        const resp = await fetch('../../backend/controller/usuario/solicitudes/verificar_nuevas_solicitudes.php');
        const data = await resp.json();

        if (data.success && data.nuevas.length > 0) {
           // console.log('📥 Nuevas solicitudes:', data.nuevas);
            // Actualizar contadores de mensajes no leídos
            data.nuevas.forEach(solicitud => {
                const card = document.querySelector(`.card-solicitud[data-id='${solicitud.id_solicitud}']`);
                if (card) {
                    let badge = card.querySelector('.bg-primary');

                    if (solicitud.cantidad_mensajes_nuevos > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'bg-primary text-white fw-semibold d-inline-flex align-items-center justify-content-center mt-1';
                            badge.style.width = '20px';
                            badge.style.height = '20px';
                            badge.style.fontSize = '0.75rem';
                            badge.style.borderRadius = '50%';
                            card.querySelector('.text-end').appendChild(badge);
                        }
                        badge.textContent = solicitud.cantidad_mensajes_nuevos;
                        badge.style.display = 'inline-block';
                    } else if (badge) {
                        badge.style.display = 'none';
                    }
                }
            });
            intervaloPolling = intervaloMin; // Reducir intervalo si hay actividad
        } else {
            intervaloPolling = Math.min(intervaloMax, intervaloPolling + 2000); // Aumentar intervalo gradualmente
        }

    } catch (err) {
        console.error('Error al verificar nuevas solicitudes:', err);
    }

    // Reiniciar el polling con el nuevo intervalo
    clearInterval(temporizadorPolling);
    temporizadorPolling = setInterval(verificarNuevasSolicitudes, intervaloPolling);
};

// Verificar nuevos mensajes en un chat abierto
const verificarNuevosMensajes = async (idSolicitud, ultimoIdMensaje) => {
    try {
        const resp = await fetch(`../../backend/controller/usuario/solicitudes/verificar_nuevos_mensajes.php?id_solicitud=${idSolicitud}&ultimo_id=${ultimoIdMensaje}`);
        const data = await resp.json();

        if (data.success && data.nuevos.length > 0) {
           // console.log('📥 Nuevos mensajes:', data.nuevos);
            intervaloPolling = intervaloMin; // Reducir intervalo si hay actividad
            return data.nuevos;
        }

        intervaloPolling = Math.min(intervaloMax, intervaloPolling + 2000); // Aumentar intervalo gradualmente
        return [];

    } catch (err) {
        console.error('Error al verificar nuevos mensajes:', err);
        return [];
    }
};

// Iniciar polling de solicitudes al cargar la página
window.addEventListener('DOMContentLoaded', () => {
    verificarNuevasSolicitudes();
});


