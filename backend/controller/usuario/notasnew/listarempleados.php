<?php
include '../../../../backend/config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$idNota = $data['id_nota'] ?? null;

if (!$idNota) {
    echo json_encode(['success' => false, 'message' => 'ID de nota faltante']);
    exit;
}

// Listar empleados que NO sean colaboradores activos de esta nota
$stmt = $pdo->prepare("
    SELECT e.id_empleado, e.nombre, e.apellido
    FROM empleados e
    WHERE e.id_empleado NOT IN (
        SELECT id_usuario 
        FROM collaboradores 
        WHERE id_nota = ? AND estado = 1
    )
    ORDER BY e.nombre
");
$stmt->execute([$idNota]);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'empleados' => $empleados
]);
