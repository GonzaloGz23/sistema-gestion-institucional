<?php
require_once "../../../config/database.php";
require_once "../../../config/usuario_actual.php";
$id_usuario_actual = $usuarioActual->id;

$checkEquipox = "SELECT `id_equipo` FROM `empleados` WHERE `id_empleado` = ?";
$stmtCheckEquipox = $pdo->prepare($checkEquipox);
$stmtCheckEquipox->execute([$id_usuario_actual]);
$id_equipo_actual = $stmtCheckEquipox->fetchColumn();




$empleadoActual = $_POST['usuarioActual'] ?? '';
$grupo = $_POST['grupo'] ?? '';

$colaboradoresIds = json_decode($_POST['colaboradores'] ?? '[]');
$equiposIds = json_decode($_POST['equipos'] ?? '[]');


$tipoGrupo = ''; // Inicializamos $tipoGrupo

if (!empty($colaboradoresIds)) {
    $tipoGrupo = 'individual';
} elseif (!empty($equiposIds)) {
    $tipoGrupo = 'equipo';
}


$zonaHorariaArgentina = new DateTimeZone('America/Argentina/Buenos_Aires');
$fechaHoraActual = new DateTime('now', $zonaHorariaArgentina);
$actual = $fechaHoraActual->format('Y-m-d H:i:s');



if (!empty($grupo)) {
    try {
        $pdo->beginTransaction(); // Iniciar transacción

        $grupoId = null;
       $grupoInsert = "INSERT INTO `grupos`(`nombre_grupo`, `fecha_creacion`, `tipo_grupo`, `id_creador`) VALUES ('$grupo','$actual','$tipoGrupo','$id_usuario_actual')";
       $stmtGrupo = $pdo->prepare($grupoInsert);
       $stmtGrupo->execute();
       $grupoId = $pdo->lastInsertId();



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
        $uniqueEmployeeIdsToAssociat = array_unique($empleadosDeEquipos);

  }

  // Obtener IDs de empleado únicos para evitar duplicados
  $uniqueEmployeeIdsToAssociate = array_unique($allEmployeeIdsToAssociate);
  // --- *** Fin Modificación: Recopilar IDs únicos *** ---


  // --- *** Modificado: Insertar UNIQUE employees into colaborador_capacitacion and eventos_asignaciones *** ---
  // 4. Insertar cada empleado único (individualmente seleccionado o parte de un equipo seleccionado)
  $queryColaboradorIndividual = "INSERT INTO `r_grupos_empleados`(`id_grupo`, `id_empleado`) VALUES (?, ?)";
  $stmtColaboradorIndividual = $pdo->prepare($queryColaboradorIndividual);

  


  foreach ($uniqueEmployeeIdsToAssociate as $idEmpleado) {
       // Verificar si idEmpleado es válido antes de insertar
       if (!empty($idEmpleado)) {
           try {

            if($tipoGrupo === 'individual') {
              // Insertar en colaborador_capacitacion (vinculando capacitación con empleado individual)
               // INSERT IGNORE intenta insertar y simplemente ignora si ya existe una fila con esa clave única.
               // Necesitas que tu tabla colaborador_capacitacion tenga un índice UNIQUE en (id_capacitacion, id_colaborador)
               $queryColaboradorIndividual = "INSERT IGNORE INTO `r_grupos_empleados`(`id_grupo`, `id_empleado`, `tipo_relacion`) VALUES (?, ?, ?)";
               $stmtColaboradorIndividual = $pdo->prepare($queryColaboradorIndividual);
               $stmtColaboradorIndividual->execute([$grupoId, $idEmpleado, 1]);
           } else if($tipoGrupo === 'equipo') {
              $queryColaboradorIndividual = "INSERT IGNORE INTO `r_grupos_empleados`(`id_grupo`, `id_empleado`, `tipo_relacion`) VALUES (?, ?, ?)";
               $stmtColaboradorIndividual = $pdo->prepare($queryColaboradorIndividual);
               $stmtColaboradorIndividual->execute([$grupoId, $idEmpleado, 2]);
           }

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









        // Insertar colaboradores
        foreach ($colaboradoresIds as $idColaborador) {
           


 // --- Verificación antes de insertar en eventos_asignaciones para colaboradores ---
 $checkColaborador = "SELECT COUNT(*) FROM `r_grupos_empleados` WHERE `id_grupo` = ? AND `id_empleado` = ?";
 $stmtCheckColaborador = $pdo->prepare($checkColaborador);
 $stmtCheckColaborador->execute([$grupoId, $idColaborador]);
 $existeColaborador = $stmtCheckColaborador->fetchColumn();

 // Si no existe la asignación, la insertamos
 if ($existeColaborador == 0) {
    $asignacionesGrupoE = "INSERT INTO `r_grupos_empleados`(`id_grupo`, `id_empleado`) VALUES (?, ?)";
    $stmtAsignacionesE = $pdo->prepare($asignacionesGrupoE);
    $stmtAsignacionesE->execute([$grupoId, $idColaborador]);
 }





        }


 if (!empty($equiposIds)) {


        // Insertar equipos
        foreach ($uniqueEmployeeIdsToAssociat as $idEmpleadoEquipo) {
          
  // --- Verificación antes de insertar en eventos_asignaciones para equipos ---
  $checkEquipo = "SELECT COUNT(*) FROM `r_grupos_empleados` WHERE `id_grupo` = ? AND `id_empleado` = ?";
  $stmtCheckEquipo = $pdo->prepare($checkEquipo);
  $stmtCheckEquipo->execute([$grupoId, $idEmpleadoEquipo]);
  $existeEquipo = $stmtCheckEquipo->fetchColumn();

  // Si no existe la asignación, la insertamos
  if ($existeEquipo == 0) {
    $asignacionesEventosQ = "INSERT INTO `r_grupos_empleados`(`id_grupo`, `id_empleado`) VALUES (?, ?)";
    $stmtAsignacionesQ = $pdo->prepare($asignacionesEventosQ);
    $stmtAsignacionesQ->execute([$grupoId, $idEmpleadoEquipo]);
  }

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