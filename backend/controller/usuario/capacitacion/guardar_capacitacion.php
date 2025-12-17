<?php
require_once "../../../config/database.php";
require_once "../../../config/usuario_actual.php";
$id_usuario_actual = $usuarioActual->id;

$checkEquipox = "SELECT `id_equipo` FROM `empleados` WHERE `id_empleado` = ?";
$stmtCheckEquipox = $pdo->prepare($checkEquipox);
$stmtCheckEquipox->execute([$id_usuario_actual]);
$id_equipo_actual = $stmtCheckEquipox->fetchColumn();



function formatearTamanoArchivo($bytes) {
    if ($bytes >= 1073741824) { // GB
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) { // MB
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) { // KB
        return number_format($bytes / 1024, 2) . ' KB';
    } else { // Bytes
        return $bytes . ' bytes';
    }
}

$link = $_POST['link'] ?? '';
$lugar = $_POST['lugar'] ?? '';
$obligacion = $_POST['obligacion'] ?? '';

$materiales = $_FILES['materiales'] ?? null;
$fechainicio = $_POST['fechaInicio'] ?? null;
$fechafin = $_POST['fechaFin'] ?? null;
$empleadoActual = $_POST['usuarioActual'] ?? '';
$tema = $_POST['temas'] ?? '';
$requerimientos = $_POST['requerimientos'] ?? '';
$grupos = $_POST['grupo'] ?? '';

if(!empty($grupos)){

  $tipoGrup = $pdo->prepare("SELECT `tipo_grupo` FROM `grupos` WHERE `id_grupo` = $grupos");
    $tipoGrup->execute();

    $Tgrupo = $tipoGrup->fetchColumn();

    if($Tgrupo == 'individual'){
$tipoGrupo = $pdo->prepare("SELECT `id_empleado` FROM `r_grupos_empleados` WHERE `id_grupo` = $grupos");
    $tipoGrupo->execute();

    $Tgrupos = $tipoGrupo->fetchAll(PDO::FETCH_ASSOC);
    } else if($Tgrupo == 'equipo'){

        $tipoGrupo = $pdo->prepare("SELECT `id_empleado` FROM `r_grupos_empleados` WHERE `id_grupo` = $grupos");
    $tipoGrupo->execute();

    $Tgrupos = $tipoGrupo->fetchAll(PDO::FETCH_ASSOC);
 
     $ipEmpleado = $pdo->prepare("SELECT `id_empleado` FROM `r_grupos_empleados` WHERE `id_grupo` = :id_grupo");
    $ipEmpleado->execute([':id_grupo' => $grupos]);
    $iTempleado = $ipEmpleado->fetchAll(PDO::FETCH_COLUMN);


  $placeholderx = implode(',', array_fill(0, count($iTempleado), '?'));

        // 2. Prepare the SQL statement with the IN clause
        // Using prepared statements with IN clause is important for security (prevents SQL injection)
        $tipoEquipo = $pdo->prepare("SELECT DISTINCT `id_equipo` FROM `empleados` WHERE `id_empleado` IN ($placeholderx)");

        // 3. Execute the statement, passing the array of IDs
        // PDO::execute() can take an array for bound parameters, which automatically binds each element
        $tipoEquipo->execute($iTempleado);

        // 4. Fetch the results
        $Tequipo = $tipoEquipo->fetchAll(PDO::FETCH_ASSOC);
    }

   

   
}

$modalidad = $_POST['modalidad'] ?? '';
$carpetaDestino = "../../../../uploads/capacitacion/";
$colaboradoresIds = json_decode($_POST['colaboradores'] ?? '[]');
$equiposIds = json_decode($_POST['equipos'] ?? '[]');

$zonaHorariaArgentina = new DateTimeZone('America/Argentina/Buenos_Aires');
$fechaHoraActual = new DateTime('now', $zonaHorariaArgentina);
$actual = $fechaHoraActual->format('Y-m-d H:i:s');

// Validar y formatear la fecha y hora (sin cambios)
if (!empty($fechainicio)) {
    $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $fechainicio);
    if ($dateTime) {
        $fechainicio = $dateTime->format('Y-m-d H:i:s');
    } else {
        echo json_encode(["success" => false, "error" => "Formato de fecha/hora inválido"]);
        exit;
    }
}

