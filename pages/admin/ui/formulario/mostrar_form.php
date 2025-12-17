<?php
include '../../../../backend/config/database.php';
$id_form = $_POST['id_form'];

// Obtener datos del formulario
$tipoFormulario = $pdo->prepare("SELECT `id_formularios`, `nombre`, `fecha_creacion`, `id_empleados`, `id_tipos_formularios`, `estado`, `fecha_hasta` FROM `formularios` WHERE `id_formularios` = ?");
$tipoFormulario->execute([$id_form]);
//$formulario = $tipoFormulario->fetch(PDO::FETCH_ASSOC);
foreach ($tipoFormulario as $f) {
    $titulo = $f['nombre'];
}

// Obtener preguntas + opciones
$pregunta = $pdo->prepare("
    SELECT 
        p.id_preguntas, p.preguntas, p.orden, p.obligatorio, 
        s.id_opcion_preguntas, s.opcion, 
        tp.id_tipo_campo, tp.descripcion, tp.tipo , tp.id_tipo_campo
    FROM preguntas p
    LEFT JOIN opcion_preguntas s ON s.id_preguntas = p.id_preguntas
    LEFT JOIN tipo_campo tp ON tp.id_tipo_campo = p.id_tipo_campo
    WHERE p.id_formularios_fk = ? AND p.estado = 'habilitado'
    ORDER BY p.orden ASC
");
$pregunta->execute([$id_form]);

$preguntas = [];
while ($row = $pregunta->fetch(PDO::FETCH_ASSOC)) {
    $id = $row['id_preguntas'];

    if (!isset($preguntas[$id])) {
        $preguntas[$id] = [
            'id_preguntas' => $row['id_preguntas'],
            'orden' => $row['orden'],
            'texto' => $row['preguntas'],
            'obligatorio' => $row['obligatorio'],
            'tipo' => $row['tipo'],
            'descripcion' => $row['descripcion'],
            'id_tipoCampo' => $row['id_tipo_campo'],
            'opciones' => []
        ];
    }

    if (!empty($row['opcion'])) {
        $preguntas[$id]['opciones'][] = $row['opcion'];
    }
}

echo '

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
<div class="card card-hover mb-3">
<div class="card-header  border-0">
    <h3 class="ms-2 display-6"> ' . $titulo . ' </h3>
    </div>
    <!-- Card Body -->
    <div class="card-body">

';
?>
<form action="" id="resp_form">
    <?php
    foreach ($preguntas as $preg) {
        echo "<div class='mb-3'>";
        echo "<label><strong>{$preg['orden']}. {$preg['texto']}</strong>";
        if ($preg['obligatorio']) echo " <span class='text-danger'>(*)</span>";
        echo "</label><br>";

        $tipo = $preg['tipo'];
        $opciones = $preg['opciones'];
        $id_campo = $preg['id_tipoCampo'];

        // Render dinámico
        if ($tipo === 'select') {
            echo "<select class='form-select' >";
            foreach ($opciones as $op) {
                echo "<option>$op</option>";
            }
            echo "</select>";
        } elseif ($tipo === 'radio') {
            if ($id_campo == 9) {
                echo '
       <div class="d-flex justify-content-between col-3 calificacion" id="estrellas">

            <input type="radio" id="campo" name="campo[]" value="1">
            <label for="star1" data-value="1">1</label>

            <input type="radio" id="campo" name="campo[]" value="2">
            <label for="star2" data-value="2">2</label>

            <input type="radio" id="campo" name="campo[]" value="3">
            <label for="star3" data-value="3">3</label>

            <input type="radio" id="campo" name="campo[]" value="4">
            <label for="star4" data-value="4">4</label>


            <input type="radio" id="campo" name="campo[]" value="5">
            <label for="star5" data-value="5">5</label>
        </div>
       
       ';
            } else if ($id_campo == 10) {
                echo '
            
            <div class="d-flex justify-content-between col-3 calificacion">
            <input type="radio" id="campo1" name="campo[]" value="1" hidden>
            <label for="campo1" data-value="1"><i class="bi bi-star"></i></label>

            <input type="radio" id="campo2" name="campo[]" value="2" hidden>
            <label for="campo2" data-value="2"><i class="bi bi-star"></i></label>

            <input type="radio" id="campo3" name="campo[]" value="3" hidden>
            <label for="campo3" data-value="3"><i class="bi bi-star"></i></label>

            <input type="radio" id="campo4" name="campo[]" value="4" hidden>
            <label for="campo4" data-value="4"><i class="bi bi-star"></i></label>

            <input type="radio" id="campo5" name="campo[]" value="5" hidden>
            <label for="campo5" data-value="5"><i class="bi bi-star"></i></label>
        </div>
            ';
            } else {

                foreach ($opciones as $i => $op) {
                    echo "<div class='form-check form-check-inline'>";
                    echo "<input class='form-check-input' type='{$tipo}' name='campo_{$preg['orden']}[]' id='op_{$preg['orden']}_$i' >";
                    echo "<label class='form-check-label' for='op_{$preg['orden']}_$i'>$op</label>";
                    echo "</div>";
                }
            }
        } else if ($tipo === 'checkbox') {
            foreach ($opciones as $i => $op) {
                echo '
           <div class="form-check">
  <input class="form-check-input" type="' . $tipo . '" value="" id="op_' . $preg['orden'] . '_' . $i . '">
  <label class="form-check-label" for="op_' . $preg['orden'] . '_' . $i . '">
    ' . $op . '
  </label>
  </div>
           ';
            }
        } else {
            echo "<input type='{$tipo}' class='form-control' >";
        }

        echo "</div>";
    }

    echo '
    
</form>
  </div>
</div>
 </div>
';
