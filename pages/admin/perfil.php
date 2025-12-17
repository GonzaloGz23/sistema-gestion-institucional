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
        <div class="row gy-4">
            <div class="col-xl-12 col-12">
                <div class="d-flex flex-column gap-4">
                    <!--card-->
                    <div class="card">
                        <!--img-->
                        <div class="rounded-top-3" style="background-image: url(../../dist/assets/images/mentor/mentor-single.png); background-position: center; background-size: cover; background-repeat: no-repeat; height: 228px"></div>
                        <div class="card-body p-md-5">
                            <div class="d-flex flex-column gap-5">
                                <!--img-->
                                <div class="mt-n8">
                                    <img src="../../dist/assets/images/default_profiles/perfil-individual.png" alt="mentor 1" class="img-fluid rounded-4 mt-n8">
                                </div>
                                <div class="d-flex flex-column gap-5">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="d-flex flex-md-row flex-column justify-content-between gap-2">
                                            <!--heading-->
                                            <div>
                                                <h1 class="mb-0"><?= htmlspecialchars($usuarioActual->nombre) ?> <?= htmlspecialchars($usuarioActual->apellido) ?> </h1>
                                                <!--content-->

                                            </div>

                                            <!--button-->

                                        </div>
                                        <div>
                                            <div class="form-check form-switch d-flex align-items-center">
                                                <i class="fe fe-bell me-2 fs-3"></i>
                                                <label class="form-check-label " for="notificame">Recibir notificaciones</label>
                                                <input class="form-check-input ms-2" type="checkbox" role="switch" name="notificame" value="1" id="notificame">
                                            </div>



                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-outline-primary mb-2" id="actualizar">Actualizar</button>

                                        </div>

                                        <div>
                                            <!-- basic table -->
                                            <!-- table head options -->
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Fecha</th>
                                                            <th scope="col">Dispositivo</th>
                                                            <th scope="col">Navegador</th>
                                                            <th scope="col">Estado</th>
                                                            <th scope="col">Check</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tablaCuerpo">
                                                        <tr id="spinnerRow">
                                                            <td colspan="4" class="text-center">
                                                                <div class="spinner-border text-primary" role="status" style="margin: 10px auto;">
                                                                    <span class="visually-hidden">Cargando...</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <!-- basic table -->
                                        </div>
                                        <div class="row">
                                            <?php
                                            $id_empleado = $usuarioActual->id;

                                            $empleado = $pdo->prepare("
                                            SELECT fr.fecha  
                                            FROM formulario_respuestas fr 
                                            LEFT JOIN empleados e ON e.id_empleado = fr.id_empleados
                                            LEFT JOIN preguntas p ON p.id_preguntas = fr.id_preguntas
                                            LEFT JOIN formularios f ON f.id_formularios = p.id_formularios_fk
                                            WHERE f.id_tipos_formularios = 1 AND fr.id_empleados = ?
                                            GROUP BY fr.fecha
                                            ORDER BY fr.fecha DESC
                                            LIMIT 1
                                        ");
                                            $empleado->execute([$id_empleado]);
                                            $resp_empleado = $empleado->fetchAll();

                                            if (count($resp_empleado) > 0) {
                                                foreach ($resp_empleado as $e) {
                                                    $fecha = $e['fecha'];
                                                    // haz lo que necesites con $fecha
                                                }
                                                // Obtener datos del formulario
                                                $respuesta = $pdo->prepare("
                                                    SELECT 
                                                        p.id_preguntas,
                                                        rf.respuesta, 
                                                        p.preguntas, 
                                                        op.opcion, 
                                                        p.orden, 
                                                        p.id_tipo_campo, 
                                                        tp.descripcion, 
                                                        rf.archivo, 
                                                        tf.id_tipos_formularios,
                                                        rf.fecha  
                                                    FROM formulario_respuestas rf
                                                    LEFT JOIN preguntas p ON p.id_preguntas = rf.id_preguntas
                                                    LEFT JOIN formularios f ON f.id_formularios = p.id_formularios_fk
                                                    LEFT JOIN empleados e ON e.id_empleado = rf.id_empleados
                                                    LEFT JOIN tipo_campo tp ON tp.id_tipo_campo = p.id_tipo_campo
                                                    LEFT JOIN tipos_formularios tf ON tf.id_tipos_formularios = f.id_tipos_formularios
                                                    LEFT JOIN opcion_preguntas op ON op.id_opcion_preguntas = rf.id_opcion_preguntas
                                                    WHERE tf.id_tipos_formularios = 1 
                                                    AND rf.fecha = ? 
                                                    AND e.id_empleado = ?
                                                    ORDER BY rf.fecha DESC
                                                ");

                                                $respuesta->execute([$fecha, $id_empleado]);

                                                $respuestas = [];
                                                $data_res = $respuesta->fetchAll();



                                                foreach ($data_res as $i => $row) { //$row = $respuesta->fetch(PDO::FETCH_ASSOC)
                                                    //while ($row = $respuesta->fetch(PDO::FETCH_ASSOC)) { //
                                                    $id = $row['id_preguntas'];

                                                    if (!isset($respuestas[$id])) {
                                                        $respuestas[$id] = [
                                                            'id_preguntas' => $row['id_preguntas'],
                                                            'preguntas' => $row['preguntas'],
                                                            'respuesta' => $row['respuesta'],
                                                            'id_tipo_campo' => $row['id_tipo_campo'],
                                                            'orden' => $row['orden'],
                                                            'archivo' => $row['archivo'],
                                                            'opciones' => []


                                                        ];
                                                    }

                                                    if (!empty($row['opcion'])) {

                                                        $respuestas[$id]['opciones'][] = [
                                                            'texto' => $row['opcion']
                                                        ];
                                                    }
                                                }

                                                foreach ($respuestas as $preg) {
                                                    $id_preguntas = $preg['id_preguntas'];
                                                    $titulo_pregunt = $preg['preguntas'];
                                                    $respuesta = $preg['respuesta'];
                                                    $id_tipo_campo = $preg['id_tipo_campo'];
                                                    $orden = $preg['orden'];
                                                    if ($id_tipo_campo == 6) {

                                                        echo  '<span class="fw-medium text-gray-800">' . $titulo_pregunt . ' : </span>';

                                                        foreach ($preg['opciones'] as $op) {
                                                            echo '<span class="text-secondary"> <small>' . htmlspecialchars($op['texto']) . ' </small></span>';
                                                        }
                                                    } else if ($id_tipo_campo == 5) {
                                                        echo  '<span class="fw-medium text-gray-800">' . $titulo_pregunt . ' : ';

                                                        foreach ($preg['opciones'] as $op) {
                                                            echo '<span class="text-secondary"> <small>' . htmlspecialchars($op['texto']) . ' </small></span>';
                                                        }
                                                        echo '</span>';
                                                    } else if ($id_tipo_campo == 7) {
                                                        echo  '<span class="fw-medium text-gray-800">' . $titulo_pregunt . ' : ';

                                                        foreach ($preg['opciones'] as $op) {
                                                            echo '<span class="text-secondary"> <small>' . htmlspecialchars($op['texto']) . ' </small></span>';
                                                        }
                                                        echo '</span>';
                                                    } else if ($id_tipo_campo == 8) {
                                                        echo '
                                                    <span class="fw-medium text-gray-800">' . $titulo_pregunt . ' : 
                                                        <span class="text-secondary">
                                                            <small>
                                                                <a href="./ui/solicitudes/archivo.php?id_pregunta=' . $id_preguntas . '" target="_blank">
                                                                    <i class="bi bi-eye-fill"></i> ' . $respuesta . '
                                                                </a>
                                                               
                                                            </small>
                                                        </span>
                                                    </span>
                                                ';
                                                    } else if ($id_tipo_campo == 9) {

                                                        $calfificacion = (int)$respuesta;

                                                        $calfificacion = intval($respuesta);

                                                        echo '<div class="" data-selected="' . $calfificacion . '">
                                                        <span class="fw-medium text-gray-800">' . $titulo_pregunt . ' :
                                                        <span class="text-secondary"> <small>
                                                        ';

                                                        for ($j = 1; $j <= 5; $j++) {
                                                            $selected = ($j === $calfificacion) ? 'selected' : '';
                                                            echo '<span class="round ' . $selected . '" data-value="' . $j . '"></span>  ' . $j . '';
                                                        }

                                                        echo '</small></span></</span></div>';
                                                    } else if ($id_tipo_campo == 10) {

                                                        $calfificacion = (int)$respuesta;

                                                        $calfificacion = intval($respuesta);

                                                        echo '<div class="rating" data-selected="' . $calfificacion . '">
                                                        <span class="fw-medium text-gray-800">' . $titulo_pregunt . ' :
                                                        ';

                                                        for ($j = 1; $j <= 5; $j++) {
                                                            $selected = ($j <= $calfificacion) ? 'selected' : '';
                                                            echo '<span class="star ' . $selected . '" data-value="' . $j . '">&#9733;</span>';
                                                        }

                                                        echo '</span></div>';
                                                    } else {
                                                        echo '
                                                    <span class="fw-medium text-gray-800 mb-2">' . $titulo_pregunt . ' : <span class="text-secondary"> <small> ' . $respuesta . '
                                                   </small></span></span>
                                                    
                                                   
                                                    ';
                                                    }
                                                }
                                            } else {
                                                echo '<p>Sin datos cargados</p>';
                                            }
                                            ?>
                                        </div>

                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                    <!--card-->

                </div>
            </div>

        </div>

    </div>
</div>






<?php include '../common/scripts.php'; ?>
<script src="./js/perfil/perfil.js"></script>


<?php include '../common/footer.php'; ?>