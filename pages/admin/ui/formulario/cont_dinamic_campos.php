<?php
if (isset($_POST['descrip'])) {
    $descrip = $_POST['descrip'];
    if ($descrip == 9) {
?>

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
    <?php

    } else if ($descrip == 10) {
    ?>
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

    <?php
    } else if ($descrip == 5 || $descrip == 6 || $descrip == 7) {
        // Generar campo repetible con botón

    ?>
        <div id="campos-container" class="mb-3">
            <div class="input-group mb-2">
                <input type="text" name="campo[]" id="campo" class="form-control" placeholder="Opcion">
                <button type="button" class="btn btn-outline-secondary" onclick="agregarCampo(this)">+</button>
            </div>
        </div>


<?php
    } else {
        // Mapeo de tipos y placeholders
        $types = [
            1 => ['type' => 'text',   'placeholder' => 'Texto de respuesta',     'disabled' => false],
            2 => ['type' => 'number', 'placeholder' => 'Número de respuesta',    'disabled' => false],
            3 => ['type' => 'date',   'placeholder' => 'Fecha',                  'disabled' => false],
            4 => ['type' => 'time',   'placeholder' => 'Hora',                   'disabled' => false],
            8 => ['type' => 'file',   'placeholder' => 'Agregar Archivo',        'disabled' => false] // este va deshabilitado
        ];

        if (array_key_exists($descrip, $types)) {
            $type = $types[$descrip]['type'];
            $placeholder = $types[$descrip]['placeholder'];
            $disabled = !empty($types[$descrip]['disabled']) ? 'disabled' : ''; // ternario

            echo '<div class="mb-3">';
            echo '<input type="' . htmlspecialchars($type) . '" name="campo[]" id="campo" class="form-control" placeholder="' . htmlspecialchars($placeholder) . '" ' . $disabled . '>';
            echo '</div>';
        }
    }
}
?>