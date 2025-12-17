<?php
require_once "../../../config/session_config.php";
include '../../../../backend/config/database.php';
header('Content-Type: application/json');

$idNota = $_GET['id_nota'] ?? null;
$idUsuario = $_SESSION['id_empleado'] ?? null; // o ajusta si tu sesión usa otro nombre

if (!$idNota) {
    echo json_encode(['error' => 'Falta el ID de la nota']);
    exit;
}

try {
    // 🔹 Colaboradores ya asignados a la nota (activos)
    $stmt = $pdo->prepare("
        SELECT e.id_empleado, e.nombre, e.apellido
        FROM collaboradores c
        INNER JOIN empleados e ON e.id_empleado = c.id_usuario
        WHERE c.id_nota = ? AND c.estado = 1
    ");
    $stmt->execute([$idNota]);
    $colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 Empleados disponibles para agregar (no están como colaboradores activos)
    $stmt2 = $pdo->prepare("
        SELECT e.id_empleado, e.nombre, e.apellido
        FROM empleados e
        WHERE e.id_empleado NOT IN (
            SELECT c.id_usuario FROM collaboradores c WHERE c.id_nota = ? AND c.estado = 1
        )
        AND e.estado = 1
        ORDER BY e.nombre ASC
    ");
    $stmt2->execute([$idNota]);
    $posibles = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'colaboradores' => $colaboradores,
        'posibles' => $posibles
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
