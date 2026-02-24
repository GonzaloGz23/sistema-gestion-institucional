// ==========================================
// DATOS Y FUNCIONES AJAX PARA EL BACKEND
// ==========================================

/**
 * MAPEO DE CAMPOS CRÍTICO - ALCANCE Y TIPO CAPACITACIÓN:
 * - Alcance: interno | estatal (campo alcance en BD)
 * - Tipo Capacitación: curso | taller (campo tipo_capacitacion en BD)
 * 
 * Ver revision-ui.js para mapeo completo Frontend ↔ Backend
 */

/*
 * TODO: PRÓXIMOS PASOS PARA IMPLEMENTACIÓN REAL
 * 
 * 1. BACKEND:
 *    ✅ listar_capacitaciones.php (GET con filtros por entidad) - COMPLETADO
 *    ✅ obtener_capacitacion.php (GET por ID) - COMPLETADO
 *    ✅ Sistema de categorías condicionales - COMPLETADO
 *    - editar_capacitacion.php (PUT para actualizar)
 *    - cambiar_estado.php (PATCH para estados)
 *    - generar_pdf.php y generar_word.php (POST para exportar)
 * 
 * 2. BASE DE DATOS:
 *    ✅ Usar conexión DatabaseManager para BD distribuidas - COMPLETADO
 *    ✅ Filtrar por id_entidad del usuario actual - COMPLETADO
 *    ✅ Implementar relación capacitaciones-equipos-entidades - COMPLETADO
 *    ✅ Consultas complejas para categorización jerárquica - COMPLETADO
 *    ✅ Obtener horarios y temas relacionados - COMPLETADO
 *    ⚠️ PENDIENTE: Agregar campos tipo_modalidad y lugar a BD
 * 
 * 3. SEGURIDAD:
 *    ✅ Validar permisos y autenticación - COMPLETADO
 *    - Sanitizar todos los inputs
 *    ✅ Respuestas JSON estandarizadas - COMPLETADO
 * 
 * 4. FRONTEND:
 *    ✅ cargarCapacitaciones() → AJAX real - COMPLETADO
 *    ✅ obtenerCapacitacion() → AJAX real - COMPLETADO
 *    ✅ Sistema de categorías condicionales - COMPLETADO
 *    ✅ Mapeo de datos reales al modal - COMPLETADO
 *    ✅ Formulario funcional con datos BD - COMPLETADO
 *    🔄 PRÓXIMO: Implementar guardado real (editar_capacitacion.php)
 *    - cambiarEstadoCapacitacion() → AJAX real
 * 
 * ESTADO ACTUAL: 
 * - ✅ Listado y modal funcionando con datos reales de BD
 * - ✅ Categorías condicionales implementadas y funcionando
 * - ✅ Formulario completo con mapeo correcto de campos
 * - ⚠️ Guardado aún simulado (próximo paso: backend de edición)
 */

// Variable global para almacenar el ID de la capacitación actual
let capacitacionActual = null;

// ==========================================
// FUNCIONES DE DATOS (AJAX REAL)
// ==========================================

// Cargar lista de capacitaciones (AJAX REAL - NUEVO)
const cargarCapacitaciones = async () => {
    try {
        // console.log('🔄 Solicitando lista de capacitaciones del servidor...');

        const response = await fetch('/sistema-gestion-institucional/backend/controller/admin/revision-capacitaciones/listar_capacitaciones.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin' // Incluir cookies de sesión
        });

        // console.log('📡 Respuesta del servidor (lista):', response.status, response.statusText);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Error del servidor (lista):', errorText);
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }

        const data = await response.json();

        // console.log('✅ CAPACITACIONES REALES RECIBIDAS:');
        // console.log(`📊 Total encontradas: ${data.capacitaciones?.length || 0}`);
        // console.log('📋 Primeras 3 capacitaciones como ejemplo:');
        // if (data.capacitaciones && data.capacitaciones.length > 0) {
        //     data.capacitaciones.slice(0, 3).forEach((cap, index) => {
        //         console.log(`🎯 Capacitación ${index + 1}:`);
        //         console.log(`   - ID: ${cap.id}`);
        //         console.log(`   - Nombre: ${cap.nombre}`);
        //         console.log(`   - Estado: ${cap.estado_nombre || 'N/A'}`);
        //         console.log(`   - Categoría: ${cap.tipo_categoria || 'N/A'}`);
        //         console.log(`   - Fecha Inicio: ${cap.fecha_inicio_cursada || 'N/A'}`);
        //         console.log(`   - Es Destacado: ${cap.es_destacado ? 'Sí' : 'No'}`);
        //     });
        // }

        if (data.success) {
            return {
                success: true,
                capacitaciones: data.capacitaciones // Ahora devolvemos datos reales
            };
        } else {
            return {
                success: false,
                error: data.error || 'Error desconocido del servidor'
            };
        }

    } catch (error) {
        console.error('❌ Error al cargar capacitaciones:', error);
        return {
            success: false,
            error: 'Error de conexión con el servidor: ' + error.message
        };
    }
};

