<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<link rel="stylesheet" href="./css/admin-archivos/styles.css">


<?php $id_usuariox = $usuarioActual->id;    
  // ... (tu código PHP)
echo "<script>let id_usuariox = " . json_encode($id_usuariox) . ";</script>";


$checkEquipox = "SELECT `id_equipo` FROM `empleados` WHERE `id_empleado` = ?";
$stmtCheckEquipox = $pdo->prepare($checkEquipox);
$stmtCheckEquipox->execute([$id_usuariox]);
$id_equipox = $stmtCheckEquipox->fetchColumn();





?>
<!-- Nav - fin-->
<main class=" db-content" data-entity="<?php echo $usuarioActual->id_entidad; ?>">
  <div class=" row d-flex justify-content-center">

    <div class="col-11 contenido-contenedor mt-2">
      <div class="card shadow-sm">
        <div class="nuevo">
        <div class="card-header d-flex justify-content-between align-items-center">
          <?php if (!isset($_GET['p'])) {    ?>
            <h4 class="mb-0 fw-bold"> Adm. de Archivos </h4>
          <?php           }            ?>
          <h4 class="mb-0 fw-bold" id="seguir">  </h4>
          <?php if (isset($_GET['p'])) {      ?>
            <div class="d-flex gap-2">
              <button role="button" class="btn btn-outline-primary btn-sm text-uppercase fw-bold" data-bs-toggle="modal"
                data-bs-target="#modalNuevaCarpeta"><i class="bi bi-folder-plus fs-4"></i> </button>
              <button role="button" class="btn btn-outline-primary btn-sm text-uppercase fw-bold" data-bs-toggle="modal"
                data-bs-target="#modalNuevoArchivo"><i class="bi bi-file-earmark-plus fs-4"></i> </button>
            </div>
          <?php       } else {        ?>
            <button role="button" class="btn btn-outline-primary btn-sm text-uppercase fw-bold" type="button"
              data-bs-toggle="modal" data-bs-target="#modalNuevaCarpeta"> <i class="bi bi-folder-plus fs-4" ></i>  </button> <?php     }      ?>
          <input type="hidden" value="<?php echo $id_usuariox; ?>" name="idusuario" id="idusuario">

          <input type="hidden" value="<?php if (isset($_GET['p'])) {
                                        echo  $_GET['p'];
                                      } ?>" name="idpadre" id="idpadre">

<input type="hidden" style="height:1px;visibility:hidden;" id="equippo" value="<?php echo $id_equipox; ?>"
              class="form-control" placeholder="..." />
            <input type="hidden" style="height:1px;visibility:hidden;" id="usuarioo" value="<?php echo $id_usuariox; ?>"
              class="form-control" placeholder="..." />
            <?php ?>
              <input type="hidden" style="height:1px;visibility:hidden;" id="carpetapadr"
              value="<?php if (isset($_GET['p'])) { echo $_GET['p']; } ?>" class="form-control" placeholder="..." />

        </div>
        <div class="card-body">

          <div class="d-flex flex-wrap gap-3" id="files-container">

          
          </div>
        </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Modales -->


<div id="stats-container">
</div>


<!-- Menú contextual personalizado -->
<div id="menuContextual" class="dropdown-menu shadow" style="position: absolute; display: none; z-index: 2000;">
  <button class="dropdown-item" id="opcionRenombrar">Renombrar</button>
  <?php if (!isset($_GET['p'])) {    ?>
  <button class="dropdown-item" id="opcionColaborador">Colaborador</button>
  <?php    }else  {   ?>
    <button class="dropdown-item" id="opcionColaborador"></button>

  <?php      }        ?>
  <div id="elimN">
</div>
  <div id="elim">
  <button class="dropdown-item text-danger" id="opcionEliminar">Eliminar</button>
  </div>
  
</div>



<div class="modal fade" id="modalNuevaCarpeta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNuevaCarpetaTitle">Nueva Capeta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="row">
        <div class="col">


        </div>
      </div>
      <div class="modal-body pb-0" id="contenido-carpeta">
        <div class="row">
          <div class="col">
          </div>
        </div>

      </div>
      <div class="modal-footer" id="footer-carpeta">
      </div>
    </div>
  </div>
</div>


 <!-- nueva archivo -->
 <div class="modal fade" id="modalNuevoArchivo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNuevoArchivoTitle">Archivo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
          ></button>
      </div>
      <div class="modal-body pb-0" id="contenido-archivo">


      </div>
      <div class="modal-footer" id="button-archivo">

      </div>
    </div>
  </div>
</div>



  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>

  <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->



<?php include '../common/scripts.php'; ?>

<script src="./js/administrador_carpeta/administrador.js"></script>


<?php include '../common/footer.php'; ?>