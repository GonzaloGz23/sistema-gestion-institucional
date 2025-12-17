<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

$id = $_POST['id_reserva'] ?? null;
$id_equipo = $_POST['id_equipo'] ?? null;

if (!$id || !$id_equipo) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos para cancelar la reserva.'
    ]);
    exit;
}

try {
    $sqlCheck = "
        SELECT fecha, hora_fin, id_equipo
        FROM reservas
        WHERE id_reserva = :id AND eliminado = 0
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sqlCheck);
    $stmt->execute([':id' => $id]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        echo json_encode([
            'success' => false,
            'message' => 'Reserva no encontrada o ya cancelada.'
        ]);
        exit;
    }

    if ($reserva['id_equipo'] != $id_equipo) {
        echo json_encode([
            'success' => false,
            'message' => 'No tenés permiso para cancelar esta reserva.'
        ]);
        exit;
    }

    $fechaHoraFin = DateTime::createFromFormat('Y-m-d H:i:s', "{$reserva['fecha']} {$reserva['hora_fin']}");
    $ahora = new DateTime();

    if (!$fechaHoraFin) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al interpretar la fecha de la reserva.'
        ]);
        exit;
    }

    if ($fechaHoraFin <= $ahora) {
        echo json_encode([
            'success' => false,
            'message' => 'No se puede cancelar una reserva que ya finalizó.'
        ]);
        exit;
    }

    $sqlUpdate = "UPDATE reservas SET eliminado = 1 WHERE id_reserva = :id";
    $stmt = $pdo->prepare($sqlUpdate);
    $stmt->execute([':id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Reserva cancelada correctamente.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cancelar reserva: ' . $e->getMessage()
    ]);
}
