<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (
        !isset($_POST['id']) ||
        !isset($_POST['nombre']) || empty(trim($_POST['nombre'])) ||
        !isset($_POST['id_edificio'])
    ) {
        echo json_encode(["success" => false, "message" => "El ID, nombre y edificio son obligatorios"]);
        exit;
    }

    $id = (int) $_POST['id'];
    $alias = trim($_POST['nombre']);
    $detalles = isset($_POST['detalles']) ? trim($_POST['detalles']) : null;
    $estado = $_POST['estado'] ?? 'habilitado';
    $id_edificio = (int) $_POST['id_edificio'];

    // Verificar que el edificio existe y está habilitado
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

    // Actualizar el espacio reservable
    $stmt = $pdo->prepare("
        UPDATE espacios_reservables
        SET alias = :alias,
            detalle = :detalle,
            id_edificio = :id_edificio,
            estado = :estado
        WHERE id_espacio = :id
    ");
    $stmt->bindParam(':alias', $alias);
    $stmt->bindParam(':detalle', $detalles);
    $stmt->bindParam(':id_edificio', $id_edificio);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Espacio actualizado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al actualizar el espacio", "debug" => $e->getMessage()]);
}
