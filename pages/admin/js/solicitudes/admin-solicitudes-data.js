// Admin Solicitudes Data - Versión solo lectura sin polling

// Configuración de archivos (reutilizada del módulo original)
let configArchivos = {
    extensiones_permitidas: [],
    tamano_maximo_bytes: 10 * 1024 * 1024
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

// Cargar equipos para el selector de admin
const cargarEquiposAdmin = async () => {
    try {
        console.log('🌐 Haciendo petición a: ../../backend/controller/admin/solicitudes/listar_equipos_admin.php');
        
        const resp = await fetch('../../backend/controller/admin/solicitudes/listar_equipos_admin.php');
        console.log('📡 Respuesta HTTP:', resp.status, resp.statusText);
        
        if (!resp.ok) {
            throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);
        }
        
        const data = await resp.json();
        console.log('📦 Datos recibidos:', data);
        
        if (data.success && Array.isArray(data.equipos)) {
            console.log('✅ Equipos válidos:', data.equipos.length);
            return data.equipos;
        } else {
            console.warn('⚠️ Respuesta no válida:', data);
            if (data.debug) {
                console.log('🐛 Debug info:', data.debug);
            }
        }
    } catch (err) {
        console.error('❌ Error al obtener equipos:', err);
    }
    return [];
};

// Listar solicitudes de un equipo específico (modo admin)
const obtenerSolicitudesAdmin = async (equipoId) => {
    if (!equipoId) {
        return [];
    }

    try {
        console.log('🔄 Obteniendo solicitudes para equipo:', equipoId);
        
        // TEMPORAL: Usar controlador simplificado para debug
        const resp = await fetch(`../../backend/controller/admin/solicitudes/listar_solicitudes_simple.php?equipo=${equipoId}`);
        console.log('📡 Respuesta HTTP:', resp.status, resp.statusText);
        
        if (!resp.ok) {
            throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);
        }
        
        const data = await resp.json();
        console.log('📦 Datos de solicitudes recibidos:', data);
        
        if (data.success && Array.isArray(data.solicitudes)) {
            console.log('✅ Solicitudes válidas:', data.solicitudes.length);
            if (data.debug) {
                console.log('🐛 Debug info solicitudes:', data.debug);
            }
            return data.solicitudes;
        } else {
            console.warn('⚠️ Respuesta no válida:', data);
            if (data.debug) {
                console.log('🐛 Debug error:', data.debug);
            }
            return [];
        }
    } catch (err) {
        console.error('❌ Error al obtener solicitudes:', err);
        return [];
    }
};

// Obtener detalles de solicitud (modo admin - solo lectura)
const obtenerDetalleSolicitudAdmin = async (id_solicitud) => {
    try {
        console.log('🔄 Obteniendo detalle de solicitud:', id_solicitud);
        
        // TEMPORAL: Usar controlador simplificado para debug
        const resp = await fetch('../../backend/controller/admin/solicitudes/detalle_simple.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id_solicitud })
        });
        
        console.log('📡 Respuesta HTTP detalle:', resp.status, resp.statusText);
        
        if (!resp.ok) {
            throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);
        }
        
        const data = await resp.json();
        console.log('📦 Datos de detalle recibidos:', data);
        
        if (data.debug) {
            console.log('🐛 Debug info detalle:', data.debug);
        }
        
        return data;
    } catch (error) {
        console.error('❌ Error al obtener detalle:', error);
        return { success: false, error: 'Error de conexión con el servidor: ' + error.message };
    }
};

// NO hay funciones de envío, eliminación o actualización en modo admin
// Las siguientes funciones están comentadas para mayor claridad:

/*
// FUNCIONES NO DISPONIBLES EN MODO ADMIN:
// - enviarSolicitud()
// - enviarMensajeSolicitud()
// - eliminarMensajeSolicitud()
// - actualizarEstadoSolicitud()
// - eliminarSolicitud()
// - marcarSolicitudComoLeida()
// - verificarNuevasSolicitudes() 
// - verificarNuevosMensajes()
*/

// Cargar configuración al iniciar
window.addEventListener('DOMContentLoaded', () => {
    cargarConfigArchivos();
});