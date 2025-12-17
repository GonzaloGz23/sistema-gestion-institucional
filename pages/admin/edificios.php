<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<main class="db-content" data-entity="<?php echo $usuarioActual->id_entidad; ?>">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gestión de Edificios</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEdificio" data-action="crear">
                <i class="bi bi-plus-lg"></i> Agregar
            </button>
        </div>

        <div id="spinnerCarga" class="text-center my-3 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <p id="mensajeNoEdificios" class="text-center text-muted d-none">No hay edificios cargados en el sistema.</p>

        <div id="contenedorTabla" class="d-none">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaEdificios">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th scope="col">Entidad Asociada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEdificiosBody">
                        <!-- Los edificios se insertarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal único para gestionar Edificios -->
    <div class="modal fade" id="modalEdificio" tabindex="-1" role="dialog" aria-labelledby="modalEdificioLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEdificioLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalEdificioBody">
                    <!-- El contenido se insertará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="modalEdificioConfirm" type="button" class="btn"></button>
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
<script src="./js/edificios/edificios-data.js"></script>
<script src="./js/edificios/edificios-ui.js"></script>
<?php include '../common/footer.php'; ?>