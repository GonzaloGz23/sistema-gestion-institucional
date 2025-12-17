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
            <h1 class="h2 mb-0">Solicitudes privadas</h1>

        </div>
        <div class="row gy-4">
            <section class="py-6">
                <div class="container">
                    <div class="row">
                        <?php
                        $tipoFormulario = $pdo->prepare(" 
                             SELECT f.nombre, DATE_FORMAT(f.fecha_creacion, '%d/%m/%Y') fechacreacion, f.estado , f.id_formularios, e.id_empleado, eq.id_equipo , fa.general FROM `formulario_asignacion` fa
                            LEFT JOIN formularios f on f.id_formularios = fa.id_formulario
                            LEFT JOIN tipos_formularios tf on tf.id_tipos_formularios = f.id_tipos_formularios
                            LEFT JOIN empleados e on e.id_empleado= fa.id_empleados
                            LEFT JOIN equipos eq on eq.id_equipo = fa.id_equipo
                            WHERE e.id_empleado = 1 || eq.id_equipo =1 || fa.general =1 and tf.id_tipos_formularios=2
                            
                                                        ORDER BY f.fecha_creacion ASC");

                        $tipoFormulario->execute();
                        foreach ($tipoFormulario as $f) {
                            // Contar solicitudes nuevas (no vistas) para este formulario
                            $contarNuevas = $pdo->prepare("
                                SELECT COUNT(*) as total_nuevas
                                FROM rrhh_solicitudes rs
                                WHERE rs.id_formulario = ? 
                                AND (rs.vista = 0 OR rs.vista IS NULL)
                                AND (rs.borrado = 0 OR rs.borrado IS NULL)
                            ");
                            $contarNuevas->execute([$f['id_formularios']]);
                            $nuevas = $contarNuevas->fetch()['total_nuevas'];
                            
                            // Mostrar contador solo si hay solicitudes nuevas
                            $contadorHtml = $nuevas > 0 ? '<span class="badge rounded-pill bg-danger contador-formulario-notificacion">' . ($nuevas > 99 ? '99+' : $nuevas) . '</span>' : '';
                            
                            echo '
                                
                                <div class="col-xl-3 col-lg-4 col-md-5 col-sm-12 col-xs-12">
                                    <div class="card card-hover mb-3 position-relative">
                                        ' . $contadorHtml . '
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
                                                        <button type="button" class="btn btn-outline-primary mb-2 border-0 button_form_respuestas " id="button-verForm">Ver Solicitudes</button>
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
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Contenido de la solicitud (se sobrescribe) -->
                                    <div id="contenidoSolicitud">
                                        <!-- Aquí se cargarán los datos dinámicamente -->
                                    </div>

                                    <!-- Campo de respuesta de RRHH -->
                                    <div class="mt-4">
                                        <h5>Respuesta de RRHH:</h5>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="inputRespuestaRRHH" placeholder="Escribe una respuesta..." aria-label="Respuesta de RRHH" aria-describedby="btnGuardarRespuesta">
                                            <button class="btn btn-outline-primary" type="button" id="btnGuardarRespuesta" data-id-solicitud="" disabled>Enviar</button>
                                            <div class="spinner-border text-primary ms-2 d-none" id="spinnerRespuestaRRHH" role="status" style="width: 1.5rem; height: 1.5rem;">
                                                <span class="visually-hidden">Guardando...</span>
                                            </div>
                                        </div>
                                    </div>
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
<script src="./js/solicitudes/solicitudes.js"></script>
<?php include '../common/footer.php'; ?>