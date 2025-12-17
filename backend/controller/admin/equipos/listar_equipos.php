<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    // Obtener los equipos junto con el área a la que pertenecen
    $stmt = $pdo->prepare("
        SELECT e.id_equipo, e.alias, e.estado, a.id_area, a.alias AS area
        FROM equipos e
        JOIN areas a ON e.id_area = a.id_area
        WHERE e.borrado = 0
        ORDER BY e.alias ASC
    ");

    $stmt->execute();
    $equipos = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $equipos]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al obtener los equipos", "debug" => $e->getMessage()]);
}
