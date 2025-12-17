<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        echo json_encode(["success" => false, "message" => "ID y estado son obligatorios"]);
        exit;
    }

    $id = (int) $_POST['id'];
    $estado = $_POST['estado'] === "habilitado" ? "deshabilitado" : "habilitado";

    $stmt = $pdo->prepare("UPDATE espacios_reservables SET estado = :estado WHERE id_espacio = :id");
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Espacio actualizado correctamente",
        "nuevoEstado" => $estado
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al actualizar el espacio", "debug" => $e->getMessage()]);
}
