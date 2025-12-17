<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        echo json_encode(["success" => false, "message" => "ID de la entidad es obligatorio"]);
        exit;
    }

    $id = (int) $_POST['id'];

    // Marcar como eliminada (soft delete)
    $stmt = $pdo->prepare("UPDATE entidades SET borrado = 1 WHERE id_entidad = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Entidad eliminada correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al eliminar la entidad", "debug" => $e->getMessage()]);
}
