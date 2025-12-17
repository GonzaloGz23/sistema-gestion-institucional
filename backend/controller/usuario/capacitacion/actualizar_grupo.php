<?php
require_once "../../../config/database.php";

$idCapacitacion = $_POST['idGrupo'] ?? null; // Renamed from idGrupo for clarity, matches JS 'idGrupo'
$tipoRelacion = $_POST['tipoRelacion'] ?? null;
$nombreGrupo = $_POST['nombreGrupo'] ?? null;
$empleadoActual = $_POST['usuarioActual'] ?? '';
$colaboradoresIds = json_decode($_POST['colaboradores'] ?? '[]'); // This will be an array of IDs

// Validar que el ID del grupo esté presente
if (empty($idCapacitacion) || !is_numeric($idCapacitacion)) {
    echo json_encode(["success" => false, "error" => "ID de grupo inválido."]);
    exit;
}
if (empty($nombreGrupo)) {
    echo json_encode(["success" => false, "error" => "El nombre del grupo no puede estar vacío."]);
    exit;
}
if (empty($tipoRelacion)) {
    echo json_encode(["success" => false, "error" => "Tipo de relación no especificado."]);
    exit;
}

$zonaHorariaArgentina = new DateTimeZone('America/Argentina/Buenos_Aires');
$fechaHoraActual = new DateTime('now', $zonaHorariaArgentina);
$actual = $fechaHoraActual->format('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    // 1. Update the group's basic information
    $grupoUpdate = "UPDATE `grupos` SET `nombre_grupo` = :nombreGrupo, `fecha_creacion` = :fechaActual, `tipo_grupo` = :tipoRelacion, `id_creador` = :idCreador WHERE `id_grupo` = :idGrupo";
    $stmtGrupo = $pdo->prepare($grupoUpdate);
    $stmtGrupo->execute([
        ':nombreGrupo' => $nombreGrupo,
        ':fechaActual' => $actual,
        ':tipoRelacion' => $tipoRelacion,
        ':idCreador' => $empleadoActual, // Assuming this is the 'id_creador' field you want to update
        ':idGrupo' => $idCapacitacion
    ]);

   

    // 3. Prepare for inserting new relationships
    $queryColaboradorIndividual = "INSERT INTO `r_grupos_empleados`(`id_grupo`, `id_empleado`, `tipo_relacion`) VALUES (?, ?, ?)";
    $stmtColaboradorIndividual = $pdo->prepare($queryColaboradorIndividual);

    $uniqueEmployeeIdsToAssociate = [];
    $relationTypeForInsert = ($tipoRelacion === 'individual') ? 1 : 2; // Determine the type_relacion to store

    if ($tipoRelacion === 'individual') {
        // If type is 'individual', $colaboradoresIds directly contains employee IDs
        $uniqueEmployeeIdsToAssociate = array_unique($colaboradoresIds);
    } elseif ($tipoRelacion === 'equipo') {
        // If type is 'equipo', $colaboradoresIds contains team IDs
        if (!empty($colaboradoresIds)) {
            $placeholders = implode(',', array_fill(0, count($colaboradoresIds), '?'));
            $queryEmpleadosEnEquipos = "SELECT DISTINCT `id_empleado` FROM `empleados` WHERE `id_equipo` IN ($placeholders)";
            $stmtEmpleadosEnEquipos = $pdo->prepare($queryEmpleadosEnEquipos);
            $stmtEmpleadosEnEquipos->execute($colaboradoresIds);
            $empleadosDeEquipos = $stmtEmpleadosEnEquipos->fetchAll(PDO::FETCH_COLUMN);
            $uniqueEmployeeIdsToAssociate = array_unique($empleadosDeEquipos);
        }
    }

    // 4. Insert the new unique employee relationships
    foreach ($uniqueEmployeeIdsToAssociate as $idEmpleado) {
        if (!empty($idEmpleado)) {
            // Using INSERT IGNORE is fine here if you want to silently skip duplicates,
            // but after deleting all existing ones, duplicates should not occur in a single batch.
            // Still, it provides robustness.
            $stmtColaboradorIndividual->execute([$idCapacitacion, $idEmpleado, $relationTypeForInsert]);
        } else {
            error_log("Advertencia: Se intentó insertar un empleado con ID vacío o nulo en el grupo " . $idCapacitacion);
        }
    }

    $pdo->commit();
    echo json_encode(["success" => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error al actualizar el grupo o sus relaciones: " . $e->getMessage()); // Log the error for debugging
    echo json_encode(["success" => false, "error" => "Error al guardar los cambios: " . $e->getMessage()]);
}
?>