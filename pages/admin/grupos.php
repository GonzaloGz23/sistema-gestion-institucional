<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>


<!-- Agregar el CSS personalizado para el estilo del index -->
<link rel="stylesheet" href="../common/css/index-custom.css">



<link rel="stylesheet" href="./css/capacitacion/styles.css">



<div class="db-content principal" >


    <div class="container mb-4">
        <div class="row my-2 mr-2">
        

            <h1 class="h2 mb-0"><b style="visibility:hidden;">a</b>Grupos</h1>
        </div>
        <div class="row gy-4">


        <div class="col-xl-1 col-lg-1 col-md-12 col-1" style="width:1px;visibility:hidden;">
                <a  class="text-decoration-none">
                    <div class="card-hover-svg card card-body d-flex flex-column gap-4">
                        <div class="">
                            <span class="icon-shape icon-xxl">
                             
                            </span>
                        </div>
                        <div>
                        </div>
                    </div>
                </a>
            </div>



   <div class="col-xl-3 col-lg-4 col-md-12 col-12">
                <a href="grupos.php?grupo_variable" class="text-decoration-none">
                    <div class="card-hover-svg card card-body d-flex flex-column gap-4">
                        <div class="">
                            <span class="icon-shape icon-xxl">
                           <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-person-plus" viewBox="0 0 16 16">
  <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
  <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
</svg>
                            </span>
                        </div>
                        <div>
                            <h3 class="mb-0">Crear Grupo</h3>
                        </div>
                    </div>
                </a>
            </div>





   
        </div>
    </div>
</div>



<div id="actual">

<div  id="actual-capac"aria-labelledby="actualCapacLabel"  name="">
   
</div>


</div>


<div class="db-content crearGrupo" style="display:none;">
    <div class="container mb-4">
      <div class="row gy-4">


                <div class="col-12">
                <a href="grupos.php" class="link-primary mb-3" style="margin-left: 10px;">
                <i class="bi bi-caret-left-fill fs-2 "></i>
                    </a>
                    <div class="accordion accordion-flush" id="accordionExample" style="margin-left: 15px;">
                        <div class="card mb-3 mt-3 border-top border-4 border-0">
                            <div class="card-body">
                                <form action="" id="noteForm">
                                <label for="fechaHora" id="labelFecha" class="form-label fs-3">Crear Grupo</label>

<br>
                         
                                    <div >


                                      


                                          <br>


     <div class="mb-3 tema">
  <label for="tema" class="form-label">Nombre del Grupo</label>
  <input type="text" class="form-control" id="grupo" name="grupo" placeholder="Escribir Grupo" required>
</div>
                      
<div class="row mb-3 align-items-center"> 

<div class="col"> 
    <label class="form-label">Seleccionar Empleados/Equipos:</label>
    <select class="js-example-basic-single form-control selecionarE"  name="state">
    <option value="" selected disabled>Seleccionar Empleados o Equipos</option>

</select>
</div>

<div class="col-auto"> 
  
     <label class="form-label d-block">Aplicar a:</label> 
    <div class="d-flex">
        <div class="form-check me-2">
            <input class="form-check-input" type="radio" name="radioDeemplead" id="radioDeemplead1" value="individual"> 
            <label class="form-check-label" for="radioDeemplead1">Individual</label>
        </div>
        <div class="form-check me-2">
            <input class="form-check-input" type="radio" name="radioDeemplead" id="radioDeemplead2" value="equipo"> 
            <label class="form-check-label" for="radioDeemplead2">Equipo</label>
        </div>
      
    </div>
</div>

</div>
<div id="selectedItemsList" class="mt-3">

</div>
                                        <input type="text" style="height:1px; visibility:hidden;" id="entidad" value="<?php echo  $usuarioActual->id; ?>">

                                        <div class="d-flex justify-content-between flex-wrap">
  

    
</div>
<div class="d-flex justify-content-between flex-wrap">












                                          
                                            <div class="d-flex align-items-center">
        <button type="submit" class="btn btn-outline-primary" id="guardarBtn">Guardar</button>
        <div class="spinner-border text-primary ms-2 d-none" role="status" id="spinnerGuardar">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
                                            </div>


                                            <div class="d-flex justify-content-between flex-wrap">

        <div id="usuarios-seleccionados" class="mt-2">
        <h6></h6>
        <ul id="lista-seleccionados" class="list-unstyled"></ul>
    </div>
    </div>


                                        </div>


                                     
                                        <input type="datetime-local" id="reminder" name="recordatorio" style="height:1px;visibility:hidden;">

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                
            </div>

            <div class="row d-flex justify-content-center" id="notesContainer">











            </div>
            <br>
        </div>
    </div>
  

    <script src="../../dist/assets/libs/jquery/jquery-3.7.0.min.js"></script>

<!-- Select2 -->
<link href="../../dist/assets/libs/select2/select2.min.css" rel="stylesheet" />
<link href="../../dist/assets/libs/select2/select2-custom.min.css" rel="stylesheet" />
<script src="../../dist/assets/libs/select2/select2.min.js"></script>


<?php include '../common/scripts.php'; ?>

<script src="./js/capacitacion/grupos.js"></script> 


<!-- Cargar los scripts -->

<?php include '../common/footer.php'; ?>









