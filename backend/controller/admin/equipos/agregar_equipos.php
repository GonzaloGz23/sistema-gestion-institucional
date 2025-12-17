<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['nombre']) || empty(trim($_POST['nombre'])) || !isset($_POST['id_area'])) {
        echo json_encode(["success" => false, "message" => "El nombre del equipo y el área son obligatorios"]);
        exit;
    }

    $alias = trim($_POST['nombre']);
    $estado = $_POST['estado'] ?? 'habilitado';
    $id_area = (int) $_POST['id_area'];

    // Verificar si el área existe y está habilitada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM areas WHERE id_area = :id AND estado = 'habilitado' AND borrado = 0");
    $stmt->bindParam(':id', $id_area);
    $stmt->execute();
    $existeArea = $stmt->fetchColumn();

    if (!$existeArea) {
        echo json_encode(["success" => false, "message" => "El área seleccionada no existe o está deshabilitada"]);
        exit;
    }

    // Insertar el equipo en la base de datos
    $stmt = $pdo->prepare("INSERT INTO equipos (alias, id_area, estado) VALUES (:nombre, :id_area, :estado)");
    $stmt->bindParam(':nombre', $alias);
    $stmt->bindParam(':id_area', $id_area);
    $stmt->bindParam(':estado', $estado);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Equipo agregado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al agregar el equipo", "debug" => $e->getMessage()]);
}