// ==========================================
// FUNCIONES PENDIENTES DE MIGRAR (SIMULADAS)
// ==========================================

// Obtener capacitación por ID (AJAX REAL - NUEVO)
const obtenerCapacitacion = async (id) => {
    try {
        // console.log(`🔄 Obteniendo detalles de capacitación ID: ${id}...`);

        const response = await fetch(`/sistema-gestion-institucional/backend/controller/admin/revision-capacitaciones/obtener_capacitacion.php?id=${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin' // Incluir cookies de sesión
        });

        // console.log('📡 Respuesta del servidor (obtener):', response.status, response.statusText);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Error del servidor (obtener):', errorText);
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }

        const data = await response.json();

        // console.log('✅ DATOS COMPLETOS RECIBIDOS (TODOS LOS DETALLES):');
        // console.log('🎯 === INFORMACIÓN BÁSICA ===');
        // console.log('ID:', data.capacitacion?.id);
        // console.log('Nombre:', data.capacitacion?.nombre);
        // console.log('Slogan:', data.capacitacion?.slogan);
        // console.log('Objetivo:', data.capacitacion?.objetivo);
        // console.log('Qué aprenderás:', data.capacitacion?.que_aprenderas);
        // console.log('Destinatarios:', data.capacitacion?.destinatarios);
        // console.log('Requisitos:', data.capacitacion?.requisitos);

        // console.log('🏷️ === CATEGORIZACIÓN COMPLETA ===');
        // console.log('Tipo Capacitación:', data.capacitacion?.tipo_capacitacion);
        // console.log('Tipo Categoría:', data.capacitacion?.tipo_categoria);
        // console.log('Categoría ID:', data.capacitacion?.categoria_id);
        // console.log('Jerarquía Completa:', data.capacitacion?.categoria_completa);
        // console.log('Modalidad:', data.capacitacion?.tipo_modalidad);
        // console.log('Lugar:', data.capacitacion?.lugar);

        // console.log('📅 === FECHAS Y LOGÍSTICA ===');
        // console.log('Fecha Inscripción:', data.capacitacion?.fecha_inicio_inscripcion);
        // console.log('Fecha Inicio:', data.capacitacion?.fecha_inicio_cursada);
        // console.log('Fecha Fin:', data.capacitacion?.fecha_fin_cursada);
        // console.log('Duración Clase (min):', data.capacitacion?.duracion_clase_minutos);
        // console.log('Total Encuentros:', data.capacitacion?.total_encuentros);
        // console.log('Cupos Máximos:', data.capacitacion?.cupos_maximos);

        // console.log('⏰ === HORARIOS ===');
        // console.log('Cronograma:', data.capacitacion?.horarios);

        // console.log('📚 === TEMAS Y CONTENIDO ===');
        // console.log('Temas Organizados:', data.capacitacion?.temas);

        // console.log('⚙️ === GESTIÓN ===');
        // console.log('Equipo ID:', data.capacitacion?.equipo_id);
        // console.log('Equipo Nombre:', data.capacitacion?.equipo_nombre);
        // console.log('Estado ID:', data.capacitacion?.estado_id);
        // console.log('Estado:', data.capacitacion?.estado_nombre);
        // console.log('Imagen URL:', data.capacitacion?.imagen_url);
        // console.log('Link Inscripción:', data.capacitacion?.link_inscripcion);
        // console.log('Es Destacado:', data.capacitacion?.es_destacado);
        // console.log('Está Publicada:', data.capacitacion?.esta_publicada);

        // console.log('📊 === OBJETO COMPLETO ===');
        // console.log(data.capacitacion);

        if (data.success) {
            return {
                success: true,
                capacitacion: data.capacitacion // Ahora devolvemos los datos reales
            };
        } else {
            return {
                success: false,
                error: data.error || 'Error desconocido del servidor'
            };
        }

    } catch (error) {
        console.error('❌ Error al obtener capacitación:', error);
        return {
            success: false,
            error: 'Error de conexión con el servidor: ' + error.message
        };
    }
};

// Guardar cambios de capacitación (REAL)
const guardarCapacitacion = async (id, datos) => {
    try {
        console.log('📡 Enviando datos al backend:', datos);

        const response = await fetch('/sistema-gestion-institucional/backend/controller/admin/revision-capacitaciones/editar_capacitacion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include', // Para incluir cookies de sesión
            body: JSON.stringify({
                id: id,
                ...datos
            })
        });

        const resultado = await response.json();

        console.log('📡 Respuesta del backend:', resultado);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${resultado.error || 'Error del servidor'}`);
        }

        return resultado;

    } catch (error) {
        console.error('❌ Error al guardar capacitación:', error);
        return {
            success: false,
            error: error.message || 'Error de conexión con el servidor'
        };
    }
};

