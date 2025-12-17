<?php
require_once '../../../config/database.php'; // Conexión a la BD

header('Content-Type: application/json');

try {
    // Validar datos recibidos
    if (!isset($_POST['id']) || !isset($_POST['nombre']) || empty(trim($_POST['nombre']))) {
        echo json_encode(["success" => false, "message" => "El ID y el nombre de la entidad son obligatorios"]);
        exit;
    }

    $id = (int) $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $estado = $_POST['estado'] ?? 'habilitado';

    // Actualizar en la base de datos
    $stmt = $pdo->prepare("UPDATE entidades SET nombre = :nombre, estado = :estado WHERE id_entidad = :id");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Entidad actualizada correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al actualizar la entidad", "debug" => $e->getMessage()]);
}
