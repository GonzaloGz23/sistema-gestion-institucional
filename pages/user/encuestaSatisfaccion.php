<!-- require_once '../../backend/config/session.php'; // Verificar acceso del admin -->
<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<?php $idUsuario = $usuarioActual->id; ?>

<div class="db-content" data-user="<?php echo $idUsuario; ?>">
    <div class="container mb-4">
        <div class="row my-2">
            <h1 class="h2 mb-0">Encuesta de uso <span class="text-gray-500">
                </span></h1>
        </div>

        <div class="row gy-4">
            <section class="py-6">
                <div class="container">
                    <div class="row">
                        <?php
                        // Obtener datos del usuario de forma segura
                        $usuarioActual = obtenerUsuarioActual();
                        $iduser = $usuarioActual['id'];
                        $idequipo = $usuarioActual['id_equipo'];

                        $tipoFormulario = $pdo->prepare(" 
                             SELECT f.nombre, DATE_FORMAT(f.fecha_creacion, '%d/%m/%Y') fechacreacion, f.estado , f.id_formularios, e.id_empleado, eq.id_equipo , fa.general 
                             FROM `formulario_asignacion` fa
                             LEFT JOIN formularios f on f.id_formularios = fa.id_formulario
                             LEFT JOIN tipos_formularios tf on tf.id_tipos_formularios = f.id_tipos_formularios
                             LEFT JOIN empleados e on e.id_empleado= fa.id_empleados
                             LEFT JOIN equipos eq on eq.id_equipo = fa.id_equipo
                             WHERE  (e.id_empleado = :iduser OR eq.id_equipo = :idequipo OR fa.general = 1)
                                AND tf.id_tipos_formularios = 7 AND f.estado = 'Visible'
                             ORDER BY f.fecha_creacion ASC");

                        $tipoFormulario->execute([
                            'iduser' => $iduser,
                            'idequipo' => $idequipo
                        ]);
                        $formularios = $tipoFormulario->fetchAll();

                        if (count($formularios) > 0) {


                            foreach ($formularios as $s): ?>
                                <div class="col-lg-3 col-md-4 col-sm-12 mb-3">
                                    <div class="card card-hover">
                                        <div class="card-body">
                                            <div class="mb-2 text-muted small">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?= date('d/m/Y ', strtotime($s['fechacreacion'])) ?>
                                            </div>
                                            <h5 class="text-truncate"><?= htmlspecialchars($s['nombre']) ?></h5>

                                            <div class="d-flex justify-content-end align-items-center">

                                                <input type="hidden" class="id_form" value="<?= $s['id_formularios'] ?>">
                                                <input type="hidden" class="id_empleado" value="<?= $iduser ?>">

                                                <button class="btn btn-outline-secondary button_form_respuestas">
                                                    Ver más
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php endforeach;
                        } else {
                            echo '<p>No hay formularios disponibles.</p>';
                        }
                        ?>
                    </div>

                    <div id="contenedorFormulario" class="contenedorFormulario"></div>



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
<script src="./js/formularios/formulariouser.js"></script>
<?php include '../common/footer.php'; ?>