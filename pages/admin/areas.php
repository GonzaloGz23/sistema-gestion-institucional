<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<main class="db-content" data-entity="<?php echo $usuarioActual->id_entidad; ?>">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gestión de Áreas</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalArea" data-action="crear">
                <i class="bi bi-plus-lg"></i> Agregar
            </button>
        </div>

        <div id="spinnerCarga" class="text-center my-3 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <p id="mensajeNoAreas" class="text-center text-muted d-none">No hay áreas cargadas en el sistema.</p>

        <div id="contenedorTabla" class="d-none">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaAreas">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Entidad Asociada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAreasBody">
                        <!-- Las áreas se insertarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal único para gestionar Áreas -->
    <div class="modal fade" id="modalArea" tabindex="-1" role="dialog" aria-labelledby="modalAreaLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAreaLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalAreaBody">
                    <!-- El contenido se insertará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="modalAreaConfirm" type="button" class="btn"></button>
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
<script src="./js/areas/areas-data.js"></script>
<script src="./js/areas/areas-ui.js"></script>

<?php include '../common/footer.php'; ?>