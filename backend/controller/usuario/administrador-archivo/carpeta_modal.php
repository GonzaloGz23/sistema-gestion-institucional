<?php
// cargar_modal_carpeta.php

// Obtener los parámetros
$usuario = $_GET['usuario'];
$padre = $_GET['padre'];

// Generar el contenido dinámicamente (ejemplo)
$contenidoHTML = '<div class="row">
    <div class="col">
        <input type="text" id="nombreCarpeta" class="form-control" placeholder="Nombre de la Carpeta" />
    </div>
</div>
<div class="row">
    <div class="col">
        <input type="hidden" style="height:1px;visibility:hidden;" id="equippo" value="2" class="form-control" placeholder="..." />
        <input type="hidden" style="height:1px;visibility:hidden;" id="usuarioo" value="' . $usuario . '" class="form-control" placeholder="..." />
        <input type="text" style="height:1px;visibility:hidden;" id="carpetapadr" value="' . $padre . '" class="form-control" placeholder="..." />
    </div>
</div>';

$footerHTML = '<button type="button" id="guardarcarpeta" class="btn btn-primary">Guardar</button>';

// Devolver el JSON
echo json_encode(['contenido' => $contenidoHTML, 'footer' => $footerHTML]);
?>







