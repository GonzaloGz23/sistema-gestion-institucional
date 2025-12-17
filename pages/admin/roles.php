<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<main class="db-content">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gestión de Roles</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearRol">
                <i class="bi bi-plus-lg"></i> Agregar
            </button>
        </div>
        <div id="contenedorTabla" class="d-none">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaRoles">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Módulos Asignados</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaRolesBody">
                        <!-- Los roles se insertarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</main>
<!-- modales -->
<?php include './ui/roles/modal_crearRol.php'; ?>
<?php include './ui/roles/modal_editarRol.php'; ?>
<?php include './ui/roles/modal_asignarRol.php'; ?>


<?php include '../common/scripts.php'; ?>
<!-- DataTables Bootstrap 5 -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/dataTables/datatables.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/dataTables/datatable-mobile.css">
<script src="<?= BASE_URL ?>assets/libs/dataTables/datatables.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<script src="./js/roles/roles-data.js"></script>
<?php include '../common/footer.php'; ?>