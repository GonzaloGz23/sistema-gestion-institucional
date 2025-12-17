<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

// Validar parámetros
$id_espacio = $_GET['id_espacio'] ?? null;
$fecha = $_GET['fecha'] ?? null;

if (!$id_espacio || !$fecha) {
    echo json_encode([
        "success" => false,
        "message" => "Faltan parámetros obligatorios (id_espacio, fecha)"
    ]);
    exit;
}

try {
    $sql = "
        SELECT 
            r.hora_inicio,
            r.hora_fin,
            eq.alias AS equipo
        FROM reservas r
        LEFT JOIN equipos eq ON r.id_equipo = eq.id_equipo
        WHERE 
            r.id_espacio = :id_espacio
            AND r.fecha = :fecha
            AND r.eliminado = 0
        ORDER BY r.hora_inicio ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_espacio' => $id_espacio,
        ':fecha' => $fecha
    ]);

    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $reservas
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener horarios ocupados: " . $e->getMessage()
    ]);
}
