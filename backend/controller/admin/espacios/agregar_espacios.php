<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (
        !isset($_POST['nombre']) || empty(trim($_POST['nombre'])) ||
        !isset($_POST['id_edificio'])
    ) {
        echo json_encode(["success" => false, "message" => "El nombre del espacio y el edificio son obligatorios"]);
        exit;
    }

    $alias = trim($_POST['nombre']);
    $detalles = isset($_POST['detalles']) ? trim($_POST['detalles']) : null;
    $estado = $_POST['estado'] ?? 'habilitado';
    $id_edificio = (int) $_POST['id_edificio'];

    // Verificar si el edificio existe y está habilitado
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM edificios
        WHERE id_edificio = :id AND estado = 'habilitado' AND borrado = 0
    ");
    $stmt->bindParam(':id', $id_edificio);
    $stmt->execute();
    $existeEdificio = $stmt->fetchColumn();

    if (!$existeEdificio) {
        echo json_encode(["success" => false, "message" => "El edificio seleccionado no existe o está deshabilitado"]);
        exit;
    }

    // Insertar el espacio reservable
    $stmt = $pdo->prepare("
        INSERT INTO espacios_reservables (alias, detalle, id_edificio, estado)
        VALUES (:nombre, :detalle, :id_edificio, :estado)
    ");
    $stmt->bindParam(':nombre', $alias);
    $stmt->bindParam(':detalle', $detalles);
    $stmt->bindParam(':id_edificio', $id_edificio);
    $stmt->bindParam(':estado', $estado);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Espacio agregado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al agregar el espacio", "debug" => $e->getMessage()]);
}
