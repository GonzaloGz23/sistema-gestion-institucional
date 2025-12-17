<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<main class="db-content">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gestión de Equipos</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEquipo" data-action="crear">
                <i class="bi bi-plus-lg"></i> Agregar
            </button>
        </div>

        <div id="spinnerCarga" class="text-center my-3 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <p id="mensajeNoEquipos" class="text-center text-muted d-none">No hay equipos cargados en el sistema.</p>

        <div id="contenedorTabla" class="d-none">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaEquipos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Área Asociada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEquiposBody">
                        <!-- Los equipos se insertarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal único para gestionar Equipos -->
    <div class="modal fade" id="modalEquipo" tabindex="-1" role="dialog" aria-labelledby="modalEquipoLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEquipoLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalEquipoBody">
                    <!-- El contenido se insertará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="modalEquipoConfirm" type="button" class="btn"></button>
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
<script src="./js/equipos/equipos-data.js"></script>
<script src="./js/equipos/equipos-ui.js"></script>
<?php include '../common/footer.php'; ?>