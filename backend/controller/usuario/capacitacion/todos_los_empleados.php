<?php
require_once "../../../config/database.php";
require_once "../../../config/usuario_actual.php";
$id_usuario_actual = $usuarioActual->id;

try {
    $selectedIds = isset($_GET['selected_ids']) ? json_decode($_GET['selected_ids'], true) : [];

    $query = "SELECT id_empleado, nombre, apellido FROM empleados WHERE id_empleado != :id_usuario_actual AND `borrado` = 0 AND `estado` = 'habilitado'";

    if (!empty($selectedIds)) {
        // Crear marcadores de posición con nombre para la cláusula NOT IN
        $placeholders = implode(',', array_map(function ($i) {
            return ':selected_id_' . $i;
        }, array_keys($selectedIds)));
        $query .= " AND id_empleado NOT IN ($placeholders)";
    }

    $stmt = $pdo->prepare($query);

    // Bindear el ID del usuario actual
    $stmt->bindParam(':id_usuario_actual', $id_usuario_actual, PDO::PARAM_INT);

    // Bindear los valores de $selectedIds usando marcadores con nombre
    if (!empty($selectedIds)) {
        foreach ($selectedIds as $key => $id) {
            $stmt->bindValue(':selected_id_' . $key, $id, PDO::PARAM_INT);
        }
    }

    $stmt->execute();
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = array_map(function ($empleado) use ($selectedIds) {
        $empleado['selected'] = false;
        return $empleado;
    }, $empleados);

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>