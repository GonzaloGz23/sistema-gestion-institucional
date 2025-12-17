<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        echo json_encode(["success" => false, "message" => "ID del área es obligatorio"]);
        exit;
    }

    $id = (int) $_POST['id'];

    // Marcar el área como eliminada (borrado = 1)
    $stmt = $pdo->prepare("UPDATE areas SET borrado = 1 WHERE id_area = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Área eliminada correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al eliminar el área", "debug" => $e->getMessage()]);
}
