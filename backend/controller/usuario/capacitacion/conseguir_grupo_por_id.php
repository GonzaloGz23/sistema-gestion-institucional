<?php
require_once "../../../config/database.php";
require_once "../../../config/usuario_actual.php";

$id_usuariox = $usuarioActual->id;

$checkEquipox = "SELECT `id_equipo` FROM `empleados` WHERE `id_empleado` = ?";
$stmtCheckEquipox = $pdo->prepare($checkEquipox);
$stmtCheckEquipox->execute([$id_usuariox]);
$id_equipo_actual = $stmtCheckEquipox->fetchColumn();

$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (!empty($id) && is_numeric($id)) {
    $query = "SELECT `id_grupo`, `nombre_grupo`, `fecha_creacion`, `tipo_grupo`, `id_creador`, `borrado`, `habilitado` FROM `grupos` WHERE `id_grupo` = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $grupoData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($grupoData as &$grupo) {
        $id_grupo = $grupo["id_grupo"];
        $tipo_grupo = $grupo["tipo_grupo"];

        // Consulta para obtener los miembros actuales del grupo (empleados)
        $grupoQuery = "SELECT c.id_grupo_empleado, c.id_grupo, c.id_empleado, e.alias, u.nombre, u.apellido, c.tipo_relacion FROM r_grupos_empleados c
                        LEFT JOIN empleados u ON c.id_empleado = u.id_empleado
                        LEFT JOIN equipos e ON u.id_equipo = e.id_equipo WHERE c.id_grupo = :id_grupo";
        $stmtGrupo = $pdo->prepare($grupoQuery);
        $stmtGrupo->bindParam(':id_grupo', $id_grupo, PDO::PARAM_INT);
        $stmtGrupo->execute();
        $grupo["colaborador"] = $stmtGrupo->fetchAll(PDO::FETCH_ASSOC);

        // Extraer los IDs de los miembros ya asignados al grupo
        $existingMemberIds = [];
        foreach ($grupo["colaborador"] as $colaborador) {
            $existingMemberIds[] = $colaborador['id_empleado'];
        }
        // Always add $id_usuariox to the list of excluded employee IDs
        $existingMemberIds[] = $id_usuariox; // Add the current user's ID
        $existingMemberIds = array_unique($existingMemberIds); // Ensure unique IDs
        $existingMemberIdsString = implode(',', $existingMemberIds);


        // Nueva consulta basada en tipo_grupo
        if ($tipo_grupo === 'individual') {
            // Traer todos los empleados que NO estén en grupoQuery y que NO sean id_usuariox
            $empleadosDisponiblesQuery = "SELECT id_empleado, nombre, apellido FROM empleados";
            
            // Add initial WHERE clause if $id_usuariox is to be excluded, or if existing members are to be excluded
            if (!empty($existingMemberIds)) {
                $empleadosDisponiblesQuery .= " WHERE id_empleado NOT IN (" . $existingMemberIdsString . ")";
            }

            $stmtEmpleadosDisponibles = $pdo->prepare($empleadosDisponiblesQuery);
            $stmtEmpleadosDisponibles->execute();
            $grupo["empleados_disponibles"] = $stmtEmpleadosDisponibles->fetchAll(PDO::FETCH_ASSOC);

        } elseif ($tipo_grupo === 'equipo') {
            // Traer todos los equipos que NO tengan empleados en grupoQuery y que NO sean id_equipo_actual
            // Primero, obtener los id_equipo de los empleados ya asignados
            $existingTeamIds = [];
            if (!empty($existingMemberIds)) { // existingMemberIds already includes $id_usuariox
                $queryTeamIds = "SELECT DISTINCT id_equipo FROM empleados WHERE id_empleado IN (" . $existingMemberIdsString . ")";
                $stmtTeamIds = $pdo->prepare($queryTeamIds);
                $stmtTeamIds->execute();
                $existingTeamIds = $stmtTeamIds->fetchAll(PDO::FETCH_COLUMN);
            }
            // Always add $id_equipo_actual to the list of excluded team IDs
            if ($id_equipo_actual !== false) { // Ensure $id_equipo_actual exists
                 $existingTeamIds[] = $id_equipo_actual;
            }
            $existingTeamIds = array_unique($existingTeamIds); // Ensure unique IDs
            $existingTeamIdsString = implode(',', $existingTeamIds);

            $equiposDisponiblesQuery = "SELECT id_equipo, alias FROM equipos";
            if (!empty($existingTeamIds)) {
                $equiposDisponiblesQuery .= " WHERE id_equipo NOT IN (" . $existingTeamIdsString . ")";
            }
            $stmtEquiposDisponibles = $pdo->prepare($equiposDisponiblesQuery);
            $stmtEquiposDisponibles->execute();
            $grupo["equipos_disponibles"] = $stmtEquiposDisponibles->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    echo json_encode($grupoData);
} else {
    echo json_encode([]); // Return an empty array if a valid ID is not provided
}
?>