<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<style>
    #menuContextual {
        min-width: 150px;
    }

    .dot-notification {
        width: 10px;
        height: 10px;
        background-color: red;
        border-radius: 50%;
        display: inline-block;
    }

    .contextual-menu {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        min-width: 150px;
    }
    
    .contextual-menu .menu-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    
    .contextual-menu .menu-item:hover {
        background-color: #f5f5f5;
    }
    
    .contextual-menu .menu-item:last-child {
        border-bottom: none;
    }

    .notification-dot {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 12px;
        height: 12px;
        background-color: #dc3545;
        border-radius: 50%;
        border: 2px solid white;
    }

    /* ESTILOS RESPONSIVOS PARA MENSAJES LARGOS EN MÓVILES */
    .mensaje-contenedor {
        max-width: 100%; /* ASEGURA que no se desborde */
    }

    .mensaje-burbuja {
        max-width: 85%; /* LIMITA el ancho máximo de la burbuja */
        word-wrap: break-word; /* ROMPE palabras largas */
        word-break: break-word; /* COMPATIBILIDAD adicional */
        overflow-wrap: break-word; /* ESTÁNDAR moderno */
        hyphens: auto; /* GUIONES automáticos cuando sea posible */
    }

    .mensaje-texto {
        white-space: pre-wrap; /* PRESERVA saltos de línea y espacios */
        word-wrap: break-word; /* ROMPE palabras largas */
        word-break: break-word; /* COMPATIBILIDAD adicional */
        overflow-wrap: break-word; /* ESTÁNDAR moderno */
        line-height: 1.4; /* MEJORA la legibilidad */
    }

    /* ESTILOS ESPECÍFICOS PARA MÓVILES */
    @media (max-width: 768px) {
        .mensaje-burbuja {
            max-width: 90%; /* MÁS ancho en móviles para aprovechar espacio */
            font-size: 14px; /* TAMAÑO de fuente ligeramente menor */
            padding: 8px 12px; /* PADDING ajustado para móviles */
        }

        .mensaje-texto {
            line-height: 1.3; /* LÍNEA más compacta en móviles */
        }

        /* AJUSTES para pantallas muy pequeñas */
        @media (max-width: 480px) {
            .mensaje-burbuja {
                max-width: 95%; /* APROVECHA casi todo el ancho disponible */
                font-size: 13px; /* FUENTE aún más pequeña */
                padding: 6px 10px; /* PADDING más compacto */
            }
        }
    }

    /* ESTILOS para mensajes que se están enviando */
    .mensaje-contenedor[data-enviando="true"] .mensaje-burbuja {
        opacity: 0.8; /* TRANSPARENCIA ligera mientras se envía */
        border: 1px solid rgba(255, 255, 255, 0.3); /* BORDE sutil */
    }

    /* ESTILOS PARA TARJETAS DE SOLICITUDES RESPONSIVAS */
    .card-solicitud .min-width-0 {
        min-width: 0; /* Permite que flex-items se reduzcan */
    }

    /* MANEJO DE ASUNTOS LARGOS */
    .card-solicitud .card-title {
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
        line-height: 1.3;
        max-width: 100%;
    }

    /* COMPORTAMIENTO RESPONSIVO DEL ENCABEZADO DE TARJETAS */
    @media (max-width: 576px) {
        .card-solicitud .flex-wrap {
            flex-wrap: wrap !important;
        }
        
        .card-solicitud .d-flex.align-items-center.gap-2.flex-wrap > div:last-child {
            margin-top: 8px;
            width: 100%;
            justify-content: flex-start;
        }
        
        .card-solicitud .badge {
            margin-right: 8px;
        }
        
        .card-solicitud .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }

    /* FORZAR SALTO DE LÍNEA PARA ASUNTOS MUY LARGOS EN TODAS LAS PANTALLAS */
    @media (min-width: 577px) {
        .card-solicitud .d-flex.align-items-center.gap-2.flex-wrap {
            align-items: flex-start !important;
        }
        
        .card-solicitud .d-flex.align-items-center.gap-2.flex-wrap > div:first-child {
            flex-basis: 100%;
            max-width: calc(100% - 140px); /* Reserva espacio para estado + botón */
        }
        
        /* Si el título es muy largo, forzar wrap */
        .card-solicitud .card-title {
            max-width: 100%;
            white-space: normal !important;
        }
        
        /* Cuando el texto se envuelve, el contenedor de estado+botón se mantiene arriba */
        .card-solicitud .d-flex.align-items-center.gap-2.flex-wrap > div:last-child {
            align-self: flex-start;
            margin-top: -2px; /* Alineación fina */
        }
    }

    /* MEJORAR TRUNCADO DE TEXTO EN MÓVILES */
    @media (max-width: 768px) {
        .card-solicitud .card-title {
            font-size: 0.9rem;
            line-height: 1.2;
        }
        
        .card-solicitud .small {
            font-size: 0.75rem !important;
        }
    }
