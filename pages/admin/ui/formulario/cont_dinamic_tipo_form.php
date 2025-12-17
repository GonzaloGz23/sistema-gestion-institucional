<?php
include '../../../../backend/config/database.php';
?>
<div class="row my-2">
    <h1 class="h2 mb-0">Lista Tipo de Formularios</h1>
</div>

<div class="row d-flex justify-content-beetwen align-items-end">
    <div class="col-6 ">
        <div class="mb-3">
            <span class="badge bg-secondary-soft ms-2" type="button" id="filt_Permanente">Permanente</span>
            <span class="badge bg-success-soft ms-2" type="button" id="filt_Habilitado">Habilitado</span>
            <span class="badge bg-danger-soft ms-2" type="button" id="filt_Inactivo">Inactivo</span>
            <span class="badge bg-primary-soft ms-2" type="button" id="filt_Todos">Todos</span>

        </div>
    </div>
    <div class="col-6">
        <div class="mb-3">
            <label for="search-input" class="form-label">Buscar</label>
            <input class="form-control" type="search" id="search-input" placeholder="Buscar formularios...">
        </div>
    </div>
</div>


<div class="row gy-4 mb-4">
    <?php
    $tipoFormulario = $pdo->prepare("SELECT *,
DATE_FORMAT(`fecha_creacion`, '%d/%m/%Y') AS fecha,
CASE 
WHEN `estados` = 'habilitado' THEN 'text-success'
WHEN `estados` = 'inactivo' THEN 'text-danger'
WHEN `estados` = 'permanente' THEN 'text-secondary'
END texto
FROM `tipos_formularios`  ORDER BY `tipos_formularios`.`id_tipos_formularios` DESC");

    $tipoFormulario->execute();

    foreach ($tipoFormulario as $t) {
        echo '

 <div class="accordion accordion-flush col-md-6 col-xl-3 col-12"  >
    <div class="card mb-3 mt-3 border-top border-4 border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="' . $t['texto'] . '">' . $t['estados'] . '</span>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn  border-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
                        <i class="bi bi-three-dots-vertical ' . $t['texto'] . '"></i>
                    </button>

                ';
        if ($t['estados'] == 'permanente') {
        } else {


            echo '
            
                <div class="dropdown-menu">
                    <a class="dropdown-item cambiar-estado" href="#" data-id="' . $t['id_tipos_formularios'] . '" data-estado="habilitado">Habilitado</a>
                    <a class="dropdown-item cambiar-estado" href="#" data-id="' . $t['id_tipos_formularios'] . '" data-estado="inactivo">Inactivo</a>
                    <a class="dropdown-item cambiar-estado" href="#" data-id="' . $t['id_tipos_formularios'] . '" data-estado="permanente">Permanente</a>
                </div>';
        }
        echo '
            </div>

        </div>
        <div class="d-flex justify-content-end align-items-center">
            <span class="mb-0 text-secondary fs-6"> <small>' . $t['fecha'] . '</small></span>
        </div>
        <div class="text-center my-4" id="accordionExample" data-bs-toggle="collapse" data-bs-target="#colapTipoForm_' . $t['id_tipos_formularios'] . '">
            <h3 id="titulo_tex" class="text-truncate"  data-bs-toggle="tooltip" title=" ' . $t['nombre'] . '">' . $t['nombre'] . '</h3>
            
        </div>
                    <div class="collapse" id="colapTipoForm_' . $t['id_tipos_formularios'] . '" data-bs-parent="#accordionExample">
                        <p  data-bs-toggle="tooltip" title="' . $t['descripcion'] . '">' . $t['descripcion'] . '</p>
                       
                    </div>
            
        </div>
    </div>
</div>

';
    }
    ?>
</div>