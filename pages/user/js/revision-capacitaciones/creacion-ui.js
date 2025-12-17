// ==========================================
// INTERFAZ DE USUARIO Y LÓGICA DEL FRONTEND
// ==========================================

// Variables globales
let tablaCapacitaciones;
let modalCapacitacion;
let categoriasManager; // Gestor de categorías

// ==========================================
// INICIALIZACIÓN
// ==========================================

$(document).ready(function() {
    // Inicializar componentes
    inicializarDataTable();
    inicializarModal();
    inicializarCategorias();
    inicializarEventos();
    
    // Cargar datos iniciales
    cargarDatos();
});

// Inicializar DataTable
const inicializarDataTable = () => {
    tablaCapacitaciones = $('#tablaCapacitaciones').DataTable({
        language: {
            "decimal": "",
            "emptyTable": "No hay capacitaciones disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ ítems",
            "infoEmpty": "Mostrando 0 a 0 de 0 ítems",
            "infoFiltered": "(Filtrado de _MAX_ total ítems)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ ítems",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "<<",
                "last": ">>",
                "next": ">",
                "previous": "<"
            }
        },
        responsive: true,
        pageLength: 10,
        order: [[0, 'desc']], // Ordenar por ID descendente (más recientes primero) 
        columnDefs: [
            { targets: [5], orderable: false }, // Columna de acciones no ordenable (índice 5)
            { targets: [0], visible: false } // Ocultar columna ID (índice 0)
        ],
        drawCallback: function() {
            // Reinicializar tooltips después de cada redibujado
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });
};

// Inicializar modal
const inicializarModal = () => {
    modalCapacitacion = new bootstrap.Modal(document.getElementById('modalCapacitacion'));
};

// Inicializar gestor de categorías
const inicializarCategorias = () => {
    console.log('🎯 Inicializando gestor de categorías...');
    
    categoriasManager = new CategoriasManager({
        selectores: {
            general: '#categoriaGeneral',
            especifica: '#categoriaEspecifica',
            subcategoria: '#subcategoria'
        }
    });
};

// Inicializar eventos
const inicializarEventos = () => {
    // Evento para cambio de modalidad
    $('#modalidad').on('change', function() {
        const lugar = $('#lugar');
        if (this.value === 'presencial' || this.value === 'mixto' ) {
            lugar.prop('required', true).prop('readonly', false);
            lugar.closest('.col-md-6').show();
        } else {
            lugar.prop('required', false).prop('readonly', true).val('');
            lugar.closest('.col-md-6').hide();
        }
    });

    // Evento para previsualización de imagen
    $('#nuevaImagen').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagenCurso').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Eventos de pestañas para responsividad móvil/tablet
    if (window.innerWidth <= 991) {
        convertirPestanasAcordeon();
    }

    // Evento de redimensionamiento
    $(window).resize(function() {
        if (window.innerWidth <= 991) {
            convertirPestanasAcordeon();
        } else {
            restaurarPestanas();
        }
    });
};

// ==========================================
// FUNCIONES DE CARGA DE DATOS
// ==========================================