// Cambiar estado de publicación de capacitación
const cambiarEstadoPublicacionCapacitacion = async (id, nuevoEstado) => {
    try {
        const response = await fetch('/sistema-gestion-institucional/backend/controller/admin/revision-capacitaciones/cambiar_publicacion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin', // Incluir cookies de sesión
            body: JSON.stringify({
                id_capacitacion: parseInt(id),
                esta_publicada: nuevoEstado ? 1 : 0
            })
        });

        // Verificar que la respuesta sea exitosa
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Error del servidor:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Error desconocido');
        }

        return {
            success: true,
            message: data.message,
            esta_publicada: data.esta_publicada
        };

    } catch (error) {
        console.error('❌ Error al cambiar estado de publicación:', error);

        return {
            success: false,
            error: error.message || 'Error de conexión al servidor'
        };
    }
};

// Cambiar estado de destacado de capacitación
const cambiarEstadoDestacadoCapacitacion = async (id, nuevoEstado) => {
    try {
        const response = await fetch('/sistema-gestion-institucional/backend/controller/admin/revision-capacitaciones/cambiar_destacado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin', // Incluir cookies de sesión
            body: JSON.stringify({
                id_capacitacion: parseInt(id),
                es_destacado: nuevoEstado ? 1 : 0
            })
        });

        // Verificar que la respuesta sea exitosa
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Error del servidor:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Error desconocido');
        }

        return {
            success: true,
            message: data.message,
            es_destacado: data.es_destacado
        };

    } catch (error) {
        console.error('❌ Error al cambiar estado de destacado:', error);

        return {
            success: false,
            error: error.message || 'Error de conexión al servidor'
        };
    }
};


// Cambiar estado de capacitación (IMPLEMENTADO - BACKEND REAL)
const cambiarEstadoCapacitacion = async (id, nuevoEstado) => {
    try {
        const response = await fetch('/sistema-gestion-institucional/backend/controller/admin/revision-capacitaciones/cambiar_estado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin', // Incluir cookies de sesión
            body: JSON.stringify({
                id: parseInt(id),
                estado: nuevoEstado
            })
        });

        // Verificar que la respuesta sea exitosa
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || `Error HTTP: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Error desconocido');
        }

        return {
            success: true,
            message: data.message,
            estado_anterior: data.estado_anterior,
            estado_nuevo: data.estado_nuevo,
            capacitacion_id: data.capacitacion_id,
            timestamp: data.timestamp
        };

    } catch (error) {
        console.error('❌ Error al cambiar estado:', error);

        return {
            success: false,
            error: error.message || 'Error de conexión al servidor'
        };
    }
};

// ==========================================
// FUNCIONES DE UTILIDAD
// ==========================================

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const opciones = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(fecha).toLocaleDateString('es-AR', opciones);
};

const formatearEstado = (estado) => {
    // Mapeo para manejar estados tanto con espacios (BD) como sin espacios (frontend)
    const estados = {
        // Estados sin espacios (frontend)
        'borrador': { texto: 'Borrador', clase: 'bg-secondary' },
        'en_espera': { texto: 'En Espera', clase: 'bg-warning' },
        'en_revision': { texto: 'En Revisión', clase: 'bg-info' },
        'aprobado': { texto: 'Aprobado', clase: 'bg-success' },
        'cerrado': { texto: 'Cerrado', clase: 'bg-danger' },

        // Estados con espacios (BD)
        'en espera': { texto: 'En Espera', clase: 'bg-warning' },
        'en revisión': { texto: 'En Revisión', clase: 'bg-info' }
    };

    return estados[estado] || { texto: estado || 'Desconocido', clase: 'bg-secondary' };
};
