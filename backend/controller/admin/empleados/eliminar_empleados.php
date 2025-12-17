<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        echo json_encode(["success" => false, "message" => "ID del empleado es obligatorio"]);
        exit;
    }

    $id = (int) $_POST['id'];

    // Marcar el empleado como eliminado (borrado = 1)
    $stmt = $pdo->prepare("UPDATE empleados SET borrado = 1 WHERE id_empleado = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Empleado eliminado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al eliminar el empleado", "debug" => $e->getMessage()]);
}
