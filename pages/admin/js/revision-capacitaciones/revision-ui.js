// ==========================================
// INTERFAZ DE USUARIO Y LÓGICA DEL FRONTEND
// ==========================================

/**
 * MAPEO DE CAMPOS CRÍTICO - ALCANCE Y TIPO CAPACITACIÓN:
 * 
 * Base de Datos → Frontend (cargar datos):
 * - BD.alcance (interno|estatal) → HTML #alcance
 * - BD.tipo_capacitacion (curso|taller) → HTML #tipoCapacitacion
 * 
 * Frontend → Base de Datos (guardar datos):
 * - HTML #alcance (interno|estatal) → BD.alcance
 * - HTML #tipoCapacitacion (curso|taller) → BD.tipo_capacitacion
 */

// Variables globales
let tablaCapacitaciones;
let modalCapacitacion;
let categoriasManager; // Gestor de categorías
let crearCapacitacion;

// ==========================================
// CONSTANTES
// ==========================================
const IMAGEN_POR_DEFECTO = '../../images/default-course.webp';

// ==========================================
// INICIALIZACIÓN
// ==========================================

$(document).ready(function () {
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
    const equipo_Actual = document.getElementById('id_equipoActual').value;

    console.log('Equipo actual, para ocultar columna:', equipo_Actual);

    // 1️⃣ Construimos el array de columnDefs
    const columnDefsConfig = [
        { targets: [7], orderable: false }, // Columna acciones
        { targets: [4], orderable: false }, // Columna acciones
        { targets: [5], orderable: false }, // Columna acciones
        { targets: [0], visible: false }    // Oculta ID
    ];

    // 2️⃣ Si el equipo es 10 o 11, ocultamos columna 6
    if (equipo_Actual == 10 || equipo_Actual == 11) {
        console.log('Oculumna oculta:', 5);
        columnDefsConfig.push({
            targets: [5],
            visible: false,     // ✔ necesario para ocultar
            searchable: false
        });
    }
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
        columnDefs: columnDefsConfig,   // 👈 usamos el array dinámico
        drawCallback: function () {
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
    $('#modalidad').on('change', function () {
        const lugar = $('#lugar');
        if (this.value === 'presencial' || this.value === 'mixto') {
            lugar.prop('required', true).prop('readonly', false);
            lugar.closest('.col-md-6').show();
        } else {
            lugar.prop('required', false).prop('readonly', true).val('');
            lugar.closest('.col-md-6').hide();
        }
    });

    // Evento para validación de imagen (sin vista previa)
    $('#nuevaImagen').on('change', function (e) {
        const file = e.target.files[0];
        const btnSubir = $('#btnSubirImagen');

        if (file) {
            // Validar tipo de archivo
            const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!tiposPermitidos.includes(file.type)) {
                mostrarAlerta('error', 'Solo se permiten imágenes JPG, PNG, GIF o WEBP', 'Formato no válido');
                this.value = '';
                btnSubir.removeClass('btn-success').addClass('btn-secondary');
                return;
            }

            // Validar tamaño (2MB máximo)
            const tamanoMaximo = 2 * 1024 * 1024; // 2MB en bytes
            if (file.size > tamanoMaximo) {
                mostrarAlerta('error', 'La imagen no debe superar 2MB', 'Archivo muy grande');
                this.value = '';
                btnSubir.removeClass('btn-success').addClass('btn-secondary');
                return;
            }

            // Archivo válido - activar botón visualmente
            btnSubir.removeClass('btn-secondary').addClass('btn-success');
            console.log('✅ Imagen válida seleccionada:', file.name);

            // Opcional: mostrar notificación pequeña
            btnSubir.attr('title', `Subir: ${file.name}`);
        } else {
            // No hay archivo - botón en estado inactivo
            btnSubir.removeClass('btn-success').addClass('btn-secondary');
            btnSubir.attr('title', 'Seleccione una imagen primero');
        }
    });

    // Eventos de pestañas para responsividad móvil/tablet
    if (window.innerWidth <= 991) {
        convertirPestanasAcordeon();
    }

    // Evento para controlar el botón Guardar según la pestaña activa
    $('#capacitacionTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const pestanaActiva = $(e.target).attr('data-bs-target');
        const btnGuardar = $('#btnGuardarCambios');

        if (pestanaActiva === '#gestion') {
            // Desactivar botón Guardar en pestaña Gestión
            btnGuardar.prop('disabled', true)
                .addClass('disabled')
                .removeClass('btn-primary')
                .addClass('btn-outline-secondary')
                .attr('title', 'El botón Guardar no está disponible en la pestaña Gestión');
        } else {
            // Activar botón Guardar en otras pestañas
            btnGuardar.prop('disabled', false)
                .removeClass('disabled btn-outline-secondary')
                .addClass('btn-primary')
                .removeAttr('title');
        }
    });

    // Evento para resetear el botón Guardar cuando se cierre el modal
    $('#modalCapacitacion').on('hidden.bs.modal', function () {
        const btnGuardar = $('#btnGuardarCambios');
        btnGuardar.prop('disabled', false)
            .removeClass('disabled btn-outline-secondary')
            .addClass('btn-primary')
            .removeAttr('title');

        // También resetear a la primera pestaña
        $('#categorizacion-tab').tab('show');
    });

    // Evento de redimensionamiento
    $(window).resize(function () {
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
        $cond = 'disabled';
        $link_lis = '';
        $publicado = "No Publicado";
        if (capacitacion.estado != 'aprobado') {
            $cond = 'disabled';

        } else {
            if (capacitacion.esta_publicada == 1) {
                $cond = "";
                $link_lis = capacitacion.link;
                $publicado = "Publicado";

            }
        }
        const botonLink = `<button ${$cond} class="btn btn-sm btn-outline-primary me-1" onclick="window.open('${$link_lis}', '_blank')">
        <i class="bi bi-box-arrow-up-right"></i>  
        </button> ${$publicado}`;
        const botonInscriptos = `
<button ${$cond}  class="btn btn-sm btn-outline-primary me-1" onclick="enviarPost(${capacitacion.id}, '${capacitacion.nombre}')">
    <i class="bi bi-card-checklist"></i>
</button>`;

        const fila = [
            capacitacion.id, // ID oculto para ordenamiento
            capacitacion.nombre,
            capacitacion.equipo,
            formatearFecha(capacitacion.fecha_inicio),
            botonLink,
            botonInscriptos,
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
    if ([10, 11].includes(capacitacion.EquipoLogued)) {
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
                `
                botones += `    <button class="btn btn-sm btn-outline-secondary" onclick="cambiarEstado(${capacitacion.id}, 'en_espera')"
                            data-bs-toggle="tooltip" title="Volver a espera">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                `;
                break;
            case 'aprobado':
                botones += `
                    <button class="btn btn-sm btn-outline-info" onclick="mostrarTextoPlano(${capacitacion.id})"
                            data-bs-toggle="tooltip" title="Ver texto plano para copiar">
                        <i class="bi bi-clipboard-data"></i>
                    </button>
                `;
                break;
        }
    } else {
        switch (capacitacion.estado) {
            case 'borrador':
                botones += `    <button class="btn btn-sm btn-outline-secondary" onclick="cambiarEstado(${capacitacion.id}, 'en_espera')"
                            data-bs-toggle="tooltip" title="Mandar a espera">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                `;
                break
        }
        if (capacitacion.estado == "borrador") {

            botones += `    <button class="btn btn-sm btn-outline-danger" onclick="eliminar_cap(${capacitacion.id})"
                                data-bs-toggle="tooltip" title="Eliminar Capacitacion">
                            <i class="bi bi-x"></i>
                        </button>
                    `;
        }
    }

    return botones;
};

// ==========================================
// FUNCIONES DEL MODAL
// ==========================================

const verDetalles = async (id) => {
    try {

        hiddenBeforeCreate({ "accion": "showIcons" })
        const resultado = await obtenerCapacitacion(id);
        if ([10, 11].includes(resultado.capacitacion.EquipoLogued)) {
            $("#profesores").addClass("d-none");
            $("#li-profesor-hide").addClass("d-none");
        }
        if (resultado.success) {
            capacitacionActual = id;
            llenarModal(resultado.capacitacion);
            showInputProfesor(true)

            // Asegurar que el botón Guardar esté habilitado al abrir el modal (pestaña Categorización activa por defecto)
            $('#btnGuardarCambios').prop('disabled', false).removeClass('disabled').removeAttr('title');

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

    // Header
    $('#capacitacionNombreHeader').text(capacitacion.nombre);
    $('#capacitacionId').text(capacitacion.id);


    // === PESTAÑA 1: CATEGORIZACIÓN ===
    $('#tipoCapacitacion').val(capacitacion.tipo_capacitacion); // curso/taller
    $('#alcance').val(capacitacion.alcance); // interno/estatal

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
    $('#id_equipoActual').val(capacitacion.EquipoLogued);
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
    // Establecer imagen con fallback a imagen por defecto
    // Determinar la raíz según el entorno
    let raiz = '';
    if (window.location.hostname === 'localhost') {
        raiz = 'localhost/newLandingPage';
    } else {
        raiz = 'example.com/training';
    }

    const imagenUrl = capacitacion.imagen_url && capacitacion.imagen_url.trim() !== ''
        ? `http://${raiz}/assets/img/capacitaciones/${capacitacion.imagen_url}`
        : IMAGEN_POR_DEFECTO;

    console.log("Este es la url de la imagen:", capacitacion);

    $('#imagenCurso').attr('src', imagenUrl);

    $('#equipoCreador').val(capacitacion.equipo_nombre);
    $('#linkInscripcion').val(capacitacion.link_inscripcion);

    // Estado
    const estadoInfo = formatearEstado(capacitacion.estado_nombre);
    $('#estadoBadge').removeClass('bg-warning bg-info bg-success bg-danger bg-secondary')
        .addClass(estadoInfo.clase)
        .text(estadoInfo.texto);

    // Actualizar botones de cambio de estado dinámicamente
    actualizarBotonesEstado(capacitacion.estado_nombre);

    // Actualizar botón de texto plano según el estado
    actualizarBotonTextoPlano(capacitacion.estado_nombre);

    // Actualizar botón de publicar según el estado actual
    const estaPublicada = capacitacion.esta_publicada === 1 || capacitacion.esta_publicada === true;
    actualizarBotonPublicar(estaPublicada);

    // Actualizar botón de destacar según el estado actual
    const esDestacado = capacitacion.es_destacado === 1 || capacitacion.es_destacado === true;
    actualizarBotonDestacar(esDestacado);


    // Trigger eventos para validaciones
    $('#modalidad').trigger('change'); console.log('✅ Modal llenado correctamente');
    console.log(![10, 11].includes(String(capacitacion.EquipoLogued)), capacitacion.EquipoLogued)
    setTimeout(function () {
        if (
            capacitacion.estado_nombre !== "borrador" &&
            ![10, 11].includes(capacitacion.EquipoLogued)
        ) {
            $("#modalCapacitacion").find("input, textarea").prop("disabled", true);
            $("#modalCapacitacion").find("select").prop("disabled", true);
            $("#gestion").addClass("d-none")
            $("#gestion-tab").addClass("d-none")

            //$("#btnGuardarCambios").prop("disabled",true)
            //mostrar solo las fechas
            $("#fechas").find("input, textarea").prop("disabled", false);
            $("#fechas").find("select").prop("disabled", false);

        } else {
            $("#modalCapacitacion").find("input, textarea").prop("disabled", false);
            $("#modalCapacitacion").find("select").prop("disabled", false);
        }
    }, 700)
    listar_profesores().then(t => console.log(""))
};

const formatearHoraParaInput = (hora) => {
    if (!hora) return '';

    // Si ya está en formato HH:MM, devolverlo tal como está
    if (hora.length === 5 && hora.includes(':')) {
        return hora;
    }

    // Si está en formato HH:MM:SS, extraer solo HH:MM
    if (hora.length === 8 && hora.includes(':')) {
        return hora.substring(0, 5); // "12:31:00" -> "12:31"
    }

    // Si es otro formato, intentar parsearlo
    try {
        const date = new Date(`1970-01-01T${hora}`);
        if (!isNaN(date.getTime())) {
            return date.toTimeString().substring(0, 5);
        }
    } catch (e) {
        console.warn('⚠️ No se pudo formatear la hora:', hora);
    }

    return hora; // Devolver original si no se puede formatear
};

const eliminar_cap = async (id_cap) => {

    try {
        // Enviar al backend
        const respuesta = await fetch("/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/eliminar_capacitacion.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: id_cap })
        });

        const resultado = await respuesta.json();

        if (!resultado.success) {
            mostrarAlerta("error", resultado.message || "Error al eliminar", "Error");
            return;
        }

        // Eliminar del DataTable SIN recargar
        tablaCapacitaciones.rows().every(function () {
            const data = this.data();
            if (data[0] == id_cap) {   // columna 0 = ID
                this.remove();
            }
        });

        tablaCapacitaciones.draw(false);

        mostrarAlerta("success", "Capacitación eliminada correctamente", "Éxito");

    } catch (error) {
        console.error(error);
        mostrarAlerta("error", "Error inesperado", "Error");
    }
};



const formatearHoraParaTexto = (hora) => {
    if (!hora) return 'No especificado';

    // Primero formatear para input (quita los segundos)
    const horaLimpia = formatearHoraParaInput(hora);
    if (!horaLimpia) return 'No especificado';

    // Retornar en formato 24 horas sin segundos (HH:MM)
    return horaLimpia;
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

        // Formatear horas para inputs time (HH:MM:SS -> HH:MM)
        const horaInicio = formatearHoraParaInput(horario.hora_inicio);
        const horaFin = formatearHoraParaInput(horario.hora_fin);

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
                    <input type="time" class="form-control horario-inicio" value="${horaInicio}">
                </div>
                <div class="col-6 col-sm-4 col-md-3 mt-1 mt-md-0">
                    <input type="time" class="form-control horario-fin" value="${horaFin}">
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
    $('#horariosContainer .horario-item').each(function (index) {
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
    $('#temasContainer .tema-card').each(function (index) {
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
    $(`.subtemas-container[data-tema="${temaIndex}"] .input-group`).each(function (index) {
        $(this).attr('data-subtema', index);
        $(this).find('button').attr('onclick', `eliminarSubtema(${temaIndex}, ${index})`);
    });
};

// ==========================================
// FUNCIONES DE ACCIONES
// ==========================================

const guardarCambios = async (event = null) => {

    console.log("sin definir", window.crearCapacitacion, "mirar este")
    if (window.crearCapacitacion) {
        hiddenBeforeCreate({ accion: "save" })
        return

    }
    if (!capacitacionActual) return;

    try {
        // 🔍 VALIDACIONES ANTES DE GUARDAR
        console.log('🔍 === INICIANDO VALIDACIONES ===');


        // Obtener el valor del input oculto con id 'id_equipoActual'
        const equipoActual = document.getElementById('id_equipoActual').value;

        console.log('Equipo actual:', equipoActual);


        if (equipoActual == 10 || equipoActual == 11) {



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
        }
        console.log('✅ TODAS LAS VALIDACIONES PASARON');
        console.log('📊 === DATOS A ENVIAR ===');

        // Recopilar datos del formulario
        const datos = recopilarDatosFormulario();
        console.log('Datos completos del formulario:', datos);

        console.log('🚀 === LISTO PARA ENVIAR AL BACKEND ===');
        console.log('ID Capacitación:', capacitacionActual);
        console.log('Datos validados y listos para actualizar');

        // Mostrar loading y bloquear botón
        const btnGuardar = document.querySelector('#btnGuardarCambios');
        let textoOriginal = 'Guardar';

        if (btnGuardar) {
            textoOriginal = btnGuardar.innerHTML;
            btnGuardar.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
            btnGuardar.disabled = true;
        }

        // Guardar los datos del formulario
        const resultado = await guardarCapacitacion(capacitacionActual, datos);

        if (resultado.success) {
            mostrarAlerta('success', resultado.message, '¡Éxito!');

            // Actualizar tabla
            await cargarDatos();

            // Actualizar badge del estado si cambió
            const estadoInfo = formatearEstado(resultado.capacitacion.estado_nombre || 'en_revision');
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

                    // Si el modal está abierto y es la misma capacitación, actualizar badge y botones
                    if (capacitacionActual === id) {
                        // Usar estado_nuevo_frontend para mantener consistencia con el frontend
                        const estadoParaUI = respuesta.estado_nuevo_frontend || nuevoEstado;
                        const estadoInfo = formatearEstado(estadoParaUI);
                        $('#estadoBadge').removeClass('bg-warning bg-info bg-success').addClass(estadoInfo.clase).text(estadoInfo.texto);
                        actualizarBotonesEstado(estadoParaUI);
                        actualizarBotonTextoPlano(estadoParaUI);
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

// Función específica para cambio de estado manual desde el modal
const cambiarEstadoManual = async (nuevoEstado) => {
    if (!capacitacionActual) {
        mostrarAlerta('error', 'No hay capacitación seleccionada', 'Error');
        return;
    }

    await cambiarEstado(capacitacionActual, nuevoEstado);
};

// Actualizar botones de cambio de estado dinámicamente según el estado actual
const actualizarBotonesEstado = (estadoActual) => {
    const btnEnEspera = $('#btnEnEspera');
    const btnEnRevision = $('#btnEnRevision');
    const btnAprobado = $('#btnAprobado');

    // Resetear todos los botones a su estado habilitado
    btnEnEspera.removeClass('disabled').prop('disabled', false).removeClass('btn-outline-secondary').addClass('btn-secondary');
    btnEnRevision.removeClass('disabled').prop('disabled', false).removeClass('btn-outline-warning').addClass('btn-warning');
    btnAprobado.removeClass('disabled').prop('disabled', false).removeClass('btn-outline-success').addClass('btn-success');

    // Deshabilitar el botón correspondiente al estado actual
    switch (estadoActual) {
        case 'en_espera':
            btnEnEspera.addClass('disabled').prop('disabled', true).removeClass('btn-secondary').addClass('btn-outline-secondary');
            break;

        case 'en_revision':
            btnEnRevision.addClass('disabled').prop('disabled', true).removeClass('btn-warning').addClass('btn-outline-warning');
            break;

        case 'aprobado':
            btnAprobado.addClass('disabled').prop('disabled', true).removeClass('btn-success').addClass('btn-outline-success');
            break;

        default:
            console.warn(`⚠️ Estado no reconocido: ${estadoActual}`);
    }

    console.log(`🔄 Botones actualizados para estado: ${estadoActual}`);
};

const actualizarBotonTextoPlano = (estadoActual) => {
    const btnTextoPlano = $('#btnTextoPlano');

    if (estadoActual === 'aprobado') {
        // Habilitar botón para capacitaciones aprobadas
        btnTextoPlano.prop('disabled', false)
            .removeClass('btn-outline-secondary')
            .addClass('btn-info');
    } else {
        // Deshabilitar para otros estados
        btnTextoPlano.prop('disabled', true)
            .removeClass('btn-info')
            .addClass('btn-outline-secondary');
    }
};

const actualizarBotonPublicar = (estaPublicada) => {
    const btnPublicar = $('#btnPublicar');
    const btnTexto = $('#btnPublicarTexto');
    const btnIcono = btnPublicar.find('i');

    if (estaPublicada) {
        // Está publicada - Mostrar opción "No Publicar"
        btnPublicar.removeClass('btn-success').addClass('btn-danger');
        btnIcono.removeClass('bi-eye').addClass('bi-eye-slash');
        btnTexto.text('No Publicar');
    } else {
        // No está publicada - Mostrar opción "Publicar"
        btnPublicar.removeClass('btn-danger').addClass('btn-success');
        btnIcono.removeClass('bi-eye-slash').addClass('bi-eye');
        btnTexto.text('Publicar');
    }
};

const actualizarBotonDestacar = (esDestacado) => {
    const btnDestacar = $('#btnDestacar');
    const btnTexto = $('#btnDestacarTexto');
    const btnIcono = btnDestacar.find('i');

    if (esDestacado) {
        // Está destacada - Mostrar opción "No Destacar"
        btnDestacar.removeClass('btn-warning').addClass('btn-secondary');
        btnIcono.removeClass('bi-star').addClass('bi-star-fill');
        btnTexto.text('No Destacar');
    } else {
        // No está destacada - Mostrar opción "Destacar"
        btnDestacar.removeClass('btn-secondary').addClass('btn-warning');
        btnIcono.removeClass('bi-star-fill').addClass('bi-star');
        btnTexto.text('Destacar');
    }
};


const cambiarEstadoPublicacion = async () => {
    if (!capacitacionActual) {
        mostrarAlerta('error', 'No hay capacitación seleccionada', 'Error');
        return;
    }

    try {
        // Obtener el estado actual de publicación
        const resultado = await obtenerCapacitacion(capacitacionActual);

        if (!resultado.success) {
            mostrarAlerta('error', 'Error al obtener datos: ' + resultado.error, 'Error');
            return;
        }

        const estaPublicada = resultado.capacitacion.esta_publicada === 1 || resultado.capacitacion.esta_publicada === true;
        const nuevoEstado = !estaPublicada;
        const mensajeConfirm = nuevoEstado
            ? '¿Está seguro de que desea publicar esta capacitación?\nSerá visible públicamente'
            : '¿Está seguro de que desea despublicar esta capacitación?\nNo será visible públicamente';

        // Confirmación usando la función del proyecto
        const tema = getTheme();
        Swal.fire({
            title: nuevoEstado ? '¿Publicar capacitación?' : '¿Despublicar capacitación?',
            text: mensajeConfirm,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: nuevoEstado ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: nuevoEstado ? 'Sí, publicar' : 'Sí, despublicar',
            cancelButtonText: 'Cancelar',
            background: tema === "dark" ? "#2a2a2a" : "#ffffff",
            color: tema === "dark" ? "#ffffff" : "#333333",
            customClass: {
                popup: tema === "dark" ? "swal-dark-mode" : "",
                confirmButton: "swal-confirm-button"
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                // Llamar al backend para cambiar el estado
                const respuesta = await cambiarEstadoPublicacionCapacitacion(capacitacionActual, nuevoEstado);

                if (respuesta.success) {
                    mostrarAlerta('success',
                        nuevoEstado ? 'Capacitación publicada correctamente' : 'Capacitación despublicada correctamente',
                        'Éxito'
                    );

                    // Actualizar el botón
                    actualizarBotonPublicar(nuevoEstado);

                    // Recargar la tabla
                    tablaCapacitaciones.ajax.reload(null, false);
                } else {
                    mostrarAlerta('error', respuesta.error || 'Error al cambiar el estado de publicación', 'Error');
                }
            }
        });
    } catch (error) {
        console.error('Error al cambiar estado de publicación:', error);
        mostrarAlerta('error', 'Error inesperado al cambiar el estado de publicación', 'Error');
    }
};

const cambiarEstadoDestacado = async () => {
    if (!capacitacionActual) {
        mostrarAlerta('error', 'No hay capacitación seleccionada', 'Error');
        return;
    }

    try {
        // Obtener el estado actual de destacado
        const resultado = await obtenerCapacitacion(capacitacionActual);

        if (!resultado.success) {
            mostrarAlerta('error', 'Error al obtener datos: ' + resultado.error, 'Error');
            return;
        }

        const esDestacado = resultado.capacitacion.es_destacado === 1 || resultado.capacitacion.es_destacado === true;
        const nuevoEstado = !esDestacado;
        const mensajeConfirm = nuevoEstado
            ? '¿Está seguro de que desea destacar esta capacitación?\nAparecerá en la sección de destacados del sitio público'
            : '¿Está seguro de que desea quitar el destacado de esta capacitación?\nNo aparecerá en la sección de destacados';

        // Confirmación usando la función del proyecto
        const tema = getTheme();
        Swal.fire({
            title: nuevoEstado ? '¿Destacar capacitación?' : '¿Quitar destacado?',
            text: mensajeConfirm,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: nuevoEstado ? '#ffc107' : '#6c757d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: nuevoEstado ? 'Sí, destacar' : 'Sí, quitar destacado',
            cancelButtonText: 'Cancelar',
            background: tema === "dark" ? "#2a2a2a" : "#ffffff",
            color: tema === "dark" ? "#ffffff" : "#333333",
            customClass: {
                popup: tema === "dark" ? "swal-dark-mode" : "",
                confirmButton: "swal-confirm-button"
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                // Llamar al backend para cambiar el estado
                const respuesta = await cambiarEstadoDestacadoCapacitacion(capacitacionActual, nuevoEstado);

                if (respuesta.success) {
                    mostrarAlerta('success',
                        nuevoEstado ? 'Capacitación marcada como destacada correctamente' : 'Capacitación desmarcada como destacada correctamente',
                        'Éxito'
                    );

                    // Actualizar el botón
                    actualizarBotonDestacar(nuevoEstado);

                    // Recargar la tabla
                    tablaCapacitaciones.ajax.reload(null, false);
                } else {
                    mostrarAlerta('error', respuesta.error || 'Error al cambiar el estado de destacado', 'Error');
                }
            }
        });
    } catch (error) {
        console.error('Error al cambiar estado de destacado:', error);
        mostrarAlerta('error', 'Error inesperado al cambiar el estado de destacado', 'Error');
    }
};


const mostrarTextoPlano = async (id) => {
    // Si se llama desde el modal, usar la capacitación actual
    const capacitacionId = id || capacitacionActual;

    if (!capacitacionId) return;

    try {
        const resultado = await obtenerCapacitacion(capacitacionId);

        if (resultado.success) {
            generarTextoPlano(resultado.capacitacion);
        } else {
            mostrarAlerta('error', 'Error al obtener datos: ' + resultado.error, 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error inesperado al obtener datos', 'Error');
    }
};

const generarTextoPlano = (capacitacion) => {
    // Generar el texto plano formateado
    const textoPlano = formatearCapacitacionTextoPlano(capacitacion);

    // Crear el HTML del modal
    const modalHTML = `
        <div class="modal fade" id="modalTextoPlano" tabindex="-1" aria-labelledby="modalTextoPlanoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTextoPlanoLabel">
                            <i class="bi bi-clipboard-data"></i> Texto Plano - ${capacitacion.nombre}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Instrucciones:</strong> Seleccione el texto que desee copiar manualmente, o use el botón "Copiar Todo" para copiar el contenido completo.
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Seleccione el texto que necesite copiar</small>
                            <button class="btn btn-sm btn-outline-primary" onclick="copiarTextoPlano()">
                                <i class="bi bi-clipboard"></i> Copiar Todo
                            </button>
                        </div>
                        <textarea class="form-control" id="textoPlanoContent" rows="20" readonly>${textoPlano}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal existente si existe
    $('#modalTextoPlano').remove();

    // Agregar el modal al body
    $('body').append(modalHTML);

    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalTextoPlano'));
    modal.show();

    // Limpiar el modal cuando se cierre
    $('#modalTextoPlano').on('hidden.bs.modal', function () {
        $(this).remove();
    });
};

const formatearCapacitacionTextoPlano = (capacitacion) => {
    let texto = '';

    // Header
    texto += `CAPACITACIÓN: ${capacitacion.nombre.toUpperCase()}\n`;
    texto += `${'='.repeat(60)}\n\n`;

    // Información básica
    texto += `INFORMACIÓN GENERAL\n`;
    texto += `-----------------\n`;
    texto += `Nombre: ${capacitacion.nombre}\n`;
    if (capacitacion.slogan) texto += `Slogan: ${capacitacion.slogan}\n`;
    texto += `Alcance: ${capacitacion.alcance?.charAt(0).toUpperCase() + capacitacion.alcance?.slice(1) || 'No especificado'}\n`;
    texto += `Tipo: ${capacitacion.tipo_capacitacion?.charAt(0).toUpperCase() + capacitacion.tipo_capacitacion?.slice(1) || 'No especificado'}\n`;
    texto += `Modalidad: ${capacitacion.tipo_modalidad?.charAt(0).toUpperCase() + capacitacion.tipo_modalidad?.slice(1) || 'No especificado'}\n`;
    if (capacitacion.lugar) texto += `Lugar: ${capacitacion.lugar}\n`;
    texto += `Equipo: ${capacitacion.equipo_nombre || 'No especificado'}\n\n`;

    // Fechas importantes
    texto += `FECHAS IMPORTANTES\n`;
    texto += `----------------\n`;
    if (capacitacion.fecha_inicio_inscripcion) texto += `Inicio de inscripciones: ${formatearFecha(capacitacion.fecha_inicio_inscripcion)}\n`;
    if (capacitacion.fecha_inicio_cursada) texto += `Inicio de cursada: ${formatearFecha(capacitacion.fecha_inicio_cursada)}\n`;
    if (capacitacion.fecha_fin_cursada) texto += `Fin de cursada: ${formatearFecha(capacitacion.fecha_fin_cursada)}\n`;
    if (capacitacion.duracion_clase_minutos) texto += `Duración por clase: ${capacitacion.duracion_clase_minutos} minutos\n`;
    if (capacitacion.total_encuentros) texto += `Total de encuentros: ${capacitacion.total_encuentros}\n`;
    if (capacitacion.cupos_maximos) texto += `Cupos máximos: ${capacitacion.cupos_maximos}\n`;
    texto += `\n`;

    // Objetivo
    if (capacitacion.objetivo) {
        texto += `OBJETIVO\n`;
        texto += `--------\n`;
        texto += `${capacitacion.objetivo}\n\n`;
    }

    // Qué aprenderás
    if (capacitacion.que_aprenderas) {
        texto += `QUÉ APRENDERÁS\n`;
        texto += `--------------\n`;
        texto += `${capacitacion.que_aprenderas}\n\n`;
    }

    // Destinatarios
    if (capacitacion.destinatarios) {
        texto += `DESTINATARIOS\n`;
        texto += `-----------\n`;
        texto += `${capacitacion.destinatarios}\n\n`;
    }

    // Requisitos
    if (capacitacion.requisitos) {
        texto += `REQUISITOS\n`;
        texto += `----------\n`;
        texto += `${capacitacion.requisitos}\n\n`;
    }

    // Horarios
    if (capacitacion.horarios && capacitacion.horarios.length > 0) {
        texto += `HORARIOS\n`;
        texto += `--------\n`;
        capacitacion.horarios.forEach(horario => {
            const dia = horario.dia_nombre?.charAt(0).toUpperCase() + horario.dia_nombre?.slice(1) || 'Día no especificado';
            const horaInicio = formatearHoraParaTexto(horario.hora_inicio);
            const horaFin = formatearHoraParaTexto(horario.hora_fin);
            texto += `${dia}: ${horaInicio} - ${horaFin}\n`;
        });
        texto += `\n`;
    }

    // Contenido/Temas
    if (capacitacion.temas && capacitacion.temas.length > 0) {
        texto += `CONTENIDO PROGRAMÁTICO\n`;
        texto += `---------------------\n`;
        capacitacion.temas.forEach((tema, index) => {
            texto += `${index + 1}. ${tema.nombre || tema.descripcion || 'Tema sin título'}\n`;
            if (tema.subtemas && tema.subtemas.length > 0) {
                tema.subtemas.forEach(subtema => {
                    texto += `   • ${subtema.nombre || subtema}\n`;
                });
            }
        });
        texto += `\n`;
    }

    // Footer con información adicional
    texto += `${'='.repeat(60)}\n`;
    texto += `Estado actual: ${capacitacion.estado_nombre || 'No especificado'}\n`;
    if (capacitacion.link_inscripcion) texto += `Link de inscripción: ${capacitacion.link_inscripcion}\n`;
    texto += `Documento generado el: ${new Date().toLocaleString()}\n`;

    return texto;
};

const copiarTextoPlano = () => {
    const textarea = document.getElementById('textoPlanoContent');
    textarea.select();
    document.execCommand('copy');

    mostrarAlerta('success', 'Texto copiado al portapapeles', '¡Copiado!');
};

/**
 * Sube la imagen de la capacitación al servidor
 * @param {number} idCapacitacion - ID de la capacitación
 * @param {File} archivo - Archivo de imagen a subir
 * @returns {Promise<Object>} - {success: boolean, imagen_url?: string, error?: string}
 */
const subirImagenCapacitacion = async (idCapacitacion, archivo) => {
    try {
        console.log('📤 Subiendo imagen:', archivo.name, 'para capacitación:', idCapacitacion);

        // Crear FormData
        const formData = new FormData();
        formData.append('id_capacitacion', idCapacitacion);
        formData.append('imagen', archivo);

        // Enviar al servidor
        const response = await fetch('/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/subir_imagen.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData // NO enviar Content-Type header, FormData lo maneja automáticamente
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Error HTTP:', response.status, errorText);
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const resultado = await response.json();

        if (resultado.success) {
            console.log('✅ Imagen subida exitosamente:', resultado.imagen_url);
            return {
                success: true,
                imagen_url: resultado.imagen_url,
                nombre_archivo: resultado.nombre_archivo
            };
        } else {
            console.error('❌ Error del servidor:', resultado.error);
            return {
                success: false,
                error: resultado.error || 'Error desconocido al subir imagen'
            };
        }

    } catch (error) {
        console.error('❌ Error al subir imagen:', error);
        return {
            success: false,
            error: 'Error de conexión: ' + error.message
        };
    }
};

/**
 * Función para subir imagen inmediatamente desde el botón específico
 */
const subirImagenAhora = async () => {
    // Verificar que haya una capacitación seleccionada
    if (!capacitacionActual) {
        mostrarAlerta('error', 'Debe abrir una capacitación antes de subir una imagen', 'Error');
        return;
    }

    // Obtener el input de archivo
    const inputImagen = document.getElementById('nuevaImagen');

    // Verificar que se haya seleccionado un archivo
    if (!inputImagen || !inputImagen.files || !inputImagen.files[0]) {
        mostrarAlerta('warning', 'Por favor seleccione una imagen primero', 'Sin archivo');
        return;
    }

    const archivo = inputImagen.files[0];

    // Bloquear botón de subir
    const btnSubir = document.getElementById('btnSubirImagen');
    const iconoOriginal = btnSubir.innerHTML;
    btnSubir.disabled = true;
    btnSubir.innerHTML = '<i class="bi bi-hourglass-split"></i>';

    try {
        // Subir imagen
        const resultado = await subirImagenCapacitacion(capacitacionActual, archivo);

        if (resultado.success) {
            // Actualizar la imagen en la vista
            $('#imagenCurso').attr('src', resultado.imagen_url + '?v=' + Date.now());

            // Limpiar el input
            inputImagen.value = '';

            // Mostrar éxito
            mostrarAlerta('success', 'Imagen subida y guardada correctamente', '¡Éxito!');

            // Actualizar la tabla para reflejar el cambio
            await cargarDatos();
        } else {
            mostrarAlerta('error', 'Error al subir imagen: ' + resultado.error, 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error inesperado al subir imagen', 'Error');
    } finally {
        // Restaurar botón
        btnSubir.disabled = false;
        btnSubir.innerHTML = iconoOriginal;
    }
};

// ==========================================
// FUNCIONES DE UTILIDAD
// ==========================================

/**
 * Sanitiza el nombre de archivo para evitar caracteres problemáticos
 * - Convierte a minúsculas
 * - Reemplaza espacios con guiones bajos
 * - Elimina acentos y caracteres especiales
 * - Mantiene solo letras, números, guiones y puntos
 * @param {string} filename - Nombre original del archivo
 * @returns {string} - Nombre sanitizado
 */
const sanitizarNombreArchivo = (filename) => {
    // Separar nombre y extensión
    const ultimoPunto = filename.lastIndexOf('.');
    let nombre = ultimoPunto !== -1 ? filename.substring(0, ultimoPunto) : filename;
    let extension = ultimoPunto !== -1 ? filename.substring(ultimoPunto) : '';

    // Convertir a minúsculas
    nombre = nombre.toLowerCase();
    extension = extension.toLowerCase();

    // Eliminar acentos y caracteres especiales
    nombre = nombre.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    // Reemplazar espacios con guiones bajos
    nombre = nombre.replace(/\s+/g, '_');

    // Eliminar caracteres no permitidos (mantener solo letras, números, guiones bajos y guiones)
    nombre = nombre.replace(/[^a-z0-9_-]/g, '');

    // Eliminar guiones bajos múltiples consecutivos
    nombre = nombre.replace(/_+/g, '_');

    // Eliminar guiones bajos al inicio y final
    nombre = nombre.replace(/^_+|_+$/g, '');

    return nombre + extension;
};

/**
 * Genera el nombre de archivo con formato: id-timestamp-nombre.ext
 * @param {number} idCurso - ID del curso
 * @param {string} nombreOriginal - Nombre original del archivo
 * @returns {string} - Nombre formateado
 */
const generarNombreArchivo = (idCurso, nombreOriginal) => {
    // Obtener timestamp en formato YYYYMMDD_HHMMSS
    const ahora = new Date();
    const timestamp = ahora.getFullYear().toString() +
        (ahora.getMonth() + 1).toString().padStart(2, '0') +
        ahora.getDate().toString().padStart(2, '0') + '_' +
        ahora.getHours().toString().padStart(2, '0') +
        ahora.getMinutes().toString().padStart(2, '0') +
        ahora.getSeconds().toString().padStart(2, '0');

    // Sanitizar el nombre original
    const nombreSanitizado = sanitizarNombreArchivo(nombreOriginal);

    // Formato: id-timestamp-nombre.ext
    return `${idCurso}-${timestamp}-${nombreSanitizado}`;
};

const recopilarDatosFormulario = () => {
    console.log('📋 Recopilando datos del formulario...');

    // Recopilar horarios (corregir estructura para backend)
    const horarios = [];
    $('#horariosContainer .horario-item').each(function () {
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
    $('#temasContainer .tema-card').each(function () {
        const nombreTema = $(this).find('.tema-nombre-input').val();

        if (nombreTema) {
            // Agregar tema principal
            temas.push({
                descripcion: nombreTema // Cambio: descripcion en lugar de nombre
            });

            // Agregar subtemas como elementos separados con referencia al tema padre
            const subtemas = [];
            $(this).find('.subtemas-container input').each(function () {
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
        alcance: $('#alcance').val(), // interno/estatal
        tipo_capacitacion: $('#tipoCapacitacion').val(), // curso/taller
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
    $('#horariosContainer .horario-item').each(function () {
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
    $(selectorTemas).each(function () {
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
    console.log(capacitacion.equipo);

    if (capacitacion.equipo == 10 || capacitacion.equipo == 11) {


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
    }
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

// ==========================================
// FUNCIONES DE RESPONSIVIDAD
// ==========================================

const convertirPestanasAcordeon = () => {
    // En móvil/tablet, mostrar todas las pestañas verticalmente
    const tabs = $('#capacitacionTab');
    const tabContent = $('#capacitacionTabContent');

    //se agrega para que se oculte la info a la hora de crear


    if (tabs.hasClass('converted-to-accordion')) return;

    tabs.addClass('converted-to-accordion d-none');
    tabContent.addClass('accordion-mode');

    // Agregar encabezados de secciones y mostrar todas las pestañas
    tabContent.find('.tab-pane').each(function (index) {
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

/**
 * Parte de Creacion de datos
 * 
 */
const verModalCreacion = async () => {
    try {
        //const resultado = await obtenerCapacitacion(id);

        //if (resultado.success) {
        window.crearCapacitacion = true;



        // Aplicar lógica responsiva al abrir el modal
        if (window.innerWidth <= 991) {
            // Forzar restauración primero
            restaurarPestanas();
            // Luego convertir a acordeón
            setTimeout(() => {
                convertirPestanasAcordeon();

            }, 100);
        }
        hiddenBeforeCreate({ "accion": "init" })

        modalCapacitacion.show();
        if ($("#capacitacionNombreHeader")) {
            $("#capacitacionNombreHeader").html("")
            $("#capacitacionId").html("")


        }
        // } else {
        //     mostrarAlerta('error', 'Error al cargar detalles: ' + resultado.error, 'Error');
        // }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error inesperado al cargar detalles', 'Error');
    }
};

const hiddenBeforeCreate = (obj) => {
    try {

        if (obj.accion == "init") {
            $("#modalCapacitacion").find("input, select").val("");
            if (window.crearCapacitacion) {
                $("#modalCapacitacion").find("input, textarea,select").prop("disabled", false);
                console.log(obj, window.crearCapacitacion, "entrar aqui?")
                $("#gestion").addClass("d-none")
                $("#gestion-tab").addClass("d-none")
                //
                $("#basicos").addClass("d-none")
                $("#basicos-tab").addClass("d-none")
                //
                $("#fechas").addClass("d-none")
                $("#fechas-tab").addClass("d-none")
                //
                $("#contenido").addClass("d-none")
                $("#contenido-tab").addClass("d-none")
                $("#profesores").addClass("d-none")
                $("#profesores-tab").addClass("d-none")
                $("#btnGuardarCambios").text("Siguiente")
            } else {

                /*    $("#gestion").removeClass("d-none")
                   $("#gestion-tab").removeClass("d-none") */
                //
                $("#basicos").removeClass("d-none")
                $("#basicos-tab").removeClass("d-none")
                //
                $("#fechas").removeClass("d-none")
                $("#fechas-tab").removeClass("d-none")
                //
                $("#contenido").removeClass("d-none")
                $("#contenido-tab").removeClass("d-none")
                //
                $("#profesores").removeClass("d-none")
                $("#profesores-tab").removeClass("d-none")
                $("#btnGuardarCambios").text("Guardar")


            }
        } else if (obj.accion == "showIcons") {
            $("#modalCapacitacion").find("input, select").val("");
            window.crearCapacitacion = false
            /*  $("#gestion").removeClass("d-none")
             $("#gestion-tab").removeClass("d-none") */
            //
            $("#basicos").removeClass("d-none")
            $("#basicos-tab").removeClass("d-none")
            //
            $("#fechas").removeClass("d-none")
            $("#fechas-tab").removeClass("d-none")
            //
            $("#contenido").removeClass("d-none")
            $("#contenido-tab").removeClass("d-none")
            //
            $("#profesores").removeClass("d-none")
            $("#profesores-tab").removeClass("d-none")
        }
        else if (obj.accion == "save") {
            var datos_enviar = obtenerCategorias("categoria")
            console.log("📤 Enviando datos:", datos_enviar["categoria"])

            fetch('/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/insert_capacitacion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    ...datos_enviar["categoria"]
                })
            })
                .then(async response => {
                    // Verificar si la respuesta es OK
                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('❌ Error HTTP:', response.status, errorText);
                        throw new Error(`Error ${response.status}: ${errorText}`);
                    }

                    // Intentar parsear JSON
                    const contentType = response.headers.get("content-type");
                    if (!contentType || !contentType.includes("application/json")) {
                        const text = await response.text();
                        console.error('❌ Respuesta no es JSON:', text);
                        throw new Error('El servidor no devolvió JSON válido');
                    }

                    return response.json();
                })
                .then(response => {
                    console.log('✅ Respuesta recibida:', response);

                    // Verificar si hay error en la respuesta
                    if (response.status === false) {
                        throw new Error(response.error || 'Error desconocido al crear capacitación');
                    }

                    $("#btnGuardarCambios").text("Guardar")
                    window.crearCapacitacion = false
                    console.log('🎉 Capacitación creada con ID:', response.idCapacitacion)

                    verDetalles(parseInt(response.idCapacitacion)).then(() => {
                        mostrarAlerta('success', 'Capacitación creada exitosamente', '¡Éxito!');
                    })
                })
                .catch(error => {
                    console.error('❌ Error completo:', error);
                    $("#btnGuardarCambios").text("Siguiente")
                    window.crearCapacitacion = true
                    mostrarAlerta('error', error.message || 'Error al crear la capacitación', 'Error');
                })
        }

    } catch (error) {

    }
}
function obtenerCategorias(prefijo) {
    const elementos = document.querySelectorAll(`input[name^='${prefijo}'], select[name^='${prefijo}']`);
    const resultado = { [prefijo]: {} };

    elementos.forEach(el => {
        resultado[prefijo][el.name.replace(prefijo + "_", "")] = el.value;
    });

    return resultado;
}

//ver inscriptos
async function agregarProfesor() {
    try {
        let apellido = $("#apellido-profesor").val()
        let nombre = $("#nombre-profesor").val()
        let telefono = $("#telefono-profesor").val()
        let correo = $("#correo-profesor").val()
        let genero = $("#genero-profesor").val()
        let dni = $("#dni-profesor").val()
        let idCapacitacion = parseInt($("#capacitacionId").text())


        if (
            apellido.length == 0 &&
            nombre.length == 0 &&
            telefono.length == 0 &&
            correo.length == 0 &&
            dni.length == 0
        ) {
            throw new Error("Datos Incompletos")
        }
        //añadir
        const formData = new FormData();
        formData.append('idCapacitacion', idCapacitacion);
        formData.append('apellido', apellido);
        formData.append('nombre', nombre);
        formData.append('telefono', telefono);
        formData.append('correo', correo);
        formData.append('dni', dni);
        formData.append('genero', genero);
        // Enviar al servidor
        const response = await fetch('/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/insert_profesores.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData // NO enviar Content-Type header, FormData lo maneja automáticamente
        });
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const resultado = await response.json();
        if (resultado.status) {

            limpiarFormularioProfesor()
            mostrarAlerta('Success', "Datos del profesor guardado correctamente", '¡Éxito!');
        }
        await listar_profesores()
    } catch (error) {
        let err = error.message.toString()
        mostrarAlerta('error', err, 'Error');
    }
}

function limpiarFormularioProfesor() {
    document.getElementById("dni-profesor").value = "";
    document.getElementById("genero-profesor").value = "";

    document.getElementById("nombre-profesor").value = "";
    document.getElementById("apellido-profesor").value = "";
    document.getElementById("telefono-profesor").value = "";
    document.getElementById("correo-profesor").value = "";
}

async function listar_profesores() {
    try {
        let idCapacitacion = parseInt($("#capacitacionId").text());

        const formData = new FormData();
        formData.append('capacitacion_id', idCapacitacion);

        const response = await fetch(
            '/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/listar_profesores.php',
            {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            }
        );

        const resultado = await response.json();
        console.log(resultado);

        let tbody = $("#list-profesores");
        tbody.html(""); // limpiar antes de volver a insertar

        $.each(resultado.profesores, function (i, item) {

            let estado = item.esta_activo == 1
                ? `<span class="badge bg-success">Activo</span>`
                : `<span class="badge bg-danger">Inactivo</span>`;

            let fila = `
                <tr data-id="${item.id_asignacion}">
                    <td>${item.apellido} ${item.nombre}</td>
                    <td>${item.dni}</td>
                    <td>${estado}</td>
                    <td>${item.fecha_asignacion}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="eliminarProfesor(${item.id_asignacion}, this)">
                            Eliminar
                        </button>
                    </td>
                </tr>
            `;

            tbody.append(fila);
        });

    } catch (error) {
        let err = error.message.toString();
        mostrarAlerta('error', err, 'Error');
    }
}
async function eliminarProfesor(id_asign, boton) {


    try {
        const formData = new FormData();
        formData.append("id_asignacion", id_asign);

        const response = await fetch(
            '/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/eliminar_profesor.php',
            {
                method: "POST",
                credentials: "same-origin",
                body: formData
            }
        );

        const result = await response.json();

        if (!result.success) {
            mostrarAlerta("error", result.message || "No se pudo eliminar", "Error");
            return;
        }

        // Animación suave de eliminación
        const fila = boton.closest("tr");
        fila.style.transition = "0.3s";
        fila.style.opacity = "0";

        setTimeout(() => fila.remove(), 300);

        mostrarAlerta("success", "Profesor eliminado correctamente", "Éxito");

    } catch (error) {
        mostrarAlerta("error", error.message, "Error");
    }
}



function enviarPost(id, nombreCap) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = './listar_inscriptos.php';
    form.target = '_blank'; // abre en nueva pestaña

    // ---- ID ----
    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'id';
    inputId.value = id;
    form.appendChild(inputId);

    // ---- NOMBRE CAPACITACION ----
    const inputNombre = document.createElement('input');
    inputNombre.type = 'hidden';
    inputNombre.name = 'nombreCap';
    inputNombre.value = nombreCap;
    form.appendChild(inputNombre);

    // ---- ENVIAR FORM ----
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}


$("#genero-profesor").change(async function () {
    try {
        const formData = new FormData();
        formData.append('dni', $("#dni-profesor").val());
        formData.append('genero', $(this).val());

        const response = await fetch('/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/verificar_profesor.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData // NO enviar Content-Type header, FormData lo maneja automáticamente
        });
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        //
        const resultado = await response.json();
        if (resultado.data == false) {
            showInputProfesor(false);
            mostrarAlerta('warning', "El profesor no existe, debe agregarlo manualmente", 'Atencion');
            $("#nombre-profesor").val("")
            $("#apellido-profesor").val("")
            $("#telefono-profesor").val("")
            $("#correo-profesor").val("")
        } else {

            $("#nombre-profesor").val(resultado.data.nombre)
            $("#apellido-profesor").val(resultado.data.apellido)
            $("#telefono-profesor").val(resultado.data.telefono)
            $("#correo-profesor").val(resultado.data.correo)
        }


    } catch (error) {
        let err = error.message.toString()
        mostrarAlerta('error', err, 'Error');
    }

})

function showInputProfesor(readonly) {
    $("#nombre-profesor").attr("readonly", readonly)
    $("#apellido-profesor").attr("readonly", readonly)
    $("#correo-profesor").attr("readonly", readonly)
    $("#telefono-profesor").attr("readonly", readonly)
}