<?php
require_once '../../../config/database.php';

// Validación
if (!isset($_POST['id_equipo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el ID del equipo']);
    exit;
}

$id_equipo = (int) $_POST['id_equipo'];

try {
    $stmt = $pdo->prepare("SELECT id_etiqueta, nombre FROM solicitudes_etiquetas WHERE id_equipo = :id_equipo AND borrado = 0 ORDER BY nombre ASC");
    $stmt->execute([':id_equipo' => $id_equipo]);

    $etiquetas = $stmt->fetchAll();

    echo json_encode(['success' => true, 'etiquetas' => $etiquetas]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al listar etiquetas: ' . $e->getMessage()]);
}
