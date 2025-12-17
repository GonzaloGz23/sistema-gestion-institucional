<?php
// Incluir configuración de sesión y validar usuario
require_once '../../backend/config/session_config.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    header("Location: /sistemaInstitucional/pages/login/login.php");
    exit;
}
?>
<?php include '../common/header.php'; ?>

<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php';  ?>

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
        <a href="./formulario.php" class="link-primary mb-3"><i class="bi bi-caret-left-fill fs-2 "></i></a><br>
        <div class="row my-2">
            <h1 class="h2 mb-0">Incio</h1>
        </div>
        <div class="row gy-4">
            <div class="col-12">
                <form method="post" id="form-creacion">
                    <div class="card mb-3 border-top border-4 card-hover-with-icon border-0">
                        <div class="card-header  border-0">


                            <div class="row">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-12">
                                    <div class="mb-3">

                                        <input type="text" id="form_titulo" name="form_titulo" class="form-control" placeholder="Formulario sin título">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-12 col-12">
                                    <div class="mb-3">

                                        <select class="form-select" id="tipo_form" name="tipo_form" aria-label="Default select example">
                                            <option selected disabled>Tipo Formulario</option>
                                            <?php

                                            $tipoFormulario = $pdo->prepare("SELECT tf.nombre, tf.id_tipos_formularios,tf.varios ,count(f.id_tipos_formularios) cantidad FROM `tipos_formularios`  tf
                                            LEFT JOIN formularios f on f.id_tipos_formularios = tf.id_tipos_formularios
                                            where tf.estados != 'inactivo'
                                            GROUP BY tf.id_tipos_formularios ");

                                            $tipoFormulario->execute();

                                            foreach ($tipoFormulario as $t) {
                                                if ($t['varios'] == 0 && $t['cantidad'] > 0) {

                                                  
                                                }else{
                                                    echo '
                                                    <option value="' . $t['id_tipos_formularios'] . '">' . $t['nombre'] . '</option>
                                                    ';
                                                }
                                            }


                                            ?>
                                            <option value="Null">Otro</option>
                                        </select>

                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-12 col-12">
                                    <div class="mb-3">
                                        <select class="form-select" id="tipo-enfoque" name="tipo-enfoque" aria-label="Default select example">
                                            <option selected id="general">General</option>
                                            <option value="equipo">Por Equipos</option>
                                            <option value="individual">Individual</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-none " id="opcion-asignacion">

                                </div>


                            </div>

                        </div>

                    </div>
                    <div id="contenedor-preguntas">
                        <!-- Tarjeta de una pregunta -->
                        <div class="card border-0 mb-3 pregunta">
                            <div class="card-header text-center border-0">
                                <i class="bi bi-grip-horizontal text-primary fs-3" type="button"></i>

                                <input type="hidden" name="orden[]" id="orden" class="orden-input" value="1">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-12 col-12">
                                        <div class="mb-3">
                                            <input type="text" name="pregunta[]" id="pregunta" class="form-control" placeholder="Pregunta">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-12 col-12">
                                        <div class="mb-3">
                                            <select class="form-select tipoCampos" name="tipoCampos[]" id="tipoCampos">
                                                <?php
                                                $tipo_campo = $pdo->prepare("SELECT * FROM `tipo_campo`");
                                                $tipo_campo->execute();
                                                foreach ($tipo_campo as $t) {
                                                    echo '<option value="' . $t['id_tipo_campo'] . '">' . $t['descripcion'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-12 cont-campos">
                                        <div class="mb-3">
                                            <input type="text" name="campo[]" id="campo" class="form-control" placeholder="Texto de respuesta">
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="card-body d-flex justify-content-end">
                                <i class="bi bi-trash text-danger fs-3" onclick="this.closest('.pregunta').remove()"></i>
                                <div class="form-check form-switch ">
                                    <label class="form-check-label" for="flexSwitchCheckDefault">Obligatorio</label>
                                    <input class="form-check-input ms-2 me-4" type="checkbox" name="obligatorio[]" id="obligatorio">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOTÓN PARA DUPLICAR -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-primary mb-2" onclick="duplicarPregunta()">Agregar Pregunta</button>
                        <button type="submit" class="btn btn-outline-primary mb-2"> Guardar<span class="spinner-border spinner-border-sm ms-2" id="spinner" role="status" aria-hidden="true" style="display: none;"></span></button>
                    </div>

                </form>

            </div>
        </div>


    </div>
</div>






<?php include '../common/scripts.php'; ?>
<script src="./js/formulario/crear_formulario.js"></script>


<?php include '../common/footer.php'; ?>