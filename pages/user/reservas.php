<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<main class="db-content" data-user="<?php echo $usuarioActual->id; ?>"
    data-team="<?php echo $usuarioActual->id_equipo; ?>">
    <div class="container-fluid mt-4">
        <h2 class="mb-4">Reservas de Espacios</h2>

        <div id="spinnerCarga" class="text-center my-3 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <h6>Reservas activas</h6>
        <div id="contenedorReservasActivas" class="mb-4"></div>

        <h6>Historial reciente</h6>
        <div id="contenedorReservasHistoricas"></div>


        <p id="mensajeSinReservas" class="text-muted d-none">Aún no hay reservas registradas.</p>
    </div>

    <!-- Botón flotante para nueva reserva -->
    <!--    <button id="btnFloatingReserva" class="btn btn-primary rounded-3 position-fixed bottom-0 end-0 m-4 shadow"
        style="z-index: 1050; width: 50px; height: 50px;">
        <i class="bi bi-calendar-plus fs-4"></i>
    </button> -->
    <button id="btnFloatingReserva" class="btn btn-primary rounded-3 position-fixed shadow"
        style="bottom: 72px; right: 16px; z-index: 1050; width: 50px; height: 50px;">
        <i class="bi bi-calendar-plus fs-4"></i>
    </button>
    <!-- Modal de reserva -->
    <div class="modal fade" id="modalReserva" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReservaLabel">Nueva Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="modalReservaBody">
                    <!-- Contenido generado dinámicamente por JS -->
                </div>
            </div>
        </div>
    </div>

</main>

<?php include '../common/scripts.php'; ?>
<script src="./js/reservas/reservas-data.js"></script>
<script src="./js/reservas/reservas-ui.js"></script>
<?php include '../common/footer.php'; ?>