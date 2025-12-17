<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>



<!-- Agregar el CSS personalizado para el estilo del index -->
<link rel="stylesheet" href="../common/css/index-custom.css">



<link rel="stylesheet" href="./css/capacitacion/styles.css">




<div class="db-content principal" >


    <div class="container mb-4">
        <div class="row my-2 mr-2">
        

            <h1 class="h2 mb-0"><b style="visibility:hidden;">a</b>Capacitaciones internas</h1>
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
                <a href="capacitacion.php?mi_variable=1" class="text-decoration-none">
                    <div class="card-hover-svg card card-body d-flex flex-column gap-4">
                        <div class="">
                            <span class="icon-shape icon-xxl">
                                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor"
                                    class="bi bi-people" viewBox="0 0 16 16">
                                    <path
                                        d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4" />
                                </svg>
                            </span>
                        </div>
                        <div>
                            <h3 class="mb-0">Crear Capacitaciones</h3>
                        </div>
                    </div>
                </a>
            </div>
        
         
        </div>
    </div>
</div>

<?php
$prueb = 0;
if (isset($_GET['mi_variable'])) {
    $prueb = 1;
   } elseif (isset($_GET['grupo_variable'])) {
     $prueb = 2;
}
?>


<div id="actual">

<div  id="actual-capac"aria-labelledby="actualCapacLabel"  name="">
   
</div>


</div>


<div id="certificaciones">

</div>


<div class="db-content crearCapacitacion" style="display:none;">
    <div class="container mb-4">
      <div class="row gy-4">
   
                <div class="col-12">
                <a href="capacitacion.php" class="link-primary mb-3" style="margin-left: 10px;">
                <i class="bi bi-caret-left-fill fs-2 "></i>
                    </a>
                    <div class="accordion accordion-flush" id="accordionExample" style="margin-left: 15px;">
                        <div class="card mb-3 mt-3 border-top border-4 border-0">
                            <div class="card-body">
                                <form action="" id="noteForm">
                                <label for="fechaHora" id="labelFecha" class="form-label fs-3">Crear Capacitacion</label>

<br>
                                <label for="fechaHora" id="labelFecha" class="form-label fs-5">Fecha y hora inicio-fin</label>

                                <div class="mb-3 row g-2 align-items-center">
    <div class="col-md-2">
        <input type="datetime-local" id="fechaHora" name="fechaHora" class="form-control" required>
    </div>
    <div class="col-md-2">
        <input type="datetime-local" id="fechaHorafin" name="fechaHorafin" class="form-control" required>
    </div>
    <div class="col-md-3">
    <b for="radioDefault1" id="labelModalidad" class="form-label">Modalidad</b>

<div style=" align-items: center;">


    <div class="form-check">
        <input class="form-check-input" type="radio" name="radioDefault" id="radioDefault1">
        <label class="form-check-label" for="radioDefault1">
            Presencial
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="radioDefault" id="radioDefault2">
        <label class="form-check-label" for="radioDefault2">
            Virtual
        </label>
    </div>

     <div class="form-check">
        <input class="form-check-input" type="radio" name="radioDefault" id="radioDefault3">
        <label class="form-check-label" for="radioDefault3">
            Asincrona
        </label>
    </div>
</div>
        </div>
</div>
                                    <div >


                                      


                                          <br>


                                          <div class="mb-3 enlace" >
  <label for="enlace" class="form-label">Enlace</label>
  <input type="url" class="form-control" id="enlace" name="enlace" placeholder="Ingresa la URL aquí">
  <div class="form-text">Por favor, introduce la dirección web (URL).</div>
</div>



                                        <div class="mb-3 lugar" >
                                            <label for="lugar" id="labeletiqueta" class="form-label">Seleccionar Lugar</label>
                                            <select id="lugar" name="newNoteTag" class="form-control" >
    <option value="" selected disabled>Seleccionar Edificio</option>
    </select>
                                        </div>


                                           <div class="mb-3 obligacion" >
                                            <label for="obligacion" id="labelobligacion" class="form-label">Obligacion</label>
                                            <select id="obligacion" name="newNoteTag" class="form-control">
                                            <option value="obligatorio">obligatorio</option>
                                            <option value="optativo">optativo</option>
                                             </select>
                                        </div>




                                        <div class="mb-3" >
                                        <label for="formFile" id="labelMateriales" class="form-label">Materiales</label>

                                        <input class="form-control" type="file" id="formFile" name="materiales[]" multiple >

                                        </div>



                                        <div class="mb-3 tema">
  <label for="tema" class="form-label">Tema</label>
  <input type="text" class="form-control" id="tema" name="tema" placeholder="Escribir Tema" required>
