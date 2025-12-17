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
        $preguntas[$id]['opciones'][] = [
            'id' => $row['id_opcion_preguntas'],
            'texto' => $row['opcion']
        ];
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
    <input type="hidden" class="id_form" value="<?= htmlspecialchars($id_form) ?>">
    <?php
    foreach ($preguntas as $preg) {
        echo "<div class='mb-3 respuestas'>";
        echo "<label><strong>{$preg['orden']}. {$preg['texto']}</strong>
        
        <input type='hidden' name='id_pregunta' value='{$preg['id_preguntas']}'>
         <input type='hidden' name='obligatorio' value='{$preg['obligatorio']}'>

        ";
        if ($preg['obligatorio'])
            echo " <span class='text-danger'>(*)</span>";
        echo "</label><br>";

        $tipo = $preg['tipo'];
        $opciones = $preg['opciones'];
        $id_campo = $preg['id_tipoCampo'];

        // Render dinámico
        if ($tipo === 'select') {
            echo '<select class="form-select" name="campo_' . $preg['orden'] . '">';
            echo '<option value="">Selecciona una opción</option>';
            foreach ($preg['opciones'] as $op) {
                echo '<option value="' . $op['id'] . '">' . htmlspecialchars($op['texto']) . '</option>';
            }
            echo '</select>';
        } elseif ($tipo === 'radio') {
            if ($id_campo == 9) {
                echo '
       <div class="d-flex justify-content-between col-3 estrellas" id="estrellas">
       ';
                for ($i = 1; $i <= 5; $i++) {
                    echo '
              <input type="radio" id="campo' . $id_campo . '" name="campo[][' . $id_campo . ']" value="' . $i . '">
            <label for="campo' . $id_campo . '" data-value="' . $i . '">' . $i . '</label>
        ';
                }
                echo '

          
        </div>
        
       
       ';
            } else if ($id_campo == 10) {
                echo '
            
            <div class="d-flex justify-content-between col-3 calificacion">

             ';
                for ($i = 1; $i <= 5; $i++) {
                    echo '
              <input type="radio" id="campo' . $i . '" name="campo[][' . $id_campo . ']" value="' . $i . '" hidden>
            <label for="campo' . $i . '" data-value="' . $i . '"><i class="bi bi-star"></i></label>
        ';
                }
                echo '

          
        </div>
            ';
            } else {

                foreach ($opciones as $i => $op) {
                    echo "<div class='form-check form-check-inline'>";
                    echo "<input class='form-check-input' type='{$tipo}' name='campo_{$preg['orden']}' id='op_{$preg['orden']}_$i' value='{$op['id']}' data-texto='" . htmlspecialchars($op['texto']) . "'>";
                    echo "<label class='form-check-label' for='op_{$preg['orden']}_$i'>{$op['texto']}</label>";
                    echo "</div>";
                }
            }
        } else if ($tipo === 'checkbox') {
            foreach ($opciones as $i => $op) {
                echo "<div class='form-check'>";
                echo "<input class='form-check-input' type='checkbox' name='campo_{$preg['orden']}[]' id='op_{$preg['orden']}_$i' value='{$op['id']}' data-texto='" . htmlspecialchars($op['texto']) . "'>";
                echo "<label class='form-check-label' for='op_{$preg['orden']}_$i'>{$op['texto']}</label>";
                echo "</div>";
            }
        } else {
            echo "<input type='{$tipo}' class='form-control' >";
        }

        echo "</div>";
    }

    echo '
    <button type="submit" class="btn btn-outline-primary mb-2"> Guardar<span class="spinner-border spinner-border-sm ms-2" id="spinner" role="status" aria-hidden="true" style="display: none;"></span></button>
</form>
  </div>
</div>
 </div>
';
