<div class="col-xl-6 col-lg-6 col-md-12 col-12">
    <?php
    include '../../../../backend/config/database.php';
    $descrip = $_POST['descrip'];

    if ($descrip === 'equipo') {

        $areas = $pdo->prepare("SELECT * FROM `equipos` WHERE `estado`= 'habilitado'");

        $areas->execute();
        echo '
    
    <div class="mb-3">
                                     
        <select class="form-select" id="sel_equipos" name="sel_equipos[]" aria-label="Default select example" >
            <option selected disabled>Seleccionar Equipo</option>
    ';

        foreach ($areas as $a) {
            echo '
        <option value="' . $a['id_equipo'] . '">' . $a['alias'] . '</option>
        ';
        }
        echo '
        </select>

    </div>
    
    ';
    } elseif ($descrip === 'individual') {
        $empleados = $pdo->prepare("SELECT * FROM `empleados` WHERE `estado`='habilitado'");

        $empleados->execute();
        echo '
    <div class="mb-3">
                                        
        <select class="form-select" id="sel_empleado" name="sel_empleado[]" aria-label="Default select example">
            <option selected disabled>Seleccionar Empleados</option>
    ';

        foreach ($empleados as $e) {
            echo '
        <option value="' . $e['id_empleado'] . '">' . $e['apellido'] . ' ' . $e['nombre'] . '</option>
        ';
        }
        echo '
        </select>

    </div>
    
    ';
    }


    ?>
</div>
<div class="col-xl-6 col-lg-6 col-md-12 col-12" id="tag-container">

</div>