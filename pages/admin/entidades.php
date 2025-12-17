<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<main class="db-content">
    <!-- Contenedor de Gestión de Entidades -->
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gestión de Entidades</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEntidad" data-action="crear">
                <i class="bi bi-plus-lg"></i> Agregar Entidad
            </button>
        </div>

        <!-- Spinner de carga -->
        <div id="spinnerCarga" class="text-center my-3 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <!-- Mensaje si no hay entidades -->
        <p id="mensajeNoEntidades" class="text-center text-muted d-none">No hay entidades cargadas en el sistema.</p>

        <!-- Contenedor de la tabla (se oculta si no hay datos) -->
        <div id="contenedorTabla" class="d-none">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Edificios Asociados</th>
                            <th scope="col">Áreas Asociadas</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEntidadesBody">
                        <!-- Las entidades se cargarán dinámicamente aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal dinámico reutilizable -->
    <div class="modal fade" id="modalEntidad" tabindex="-1" role="dialog" aria-labelledby="modalEntidadLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEntidadLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalEntidadBody">
                    <!-- El contenido del modal se genera dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="modalEntidadConfirm" type="button" class="btn"></button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../common/scripts.php'; ?>

<!-- Cargar los scripts -->
<script src="./js/entidades/entidades-data.js"></script>
<script src="./js/entidades/entidades-ui.js"></script>
<?php include '../common/footer.php'; ?>