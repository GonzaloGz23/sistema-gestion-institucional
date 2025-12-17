<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (
        !isset($_POST['nombre']) || empty(trim($_POST['nombre'])) ||
        !isset($_POST['id_entidad'])
    ) {
        echo json_encode(["success" => false, "message" => "El nombre del edificio es obligatorio."]);
        exit;
    }

    $alias = trim($_POST['nombre']);
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;
    $estado = $_POST['estado'] ?? 'habilitado';
    $id_entidad = (int) $_POST['id_entidad'];

    // Verificar que la entidad existe y está habilitada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM entidades WHERE id_entidad = :id");
    $stmt->bindParam(':id', $id_entidad);
    $stmt->execute();
    $existeEntidad = $stmt->fetchColumn();

    if (!$existeEntidad) {
        echo json_encode(["success" => false, "message" => "La entidad seleccionada no existe o está deshabilitada"]);
        exit;
    }

    // Insertar edificio
    $stmt = $pdo->prepare("
        INSERT INTO edificios (alias, direccion, id_entidad, estado)
        VALUES (:nombre, :direccion, :id_entidad, :estado)
    ");
    $stmt->bindParam(':nombre', $alias);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':id_entidad', $id_entidad);
    $stmt->bindParam(':estado', $estado);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Edificio agregado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al agregar el edificio", "debug" => $e->getMessage()]);
}
