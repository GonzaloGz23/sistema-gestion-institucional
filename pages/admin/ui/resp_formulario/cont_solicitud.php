<?php
include '../../../../backend/config/database.php';
$id_form = $_POST['id_form'];
$fecha = $_POST['fecha'];
$id_empleado = $_POST['id_empleado'];

// Obtener datos del formulario
$respuesta = $pdo->prepare("SELECT rf.id_formulario_respuestas id_formRespuesta, p.id_preguntas,rf.respuesta, p.preguntas, op.opcion, p.orden, p.id_tipo_campo, tp.descripcion, rf.archivo  FROM `formulario_respuestas` rf
LEFT JOIN preguntas p on p.id_preguntas = rf.id_preguntas
LEFT JOIN formularios f on f.id_formularios = p.id_formularios_fk
LEFT JOIN empleados e on e.id_empleado = rf.id_empleados
LEFT JOIN tipo_campo tp on tp.id_tipo_campo = p.id_tipo_campo
LEFT JOIN opcion_preguntas op on op.id_opcion_preguntas = rf.id_opcion_preguntas
WHERE  f.id_formularios = ? and rf.fecha =? and e.id_empleado =?
ORDER BY  p.orden ASC");
$respuesta->execute([$id_form, $fecha, $id_empleado]);

$respuestas = [];
$data_res = $respuesta->fetchAll();


if (count($data_res) > 0) {
    foreach ($data_res as $i => $row) { //$row = $respuesta->fetch(PDO::FETCH_ASSOC)
        //while ($row = $respuesta->fetch(PDO::FETCH_ASSOC)) { //
        $id = $row['id_preguntas'];

        if (!isset($respuestas[$id])) {
            $respuestas[$id] = [
                'id_formRespuesta'=>$row['id_formRespuesta'],
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

?>

    <style>
        .rating {
            display: inline-flex;
            align-items: center;
        }

        .star {
            font-size: 1rem;
            color: #ccc;
            /* Color gris para estrellas no seleccionadas */
            transition: color 0.2s;
        }

        .star.selected {
            color: #ffc107;
            /* Color dorado para estrellas seleccionadas */
        }

        .rating .star.selected {
            color: #ffc107 !important;
        }

        .round {
            width: 0.7rem;
            /* Tamaño del círculo */
            height: 0.7rem;
            /* Igual que el ancho para que sea redondo */
            background-color: white;
            /* Fondo blanco dentro del círculo */
            border: 1px solid #999191;
            /* Borde negro alrededor del círculo */
            transition: background-color 0.2s, border-color 0.2s;
            /* Transición suave al cambiar de color */
            border-radius: 50%;
            /* Hace que sea un círculo */
            display: inline-block;
            /* Los elementos se muestran en línea */
            margin: 0 5px;
            /* Espaciado entre los círculos */
        }

        /* Estilo para el círculo seleccionado */
        .round.selected {
            background-color: rgb(121, 121, 121);
            /* Cambia el color del círculo cuando está seleccionado */
        }
    </style>


    <div class="row my-2">
        <span class="text-gray-500">
            <span class="fw-bold">Empleados</span>

        </span>

    </div>

    <div class="row">
        <?php
        foreach ($respuestas as $preg) {
            $id_preguntas = $preg['id_preguntas'];
            $titulo_pregunt = $preg['preguntas'];
            $respuesta = $preg['respuesta'];
            $id_tipo_campo = $preg['id_tipo_campo'];
            $orden = $preg['orden'];
            $id_formRespuesta=$preg['id_formRespuesta'];
            if ($id_tipo_campo == 6) {

                echo  '<span class="h4 mb-0">' . $titulo_pregunt . ' : </span>';

                foreach ($preg['opciones'] as $op) {
                    echo '<span class="text-secondary"> <small>' . htmlspecialchars($op['texto']) . ' </small></span>';
                }
            } else if ($id_tipo_campo == 5) {
                echo  '<span class="h4 mb-0">' . $titulo_pregunt . ' : ';

                foreach ($preg['opciones'] as $op) {
                    echo '<span class="text-secondary"> <small>' . htmlspecialchars($op['texto']) . ' </small></span>';
                }
                echo '</span>';
            } else if ($id_tipo_campo == 7) {
                echo  '<span class="h4 mb-0">' . $titulo_pregunt . ' : ';

                foreach ($preg['opciones'] as $op) {
                    echo '<span class="text-secondary"> <small>' . htmlspecialchars($op['texto']) . ' </small></span>';
                }
                echo '</span>';
            } else if ($id_tipo_campo == 8) {
                echo '
            <span class="h4 mb-0">' . $titulo_pregunt . ' : 
                <span class="text-secondary">
                    <small>
                        <a href="./ui/solicitudes/archivo.php?id_pregunta=' . $id_formRespuesta . '" target="_blank">
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
                <span class="h4 mb-0">' . $titulo_pregunt . ' :
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
                <span class="h4 mb-0">' . $titulo_pregunt . ' :
                ';

                for ($j = 1; $j <= 5; $j++) {
                    $selected = ($j <= $calfificacion) ? 'selected' : '';
                    echo '<span class="star ' . $selected . '" data-value="' . $j . '">&#9733;</span>';
                }

                echo '</span></div>';
            } else {
                echo '
            <span class="h4 mb-0">' . $titulo_pregunt . ' : <span class="text-secondary"> <small> ' . $respuesta . '
           </small></span></span>
            
           
            ';
            }
        }


        ?>

    </div>
<?php

} else {
    echo '<p>No hay ninguna respuesta.</p>';
}

?>