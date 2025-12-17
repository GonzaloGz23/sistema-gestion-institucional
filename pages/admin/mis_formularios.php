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
            <h1 class="h2 mb-0">Mis Formularios</h1>
        </div>
        <div class="row gy-4">
            <section class="py-6">
                <div class="container">
                    <div class="row">
                        <?php
                       // Obtener datos del usuario de forma segura
                       $usuarioActual = obtenerUsuarioActual();
                       $id_equipo = $usuarioActual['id_equipo'];

                       $tipoFormulario = $pdo->prepare("
                           SELECT 
                               f.id_formularios, 
                               f.estado,  
                               e.id_equipo, 
                               f.nombre, 
                               f.id_empleados,  
                               DATE_FORMAT(f.fecha_creacion, '%d/%m/%Y') AS fechacreacion 
                           FROM `formularios` f
                           LEFT JOIN empleados e ON e.id_empleado = f.id_empleados
                           WHERE e.id_equipo = ?
                           ORDER BY f.fecha_creacion ASC
                       ");
                       
                       $tipoFormulario->execute([$id_equipo]);
                        foreach ($tipoFormulario as $f) {
                            echo '
                            <div class="col-lg-3 col-md-4 col-sm-12 mb-3">
                                    <div class="card card-hover">
                                        <div class="card-body text-center">
                                       
                                           <div class="d-flex justify-content-between align-items-center  mb-3">
                                                <div>
                                                    <i class="bi bi-calendar3 text-secondary mx-2"></i>
                                                    ' . $f['fechacreacion'] . '
                                                </div>
                                                 <div class="">
                                                  <span class="badge bg-secondary-soft">' . $f['estado'] . '</span>
                                                <span></span>
                                               
                                                </div>
                                                
                                            </div>
                                            <h3 class="text-truncate" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="' . $f['nombre'] . '">' . $f['nombre'] . '</h3>

                                            <div class="d-flex justify-content-between align-items-center">
                                               
                                                <div class="">             
                                                    <button class="btn btn-outline-primary border-0 button_form" id="button-verForm">Ver Formulario</button>
                                                    <input type="hidden" name="id_form" class="id_form" value="' . $f['id_formularios'] . '">
                                                </div>
                                                <div class="btn-group">
                                                        <button type="button" class="btn  border-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
                                                            <i class="bi bi-three-dots-vertical text-primary"></i>
                                                        </button>

                                                    
                                                
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item cambiar-estado" href="#" data-id="' . $f['id_formularios'] . '" data-estado="Visible">Visible</a>
                                                        <a class="dropdown-item cambiar-estado" href="#" data-id="' . $f['id_formularios'] . '" data-estado="Oculto">Oculto</a>
                                                        
                                                    </div>
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
                </div>
            </section>
        </div>
    </div>
</div>




<?php include '../common/scripts.php'; ?>
<script src="./js/formulario/misformularios.js"></script>
<?php include '../common/footer.php'; ?>