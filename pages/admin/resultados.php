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
</style>
<div class="db-content">
    <div class="container mb-4">
        <div class="row my-2">
            <h1 class="h2 mb-0">Resultados <span class="text-gray-500">
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
                             SELECT f.nombre, DATE_FORMAT(f.fecha_creacion, '%d/%m/%Y') fechacreacion, f.estado , f.id_formularios, e.id_empleado, eq.id_equipo , fa.general FROM `formulario_asignacion` fa
                            LEFT JOIN formularios f on f.id_formularios = fa.id_formulario
                            LEFT JOIN tipos_formularios tf on tf.id_tipos_formularios = f.id_tipos_formularios
                            LEFT JOIN empleados e on e.id_empleado= fa.id_empleados
                            LEFT JOIN equipos eq on eq.id_equipo = fa.id_equipo
                            WHERE  (e.id_empleado = :iduser OR eq.id_equipo = :idequipo OR fa.general = 1)
                            AND tf.id_tipos_formularios = 4
                            
                                                        ORDER BY f.fecha_creacion ASC");


                        $tipoFormulario->execute([
                            'iduser' => $iduser,
                            'idequipo' => $idequipo
                        ]);
                        foreach ($tipoFormulario as $f) {
                            echo '
                                
                                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                    <div class="card card-hover mb-3">

                                        <!-- Card Body -->
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
                                                        <button type="button" class="btn btn-outline-primary mb-2 border-0 button_form_respuestas " id="button-verForm">Ver Resultados</button>
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


                    <div id="contenedorFormulario" class="contenedorFormulario">


                    </div>

                    <div class="modal fade" id="modalVerSolicitud" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Solicitud</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" id="contenidoSolicitud">
                                    <!-- Aquí se cargan los datos -->
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>
</div>




<?php include '../common/scripts.php'; ?>
<script src="./js/resp_formulario/solicitudes.js"></script>
<?php include '../common/footer.php'; ?>