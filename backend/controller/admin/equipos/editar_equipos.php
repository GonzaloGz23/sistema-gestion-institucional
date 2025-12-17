<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (
        !isset($_POST['id']) ||
        !isset($_POST['nombre']) || empty(trim($_POST['nombre'])) ||
        !isset($_POST['id_area'])
    ) {
        echo json_encode(["success" => false, "message" => "El ID, nombre y área son obligatorios"]);
        exit;
    }

    $id = (int) $_POST['id'];
    $alias = trim($_POST['nombre']);
    $estado = $_POST['estado'] ?? 'habilitado';
    $id_area = (int) $_POST['id_area'];

    // Verificar que el área existe y está habilitada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM areas WHERE id_area = :id AND estado = 'habilitado' AND borrado = 0");
    $stmt->bindParam(':id', $id_area);
    $stmt->execute();
    $existeArea = $stmt->fetchColumn();

    if (!$existeArea) {
        echo json_encode(["success" => false, "message" => "El área seleccionada no existe o está deshabilitada"]);
        exit;
    }

    // Actualizar el equipo
    $stmt = $pdo->prepare("
        UPDATE equipos
        SET alias = :alias, id_area = :id_area, estado = :estado
        WHERE id_equipo = :id
    ");
    $stmt->bindParam(':alias', $alias);
    $stmt->bindParam(':id_area', $id_area);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Equipo actualizado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al actualizar el equipo", "debug" => $e->getMessage()]);
}
