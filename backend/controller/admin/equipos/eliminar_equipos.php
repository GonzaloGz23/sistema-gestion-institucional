<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        echo json_encode(["success" => false, "message" => "ID del equipo es obligatorio"]);
        exit;
    }

    $id = (int) $_POST['id'];

    // Marcar el equipo como eliminado (borrado = 1)
    $stmt = $pdo->prepare("UPDATE equipos SET borrado = 1 WHERE id_equipo = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Equipo eliminado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al eliminar el equipo", "debug" => $e->getMessage()]);
}