</style>

<main class="db-content" data-team="<?= $usuarioActual->id_equipo ?? ''; ?>">
    <div class="container-fluid mt-4" id="contenedorSolicitudes">
        <!-- TÍTULO -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Todas las Solicitudes</h2>
        </div>

        <!-- SELECTOR DE EQUIPOS (NUEVO) -->
        <div class="mb-3">
            <label for="selectEquipoAdmin" class="form-label fw-semibold">
                <i class="bi bi-people me-1"></i>Seleccionar Equipo:
            </label>
            <select class="form-select" id="selectEquipoAdmin">
                <option value="">Cargando equipos...</option>
            </select>
        </div>

        <!-- BUSCADOR DE SOLICITUDES -->
        <div class="mb-3">
            <input type="text" class="form-control"
                placeholder="Primero selecciona un equipo para habilitar la búsqueda..." 
                id="buscadorSolicitudes" disabled>
        </div>

        <!-- PESTAÑAS DESLIZABLES + BOTÓN + -->
        <div class="d-flex align-items-center mb-3 gap-2">
            <div class="flex-grow-1 overflow-auto" style="white-space: nowrap;" id="tabsScroll">
                <div class="d-flex flex-nowrap gap-2 align-items-center" id="tabsFiltros">
                    <button class="btn btn-sm btn-outline-primary active" data-filtro="todas">Todas</button>
                    <button class="btn btn-sm btn-outline-primary" data-filtro="recibidas">Recibidas</button>
                    <button class="btn btn-sm btn-outline-primary" data-filtro="enviadas">Enviadas</button>
                    <button class="btn btn-sm btn-outline-primary" data-filtro="pendiente">Pendiente</button>
                    <button class="btn btn-sm btn-outline-primary" data-filtro="resuelta">Resuelta</button>
                    <button class="btn btn-sm btn-outline-primary" data-filtro="rechazada">Rechazada</button>
                </div>
            </div>
        </div>

        <!-- Spinner de carga -->
        <div id="spinnerCarga" class="text-center my-3 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <!-- Mensajes si no hay resultados por filtro -->
        <p class="text-center text-muted d-none" data-mensaje-vacio="recibidas">No hay solicitudes recibidas.</p>
        <p class="text-center text-muted d-none" data-mensaje-vacio="enviadas">No hay solicitudes enviadas.</p>
        <p class="text-center text-muted d-none" data-mensaje-vacio="todas">No hay solicitudes disponibles.</p>
        <p class="text-center text-muted d-none" data-mensaje-vacio="pendiente">No hay solicitudes pendientes.</p>
        <p class="text-center text-muted d-none" data-mensaje-vacio="resuelta">No hay solicitudes resueltas.</p>
        <p class="text-center text-muted d-none" data-mensaje-vacio="rechazada">No hay solicitudes rechazadas.</p>
        <!-- Mensaje si no hay resultados por búsqueda -->
        <p id="mensajeBusquedaSinResultados" class="text-center text-muted d-none">
            No hay coincidencias.
        </p>

        <!-- Mensaje de error se puede mantener global -->
        <p id="mensajeErrorSolicitudes" class="text-center text-danger d-none">
            Ocurrió un error al cargar las solicitudes.
        </p>
        <!-- BANDEJA UNIFICADA -->
        <div id="bandejaUnificada"></div>
    </div>

    <?php include 'ui/solicitudes/admin-detalle-solicitud.php'; ?>
    <!-- MENÚ CONTEXTUAL PERSONALIZADO - DESHABILITADO EN MODO ADMIN -->
    <!-- <div id="menuContextual" class="dropdown-menu shadow" style="position: absolute; display: none; z-index: 2000;"></div> -->

    <!-- BOTÓN FLOTANTE NUEVA SOLICITUD - REMOVIDO EN MODO ADMIN -->
    <!-- Los administradores no pueden crear solicitudes desde esta vista -->
</main>
<?php include '../common/scripts.php'; ?>

<!-- jQuery -->
<script src="<?= BASE_URL ?>assets/libs/jquery/jquery-3.7.0.min.js"></script>

<!-- Select2 -->
<link href="<?= BASE_URL ?>assets/libs/select2/select2.min.css" rel="stylesheet" />
<link href="<?= BASE_URL ?>assets/libs/select2/select2-custom.min.css" rel="stylesheet" />
<script src="<?= BASE_URL ?>assets/libs/select2/select2.min.js"></script>

<!-- JS de la vista ADMIN -->
<script src="js/solicitudes/admin-solicitudes-data.js"></script>
<script src="js/solicitudes/admin-solicitudes-ui.js"></script>
<?php include '../common/footer.php'; ?>