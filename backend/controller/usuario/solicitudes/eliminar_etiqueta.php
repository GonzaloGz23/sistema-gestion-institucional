<?php
require_once '../../../config/database.php';

// Verificamos que se haya enviado el ID de la etiqueta
if (!isset($_POST['id_etiqueta'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro id_etiqueta']);
    exit;
}

$id_etiqueta = (int) $_POST['id_etiqueta'];

try {
    $stmt = $pdo->prepare("UPDATE solicitudes_etiquetas SET borrado = 1 WHERE id_etiqueta = :id_etiqueta");
    $stmt->bindParam(':id_etiqueta', $id_etiqueta, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar la etiqueta: ' . $e->getMessage()]);
}
