<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    // Obtener las áreas junto con la entidad a la que pertenecen
    $stmt = $pdo->prepare("
        SELECT a.id_area, a.alias, a.estado, ent.nombre AS entidad
        FROM areas a
        JOIN entidades ent ON a.id_entidad = ent.id_entidad
        WHERE a.borrado = 0
        ORDER BY a.alias ASC
    ");
    
    $stmt->execute();
    $areas = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $areas]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al obtener las áreas", "debug" => $e->getMessage()]);
}
