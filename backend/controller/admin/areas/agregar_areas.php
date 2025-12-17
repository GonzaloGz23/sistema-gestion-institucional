<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {

    if (!isset($_POST['nombre']) || empty(trim(string: $_POST['nombre'])) || !isset($_POST['id_entidad'])) {
        echo json_encode(value: ["success" => false, "message" => "El nombre del área es obligatorio"]);
        exit;
    }

    $alias = trim($_POST['nombre']);
    $estado = $_POST['estado'] ?? 'habilitado';
    $id_entidad = (int) $_POST['id_entidad'];

    // Verificar si la entidad existe y está habilitada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM entidades WHERE id_entidad = :id");
    $stmt->bindParam(':id', $id_entidad);
    $stmt->execute();
    $existeEntidad = $stmt->fetchColumn();

    if (!$existeEntidad) {
        echo json_encode(["success" => false, "message" => "La entidad seleccionada no existe o está deshabilitada"]);
        exit;
    }

    // Insertar el área en la base de datos
    $stmt = $pdo->prepare("INSERT INTO areas (alias, id_entidad, estado) VALUES (:alias, :id_entidad, :estado)");
    $stmt->bindParam(':alias', $alias);
    $stmt->bindParam(':id_entidad', $id_entidad);
    $stmt->bindParam(':estado', $estado);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Área agregada correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al agregar el área",  "debug"   => $e->getMessage()]);
}

