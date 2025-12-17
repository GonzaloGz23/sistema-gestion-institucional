<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<style>


/* Estilo para las etiquetas (tags) seleccionadas dentro de Select2 */
.select2-container .select2-selection--multiple .select2-selection__choice {
    /* Permite que el texto se envuelva y pase a la siguiente línea */
    white-space: normal !important;
    /* Ajusta la altura del tag para que contenga las múltiples líneas */
    height: auto !important;
    /* Asegura que el tag no ocupe más espacio del necesario */
    max-width: 100%;
}

/* Para mejorar la separación visual entre los tags si se envuelven */
.select2-container .select2-selection--multiple {
    height: auto !important; /* Asegura que la caja de selección crezca */
    padding-top: 5px; /* Pequeño padding superior para mejor aspecto */
    padding-bottom: 5px; /* Pequeño padding inferior para mejor aspecto */
}


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
</style>

<main class="db-content" data-team="<?= $usuarioActual->id_equipo ?? ''; ?>">
    <div class="container-fluid mt-4" id="contenedorSolicitudes">
        <!-- TÍTULO Y BOTÓN + TAG -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Solicitudes</h2>
        </div>

        <!-- BUSCADOR DE SOLICITUDES -->
        <div class="mb-3">
            <input type="text" class="form-control"
                placeholder="Buscar solicitud por asunto, destinatario o etiqueta..." id="buscadorSolicitudes">
        </div>

        <!-- PESTAÑAS DESLIZABLES + BOTÓN + -->
        <div class="d-flex align-items-center mb-3 gap-2">
            <div class="flex-grow-1 overflow-auto" style="white-space: nowrap;" id="tabsScroll">
                <div class="d-flex flex-nowrap gap-2 align-items-center" id="tabsFiltros">
                    <button class="btn btn-sm btn-outline-primary active" data-filtro="recibidas">Recibidas</button>
                    <button class="btn btn-sm btn-outline-primary" data-filtro="enviadas">Enviadas</button>
                    <button class="btn btn-sm btn-outline-primary" data-filtro="todas">Todas</button>
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

    <?php include 'ui/solicitudes/detalle_solicitud.php'; ?>
    <!-- MENÚ CONTEXTUAL PERSONALIZADO -->
    <div id="menuContextual" class="dropdown-menu shadow" style="position: absolute; display: none; z-index: 2000;">
        <!-- contenido del menu contextual -->
    </div>

    <!-- BOTÓN FLOTANTE NUEVA SOLICITUD -->
    <!--  <button id="btnNuevaSolicitud" class="btn btn-primary rounded-3 position-fixed bottom-0 end-0 m-4 shadow"
        style="z-index: 1050; width: 50px; height: 50px;" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud">
        <i class="bi bi-plus-lg"></i>
    </button> -->
    <button id="btnNuevaSolicitud" class="btn btn-primary rounded-3 position-fixed shadow"
        style="bottom: 72px; right: 16px; z-index: 99; width: 50px; height: 50px;" data-bs-toggle="modal"
        data-bs-target="#modalNuevaSolicitud">
        <i class="bi bi-plus-lg"></i>
    </button>


    <!-- MODALES -->
    <?php include 'ui/solicitudes/modal_nueva_solicitud.php'; ?>
</main>
<?php include '../common/scripts.php'; ?>

<!-- jQuery -->
<script src="<?= BASE_URL ?>assets/libs/jquery/jquery-3.7.0.min.js"></script>

<!-- Select2 -->
<link href="<?= BASE_URL ?>assets/libs/select2/select2.min.css" rel="stylesheet" />
<link href="<?= BASE_URL ?>assets/libs/select2/select2-custom.min.css" rel="stylesheet" />
<script src="<?= BASE_URL ?>assets/libs/select2/select2.min.js"></script>

<!-- JS de la vista -->
<script src="js/solicitudes/solicitudes-data.js"></script>
<script src="js/solicitudes/solicitudes-ui.js"></script>
<?php include '../common/footer.php'; ?>