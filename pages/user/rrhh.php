<!-- require_once '../../backend/config/session.php'; // Verificar acceso del admin -->
<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>
<style>
    .bi-star-fill {
        color: gold;
    }

    .cursor-move {
        cursor: move;
    }

    .bg-light {
        opacity: 0.8;
        border: 2px
            /*  dashed #007bff */
        ;
    }
</style>

<?php $idUsuario = $usuarioActual->id; ?>

<?php $idEquipo = $usuarioActual->id_equipo; ?>


<div class="db-content" data-user="<?php echo $idUsuario; ?>">
    <div class="container mb-4">
        <div class="row my-2">
            <h1 class="h2 mb-0">Solicitudes privadas <span class="text-gray-500">
                </span></h1>
        </div>

        <div class="row gy-4">
            <section class="py-6">
                <div class="container">
                    <div class="row">
                        <?php
                        $tipoFormulario = $pdo->prepare(" 
                             SELECT f.nombre, DATE_FORMAT(f.fecha_creacion, '%d/%m/%Y') fechacreacion, f.estado , f.id_formularios, e.id_empleado, eq.id_equipo , fa.general 
                             FROM formulario_asignacion fa
                             LEFT JOIN formularios f on f.id_formularios = fa.id_formulario
                             LEFT JOIN tipos_formularios tf on tf.id_tipos_formularios = f.id_tipos_formularios
                             LEFT JOIN empleados e on e.id_empleado= fa.id_empleados
                             LEFT JOIN equipos eq on eq.id_equipo = fa.id_equipo
                             WHERE  (e.id_empleado = :iduser OR eq.id_equipo = :idequipo OR fa.general = 1)
                                AND tf.id_tipos_formularios = 2 AND f.estado = 'Visible'
                             ORDER BY f.fecha_creacion ASC");

                         $tipoFormulario->execute([
                            'iduser' => $idUsuario,
                            'idequipo' => $idEquipo
                        ]);
                        foreach ($tipoFormulario as $f) {
                            echo '
                                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                    <div class="card card-hover mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-end align-items-center mb-3">
                                                <i class="bi bi-calendar3 text-primary mx-2"></i>
                                                ' . $f['fechacreacion'] . '
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="text-truncate" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    data-bs-custom-class="custom-tooltip"
                                                    data-bs-title="' . $f['nombre'] . '">' . $f['nombre'] . '</h4>
                                            </div>
                                            <div class="row d-flex justify-content-end g-0">
                                                <div class="col-auto">
                                                    <button type="button" class="btn btn-outline-primary mb-2 border-0 button_form_respuestas" id="button-verForm">Solicitar</button>
                                                    <input type="hidden" name="id_form" class="id_form" value="' . $f['id_formularios'] . '">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                        ?>
                    </div>

                    <div id="contenedorFormulario" class="contenedorFormulario"></div>

                    <!-- Mis solicitudes -->
                    <hr class="my-4">
                    <h4 class="mt-4">Mis Solicitudes</h4>

                    <div class="row solicitudes-container">
                        <?php
                        $sqlSolicitudes = $pdo->prepare("
                            SELECT rs.id_solicitud_rh, rs.fecha_solicitud, rs.respuesta AS respuesta_rrhh,
                                   f.nombre AS nombre_formulario, f.id_formularios
                            FROM rrhh_solicitudes rs
                            INNER JOIN formularios f ON f.id_formularios = rs.id_formulario
                            WHERE rs.id_empleado = ? AND f.id_tipos_formularios = 2 
                                  AND (rs.borrado = 0 OR rs.borrado IS NULL)
                            ORDER BY rs.fecha_solicitud DESC
                        ");
                        $sqlSolicitudes->execute([$idUsuario]);
                        $solicitudes = $sqlSolicitudes->fetchAll();
                        ?>

                        <?php if (count($solicitudes) === 0): ?>
                            <div class="col-12">
                                <p class="text-muted">No hay solicitudes hechas hasta el momento.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($solicitudes as $s): ?>
                                <div class="col-lg-3 col-md-4 col-sm-12 mb-3">
                                    <div class="card card-hover">
                                        <div class="card-body">
                                            <div class="mb-2 text-muted small">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?>
                                            </div>
                                            <h5 class="text-truncate"><?= htmlspecialchars($s['nombre_formulario']) ?></h5>

                                            <div class="d-flex justify-content-end align-items-center">
                                                <input type="hidden" class="id_solicitud_rh"
                                                    value="<?= $s['id_solicitud_rh'] ?>">
                                                <input type="hidden" class="id_form" value="<?= $s['id_formularios'] ?>">
                                                <input type="hidden" class="fecha" value="<?= $s['fecha_solicitud'] ?>">
                                                <input type="hidden" class="id_empleado" value="<?= $idUsuario ?>">
                                                <button class="btn btn-outline-secondary button_ver_solicitud_usuario">
                                                    Ver más
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </section>
        </div>
    </div>
    <!-- Modal para ver el detalle de la solicitud -->
    <div class="modal fade" id="modalSolicitudUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Solicitud</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContenidoSolicitud">
                    <!-- Aquí se cargará el contenido de la solicitud -->
                </div>
            </div>
        </div>
    </div>

</div>

<?php include '../common/scripts.php'; ?>
<script src="./js/rrhh/rrhh_solicitud.js"></script>
<?php include '../common/footer.php'; ?>