</div>


                                        <div class="mb-3" >
  <label for="requerimientos" class="form-label">Requerimientos</label>
 <textarea id="requerimientos" class="form-control"></textarea>
</div>

      <div class="mb-3" ></div>
 <div class="form-check">
  <input class="form-check-input" type="checkbox" value="" id="Equipos">
  <label class="form-check-label" for="Equipos">
    Grupos
  </label>
</div>
</div>
<br>
<div class="row mb-3 align-items-center"> 
<div class="col" id="tGrupos" style="display: none;"> 
    <label class="form-label" for="selectGrupos" >Seleccionar Grupos:</label>
 <select class="form-control selecionarE" name="grupos" id="selectGrupos" style="display: none;">
    <option value="" selected disabled>Seleccionar Grupos</option>
    </select>
</div>

<div class="col" id="nuevoS"> 
    <label class="form-label">Seleccionar Empleados/Equipos:</label>
    <select class="js-example-basic-single form-control selecionarE"  name="state">
    <option value="" selected disabled>Seleccionar Empleados o Equipos</option>

</select>
</div>

<div class="col-auto" id="nuevoB"> 
  
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
        <div class="form-check">
            <input class="form-check-input" type="radio" name="radioDeemplead" id="radioDeemplead3" value="todos"> 
            <label class="form-check-label" for="radioDeemplead3">Todos</label>
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








<div class="dropdown me-2">
     <!--   <a class="btn border-0" href="#" role="button"
           data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-add fs-3"></i>
        </a> -->
        <ul class="dropdown-menu p-2" id="colabore" style="width: 500px;">
            <li class="mb-2">
                <div class="d-flex align-items-center">
                    <input type="search" class="form-control me-2" placeholder="Buscar Empleado/Equipo" id="buscar-colaborador" style="width: 50%;">
                    <div class="d-flex">
                        <div class="form-check me-2">
                            <input class="form-check-input" type="radio" name="radioDeempleado" id="radioDeempleado1" >
                            <label class="form-check-label" for="radioDeempleado1">Individual</label>
                        </div>
                        <div class="form-check me-2">
                            <input class="form-check-input" type="radio" name="radioDeempleado" id="radioDeempleado2">
                            <label class="form-check-label" for="radioDeempleado2">Equipo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="radioDeempleado" id="radioDeempleado3">
                            <label class="form-check-label" for="radioDeempleado3">Todos</label>
                        </div>
                    </div>
                </div>
            </li>
            <li>
                <div id="lista-usuarios">
                    </div>
            </li>
        </ul>
    </div>




                                            <div class="dropdown me-2">
                                              <!--  <a class="btn border-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Crear Requerimientos
                                                </a>
                                                <ul class="dropdown-menu p-2" style="width: 250px;">
                                                    <li class="mb-2">
                                                        <input type="search" class="form-control" id="nuevaEtiquetaInput" placeholder="Ingresa el nombre de la etiqueta">
                                                    </li>
                                                    <div id="listaEtiquetas">
                                                        <?php
                                                        /*foreach ($contenidoetiqueta as $eticax) {
                                                            $idEtiquetasx = $eticax['id_etiqueta'];
                                                            $descripcionEtiquetasx = $eticax['descripcion_etiqueta']; */
                                                        ?>
                                                            <li>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" value="<?php //echo $descripcionEtiquetasx; ?>" id="etiqueta<?php //echo $idEtiquetasx; ?>">
                                                                    <label class="form-check-label" for="etiqueta<?php //echo $idEtiquetasx; ?>">
                                                                        <?php //echo $descripcionEtiquetasx; ?>
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        <?php //} ?>
                                                    </div>
                                                </ul>-->
                                            </div>
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

<script src="./js/capacitacion/capacitaciones.js"></script> 

<!-- Cargar los scripts -->

<?php include '../common/footer.php'; ?>