const cargarDatos = async () => {
    mostrarSpinner(true);
    
    try {
        const resultado = await cargarCapacitaciones();
        
        if (resultado.success) {
            poblarTabla(resultado.capacitaciones);
            
            if (resultado.capacitaciones.length === 0) {
                mostrarMensajeVacio(true);
            } else {
                mostrarTabla(true);
            }
        } else {
            mostrarAlerta('error', 'Error al cargar capacitaciones: ' + resultado.error, 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error inesperado al cargar capacitaciones', 'Error');
    } finally {
        mostrarSpinner(false);
    }
};

const poblarTabla = (capacitaciones) => {
    tablaCapacitaciones.clear();
    
    capacitaciones.forEach(capacitacion => {
        const estadoInfo = formatearEstado(capacitacion.estado);
        
        const fila = [
            capacitacion.id, // ID oculto para ordenamiento
            capacitacion.nombre,
            capacitacion.equipo,
            formatearFecha(capacitacion.fecha_inicio),
            `<span class="badge ${estadoInfo.clase}">${estadoInfo.texto}</span>`,
            generarBotonesAccion(capacitacion)
        ];
        
        tablaCapacitaciones.row.add(fila);
    });
    
    tablaCapacitaciones.draw();
};

const generarBotonesAccion = (capacitacion) => {
    let botones = `
        <button class="btn btn-sm btn-outline-primary me-1" onclick="verDetalles(${capacitacion.id})" 
                data-bs-toggle="tooltip" title="Ver detalles">
            <i class="bi bi-eye"></i>
        </button>
    `;
    
    // Botones según el estado
    switch (capacitacion.estado) {
        case 'en_espera':
            botones += `
                <button class="btn btn-sm btn-outline-warning me-1" onclick="cambiarEstado(${capacitacion.id}, 'en_revision')"
                        data-bs-toggle="tooltip" title="Pasar a revisión">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="cambiarEstado(${capacitacion.id}, 'aprobado')"
                        data-bs-toggle="tooltip" title="Aprobar directamente">
                    <i class="bi bi-check-circle"></i>
                </button>
            `;
            break;
        case 'en_revision':
            botones += `
                <button class="btn btn-sm btn-outline-success me-1" onclick="cambiarEstado(${capacitacion.id}, 'aprobado')"
                        data-bs-toggle="tooltip" title="Aprobar">
                    <i class="bi bi-check-circle"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="cambiarEstado(${capacitacion.id}, 'en_espera')"
                        data-bs-toggle="tooltip" title="Volver a espera">
                    <i class="bi bi-arrow-left"></i>
                </button>
            `;
            break;
        case 'aprobado':
            botones += `
                <button class="btn btn-sm btn-outline-danger me-1" onclick="exportarPDF(${capacitacion.id})"
                        data-bs-toggle="tooltip" title="Exportar PDF">
                    <i class="bi bi-file-pdf"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="exportarWord(${capacitacion.id})"
                        data-bs-toggle="tooltip" title="Exportar Word">
                    <i class="bi bi-file-word"></i>
                </button>
            `;
            break;
    }
    
    return botones;
};

// ==========================================
// FUNCIONES DEL MODAL
// ==========================================

const verDetalles = async (id) => {
    try {
        const resultado = await obtenerCapacitacion(id);
        
        if (resultado.success) {
            capacitacionActual = id;
            llenarModal(resultado.capacitacion);
            
            // Aplicar lógica responsiva al abrir el modal
            if (window.innerWidth <= 991) {
                // Forzar restauración primero
                restaurarPestanas();
                // Luego convertir a acordeón
                setTimeout(() => {
                    convertirPestanasAcordeon();
                }, 100);
            }
            
            modalCapacitacion.show();
        } else {
            mostrarAlerta('error', 'Error al cargar detalles: ' + resultado.error, 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error inesperado al cargar detalles', 'Error');
    }
};


const llenarModal = (capacitacion) => {
    console.log('🎯 Llenando modal con datos reales:', capacitacion);
    $.each(capacitacion, function(i, item) {
        console.log(i, item);
        $("#"+i).val(item)
    })
            
    // Header
    $('#capacitacionNombreHeader').text(capacitacion.nombre);
    $('#capacitacionId').text(capacitacion.id);
    
    // === PESTAÑA 1: CATEGORIZACIÓN ===
    $('#alcance').val(capacitacion.tipo_capacitacion); // interno/estatal
    
    // TODO: Agregar campo tipo_capacitacion (curso/taller) cuando se agregue a la BD
    // Por ahora usar valor por defecto
    $('#tipoCapacitacion').val('curso');
    
    // Categorización dinámica - usar el gestor de categorías
    if (capacitacion.categoria_completa) {
        const categoria = capacitacion.categoria_completa;
        console.log('📂 Estableciendo categorías:', categoria);
        
        // Establecer valores usando el gestor de categorías
        const valoresCategoria = {
            general: categoria.general?.id || null,
            especifica: categoria.especifica?.id || null,
            subcategoria: categoria.subcategoria?.id || null
        };
        
        console.log('🎯 Valores a establecer:', valoresCategoria);
        
        // Usar timeout para asegurar que el gestor esté inicializado
        setTimeout(() => {
            if (categoriasManager) {
                categoriasManager.establecerValores(valoresCategoria);
            } else {
                console.warn('⚠️ Gestor de categorías no inicializado');
            }
        }, 100);
    }
    
    // Modalidad y lugar
    $('#modalidad').val(capacitacion.tipo_modalidad || 'virtual');
    $('#lugar').val(capacitacion.lugar || '');
    
    // === PESTAÑA 2: DATOS BÁSICOS ===
    $('#nombreCapacitacion').val(capacitacion.nombre);
    $('#slogan').val(capacitacion.slogan);
    $('#objetivo').val(capacitacion.objetivo);
    $('#descripcion').val(capacitacion.que_aprenderas); // Mapeo correcto del campo BD
    $('#destinatarios').val(capacitacion.destinatarios);
    $('#requisitos').val(capacitacion.requisitos);
    
    // === PESTAÑA 3: FECHAS Y LOGÍSTICA ===
    $('#fechaInscripcion').val(capacitacion.fecha_inicio_inscripcion);
    $('#fechaInicio').val(capacitacion.fecha_inicio_cursada);
    $('#fechaFin').val(capacitacion.fecha_fin_cursada);
    $('#duracionClase').val(capacitacion.duracion_clase_minutos);
    $('#cantidadEncuentros').val(capacitacion.total_encuentros);
    $('#cupos').val(capacitacion.cupos_maximos);
    
    // === PESTAÑA 4: HORARIOS ===
    llenarHorarios(capacitacion.horarios || []);
    
    // === PESTAÑA 5: CONTENIDO (TEMAS) ===
    llenarTemas(capacitacion.temas || []);
    
    // === PESTAÑA 6: GESTIÓN ===
    $('#imagenCurso').attr('src', capacitacion.imagen_url);
    $('#equipoCreador').val(capacitacion.equipo_nombre);
    $('#linkInscripcion').val(capacitacion.link_inscripcion);
    
    // Estado
    const estadoInfo = formatearEstado(capacitacion.estado_nombre);
    $('#estadoBadge').removeClass('bg-warning bg-info bg-success bg-danger bg-secondary')
                    .addClass(estadoInfo.clase)
                    .text(estadoInfo.texto);
    
    // Trigger eventos para validaciones
    $('#modalidad').trigger('change');
    
    console.log('✅ Modal llenado correctamente');
};

const llenarHorarios = (horarios) => {
    const container = $('#horariosContainer');
    container.empty();
    
    console.log('⏰ Llenando horarios:', horarios);
    
    if (!horarios || horarios.length === 0) {
        console.log('📝 No hay horarios definidos');
        return;
    }
    
    horarios.forEach((horario, index) => {
        // Mapear nombre completo del día
        const diasMap = {
            'lunes': 'Lunes',
            'martes': 'Martes', 
            'miércoles': 'Miércoles',
            'jueves': 'Jueves',
            'viernes': 'Viernes',
            'sábado': 'Sábado',
            'domingo': 'Domingo'
        };
        
        const nombreDia = diasMap[horario.dia_nombre?.toLowerCase()] || horario.dia_nombre || 'Lunes';
        
        const horarioHtml = `
            <div class="row mb-2 horario-item" data-index="${index}">
                <div class="col-12 col-sm-4 col-md-4">
                    <select class="form-select horario-dia">
                        <option value="Lunes" ${nombreDia === 'Lunes' ? 'selected' : ''}>Lunes</option>
                        <option value="Martes" ${nombreDia === 'Martes' ? 'selected' : ''}>Martes</option>
                        <option value="Miércoles" ${nombreDia === 'Miércoles' ? 'selected' : ''}>Miércoles</option>
                        <option value="Jueves" ${nombreDia === 'Jueves' ? 'selected' : ''}>Jueves</option>
                        <option value="Viernes" ${nombreDia === 'Viernes' ? 'selected' : ''}>Viernes</option>
                        <option value="Sábado" ${nombreDia === 'Sábado' ? 'selected' : ''}>Sábado</option>
                        <option value="Domingo" ${nombreDia === 'Domingo' ? 'selected' : ''}>Domingo</option>
                    </select>
                </div>
                <div class="col-6 col-sm-4 col-md-3 mt-1 mt-md-0">
                    <input type="time" class="form-control horario-inicio" value="${horario.hora_inicio || ''}">
                </div>
                <div class="col-6 col-sm-4 col-md-3 mt-1 mt-md-0">
                    <input type="time" class="form-control horario-fin" value="${horario.hora_fin || ''}">
                </div>
                <div class="col-12 col-md-1 d-flex justify-content-end align-content-center py-2 py-md-0">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarHorario(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.append(horarioHtml);
    });
    
    console.log(`✅ ${horarios.length} horarios cargados en el modal`);
};

const llenarTemas = (temas) => {
    const container = $('#temasContainer');
    container.empty();
    
    console.log('📚 Llenando temas:', temas);
    
    if (!temas || temas.length === 0) {
        console.log('📝 No hay temas definidos');
        return;
    }
    
    temas.forEach((tema, index) => {
        const temaHtml = `
            <div class="tema-card mb-4 border rounded-3 shadow-sm" data-index="${index}">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-book"></i> Tema ${index + 1}: <span class="tema-nombre">${tema.nombre || 'Sin título'}</span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="eliminarTema(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre del tema:</label>
                        <input type="text" class="form-control tema-nombre-input" value="${tema.nombre || ''}" 
                               onchange="actualizarNombreTema(${index}, this.value)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subtemas (opcional):</label>
                        <div class="subtemas-container" data-tema="${index}">
                            ${(tema.subtemas || []).map((subtema, subIndex) => `
                                <div class="input-group mb-2" data-subtema="${subIndex}">
                                    <input type="text" class="form-control" value="${subtema.nombre || subtema}">
                                    <button class="btn btn-outline-danger" type="button" onclick="eliminarSubtema(${index}, ${subIndex})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarSubtema(${index})">
                            <i class="bi bi-plus"></i> Agregar Subtema
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.append(temaHtml);
    });
    
    console.log(`✅ ${temas.length} temas cargados en el modal`);
};

// ==========================================
// FUNCIONES DE GESTIÓN DE HORARIOS
// ==========================================

const agregarHorario = () => {
    const container = $('#horariosContainer');
    const index = container.children().length;
    
    const horarioHtml = `
        <div class="row mb-2 horario-item" data-index="${index}">
            <div class="col-md-4">
                <select class="form-select horario-dia">
                    <option value="Lunes">Lunes</option>
                    <option value="Martes">Martes</option>
                    <option value="Miércoles">Miércoles</option>
                    <option value="Jueves">Jueves</option>
                    <option value="Viernes">Viernes</option>
                    <option value="Sábado">Sábado</option>
                    <option value="Domingo">Domingo</option>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-3 mt-1 mt-md-0">
                <input type="time" class="form-control horario-inicio">
            </div>
            <div class="col-6 col-sm-4 col-md-3 mt-1 mt-md-0">
                <input type="time" class="form-control horario-fin">
            </div>
            <div class="col-12 col-md-1 d-flex justify-content-end align-content-center py-2 py-md-0">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarHorario(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.append(horarioHtml);
};

const eliminarHorario = (index) => {
    $(`.horario-item[data-index="${index}"]`).remove();
    reindexarHorarios();
};

const reindexarHorarios = () => {
    $('#horariosContainer .horario-item').each(function(index) {
        $(this).attr('data-index', index);
        $(this).find('button').attr('onclick', `eliminarHorario(${index})`);
    });
};

// ==========================================
// FUNCIONES DE GESTIÓN DE TEMAS
// ==========================================

const agregarTema = () => {
    const container = $('#temasContainer');
    const index = container.children().length;
    
    const temaHtml = `
        <div class="tema-card mb-4 border rounded-3 shadow-sm" data-index="${index}">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-book"></i> Tema ${index + 1}: <span class="tema-nombre">Nuevo Tema</span>
                </h6>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="eliminarTema(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre del tema:</label>
                    <input type="text" class="form-control tema-nombre-input" value="Nuevo Tema" 
                           onchange="actualizarNombreTema(${index}, this.value)">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Subtemas (opcional):</label>
                    <div class="subtemas-container" data-tema="${index}">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarSubtema(${index})">
                        <i class="bi bi-plus"></i> Agregar Subtema
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.append(temaHtml);
};

const eliminarTema = (index) => {
    $(`.tema-card[data-index="${index}"]`).remove();
    reindexarTemas();
};

const reindexarTemas = () => {
    $('#temasContainer .tema-card').each(function(index) {
        $(this).attr('data-index', index);
        $(this).find('.card-header h6').html(`<i class="bi bi-book"></i> Tema ${index + 1}: <span class="tema-nombre">${$(this).find('.tema-nombre-input').val()}</span>`);
        $(this).find('.card-header button').attr('onclick', `eliminarTema(${index})`);
        $(this).find('.tema-nombre-input').attr('onchange', `actualizarNombreTema(${index}, this.value)`);
        $(this).find('.subtemas-container').attr('data-tema', index);
        $(this).find('.btn-outline-primary').attr('onclick', `agregarSubtema(${index})`);
    });
};

const actualizarNombreTema = (index, nombre) => {
    $(`.tema-card[data-index="${index}"] .tema-nombre`).text(nombre);
};

const agregarSubtema = (temaIndex) => {
    const container = $(`.subtemas-container[data-tema="${temaIndex}"]`);
    const subIndex = container.children().length;
    
    const subtemaHtml = `
        <div class="input-group mb-2" data-subtema="${subIndex}">
            <input type="text" class="form-control" placeholder="Nuevo subtema">
            <button class="btn btn-outline-danger" type="button" onclick="eliminarSubtema(${temaIndex}, ${subIndex})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    container.append(subtemaHtml);
};

const eliminarSubtema = (temaIndex, subIndex) => {
    $(`.subtemas-container[data-tema="${temaIndex}"] [data-subtema="${subIndex}"]`).remove();
    reindexarSubtemas(temaIndex);
};

const reindexarSubtemas = (temaIndex) => {
    $(`.subtemas-container[data-tema="${temaIndex}"] .input-group`).each(function(index) {
        $(this).attr('data-subtema', index);
        $(this).find('button').attr('onclick', `eliminarSubtema(${temaIndex}, ${index})`);
    });
};

// ==========================================
// FUNCIONES DE ACCIONES
// ==========================================

const guardarCambios = async (event = null) => {
    if (!capacitacionActual) return;
    
    try {
        // 🔍 VALIDACIONES ANTES DE GUARDAR
        console.log('🔍 === INICIANDO VALIDACIONES ===');
        
        const validacionResultado = validarFormularioCompleto();
        
        if (!validacionResultado.valido) {
            console.error('❌ VALIDACIÓN FALLIDA:');
            console.error('Errores encontrados:', validacionResultado.errores);
            
            // Mostrar errores al usuario
            mostrarAlerta('error', 
                'Por favor corrija los siguientes errores:\n• ' + validacionResultado.errores.join('\n• '), 
                'Datos incompletos');
            return;
        }
        
        console.log('✅ TODAS LAS VALIDACIONES PASARON');
        console.log('📊 === DATOS A ENVIAR ===');
        
        // Recopilar datos del formulario
        const datos = recopilarDatosFormulario();
        console.log('Datos completos del formulario:', datos);
        
        console.log('🚀 === LISTO PARA ENVIAR AL BACKEND ===');
        console.log('ID Capacitación:', capacitacionActual);
        console.log('Datos validados y listos para actualizar');
        
        // Mostrar loading - usar el ID del botón
        const btnGuardar = document.querySelector('#btnGuardarCambios');
        let textoOriginal = 'Guardar';
        
        if (btnGuardar) {
            textoOriginal = btnGuardar.innerHTML;
            btnGuardar.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
            btnGuardar.disabled = true;
        }
        
        const resultado = await guardarCapacitacion(capacitacionActual, datos);
        
        if (resultado.success) {
            mostrarAlerta('success', resultado.message, '¡Éxito!');
            
            // Actualizar tabla
            await cargarDatos();
            
            // Actualizar badge del estado si cambió
            const estadoInfo = formatearEstado(resultado.capacitacion.estado_id);
            $('#estadoBadge').removeClass('bg-warning bg-info bg-success').addClass(estadoInfo.clase).text(estadoInfo.texto);
            
            // Cerrar modal
            modalCapacitacion.hide();
        } else {
            mostrarAlerta('error', 'Error al guardar: ' + resultado.error, 'Error');
        }
        
        // Restaurar botón
        if (btnGuardar) {
            btnGuardar.innerHTML = textoOriginal;
            btnGuardar.disabled = false;
        }
        
    } catch (error) {
        console.error('❌ Error en validación:', error);
        mostrarAlerta('error', 'Error inesperado en validación: ' + error.message, 'Error');
    }
};

const cambiarEstado = async (id, nuevoEstado) => {
    const estadosTexto = {
        'en_espera': 'En Espera',
        'en_revision': 'En Revisión',
        'aprobado': 'Aprobado'
    };
    
    // Usar la función estándar de confirmación
    confirmarAccion(
        `La capacitación pasará a estado: ${estadosTexto[nuevoEstado]}. ¿Desea continuar?`,
        async () => {
            try {
                const respuesta = await cambiarEstadoCapacitacion(id, nuevoEstado);
                
                if (respuesta.success) {
                    mostrarAlerta('success', respuesta.message, '¡Éxito!');
                    await cargarDatos();
                    
                    // Si el modal está abierto y es la misma capacitación, actualizar badge
                    if (capacitacionActual === id) {
                        const estadoInfo = formatearEstado(nuevoEstado);
                        $('#estadoBadge').removeClass('bg-warning bg-info bg-success').addClass(estadoInfo.clase).text(estadoInfo.texto);
                    }
                } else {
                    mostrarAlerta('error', 'Error al cambiar estado: ' + respuesta.error, 'Error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('error', 'Error inesperado al cambiar estado', 'Error');
            }
        }
    );
};

const exportarPDF = async (id = null) => {
    const capacitacionId = id || capacitacionActual;
    if (!capacitacionId) return;
    
    try {
        const resultado = await obtenerCapacitacion(capacitacionId);
        
        if (resultado.success) {
            generarPDF(resultado.capacitacion);
        } else {
            mostrarAlerta('error', 'Error al obtener datos para PDF: ' + resultado.error, 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error inesperado al exportar PDF', 'Error');
    }
};

const exportarWord = async (id = null) => {
    const capacitacionId = id || capacitacionActual;
    if (!capacitacionId) return;
    
    mostrarAlerta('info', 'Función de exportación a Word en desarrollo', 'Información');
    // TODO: Implementar exportación a Word
};

// ==========================================
// FUNCIONES DE UTILIDAD
// ==========================================

const recopilarDatosFormulario = () => {
    console.log('📋 Recopilando datos del formulario...');
    
    // Recopilar horarios (corregir estructura para backend)
    const horarios = [];
    $('#horariosContainer .horario-item').each(function() {
        const dia = $(this).find('.horario-dia').val();
        const inicio = $(this).find('.horario-inicio').val();
        const fin = $(this).find('.horario-fin').val();
        
        if (dia && inicio && fin) {
            horarios.push({ 
                dia: dia.toLowerCase(), // Cambio: dia en lugar de dia_nombre
                hora_inicio: inicio, 
                hora_fin: fin 
            });
        }
    });
    
    // Recopilar temas (aplanar estructura para backend)
    const temas = [];
    $('#temasContainer .tema-card').each(function() {
        const nombreTema = $(this).find('.tema-nombre-input').val();
        
        if (nombreTema) {
            // Agregar tema principal
            temas.push({ 
                descripcion: nombreTema // Cambio: descripcion en lugar de nombre
            });
            
            // Agregar subtemas como elementos separados con referencia al tema padre
            const subtemas = [];
            $(this).find('.subtemas-container input').each(function() {
                const subtema = $(this).val().trim();
                if (subtema) {
                    subtemas.push(subtema);
                }
            });
            
            // Agregar cada subtema como un elemento del array con estructura simple
            for (const subtema of subtemas) {
                temas.push({
                    descripcion: subtema,
                    es_subtema: true, // Flag para identificar subtemas
                    tema_padre_nombre: nombreTema // Referencia al tema padre
                });
            }
        }
    });
    
    // Obtener valores de categorización usando el gestor
    const valoresCategoria = categoriasManager ? categoriasManager.obtenerValores() : {
        general: $('#categoriaGeneral').val(),
        especifica: $('#categoriaEspecifica').val(), 
        subcategoria: $('#subcategoria').val()
    };
    
    // Determinar tipo de categoría y ID final
    let tipoCategoriaFinal = 'general';
    let categoriaIdFinal = valoresCategoria.general;
    
    if (valoresCategoria.subcategoria) {
        tipoCategoriaFinal = 'subcategoria';
        categoriaIdFinal = valoresCategoria.subcategoria;
    } else if (valoresCategoria.especifica) {
        tipoCategoriaFinal = 'especifica';
        categoriaIdFinal = valoresCategoria.especifica;
    }
    
    const datosRecopilados = {
        // === DATOS BÁSICOS ===
        nombre: $('#nombreCapacitacion').val(),
        slogan: $('#slogan').val(),
        objetivo: $('#objetivo').val(),
        que_aprenderas: $('#descripcion').val(),
        destinatarios: $('#destinatarios').val(),
        requisitos: $('#requisitos').val(),
        
        // === CATEGORIZACIÓN (mapeo correcto para backend) ===
        alcance: $('#alcance').val(), // Cambio: alcance en lugar de tipo_capacitacion
        tipo_categoria: tipoCategoriaFinal,
        categoria_id: parseInt(categoriaIdFinal) || null,
        modalidad: $('#modalidad').val(), // Cambio: modalidad en lugar de tipo_modalidad
        lugar: $('#lugar').val() || null,
        
        // === FECHAS Y LOGÍSTICA ===
        fecha_inicio_inscripcion: $('#fechaInscripcion').val(),
        fecha_inicio_cursada: $('#fechaInicio').val(),
        fecha_fin_cursada: $('#fechaFin').val(),
        duracion_clase_minutos: parseInt($('#duracionClase').val()) || 0,
        total_encuentros: parseInt($('#cantidadEncuentros').val()) || 0,
        cupos_maximos: parseInt($('#cupos').val()) || 0,
        
        // === DATOS COMPLEJOS ===
        horarios: horarios,
        temas: temas
    };
    
    console.log('📊 Datos recopilados:', datosRecopilados);
    console.log('🏷️ Categorización final:', {
        tipo: tipoCategoriaFinal,
        id: categoriaIdFinal,
        valores: valoresCategoria
    });
    
    return datosRecopilados;
};

// ==========================================
// FUNCIONES DE VALIDACIÓN
// ==========================================

/**
 * Validación completa del formulario antes de enviar
 * @returns {Object} {valido: boolean, errores: array}
 */
const validarFormularioCompleto = () => {
    const errores = [];
    
    console.log('🔍 Iniciando validación completa...');
    
    // 1. VALIDAR CATEGORIZACIÓN
    const erroresCategorizacion = validarCategorizacion();
    errores.push(...erroresCategorizacion);
    
    // 2. VALIDAR DATOS BÁSICOS
    const erroresDatosBasicos = validarDatosBasicos();
    errores.push(...erroresDatosBasicos);
    
    // 3. VALIDAR FECHAS
    const erroresFechas = validarFechas();
    errores.push(...erroresFechas);
    
    // 4. VALIDAR CONTENIDO
    const erroresContenido = validarContenido();
    errores.push(...erroresContenido);
    
    // EXCLUIDA: Pestaña "gestión" según especificación
    
    const resultado = {
        valido: errores.length === 0,
        errores: errores
    };
    
    console.log('🔍 Resultado validación:', resultado);
    
    return resultado;
};

/**
 * Validar pestaña de Categorización
 */
const validarCategorizacion = () => {
    const errores = [];
    console.log('🏷️ Validando Categorización...');
    
    // 1. Alcance (obligatorio)
    const alcance = $('#alcance').val();
    if (!alcance || alcance.trim() === '') {
        errores.push('El alcance es obligatorio');
    }
    
    // 2. Tipo de Capacitación (obligatorio)
    const tipoCapacitacion = $('#tipoCapacitacion').val();
    if (!tipoCapacitacion || tipoCapacitacion.trim() === '') {
        errores.push('El tipo de capacitación es obligatorio');
    }
    
    // 3. Categoría General (obligatoria)
    const categoriaGeneral = $('#categoriaGeneral').val();
    if (!categoriaGeneral || categoriaGeneral.trim() === '') {
        errores.push('La categoría general es obligatoria');
    }
    
    // 4. Categoría Específica y Subcategoría son OPCIONALES según especificación
    // No validamos estos campos
    
    // 5. Modalidad (obligatoria)
    const modalidad = $('#modalidad').val();
    if (!modalidad || modalidad.trim() === '') {
        errores.push('La modalidad es obligatoria');
    }
    
    // 6. Validar Lugar según Modalidad
    const lugar = $('#lugar').val();
    if (modalidad === 'virtual') {
        // Si es virtual, lugar debe ser null (no validamos que esté vacío)
        console.log('📍 Modalidad virtual: lugar será null');
    } else if (modalidad === 'presencial' || modalidad === 'mixto') {
        // Si es presencial o mixto, lugar NO puede estar vacío
        if (!lugar || lugar.trim() === '') {
            errores.push('El lugar es obligatorio para modalidades presencial y mixta');
        }
    }
    
    console.log('🏷️ Categorización - errores encontrados:', errores.length);
    return errores;
};

/**
 * Validar pestaña de Datos Básicos
 */
const validarDatosBasicos = () => {
    const errores = [];
    console.log('📝 Validando Datos Básicos...');
    
    // Todos los campos son obligatorios
    const campos = [
        { id: '#nombreCapacitacion', nombre: 'Nombre de la capacitación' },
        { id: '#slogan', nombre: 'Slogan' },
        { id: '#objetivo', nombre: 'Objetivo' },
        { id: '#descripcion', nombre: 'Descripción (Qué aprenderás)' },
        { id: '#destinatarios', nombre: 'Destinatarios' },
        { id: '#requisitos', nombre: 'Requisitos' }
    ];
    
    campos.forEach(campo => {
        const valor = $(campo.id).val();
        if (!valor || valor.trim() === '') {
            errores.push(`${campo.nombre} es obligatorio`);
        }
    });
    
    console.log('📝 Datos Básicos - errores encontrados:', errores.length);
    return errores;
};

/**
 * Validar pestaña de Fechas
 */
const validarFechas = () => {
    const errores = [];
    console.log('📅 Validando Fechas...');
    
    // 1. Validar campos de fecha obligatorios
    const camposFecha = [
        { id: '#fechaInscripcion', nombre: 'Fecha de inicio de inscripción' },
        { id: '#fechaInicio', nombre: 'Fecha de inicio de cursada' },
        { id: '#fechaFin', nombre: 'Fecha de fin de cursada' },
        { id: '#duracionClase', nombre: 'Duración de clase' },
        { id: '#cantidadEncuentros', nombre: 'Cantidad de encuentros' },
        { id: '#cupos', nombre: 'Cupos' }
    ];
    
    camposFecha.forEach(campo => {
        const valor = $(campo.id).val();
        if (!valor || valor === '' || (campo.id !== '#fechaInscripcion' && campo.id !== '#fechaInicio' && campo.id !== '#fechaFin' && parseInt(valor) <= 0)) {
            errores.push(`${campo.nombre} es obligatorio y debe ser válido`);
        }
    });
    
    // 2. Validar Horarios vs Cantidad de Encuentros
    const cantidadEncuentros = parseInt($('#cantidadEncuentros').val()) || 0;
    
    // Contar horarios válidos
    let horariosValidos = 0;
    $('#horariosContainer .horario-item').each(function() {
        const dia = $(this).find('select').val();
        const horaInicio = $(this).find('input[type="time"]').eq(0).val();
        const horaFin = $(this).find('input[type="time"]').eq(1).val();
        
        if (dia && horaInicio && horaFin) {
            horariosValidos++;
        }
    });
    
    console.log('⏰ Horarios válidos encontrados:', horariosValidos);
    console.log('📊 Cantidad de encuentros esperados:', cantidadEncuentros);
    
    if (cantidadEncuentros > 0 && horariosValidos !== cantidadEncuentros) {
        errores.push(`Los horarios configurados (${horariosValidos}) deben coincidir con la cantidad de encuentros (${cantidadEncuentros})`);
    }
    
    if (horariosValidos === 0) {
        errores.push('Debe configurar al menos un horario');
    }
    
    console.log('📅 Fechas - errores encontrados:', errores.length);
    return errores;
};

/**
 * Validar pestaña de Contenido
 */
const validarContenido = () => {
    const errores = [];
    console.log('📚 Validando Contenido...');
    
    // Contar temas válidos (sin contar subtemas)
    let temasValidos = 0;
    
    // Buscar tanto .tema-item (nuevos) como .tema-card (cargados desde BD)
    const selectorTemas = '#temasContainer .tema-item, #temasContainer .tema-card';
    $(selectorTemas).each(function() {
        // Para temas nuevos (.tema-item)
        let nombreTema = $(this).find('input[placeholder*="Nombre del tema"]').val();
        
        // Para temas cargados desde BD (.tema-card)
        if (!nombreTema) {
            nombreTema = $(this).find('.tema-nombre-input').val();
        }
        
        if (nombreTema && nombreTema.trim() !== '') {
            temasValidos++;
        }
    });
    
    console.log('📖 Temas válidos encontrados:', temasValidos);
    console.log('🔍 Elementos de temas encontrados:', $(selectorTemas).length);
    
    if (temasValidos === 0) {
        errores.push('Debe agregar al menos un tema');
    }
    
    console.log('📚 Contenido - errores encontrados:', errores.length);
    return errores;
};

// ==========================================
// FUNCIONES DE PRUEBA Y DEBUG
// ==========================================

/**
 * Función para probar validaciones desde consola (solo desarrollo)
 */
window.probarValidaciones = () => {
    console.log('🧪 === PRUEBA DE VALIDACIONES ===');
    
    // Debug específico
    console.log('🔍 === DEBUG ELEMENTOS ===');
    console.log('Temas .tema-item encontrados:', $('#temasContainer .tema-item').length);
    console.log('Temas .tema-card encontrados:', $('#temasContainer .tema-card').length);
    console.log('Horarios .horario-item encontrados:', $('#horariosContainer .horario-item').length);
    
    const resultado = validarFormularioCompleto();
    
    console.log('📊 Resultado completo:', resultado);
    
    if (resultado.valido) {
        console.log('✅ TODAS LAS VALIDACIONES PASAN');
        mostrarAlerta('success', 'Todas las validaciones son correctas', 'Validación Exitosa');
    } else {
        console.log('❌ VALIDACIONES FALLARON');
        console.log('Errores:', resultado.errores);
        mostrarAlerta('error', 'Errores encontrados:\n• ' + resultado.errores.join('\n• '), 'Validación Fallida');
    }
    
    return resultado;
};

const copiarLink = () => {
    const link = $('#linkInscripcion').val();
    navigator.clipboard.writeText(link).then(() => {
        mostrarAlerta('success', 'Link copiado al portapapeles', '¡Éxito!');
    }).catch(() => {
        mostrarAlerta('error', 'Error al copiar link', 'Error');
    });
};

const abrirLink = () => {
    const link = $('#linkInscripcion').val();
    if (link) {
        window.open(link, '_blank');
    }
};

// Funciones de generación de PDF (simulada)
const generarPDF = (capacitacion) => {
    // Aquí iría la lógica real de generación de PDF
    // Por ahora solo mostramos un mensaje
    mostrarAlerta('success', `PDF de "${capacitacion.nombre}" generado correctamente`, '¡Éxito!');
    
    // Simular descarga
    console.log('Generando PDF para:', capacitacion);
};

// ==========================================
// FUNCIONES DE RESPONSIVIDAD
// ==========================================

const convertirPestanasAcordeon = () => {
    // En móvil/tablet, mostrar todas las pestañas verticalmente
    const tabs = $('#capacitacionTab');
    const tabContent = $('#capacitacionTabContent');
    
    if (tabs.hasClass('converted-to-accordion')) return;
    
    tabs.addClass('converted-to-accordion d-none');
    tabContent.addClass('accordion-mode');
    
    // Agregar encabezados de secciones y mostrar todas las pestañas
    tabContent.find('.tab-pane').each(function(index) {
        const tabId = $(this).attr('id');
        const tabButton = $(`button[data-bs-target="#${tabId}"]`);
        const tabText = tabButton.find('span').text() || tabButton.text();
        const tabIcon = tabButton.find('i').attr('class');
        
        const sectionHeader = `
            <div class="section-header bg-light text-white p-3 mb-0">
                <h5 class="mb-0">
                    <i class="${tabIcon}"></i> ${tabText}
                </h5>
            </div>
        `;
        
        $(this).prepend(sectionHeader);
        $(this).addClass('section-content show active');
        $(this).removeClass('fade'); // Remover animación fade para móvil
        
        // Agregar separador entre secciones (excepto la última)
        if (index < tabContent.find('.tab-pane').length - 1) {
            $(this).after('<div class="section-separator mb-4"></div>');
        }
    });
};

const restaurarPestanas = () => {
    const tabs = $('#capacitacionTab');
    const tabContent = $('#capacitacionTabContent');
    
    if (!tabs.hasClass('converted-to-accordion')) return;
    
    tabs.removeClass('converted-to-accordion d-none');
    tabContent.removeClass('accordion-mode');
    
    // Remover encabezados de secciones y separadores
    tabContent.find('.section-header').remove();
    tabContent.find('.section-separator').remove();
    tabContent.find('.tab-pane').removeClass('section-content');
    tabContent.find('.tab-pane').addClass('fade');
    
    // Restaurar estado inicial: primera pestaña activa
    tabContent.find('.tab-pane').removeClass('show active');
    tabContent.find('.tab-pane').first().addClass('show active');
    tabs.find('.nav-link').removeClass('active');
    tabs.find('.nav-link').first().addClass('active');
};

// ==========================================
// FUNCIONES DE UI
// ==========================================

const mostrarSpinner = (mostrar) => {
    const spinner = $('#spinnerCarga');
    if (mostrar) {
        spinner.removeClass('d-none');
    } else {
        spinner.addClass('d-none');
    }
};

const mostrarTabla = (mostrar) => {
    const tabla = $('#contenedorTabla');
    if (mostrar) {
        tabla.removeClass('d-none');
    } else {
        tabla.addClass('d-none');
    }
};

const mostrarMensajeVacio = (mostrar) => {
    const mensaje = $('#mensajeNoCapacitaciones');
    if (mostrar) {
        mensaje.removeClass('d-none');
    } else {
        mensaje.addClass('d-none');
    }
};
