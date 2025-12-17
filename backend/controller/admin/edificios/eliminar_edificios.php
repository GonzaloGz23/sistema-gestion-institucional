<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        echo json_encode(["success" => false, "message" => "ID del edificio es obligatorio"]);
        exit;
    }

    $id = (int) $_POST['id'];

    // Marcar el edificio como eliminado (borrado = 1)
    $stmt = $pdo->prepare("UPDATE edificios SET borrado = 1 WHERE id_edificio = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Edificio eliminado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al eliminar el edificio", "debug" => $e->getMessage()]);
}
