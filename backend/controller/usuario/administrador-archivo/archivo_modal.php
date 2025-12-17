<?php
// cargar_modal_carpeta.php

// Obtener los parámetros
$usuario = $_GET['usuario'];
$padre = $_GET['padre'];

// Generar el contenido dinámicamente (ejemplo)
$contenidoHTML = '  <form id="addFileManagment" enctype="multipart/form-data">
            <div class="row">
              <div class="col mb-2">
                <label for="nombrearchivo" class="form-label">Nuevo archivo</label>
                <!-- no me guarda archivos, .rar-.zip-.exe-.xls    -->
                <input type="file" id="archivopuro"
                  accept=".doc, .docx, .xlsx, .xls, .pdf, .csv, .pptx, .jpeg, .txt, .png, .jpg, .ppt"
                  class="form-control" placeholder="..." multiple/>
              </div>
            </div>
            <div class="row">

              <div class="col">
                <label for="nombrearchiv" class="form-label">Nombre del archivo</label>

                <input type="text" id="nombrearchivo" class="form-control"
                  placeholder="Ingrese aqui el nombre del Archivo" />
              </div>
            </div>

          
          </form>';

$footerHTML = '<button type="button" id="guardararchivo" class="btn btn-primary">Guardar</button>';

// Devolver el JSON
echo json_encode(['contenido' => $contenidoHTML, 'footer' => $footerHTML]);
?>