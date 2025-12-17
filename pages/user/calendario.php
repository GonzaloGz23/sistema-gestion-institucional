<?php
include '../common/header.php';
include '../common/navbar.php';
include '../common/sidebar.php';
?>
<link href='css/calendario/calendario-personalizado.css' rel='stylesheet' />

<main class="db-content" data-user="<?php echo $usuarioActual->id; ?>"
    data-team="<?php echo $usuarioActual->id_equipo; ?>">
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-lg-12 card">
                <div id="calendar" class="col-centered"></div>
                <!-- Contenedor de eventos del día seleccionado -->
                <div id="eventosDia" class="card shadow-sm p-3 mb-4 d-block d-lg-none">
                    <h5 class="fw-bold mb-3">Eventos del día seleccionado</h5>

                    <!-- Spinner al cargar -->
                    <div id="spinnerEventos" class="d-none justify-content-center my-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <!-- Lista de eventos -->
                    <div id="listaEventosDia" class="list-group">
                        <div class="text-muted">Seleccioná una fecha para ver los eventos.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once './ui/calendario/modal_nuevo_evento.php'; ?>
    <?php require_once './ui/calendario/modal_evento_dia.php'; ?>
    <?php require_once './ui/calendario/modal_editar_evento.php'; ?>
    

        <button id="btnNuevoEvento" class="btn btn-primary rounded-3 position-fixed shadow"
            style="bottom: 72px; right: 16px; z-index: 99; width: 50px; height: 50px;" data-bs-toggle="modal"
            data-bs-target="#ModalAdd">
            <i class="bi bi-calendar-plus"></i>
        </button>
</main>

<?php include '../common/scripts.php'; ?>

<!-- FullCalendar moderno -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<!-- FullCalendar en español -->
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.17/locales/es.global.min.js"></script>


<!-- Tippy.js para tooltips -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/animations/scale.css" />
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css" />
<!-- jQuery -->
<script src="<?= BASE_URL ?>assets/libs/jquery/jquery-3.7.0.min.js"></script>

<!-- Select2 -->
<link href="<?= BASE_URL ?>assets/libs/select2/select2.min.css" rel="stylesheet" />
<link href="<?= BASE_URL ?>assets/libs/select2/select2-custom.min.css" rel="stylesheet" />
<script src="<?= BASE_URL ?>assets/libs/select2/select2.min.js"></script>

<script src="js/calendario/calendario-data.js"></script>
<script src="js/calendario/calendario-ui.js"></script>

<?php include '../common/footer.php'; ?>