<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        echo json_encode(["success" => false, "message" => "ID del espacio es obligatorio"]);
        exit;
    }

    $id = (int) $_POST['id'];

    // Marcar el espacio como eliminado (borrado = 1)
    $stmt = $pdo->prepare("UPDATE espacios_reservables SET borrado = 1 WHERE id_espacio = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Espacio eliminado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al eliminar el espacio", "debug" => $e->getMessage()]);
}
