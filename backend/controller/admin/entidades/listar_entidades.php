<?php
require_once '../../../config/database.php'; // Conexión a la BD

header('Content-Type: application/json');

try {
    // Consulta de entidades
    $stmt = $pdo->prepare("
        SELECT
            e.id_entidad,
            e.nombre,
            e.estado,
            -- Contar edificios asociados a la entidad
            (SELECT COUNT(*) FROM edificios ed 
             WHERE ed.id_entidad = e.id_entidad AND ed.borrado = 0) AS cantidad_edificios,
            -- Contar áreas asociadas a la entidad
            (SELECT COUNT(*) FROM areas a 
             WHERE a.id_entidad = e.id_entidad AND a.borrado = 0) AS cantidad_areas
        FROM entidades e
        WHERE e.borrado = 0
        ORDER BY e.nombre ASC
    ");
    $stmt->execute();
    $entidades = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $entidades]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al obtener entidades", "debug" => $e->getMessage()]);
}
