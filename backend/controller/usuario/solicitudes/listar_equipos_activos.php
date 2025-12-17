<?php
require_once '../../../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT id_equipo, alias FROM equipos WHERE estado = 'habilitado'  AND borrado = 0");
    $stmt->execute();
    $equipos = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'equipos' => $equipos
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener los equipos: ' . $e->getMessage()
    ]);
}
