<?php
require_once "../../../config/database.php";
require_once "../../../config/usuario_actual.php";

$id_usuariox = $usuarioActual->id;


$checkEquipox = "SELECT `id_equipo` FROM `empleados` WHERE `id_empleado` = ?";
$stmtCheckEquipox = $pdo->prepare($checkEquipox);
$stmtCheckEquipox->execute([$id_usuariox]);
$id_equipo_actual = $stmtCheckEquipox->fetchColumn();


try {
    $selectedIds = isset($_GET['selected_ids']) ? json_decode($_GET['selected_ids'], true) : [];

    $query = "SELECT `id_equipo`, `alias` FROM `equipos` WHERE id_equipo != $id_equipo_actual AND `borrado` = 0 AND `estado` = 'habilitado' ";

    if (!empty($selectedIds)) {
        // Crear marcadores de posición para la cláusula NOT IN
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $query .= " AND `id_equipo` NOT IN ($placeholders)";
    }

    $stmt = $pdo->prepare($query);

    // Bindear los valores de $selectedIds si la cláusula NOT IN está presente
    if (!empty($selectedIds)) {
        foreach ($selectedIds as $key => $id) {
            $stmt->bindValue($key + 1, $id, PDO::PARAM_INT);
        }
    }

    $stmt->execute();
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = array_map(function ($equipo) use ($selectedIds) {
        // Ya no necesitamos verificar 'selected' aquí, porque la consulta ya filtró los no seleccionados.
        $equipo['selected'] = false; // Todos los que vienen ahora no están seleccionados.
        return $equipo;
    }, $equipos);

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>