if (!empty($fechafin)) {
    $dateTime2 = DateTime::createFromFormat('Y-m-d\TH:i', $fechafin);
    if ($dateTime2) {
        $fechafin = $dateTime2->format('Y-m-d H:i:s');
    } else {
        echo json_encode(["success" => false, "error" => "Formato de fecha/hora inválido"]);
        exit;
    }
}

if (!empty($fechainicio) && !empty($fechafin) && !empty($tema)) {
    try {
        $pdo->beginTransaction(); // Iniciar transacción

        $eventoId = null;
       $eventosInsert = "INSERT INTO `eventos`(`id_creador`, `id_equipo_creador`, `titulo`, `descripcion`, `color`, `start`, `end`, `tipo_evento`, `borrado`, `fecha_creacion`) VALUES ('$id_usuario_actual','$id_equipo_actual','Capacitacion Interna','$tema','#06a17d','$fechainicio','$fechafin','Personalizado','0','$actual')";
       $stmtEvento = $pdo->prepare($eventosInsert);
       $stmtEvento->execute();
       $eventoId = $pdo->lastInsertId();


if(!empty($requerimientos)){

        // Insertar en la tabla de capacitación
        $capacitacionId = null;
        $queryCapacitacion = "INSERT INTO `capacitacion`(`fecha-inicio`, `fecha-fin`, `modalidad`, `link`, `lugar`, `temas`, `requerimientos`, `id_empleado`, `fecha_creacion`,`id_eventos`, `obligacion`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtCapacitacion = $pdo->prepare($queryCapacitacion);
        $stmtCapacitacion->execute([$fechainicio, $fechafin, $modalidad, $link ?: null, $lugar ?: null, $tema, $requerimientos, $empleadoActual,$actual ,$eventoId, $obligacion]);
        $capacitacionId = $pdo->lastInsertId();
}else {
        // Insertar en la tabla de capacitación
        $capacitacionId = null;
        $queryCapacitacion = "INSERT INTO `capacitacion`(`fecha-inicio`, `fecha-fin`, `modalidad`, `link`, `lugar`, `temas`, `id_empleado`, `fecha_creacion`, `id_eventos`, `obligacion`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtCapacitacion = $pdo->prepare($queryCapacitacion);
        $stmtCapacitacion->execute([$fechainicio, $fechafin, $modalidad, $link ?: null, $lugar ?: null, $tema, $empleadoActual,$actual, $eventoId, $obligacion]);
        $capacitacionId = $pdo->lastInsertId();
}
        // Procesar y guardar archivos
        if (!empty($materiales) && is_array($materiales['name'])) {
            for ($i = 0; $i < count($materiales['name']); $i++) {
                $nombreArchivoOriginal = $materiales['name'][$i];
                $archivoTemporal = $materiales['tmp_name'][$i];
                $tamano = $materiales['size'][$i];
                $tamanoFormateado = formatearTamanoArchivo($tamano);
                $nombreArchivoOriginal = str_replace(' ', '_', $nombreArchivoOriginal);
                $nombreArchivoUnico = uniqid() . '_' . $nombreArchivoOriginal;
                $rutaCompleta = $carpetaDestino . $nombreArchivoUnico;

                if (move_uploaded_file($archivoTemporal, $rutaCompleta)) {
                    $sqlMaterial = "INSERT INTO `materiales`(`ruta_material`, `nombre_material`, `tamano_material`, `id_capacitacion`) VALUES (?, ?, ?, ?)";
                    $stmtMaterial = $pdo->prepare($sqlMaterial);
                    $stmtMaterial->execute([$rutaCompleta, $nombreArchivoUnico, $tamanoFormateado, $capacitacionId]);
                } else {
                    // Manejar el error de la subida del archivo si es necesario
                    echo json_encode(["success" => false, "error" => "Error al subir uno de los archivos."]);
                    $pdo->rollBack();
                    exit;
                }
            }
        }







  // --- *** Modificado: Recopilar IDs únicos de todos los empleados a asociar *** ---
  $allEmployeeIdsToAssociate = $colaboradoresIds; // Empezar con empleados seleccionados individualmente

  if (!empty($equiposIds)) {
      // Obtener IDs de empleado para todos los equipos seleccionados
      // Usamos placeholders (?) y execute con array para seguridad
      $placeholders = implode(',', array_fill(0, count($equiposIds), '?'));
      $queryEmpleadosEnEquipos = "SELECT `id_empleado` FROM `empleados` WHERE `id_equipo` IN ($placeholders)";
      $stmtEmpleadosEnEquipos = $pdo->prepare($queryEmpleadosEnEquipos);
      $stmtEmpleadosEnEquipos->execute($equiposIds);
      $empleadosDeEquipos = $stmtEmpleadosEnEquipos->fetchAll(PDO::FETCH_COLUMN); // Obtener solo la columna 'id_empleado'

      // Combinar los IDs de los empleados individuales y los IDs de los empleados de los equipos
      $allEmployeeIdsToAssociate = array_merge($allEmployeeIdsToAssociate, $empleadosDeEquipos);
  }

  // Obtener IDs de empleado únicos para evitar duplicados
  $uniqueEmployeeIdsToAssociate = array_unique($allEmployeeIdsToAssociate);
  // --- *** Fin Modificación: Recopilar IDs únicos *** ---


  // --- *** Modificado: Insertar UNIQUE employees into colaborador_capacitacion and eventos_asignaciones *** ---
  // 4. Insertar cada empleado único (individualmente seleccionado o parte de un equipo seleccionado)
  $queryColaboradorIndividual = "INSERT INTO `colaborador_capacitacion`(`id_capacitacion`, `id_colaborador`, `visible`) VALUES (?, ?, 'no')";
  $stmtColaboradorIndividual = $pdo->prepare($queryColaboradorIndividual);

  


  foreach ($uniqueEmployeeIdsToAssociate as $idEmpleado) {
       // Verificar si idEmpleado es válido antes de insertar
       if (!empty($idEmpleado)) {
           try {
               // Insertar en colaborador_capacitacion (vinculando capacitación con empleado individual)
               // INSERT IGNORE intenta insertar y simplemente ignora si ya existe una fila con esa clave única.
               // Necesitas que tu tabla colaborador_capacitacion tenga un índice UNIQUE en (id_capacitacion, id_colaborador)
               $queryColaboradorIndividual = "INSERT IGNORE INTO `colaborador_capacitacion`(`id_capacitacion`, `id_colaborador`, `visible`) VALUES (?, ?, 'no')";
               $stmtColaboradorIndividual = $pdo->prepare($queryColaboradorIndividual);
               $stmtColaboradorIndividual->execute([$capacitacionId, $idEmpleado]);

           

           } catch (PDOException $e) {
               // Manejar errores de inserción, especialmente si no usas INSERT IGNORE y hay duplicados
               // Si el error es una violación de clave única (código '23000'), puedes ignorarlo o loguearlo
               if ($e->getCode() == '23000') {
                   error_log("Advertencia: Duplicado al insertar colaborador/evento para empleado " . $idEmpleado . " y capacitación/evento " . $capacitacionId . "/" . $eventoId);
               } else {
                    // Si es otro tipo de error, puedes hacer rollback o loguearlo severamente
                    error_log("Error grave al insertar colaborador/evento para empleado " . $idEmpleado . ": " . $e->getMessage());
                    // Opcional: $pdo->rollBack(); echo json_encode(["success" => false, "error" => "Error interno al asignar colaboradores: " . $e->getMessage()]); exit;
               }
           }
       } else {
            error_log("Advertencia: Se intentó insertar un empleado con ID vacío o nulo.");
       }
  }
  // --- *** Fin Modificación: Insertar UNIQUE employees *** ---



if(!empty($grupos)){


 if($Tgrupo == 'individual'){
// Insertar grupos
        foreach ($Tgrupos as $igrupos) {

    $id_Emplead = $igrupos["id_empleado"];

       $queryGrupox = "INSERT INTO `colaborador_capacitacion`(`id_capacitacion`, `id_colaborador`) VALUES (?, ?)";
            $stmtGrupo = $pdo->prepare($queryGrupox);
            $stmtGrupo->execute([$capacitacionId, $id_Emplead]);




  try {
               // Insertar en colaborador_capacitacion (vinculando capacitación con empleado individual)
               // INSERT IGNORE intenta insertar y simplemente ignora si ya existe una fila con esa clave única.
               // Necesitas que tu tabla colaborador_capacitacion tenga un índice UNIQUE en (id_capacitacion, id_colaborador)
               $queryColaboradorIndividual = "INSERT IGNORE INTO `colaborador_capacitacion`(`id_capacitacion`, `id_colaborador`, `visible`) VALUES (?, ?, 'no')";
               $stmtColaboradorIndividual = $pdo->prepare($queryColaboradorIndividual);
               $stmtColaboradorIndividual->execute([$capacitacionId, $id_Emplead]);

           

           } catch (PDOException $e) {
               // Manejar errores de inserción, especialmente si no usas INSERT IGNORE y hay duplicados
               // Si el error es una violación de clave única (código '23000'), puedes ignorarlo o loguearlo
               if ($e->getCode() == '23000') {
                   error_log("Advertencia: Duplicado al insertar colaborador/evento para empleado " . $idEmpleado . " y capacitación/evento " . $capacitacionId . "/" . $eventoId);
               } else {
                    // Si es otro tipo de error, puedes hacer rollback o loguearlo severamente
                    error_log("Error grave al insertar colaborador/evento para empleado " . $idEmpleado . ": " . $e->getMessage());
                    // Opcional: $pdo->rollBack(); echo json_encode(["success" => false, "error" => "Error interno al asignar colaboradores: " . $e->getMessage()]); exit;
               }
           }


          
 // --- Verificación antes de insertar en eventos_asignaciones para colaboradores ---
 $checkColaborador = "SELECT COUNT(*) FROM `eventos_asignaciones` WHERE `id_evento` = ? AND `id_empleado` = ?";
 $stmtCheckColaborador = $pdo->prepare($checkColaborador);
 $stmtCheckColaborador->execute([$eventoId,  $id_Emplead]);
 $existeColaborador = $stmtCheckColaborador->fetchColumn();

 // Si no existe la asignación, la insertamos
 if ($existeColaborador == 0) {

              $asignacionesEventos = "INSERT INTO `eventos_asignaciones`(`id_evento`, `id_empleado`) VALUES (?, ?)";
          $stmtAsignaciones = $pdo->prepare($asignacionesEventos);
          $stmtAsignaciones->execute([$eventoId, $id_Emplead]);
           }
   
        }

        } else if($Tgrupo == 'equipo'){

   foreach ($Tgrupos as $igrupos) {

    $id_Emplead = $igrupos["id_empleado"];
  try {
               // Insertar en colaborador_capacitacion (vinculando capacitación con empleado individual)
               // INSERT IGNORE intenta insertar y simplemente ignora si ya existe una fila con esa clave única.
               // Necesitas que tu tabla colaborador_capacitacion tenga un índice UNIQUE en (id_capacitacion, id_colaborador)
               $queryColaboradorIndividual = "INSERT IGNORE INTO `colaborador_capacitacion`(`id_capacitacion`, `id_colaborador`, `visible`) VALUES (?, ?, 'no')";
               $stmtColaboradorIndividual = $pdo->prepare($queryColaboradorIndividual);
               $stmtColaboradorIndividual->execute([$capacitacionId, $id_Emplead]);

           

           } catch (PDOException $e) {
               // Manejar errores de inserción, especialmente si no usas INSERT IGNORE y hay duplicados
               // Si el error es una violación de clave única (código '23000'), puedes ignorarlo o loguearlo
               if ($e->getCode() == '23000') {
                   error_log("Advertencia: Duplicado al insertar colaborador/evento para empleado " . $idEmpleado . " y capacitación/evento " . $capacitacionId . "/" . $eventoId);
               } else {
                    // Si es otro tipo de error, puedes hacer rollback o loguearlo severamente
                    error_log("Error grave al insertar colaborador/evento para empleado " . $idEmpleado . ": " . $e->getMessage());
                    // Opcional: $pdo->rollBack(); echo json_encode(["success" => false, "error" => "Error interno al asignar colaboradores: " . $e->getMessage()]); exit;
               }
           }

}


           // Insertar grupos
        foreach ($Tequipo as $igrupos) {

    $id_EquiP = $igrupos["id_equipo"];

       
           $queryGrupox = "INSERT INTO `colaborador_capacitacion`(`id_capacitacion`, `id_equipo`) VALUES (?, ?)";
            $stmtGrupo = $pdo->prepare($queryGrupox);
            $stmtGrupo->execute([$capacitacionId, $id_EquiP]);
        
 // --- Verificación antes de insertar en eventos_asignaciones para colaboradores ---
 $checkColaborador = "SELECT COUNT(*) FROM `eventos_asignaciones` WHERE `id_evento` = ? AND `id_equipo` = ?";
 $stmtCheckColaborador = $pdo->prepare($checkColaborador);
 $stmtCheckColaborador->execute([$eventoId, $id_EquiP]);
 $existeColaborador = $stmtCheckColaborador->fetchColumn();

 // Si no existe la asignación, la insertamos
 if ($existeColaborador == 0) {
     $asignacionesEventos = "INSERT INTO `eventos_asignaciones`(`id_evento`, `id_equipo`) VALUES (?, ?)";
          $stmtAsignaciones = $pdo->prepare($asignacionesEventos);
          $stmtAsignaciones->execute([$eventoId, $id_EquiP]);
     }
   
        }

      }

    }


        // Insertar colaboradores
        foreach ($colaboradoresIds as $idColaborador) {
            $queryColaborador = "INSERT INTO `colaborador_capacitacion`(`id_capacitacion`, `id_colaborador`) VALUES (?, ?)";
            $stmtColaborador = $pdo->prepare($queryColaborador);
            $stmtColaborador->execute([$capacitacionId, $idColaborador]);

          $asignacionesEventos = "INSERT INTO `eventos_asignaciones`(`id_evento`, `id_empleado`) VALUES (?, ?)";
          $stmtAsignaciones = $pdo->prepare($asignacionesEventos);
          $stmtAsignaciones->execute([$eventoId, $idColaborador]);




 // --- Verificación antes de insertar en eventos_asignaciones para colaboradores ---
 $checkColaborador = "SELECT COUNT(*) FROM `eventos_asignaciones` WHERE `id_evento` = ? AND `id_equipo` = ?";
 $stmtCheckColaborador = $pdo->prepare($checkColaborador);
 $stmtCheckColaborador->execute([$eventoId, $id_equipo_actual]);
 $existeColaborador = $stmtCheckColaborador->fetchColumn();

 // Si no existe la asignación, la insertamos
 if ($existeColaborador == 0) {
    $asignacionesEventosE = "INSERT INTO `eventos_asignaciones`(`id_evento`, `id_equipo`) VALUES (?, ?)";
    $stmtAsignacionesE = $pdo->prepare($asignacionesEventosE);
    $stmtAsignacionesE->execute([$eventoId, $id_equipo_actual]);
 }





        }

        // Insertar equipos
        foreach ($equiposIds as $idEquipo) {
            $queryEquipo = "INSERT INTO `colaborador_capacitacion`(`id_capacitacion`, `id_equipo`) VALUES (?, ?)";
            $stmtEquipo = $pdo->prepare($queryEquipo);
            $stmtEquipo->execute([$capacitacionId, $idEquipo]);

            $asignacionesEventos = "INSERT INTO `eventos_asignaciones`(`id_evento`, `id_equipo`) VALUES (?, ?)";
            $stmtAsignaciones = $pdo->prepare($asignacionesEventos);
            $stmtAsignaciones->execute([$eventoId, $idEquipo]);



  // --- Verificación antes de insertar en eventos_asignaciones para equipos ---
  $checkEquipo = "SELECT COUNT(*) FROM `eventos_asignaciones` WHERE `id_evento` = ? AND `id_equipo` = ?";
  $stmtCheckEquipo = $pdo->prepare($checkEquipo);
  $stmtCheckEquipo->execute([$eventoId, $id_equipo_actual]);
  $existeEquipo = $stmtCheckEquipo->fetchColumn();

  // Si no existe la asignación, la insertamos
  if ($existeEquipo == 0) {
    $asignacionesEventosQ = "INSERT INTO `eventos_asignaciones`(`id_evento`, `id_equipo`) VALUES (?, ?)";
    $stmtAsignacionesQ = $pdo->prepare($asignacionesEventosQ);
    $stmtAsignacionesQ->execute([$eventoId, $id_equipo_actual]);
  }






        }

        $pdo->commit(); // Confirmar transacción
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        $pdo->rollBack(); // Revertir transacción en caso de error
        echo json_encode(["success" => false, "error" => "Error al insertar la nota: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
}
?>