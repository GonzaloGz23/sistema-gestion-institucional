<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    // Obtener los edificios junto con la entidad a la que pertenecen
    $stmt = $pdo->prepare("
        SELECT e.id_edificio, e.alias, e.direccion, e.estado, ent.nombre AS entidad
        FROM edificios e
        JOIN entidades ent ON e.id_entidad = ent.id_entidad
        WHERE e.borrado = 0
        ORDER BY e.alias ASC
    ");

    $stmt->execute();
    $edificios = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $edificios]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al obtener los edificios", "debug" => $e->getMessage()]);
}
