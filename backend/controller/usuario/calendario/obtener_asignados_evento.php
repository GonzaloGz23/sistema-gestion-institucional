<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

try {
    if (empty($_GET['id_evento'])) {
        throw new Exception("ID de evento no proporcionado.");
    }

    $idEvento = (int) $_GET['id_evento'];

    // Buscar si hay empleados asignados
    $stmt = $pdo->prepare("
        SELECT e.id_empleado, emp.nombre
        FROM eventos_asignaciones e
        INNER JOIN empleados emp ON e.id_empleado = emp.id_empleado
        WHERE e.id_evento = :id_evento AND e.id_empleado IS NOT NULL
    ");
    $stmt->execute([':id_evento' => $idEvento]);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($empleados)) {
        echo json_encode([
            'tipo' => 'empleados',
            'lista' => $empleados
        ]);
        exit;
    }

    // Si no hay empleados, buscar equipos
    $stmt = $pdo->prepare("
        SELECT e.id_equipo, eq.alias
        FROM eventos_asignaciones e
        INNER JOIN equipos eq ON e.id_equipo = eq.id_equipo
        WHERE e.id_evento = :id_evento AND e.id_equipo IS NOT NULL
    ");
    $stmt->execute([':id_evento' => $idEvento]);
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($equipos)) {
        echo json_encode([
            'tipo' => 'equipos',
            'lista' => $equipos
        ]);
        exit;
    }

    // Si no encuentra asignados
    echo json_encode([
        'tipo' => 'ninguno',
        'lista' => []
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
