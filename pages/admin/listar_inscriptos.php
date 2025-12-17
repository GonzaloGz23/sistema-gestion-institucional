<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<?php
$idCapacitacion = $_POST['id'] ?? null;
$nombreCapacitacion = $_POST['nombreCap'] ?? "Sin Título";
?>
<main class="db-content"
      data-entity="<?= $usuarioActual->id_entidad ?>"
      data-user="<?= $usuarioActual->id ?>"
      data-capacitacion="<?= $idCapacitacion ?>"
      data-capacitacion-nombre="<?= htmlspecialchars($nombreCapacitacion) ?>">

    <div class="container-fluid mt-4">

        <!-- TÍTULO DE LA CAPACITACIÓN -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><?= htmlspecialchars($nombreCapacitacion) ?></h2>
        </div>

        <!-- SPINNER DE CARGA -->
        <div id="spinnerCarga" class="text-center my-3 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <!-- MENSAJE SI NO HAY DATOS -->
        <p id="mensajeNoCapacitaciones" class="text-center text-muted d-none">
            No hay inscriptos para esta capacitación.
        </p>

        <!-- TABLA -->
        <div id="contenedorTabla" class="d-none">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaCapacitaciones">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>DNI</th>
                            <th>Sexo</th>
                            <th>Edad</th>
                            <th>Teléfono</th>
                            <th>Fecha de Inscripción</th>
                            <th>Correo</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCapacitacionesBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- ========================== -->
<!-- ESTILOS EXTRA -->
<!-- ========================== -->

<style>
.accordion-mode .section-header {
    border-radius: 8px 8px 0 0;
}

.accordion-mode .section-content {
    border-radius: 0 0 8px 8px;
    margin-bottom: 1rem;
}

.section-separator {
    height: 20px;
}

@media (max-width: 991px) {
    .accordion-mode .section-content {
        margin-bottom: 2rem;
    }
}

[data-theme="dark"] .accordion-mode .section-header {
    background-color: var(--bs-primary) !important;
}
</style>

<?php include '../common/scripts.php'; ?>

<!-- ========================== -->
<!-- LIBRERÍAS DE DATATABLES -->
<!-- ========================== -->

<!-- DataTables Bootstrap 5 -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/dataTables/datatables.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/dataTables/datatable-mobile.css">
<script src="<?= BASE_URL ?>assets/libs/dataTables/datatables.min.js"></script>

<!-- BOTONES DE EXPORTACIÓN -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- Dependencias para exportar PDF / Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- ========================== -->
<!-- TU JS FINAL -->
<!-- ========================== -->
<script src="js/revision-capacitaciones/inscriptos-ui.js"></script>

<?php include '../common/footer.php'; ?>
