<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    // Validar datos recibidos
    if (
        !isset($_POST['id']) ||
        !isset($_POST['nombre']) || empty(trim($_POST['nombre'])) ||
        !isset($_POST['id_entidad'])
    ) {
        echo json_encode(["success" => false, "message" => "El nombre es obligatorio."]);
        exit;
    }

    $id = (int) $_POST['id'];
    $alias = trim($_POST['nombre']);
    $estado = $_POST['estado'] ?? 'habilitado';
    $id_entidad = (int) $_POST['id_entidad'];

    // Verificar que la entidad seleccionada existe y está habilitada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM entidades WHERE id_entidad = :id");
    $stmt->bindParam(':id', $id_entidad);
    $stmt->execute();
    $existeEntidad = $stmt->fetchColumn();

    if (!$existeEntidad) {
        echo json_encode(["success" => false, "message" => "La entidad seleccionada no existe o está deshabilitada"]);
        exit;
    }

    // Actualizar el área
    $stmt = $pdo->prepare("UPDATE areas SET alias = :alias, id_entidad = :id_entidad, estado = :estado WHERE id_area = :id");
    $stmt->bindParam(':alias', $alias);
    $stmt->bindParam(':id_entidad', $id_entidad);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Área actualizada correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al actualizar el área", "debug" => $e->getMessage()]);
}
