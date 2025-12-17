<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<main class="db-content">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gestión de Espacios Reservables</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEspacio" data-action="crear">
                <i class="bi bi-plus-lg"></i> Agregar
            </button>
        </div>

        <div id="spinnerCarga" class="text-center my-3 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <p id="mensajeNoEspacios" class="text-center text-muted d-none">No hay espacios reservables cargados en el
            sistema.</p>

        <div id="contenedorTabla" class="d-none">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaEspacios">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Detalles</th>
                            <th>Edificio Asociado</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEspaciosBody">
                        <!-- Los espacios reservables se insertarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal único para gestionar Espacios Reservables -->
    <div class="modal fade" id="modalEspacio" tabindex="-1" role="dialog" aria-labelledby="modalEspacioLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEspacioLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalEspacioBody">
                    <!-- El contenido se insertará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="modalEspacioConfirm" type="button" class="btn"></button>
                </div>
            </div>
        </div>
    </div>

</main>

<?php include '../common/scripts.php'; ?>

<!-- DataTables Bootstrap 5 -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/dataTables/datatables.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/dataTables/datatable-mobile.css">
<script src="<?= BASE_URL ?>assets/libs/dataTables/datatables.min.js"></script>
<!-- Cargar los scripts -->
<script src="./js/espacios/espacios-data.js"></script>
<script src="./js/espacios/espacios-ui.js"></script>
<?php include '../common/footer.php'; ?>