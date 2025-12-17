<?php
include '../../../../backend/config/database.php';
$id_form = $_POST['id_form'];

// Obtener datos del formulario incluyendo el estado de vista
$tipoFormulario = $pdo->prepare("SELECT e.id_empleado, e.nombre, e.apellido, rf.fecha, f.id_formularios, rs.vista, rs.id_solicitud_rh
FROM `formulario_respuestas` rf
LEFT JOIN preguntas p on p.id_preguntas = rf.id_preguntas
LEFT JOIN formularios f on f.id_formularios = p.id_formularios_fk
LEFT JOIN empleados e on e.id_empleado = rf.id_empleados
LEFT JOIN rrhh_solicitudes rs on rs.id_solicitud_rh = rf.id_solicitud_rh
WHERE f.id_formularios = ? AND (rs.borrado = 0 OR rs.borrado IS NULL)
GROUP BY rf.fecha ORDER BY rf.fecha DESC");
$tipoFormulario->execute([$id_form]);
//$formulario = $tipoFormulario->fetch(PDO::FETCH_ASSOC);
$tp_form = $tipoFormulario->fetchAll();

if (count($tp_form) > 0) {
?>




<div class="row my-2">
    <span class="text-gray-500">
        <span class="fw-bold">Empleados</span>

    </span>

</div>

<div class="row">
    <?php


    foreach ($tp_form as $f) {
        // Determinar si mostrar punto rojo (solicitud no vista)
        $puntoRojo = ($f['vista'] == 0 || $f['vista'] === null) ? 
            '<span class="punto-rojo-notificacion"></span>' : '';
        
        echo '
                                
                                <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12 col-xl-4">
                                    <div class="card card-hover mb-3 position-relative">
                                        ' . $puntoRojo . '
                                        <!-- Card Body -->
                                        <div class="card-body">

                                            <div class="d-flex flex-row gap-3 align-items-center">
                                                <div>
                                                <img src="../../dist/assets/images/avatar/avatar-1.jpg" alt="avatar" class="rounded-circle icon-shape icon-xl">
                                                </div>
                                                <div class="d-flex flex-column">
                                                <span class="text-secondary"> <small> 
                                                    ' . $f['fecha'] . ' <i class="bi bi-calendar3 mx-2"></i></small></span>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-truncate" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-custom-class="custom-tooltip"
                                                        data-bs-title="' . $f['nombre'] . ' ' . $f['apellido'] . '">' . $f['nombre'] . ' ' . $f['apellido'] . '</span>
                                                </div>
                                            
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-outline-primary mb-2 border-0  button_ver_solicitud" id="button_ver_solicitud" data-id-solicitud="' . $f['id_solicitud_rh'] . '">Ver solicitud</button>
                                                <input type="hidden" name="id_form" class="id_form" value="' . $f['id_formularios'] . '">
                                                <input type="hidden" name="fecha" class="fecha" value="' . $f['fecha'] . '">
                                                 <input type="hidden" name="id_empleado" class="id_empleado" value="' . $f['id_empleado'] . '">

                                            </div>
                                            
                                        </div>
                                        
                                    </div>
                                </div>
                                ';
    }
    ?>


</div>
<?php
} else {
    echo '<p>No hay ninguna respuesta.</p>';
}

?>